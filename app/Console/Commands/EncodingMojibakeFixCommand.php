<?php

namespace App\Console\Commands;

use App\Support\Encoding\MojibakeFixer;
use Illuminate\Console\Command;

class EncodingMojibakeFixCommand extends Command
{
    protected $signature = 'encoding:mojibake-fix
        {--path= : Caminho especifico para corrigir}
        {--write : Gravar alteracoes}
        {--backup : Criar backup antes de alterar}
        {--report= : Caminho para salvar relatorio JSON}
        {--max-size=2097152 : Tamanho maximo de arquivo em bytes}';

    protected $description = 'Corrige textos quebrados por mojibake em arquivos do projeto.';

    public function handle(MojibakeFixer $fixer): int
    {
        $path = $this->resolvePath($this->option('path') ?: base_path());
        $write = (bool) $this->option('write');

        if (! file_exists($path)) {
            $this->error("Caminho nao encontrado: {$path}");

            return self::FAILURE;
        }

        $scan = $fixer->scan($path, [
            'max_size' => (int) $this->option('max-size'),
        ]);

        $report = [
            'mode' => $write ? 'write' : 'dry-run',
            'summary' => array_merge($scan['summary'], [
                'files_fixable' => 0,
                'files_fixed' => 0,
                'backups_created' => 0,
                'errors' => 0,
            ]),
            'files' => [],
            'ignored_files' => $scan['ignored_files'],
        ];

        $this->line('Mojibake fix');
        $this->line('Modo: '.($write ? 'write' : 'dry-run'));
        $this->line('Base: '.$path);
        $this->newLine();

        foreach ($scan['files'] as $file) {
            $result = $fixer->fixFile($file['path'], [
                'write' => $write,
                'backup' => (bool) $this->option('backup'),
                'max_size' => (int) $this->option('max-size'),
            ]);

            $result['patterns'] = $file['patterns'];
            $report['files'][] = $result;

            if (($result['changed'] ?? false) === true) {
                $report['summary']['files_fixable']++;
            }

            if ($result['status'] === 'fixed') {
                $report['summary']['files_fixed']++;
                $this->info('Alterado: '.$this->relativePath($result['path']));
            } elseif ($result['status'] === 'dry-run') {
                $this->warn('Alteraria: '.$this->relativePath($result['path']));
            } elseif ($result['status'] === 'error') {
                $report['summary']['errors']++;
                $this->error('Erro: '.$this->relativePath($result['path']).' - '.$result['reason']);
            } elseif ($this->isVerboseRequested()) {
                $this->line('Pulado: '.$this->relativePath($result['path']).' - '.$result['reason']);
            }

            if (! empty($result['backup_path'])) {
                $report['summary']['backups_created']++;
            }

            if ($this->isVerboseRequested()) {
                $this->line("  Score: {$result['score_before']} -> {$result['score_after']}");
            }
        }

        if (count($scan['files']) === 0) {
            $this->info('Nenhum mojibake suspeito encontrado.');
        }

        $this->newLine();
        $this->printSummary($report['summary'], $report['mode']);

        if ($this->option('report')) {
            $this->writeReport($this->resolvePath($this->option('report')), $report);
        }

        return $report['summary']['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function printSummary(array $summary, string $mode): void
    {
        $this->line('Resumo:');
        $this->line('  Modo: '.$mode);
        $this->line('  Arquivos analisados: '.$summary['files_analyzed']);
        $this->line('  Arquivos ignorados: '.$summary['files_ignored']);
        $this->line('  Arquivos suspeitos: '.$summary['files_suspicious']);
        $this->line('  Arquivos corrigiveis: '.$summary['files_fixable']);
        $this->line('  Arquivos corrigidos: '.$summary['files_fixed']);
        $this->line('  Backups criados: '.$summary['backups_created']);
        $this->line('  Erros: '.$summary['errors']);
    }

    private function writeReport(string $path, array $report): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->info('Relatorio salvo em: '.$path);
    }

    private function resolvePath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        }

        return base_path($path);
    }

    private function relativePath(string $path): string
    {
        $basePath = rtrim(base_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($path, $basePath) ? substr($path, strlen($basePath)) : $path;
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, '/') || str_starts_with($path, '\\');
    }

    private function isVerboseRequested(): bool
    {
        return $this->getOutput()->isVerbose();
    }
}
