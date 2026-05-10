<?php

namespace App\Console\Commands;

use App\Support\Encoding\MojibakeFixer;
use Illuminate\Console\Command;

class EncodingMojibakeCheckCommand extends Command
{
    protected $signature = 'encoding:mojibake-check
        {--path= : Caminho especifico para analisar}
        {--report= : Caminho para salvar relatorio JSON}
        {--max-size=2097152 : Tamanho maximo de arquivo em bytes}';

    protected $description = 'Detecta possiveis textos quebrados por mojibake no projeto.';

    public function handle(MojibakeFixer $fixer): int
    {
        $path = $this->resolvePath($this->option('path') ?: base_path());

        if (! file_exists($path)) {
            $this->error("Caminho nao encontrado: {$path}");

            return self::FAILURE;
        }

        $report = $fixer->scan($path, [
            'max_size' => (int) $this->option('max-size'),
        ]);

        $this->line('Mojibake check');
        $this->line('Base: '.$path);
        $this->newLine();

        foreach ($report['files'] as $file) {
            $this->warn($this->relativePath($file['path'])." ({$file['occurrences']} ocorrencias, score {$file['score']})");

            foreach ($file['patterns'] as $pattern) {
                $this->line("  - {$pattern['pattern']}: {$pattern['count']}");

                if ($this->isVerboseRequested() && $pattern['snippet'] !== '') {
                    $this->line("    {$pattern['snippet']}");
                }
            }
        }

        if (count($report['files']) === 0) {
            $this->info('Nenhum mojibake suspeito encontrado.');
        }

        $this->newLine();
        $this->printSummary($report['summary']);

        if ($this->option('report')) {
            $this->writeReport($this->resolvePath($this->option('report')), $report);
        }

        return $report['summary']['files_suspicious'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function printSummary(array $summary): void
    {
        $this->line('Resumo:');
        $this->line('  Arquivos analisados: '.$summary['files_analyzed']);
        $this->line('  Arquivos ignorados: '.$summary['files_ignored']);
        $this->line('  Arquivos suspeitos: '.$summary['files_suspicious']);
        $this->line('  Total de ocorrencias: '.$summary['total_occurrences']);
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
