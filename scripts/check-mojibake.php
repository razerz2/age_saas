<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

$allowlist = [
    'TENANT.md',
    'docs/encoding.md',
    'scripts/check-mojibake.php',
    'tests/Unit/WhatsApp/WhatsAppBotMessageFormatterTest.php',
];

$excludedPrefixes = [
    '.git/',
    'bootstrap/cache/',
    'node_modules/',
    'public/',
    'storage/',
    'vendor/',
];

$scanSuffixes = [
    '.blade.php',
    '.php',
    '.js',
    '.ts',
    '.json',
    '.md',
    '.yml',
    '.yaml',
    '.xml',
    '.txt',
    '.css',
    '.html',
];

$mojibakePatterns = [
    ['label' => 'ÃƒÂ', 'regex' => '/ÃƒÂ/u'],
    ['label' => 'â€™', 'regex' => '/â€™/u'],
    ['label' => 'â€œ', 'regex' => '/â€œ/u'],
    ['label' => 'â€', 'regex' => '/â€/u'],
    ['label' => 'Ã', 'regex' => '/Ã[\x{0080}-\x{00BF}]/u'],
    ['label' => 'Â', 'regex' => '/Â[\x{0080}-\x{00BF}]/u'],
    ['label' => '�', 'regex' => '/�/u'],
];

$issues = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $absolutePath = $fileInfo->getPathname();
    $relativePath = str_replace('\\', '/', substr($absolutePath, strlen($projectRoot) + 1));

    if (str_starts_with($relativePath, '.codex_')) {
        continue;
    }

    if (in_array($relativePath, $allowlist, true)) {
        continue;
    }

    if (startsWithAny($relativePath, $excludedPrefixes)) {
        continue;
    }

    if (!endsWithAny(strtolower($relativePath), $scanSuffixes)) {
        continue;
    }

    $content = file_get_contents($absolutePath);
    if ($content === false) {
        $issues[] = [
            'path' => $relativePath,
            'type' => 'read_error',
            'line' => null,
            'pattern' => null,
            'message' => 'Falha ao ler arquivo.',
        ];
        continue;
    }

    if (!isValidUtf8($content)) {
        $issues[] = [
            'path' => $relativePath,
            'type' => 'invalid_utf8',
            'line' => null,
            'pattern' => null,
            'message' => 'Arquivo com bytes invalidos para UTF-8.',
        ];
    }

    $lines = explode("\n", $content);
    foreach ($lines as $lineIndex => $lineContent) {
        foreach ($mojibakePatterns as $pattern) {
            if (preg_match($pattern['regex'], $lineContent) === 1) {
                $issues[] = [
                    'path' => $relativePath,
                    'type' => 'mojibake_pattern',
                    'line' => $lineIndex + 1,
                    'pattern' => $pattern['label'],
                    'message' => 'Padrao suspeito de mojibake encontrado.',
                ];
            }
        }
    }
}

if ($issues !== []) {
    fwrite(STDERR, "Problemas de encoding/mojibake detectados:\n\n");

    foreach ($issues as $issue) {
        $lineInfo = $issue['line'] !== null ? ':' . $issue['line'] : '';
        $patternInfo = $issue['pattern'] !== null ? " [{$issue['pattern']}]" : '';
        fwrite(
            STDERR,
            "- {$issue['path']}{$lineInfo} ({$issue['type']}){$patternInfo} {$issue['message']}\n"
        );
    }

    fwrite(STDERR, "\nTotal de ocorrencias: " . count($issues) . "\n");
    exit(1);
}

fwrite(STDOUT, "OK: nenhum problema de encoding/mojibake detectado.\n");
exit(0);

function startsWithAny(string $value, array $prefixes): bool
{
    foreach ($prefixes as $prefix) {
        if (str_starts_with($value, $prefix)) {
            return true;
        }
    }

    return false;
}

function endsWithAny(string $value, array $suffixes): bool
{
    foreach ($suffixes as $suffix) {
        if (str_ends_with($value, $suffix)) {
            return true;
        }
    }

    return false;
}

function isValidUtf8(string $content): bool
{
    return preg_match('//u', $content) === 1;
}
