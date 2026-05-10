<?php

namespace App\Support\Encoding;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class MojibakeFixer
{
    private const DEFAULT_MAX_SIZE = 2097152;

    private ?string $currentFilePath = null;

    private const ALLOWED_EXTENSIONS = [
        'php',
        'js',
        'ts',
        'vue',
        'css',
        'scss',
        'json',
        'md',
        'txt',
        'yml',
        'yaml',
        'xml',
        'html',
    ];

    private const IGNORED_DIRECTORIES = [
        'vendor',
        'node_modules',
        'storage/framework',
        'storage/logs',
        'storage/app/public',
        'storage/app/encoding-backups',
        'bootstrap/cache',
        'public/build',
        'public/hot',
        'public/vendor',
        '.git',
        'coverage',
        '.phpunit.cache',
    ];

    private const IGNORED_FILES = [
        'composer.lock',
        'package-lock.json',
        'yarn.lock',
        'pnpm-lock.yaml',
    ];

    private const IGNORED_RELATIVE_FILES = [
        'docs/encoding.md',
        'docs/encoding-mojibake.md',
        'scripts/check-mojibake.php',
        'scripts/fix-mojibake.php',
    ];

    private const BINARY_EXTENSIONS = [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'webp',
        'ico',
        'svgz',
        'pdf',
        'zip',
        'gz',
        'rar',
        '7z',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'mp3',
        'mp4',
        'mov',
        'avi',
        'xlsx',
        'docx',
    ];

    public function scan(string $basePath, array $options = []): array
    {
        $maxSize = (int) ($options['max_size'] ?? self::DEFAULT_MAX_SIZE);
        $basePath = $this->normalizePath($basePath);
        $files = [];
        $ignoredFiles = [];
        $summary = [
            'base_path' => $basePath,
            'max_size' => $maxSize,
            'files_analyzed' => 0,
            'files_ignored' => 0,
            'files_suspicious' => 0,
            'total_occurrences' => 0,
        ];

        foreach ($this->getFiles($basePath) as $file) {
            $filePath = $this->normalizePath($file->getPathname());
            $ignoredReason = $this->getIgnoredReason($filePath, $maxSize);

            if ($ignoredReason !== null) {
                $summary['files_ignored']++;
                $ignoredFiles[] = [
                    'path' => $filePath,
                    'reason' => $ignoredReason,
                ];
                continue;
            }

            $summary['files_analyzed']++;
            $result = $this->detectInFile($filePath);

            if ($result['suspicious']) {
                $summary['files_suspicious']++;
                $summary['total_occurrences'] += $result['occurrences'];
                $files[] = $result;
            }
        }

        return [
            'summary' => $summary,
            'files' => $files,
            'ignored_files' => $ignoredFiles,
        ];
    }

    public function detectInFile(string $filePath): array
    {
        $filePath = $this->normalizePath($filePath);

        if (! is_readable($filePath)) {
            return [
                'path' => $filePath,
                'suspicious' => false,
                'occurrences' => 0,
                'score' => 0,
                'patterns' => [],
                'error' => 'File is not readable.',
            ];
        }

        $contents = (string) file_get_contents($filePath);
        $this->currentFilePath = $filePath;
        $patterns = $this->detectPatterns($contents);
        $this->currentFilePath = null;
        $occurrences = array_sum(array_column($patterns, 'count'));

        return [
            'path' => $filePath,
            'suspicious' => $occurrences > 0,
            'occurrences' => $occurrences,
            'score' => $this->suspiciousScore($contents),
            'patterns' => $patterns,
        ];
    }

    public function fixFile(string $filePath, array $options = []): array
    {
        $filePath = $this->normalizePath($filePath);
        $write = (bool) ($options['write'] ?? false);
        $backup = (bool) ($options['backup'] ?? false);
        $maxSize = (int) ($options['max_size'] ?? self::DEFAULT_MAX_SIZE);

        $ignoredReason = $this->getIgnoredReason($filePath, $maxSize);
        if ($ignoredReason !== null) {
            return [
                'path' => $filePath,
                'status' => 'ignored',
                'reason' => $ignoredReason,
                'changed' => false,
            ];
        }

        if (! is_readable($filePath)) {
            return [
                'path' => $filePath,
                'status' => 'error',
                'reason' => 'File is not readable.',
                'changed' => false,
            ];
        }

        $contents = (string) file_get_contents($filePath);
        $this->currentFilePath = $filePath;
        $fixed = $this->fixContents($contents);
        $this->currentFilePath = null;

        $result = [
            'path' => $filePath,
            'status' => $fixed['status'],
            'reason' => $fixed['reason'],
            'changed' => $fixed['changed'],
            'safe' => $fixed['safe'],
            'score_before' => $fixed['score_before'],
            'score_after' => $fixed['score_after'],
            'backup_path' => null,
        ];

        if (! $fixed['changed'] || ! $fixed['safe']) {
            return $result;
        }

        if (! $write) {
            $result['status'] = 'dry-run';

            return $result;
        }

        try {
            if ($backup) {
                $result['backup_path'] = $this->makeBackup($filePath);
            }

            file_put_contents($filePath, $fixed['contents'], LOCK_EX);
            $result['status'] = 'fixed';
        } catch (Throwable $exception) {
            $result['status'] = 'error';
            $result['reason'] = $exception->getMessage();
        }

        return $result;
    }

    public function fixContents(string $contents): array
    {
        $contents = $this->ensureUtf8NoBom($contents);
        $beforeScore = $this->suspiciousScore($contents);

        $fixed = strtr($contents, $this->replacementMap());
        $badPrefix = preg_quote($this->badBytes('C382'), '/');
        $fixed = preg_replace('/'.$badPrefix.'(?=[\s.,;:!?()\[\]{}"\'\/\\\\-])/u', '', $fixed) ?? $fixed;
        $fixed = $this->ensureUtf8NoBom($fixed);
        $afterScore = $this->suspiciousScore($fixed);
        $changed = $fixed !== $contents;

        $safe = true;
        $reason = $changed ? 'Correction improves suspicious score.' : 'No changes needed.';

        if (! $changed) {
            $safe = false;
        } elseif (! $this->isValidUtf8($fixed)) {
            $safe = false;
            $reason = 'Correction would produce invalid UTF-8.';
        } elseif (str_contains($fixed, $this->badBytes('EFBFBD'))) {
            $safe = false;
            $reason = 'Correction would contain replacement characters.';
        } elseif ($afterScore >= $beforeScore) {
            $safe = false;
            $reason = 'Correction did not reduce suspicious score.';
        } elseif ($this->hasDrasticSizeChange($contents, $fixed)) {
            $safe = false;
            $reason = 'Correction changed file size too much.';
        }

        if (! $safe) {
            $fixed = $contents;
        }

        return [
            'status' => $safe ? 'fixable' : 'skipped',
            'reason' => $reason,
            'changed' => $changed && $safe,
            'safe' => $safe,
            'contents' => $fixed,
            'score_before' => $beforeScore,
            'score_after' => $afterScore,
        ];
    }

    public function detectPatterns(string $contents): array
    {
        $patterns = [];
        $needles = array_values(array_unique(array_merge($this->suspiciousPatterns(), array_keys($this->replacementMap()))));

        foreach ($needles as $needle) {
            $count = $this->countRelevantOccurrences($contents, $needle);
            if ($count === 0) {
                continue;
            }

            $patterns[] = [
                'pattern' => $needle,
                'count' => $count,
                'snippet' => $this->getRelevantSnippet($contents, $needle),
            ];
        }

        usort($patterns, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return $patterns;
    }

    public function isAllowedFile(string $filePath): bool
    {
        $baseName = strtolower(basename($filePath));

        if (in_array($baseName, self::IGNORED_FILES, true)) {
            return false;
        }

        if (str_ends_with($baseName, '.blade.php')) {
            return true;
        }

        if ($baseName === '.env.example' || str_ends_with($baseName, '.env.example')) {
            return true;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    public function isIgnoredPath(string $filePath): bool
    {
        $path = '/'.trim(str_replace('\\', '/', strtolower($filePath)), '/').'/';

        foreach (self::IGNORED_DIRECTORIES as $directory) {
            $directory = '/'.trim(strtolower($directory), '/').'/';

            if (str_contains($path, $directory)) {
                return true;
            }
        }

        return false;
    }

    public function isBinaryFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($extension, self::BINARY_EXTENSIONS, true)) {
            return true;
        }

        if (! is_readable($filePath)) {
            return false;
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return false;
        }

        $chunk = fread($handle, 8192);
        fclose($handle);

        return $chunk !== false && str_contains($chunk, "\0");
    }

    public function ensureUtf8NoBom(string $contents): string
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            $contents = substr($contents, 3);
        }

        if ($this->isValidUtf8($contents)) {
            return $contents;
        }

        $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $contents);

        return $cleaned === false ? $contents : $cleaned;
    }

    public function makeBackup(string $filePath): ?string
    {
        $backupRoot = storage_path('app/encoding-backups/'.date('Ymd-His'));
        if (! is_dir($backupRoot)) {
            mkdir($backupRoot, 0775, true);
        }

        $relativePath = $this->relativePath($filePath, $this->projectBasePath());
        $backupPath = $backupRoot.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $backupDirectory = dirname($backupPath);

        if (! is_dir($backupDirectory)) {
            mkdir($backupDirectory, 0775, true);
        }

        copy($filePath, $backupPath);

        return $backupPath;
    }

    public function getSnippet(string $contents, string $needle): string
    {
        $position = function_exists('mb_strpos') ? mb_strpos($contents, $needle) : strpos($contents, $needle);
        if ($position === false) {
            return '';
        }

        $start = max(0, $position - 40);
        $snippet = function_exists('mb_substr')
            ? mb_substr($contents, $start, 100)
            : substr($contents, $start, 100);

        $snippet = preg_replace('/\s+/u', ' ', $snippet) ?? $snippet;

        return trim($snippet);
    }

    public function suspiciousScore(string $contents): int
    {
        $score = 0;

        foreach ($this->suspiciousPatterns() as $pattern) {
            $score += $this->countRelevantOccurrences($contents, $pattern) * 3;
        }

        foreach ($this->replacementMap() as $bad => $good) {
            $score += $this->countRelevantOccurrences($contents, $bad) * 5;
        }

        return $score;
    }

    private function getRelevantSnippet(string $contents, string $needle): string
    {
        $position = $this->firstRelevantPosition($contents, $needle);

        if ($position === false) {
            return '';
        }

        $start = max(0, $position - 40);
        $snippet = function_exists('mb_substr')
            ? mb_substr($contents, $start, 100)
            : substr($contents, $start, 100);

        $snippet = preg_replace('/\s+/u', ' ', $snippet) ?? $snippet;

        return trim($snippet);
    }

    private function countRelevantOccurrences(string $contents, string $needle): int
    {
        if ($needle === '') {
            return 0;
        }

        $count = 0;
        $offset = 0;

        while (($position = strpos($contents, $needle, $offset)) !== false) {
            if (! $this->isIgnoredOccurrence($contents, $needle, $position)) {
                $count++;
            }

            $offset = $position + strlen($needle);
        }

        return $count;
    }

    private function firstRelevantPosition(string $contents, string $needle): int|false
    {
        if ($needle === '') {
            return false;
        }

        $offset = 0;

        while (($position = strpos($contents, $needle, $offset)) !== false) {
            if (! $this->isIgnoredOccurrence($contents, $needle, $position)) {
                return $position;
            }

            $offset = $position + strlen($needle);
        }

        return false;
    }

    private function isIgnoredOccurrence(string $contents, string $needle, int $position): bool
    {
        if ($needle !== $this->badBytes('EFBFBD')) {
            return false;
        }

        if (! $this->isDocumentationOrTestContext()) {
            return false;
        }

        $line = $this->lineAtPosition($contents, $position);
        $replacement = $this->badBytes('EFBFBD');

        foreach ([
            "not->toContain('{$replacement}')",
            "not->toContain(\"{$replacement}\")",
            "not()->toContain('{$replacement}')",
            "not()->toContain(\"{$replacement}\")",
            "assertStringNotContainsString('{$replacement}'",
            "assertStringNotContainsString(\"{$replacement}\"",
        ] as $intentionalTestPattern) {
            if (str_contains($line, $intentionalTestPattern)) {
                return true;
            }
        }

        $normalizedLine = function_exists('mb_strtolower')
            ? mb_strtolower($line, 'UTF-8')
            : strtolower($line);

        $hasExampleMarker = str_contains($normalizedLine, 'ex.:')
            || str_contains($normalizedLine, 'exemplo:')
            || str_contains($normalizedLine, 'exemplo de mojibake');

        $hasBrokenCharacterContext = str_contains($normalizedLine, 'caracteres quebrados')
            || str_contains($normalizedLine, 'evitar caracteres quebrados')
            || str_contains($normalizedLine, 'acentos aparecem');

        $hasInlineReplacementExample = str_contains($line, '`'.$replacement.'`');

        $hasEncodingContext = str_contains($normalizedLine, 'mojibake')
            || str_contains($normalizedLine, 'encoding')
            || str_contains($normalizedLine, 'utf-8')
            || str_contains($normalizedLine, 'codificacao')
            || str_contains($normalizedLine, 'codificação')
            || str_contains($normalizedLine, 'caracteres quebrados');

        return $hasBrokenCharacterContext
            || ($hasInlineReplacementExample && ($hasExampleMarker || $hasBrokenCharacterContext))
            || ($hasExampleMarker && $hasEncodingContext);
    }

    private function isDocumentationOrTestContext(): bool
    {
        return $this->isDocumentationContext() || $this->isTestContext();
    }

    private function isDocumentationContext(): bool
    {
        $relativePath = $this->currentRelativePath();

        return str_starts_with($relativePath, 'docs/')
            || str_ends_with($relativePath, '.md')
            || str_ends_with($relativePath, '.txt');
    }

    private function isTestContext(): bool
    {
        $relativePath = $this->currentRelativePath();

        return str_starts_with($relativePath, 'tests/')
            || str_ends_with($relativePath, 'test.php');
    }

    private function currentRelativePath(): string
    {
        if ($this->currentFilePath === null) {
            return '';
        }

        return str_replace('\\', '/', strtolower($this->relativePath($this->currentFilePath, $this->projectBasePath())));
    }

    private function lineAtPosition(string $contents, int $position): string
    {
        $lineStart = strrpos(substr($contents, 0, $position), "\n");
        $lineStart = $lineStart === false ? 0 : $lineStart + 1;
        $lineEnd = strpos($contents, "\n", $position);
        $lineEnd = $lineEnd === false ? strlen($contents) : $lineEnd;

        return substr($contents, $lineStart, $lineEnd - $lineStart);
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function getFiles(string $basePath): iterable
    {
        if (is_file($basePath)) {
            yield new SplFileInfo($basePath);

            return;
        }

        if (! is_dir($basePath)) {
            return;
        }

        $directory = new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS);
        $filter = new RecursiveCallbackFilterIterator(
            $directory,
            fn (SplFileInfo $current): bool => ! ($current->isDir() && $this->isIgnoredPath($current->getPathname()))
        );

        $iterator = new RecursiveIteratorIterator($filter);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                yield $file;
            }
        }
    }

    private function getIgnoredReason(string $filePath, int $maxSize): ?string
    {
        if ($this->isIgnoredPath($filePath)) {
            return 'ignored_path';
        }

        if ($this->isIgnoredRelativeFile($filePath)) {
            return 'ignored_file';
        }

        if ($this->isIgnoredGeneratedReport($filePath)) {
            return 'generated_report';
        }

        if (! $this->isAllowedFile($filePath)) {
            return 'unsupported_file';
        }

        if ($this->isBinaryFile($filePath)) {
            return 'binary_file';
        }

        $size = is_file($filePath) ? filesize($filePath) : false;
        if ($size !== false && $size > $maxSize) {
            return 'file_too_large';
        }

        return null;
    }

    private function isIgnoredRelativeFile(string $filePath): bool
    {
        $relativePath = str_replace('\\', '/', strtolower($this->relativePath($filePath, $this->projectBasePath())));

        return in_array($relativePath, self::IGNORED_RELATIVE_FILES, true);
    }

    private function isIgnoredGeneratedReport(string $filePath): bool
    {
        $relativePath = str_replace('\\', '/', strtolower($this->relativePath($filePath, $this->projectBasePath())));
        $baseName = basename($relativePath);

        return str_starts_with($relativePath, 'storage/app/')
            && str_starts_with($baseName, 'mojibake-')
            && str_ends_with($baseName, '.json');
    }

    /**
     * @return array<string, string>
     */
    private function replacementMap(): array
    {
        $map = [
            $this->badBytes('C383C3A0') => $this->unicode('00E0'),
            $this->badBytes('C38220') => ' ',
        ];

        foreach ($this->replacementCharacters() as $character) {
            $mojibake = $this->toMojibake($character);
            if ($mojibake !== $character) {
                $map[$mojibake] = $character;
            }
        }

        foreach ($this->legacyUppercaseMap() as $bad => $good) {
            $map[$bad] = $good;
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    private function suspiciousPatterns(): array
    {
        $patterns = [
            $this->badBytes('EFBFBD'),
            $this->toMojibake('Não'),
            'N'.$this->toMojibake($this->unicode('00C3')).'O',
            'N'.$this->toMojibake($this->unicode('00E3')),
            'N'.$this->toMojibake($this->unicode('00E1')),
            'N'.$this->toMojibake($this->unicode('00E9')),
            'N'.$this->toMojibake($this->unicode('00ED')),
            $this->toMojibake('Você'),
            $this->toMojibake('méd'),
            $this->toMojibake('míd'),
            $this->toMojibake('público'),
            $this->toMojibake('página'),
            $this->toMojibake('saúde'),
            $this->toMojibake('opç'),
            $this->toMojibake('botão'),
            $this->badBytes('636F6E666967757261C383'),
            $this->badBytes('6E6F746966696361C383'),
            $this->badBytes('7065726D697373C383'),
            $this->badBytes('63616C656E64C383'),
            $this->badBytes('686F72C383'),
            $this->badBytes('757375C383'),
            $this->badBytes('646573637269C383'),
            $this->badBytes('696E7465677261C383'),
            $this->badBytes('73696E63726F6E697A61C383'),
            $this->badBytes('6174656EC383'),
            $this->badBytes('6167656E64616D656E746F20636F6E636C75C383'),
        ];

        foreach ($this->replacementCharacters() as $character) {
            $patterns[] = $this->toMojibake($character);
        }

        return array_values(array_filter(array_unique($patterns), fn (string $pattern): bool => $pattern !== ''));
    }

    /**
     * @return array<int, string>
     */
    private function replacementCharacters(): array
    {
        return array_map(
            fn (string $codepoint): string => $this->unicode($codepoint),
            [
                '00E1',
                '00E0',
                '00E2',
                '00E3',
                '00E4',
                '00E9',
                '00EA',
                '00EB',
                '00ED',
                '00EE',
                '00EF',
                '00F3',
                '00F4',
                '00F5',
                '00F6',
                '00FA',
                '00FC',
                '00E7',
                '00C1',
                '00C0',
                '00C2',
                '00C3',
                '00C9',
                '00CA',
                '00CD',
                '00D3',
                '00D4',
                '00D5',
                '00DA',
                '00C7',
                '00BA',
                '00AA',
                '00B7',
                '00A9',
                '00AE',
                '00AB',
                '00BB',
                '2013',
                '2014',
                '2018',
                '2019',
                '201C',
                '201D',
                '2026',
                '2022',
                '2122',
                '20AC',
            ]
        );
    }

    /**
     * @return array<string, string>
     */
    private function legacyUppercaseMap(): array
    {
        $prefix = $this->badBytes('C383');
        $map = [];

        foreach ([
            '00C1',
            '00C0',
            '00C2',
            '00C3',
            '00C9',
            '00CA',
            '00CD',
            '00D3',
            '00D4',
            '00D5',
            '00DA',
            '00C7',
        ] as $codepoint) {
            $map[$prefix.$this->unicode($codepoint)] = $this->unicode($codepoint);
        }

        return $map;
    }

    private function toMojibake(string $character): string
    {
        if (! function_exists('mb_convert_encoding')) {
            return $character;
        }

        $converted = @mb_convert_encoding($character, 'UTF-8', 'Windows-1252');

        return is_string($converted) ? $converted : $character;
    }

    private function badBytes(string $hex): string
    {
        $bytes = hex2bin($hex);

        return is_string($bytes) ? $bytes : '';
    }

    private function unicode(string $codepoint): string
    {
        return html_entity_decode('&#x'.$codepoint.';', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function isValidUtf8(string $contents): bool
    {
        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($contents, 'UTF-8');
        }

        return preg_match('//u', $contents) === 1;
    }

    private function hasDrasticSizeChange(string $before, string $after): bool
    {
        $beforeLength = strlen($before);
        if ($beforeLength === 0) {
            return false;
        }

        $difference = abs(strlen($after) - $beforeLength);

        return $difference > 4096 && $difference / $beforeLength > 0.2;
    }

    private function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    private function projectBasePath(): string
    {
        return function_exists('base_path') ? base_path() : (string) getcwd();
    }

    private function relativePath(string $path, string $basePath): string
    {
        $path = $this->normalizePath($path);
        $basePath = rtrim($this->normalizePath($basePath), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (str_starts_with($path, $basePath)) {
            return substr($path, strlen($basePath));
        }

        return basename($path);
    }
}
