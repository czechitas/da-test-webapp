<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

class LogViewer
{
    private string $file;

    private string $folder;

    private string $storage_path;

    /** @var array<string, string> */
    private array $levels_classes = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
        'processed' => 'info',
        'failed' => 'warning',
    ];

    /** @var array<string, string> */
    private array $levels_imgs = [
        'debug' => 'info-circle',
        'info' => 'info-circle',
        'notice' => 'info-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'exclamation-triangle',
        'critical' => 'exclamation-triangle',
        'alert' => 'exclamation-triangle',
        'emergency' => 'exclamation-triangle',
        'processed' => 'info-circle',
        'failed' => 'exclamation-triangle',
    ];

    /**
     * Why? Uh... Sorry
     */
    private const MAX_FILE_SIZE = 52428800;

    public function __construct()
    {
        $this->storage_path = \storage_path('logs');
    }

    public function setFolder(string $folder): void
    {
        if (\app('files')->exists($folder)) {
            $this->folder = $folder;
        }

        if ($this->storage_path) {
            $logsPath = $this->storage_path . '/' . $folder;
            if (\app('files')->exists($logsPath)) {
                $this->folder = $folder;
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function setFile(string $file): void
    {
        $file = $this->pathToLogFile($file);

        if (\app('files')->exists($file)) {
            $this->file = $file;
        }
    }

    public function pathToLogFile(string $file): string
    {
        // try the absolute path
        if (\app('files')->exists($file)) {
            return $file;
        }

        $logsPath = $this->storage_path;
        $logsPath .= $this->folder ? '/' . $this->folder : '';
        $file = $logsPath . '/' . $file;
        // check if requested file is really in the logs directory
        if (\dirname($file) !== $logsPath) {
            throw new \Exception('No such log file');
        }

        return $file;
    }

    public function getFolderName(): ?string
    {
        return $this->folder;
    }

    public function getFileName(): string
    {
        if (empty($this->file)) {
            return '';
        }

        return \basename($this->file);
    }

    /**
     * @return array<array<string, mixed>>|null
     */
    public function all(): ?array
    {
        $log = [];

        if (!$this->file) {
            $log_file = !$this->folder ? $this->getFiles() : $this->getFolderFiles();
            if (!\count($log_file)) {
                return [];
            }
            $this->file = $log_file[0];
        }

        if (\app('files')->size($this->file) > self::MAX_FILE_SIZE) {
            return null;
        }

        $file = \app('files')->get($this->file);

        $log_data = \preg_split(
            '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})(?:[\+-]\d{4})?\] (\w+)\.(\w+): (.*)/',
            $file,
            -1,
            \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY,
        );

        $cntLogData = \count($log_data);
        for ($i = 0; $i < $cntLogData; $i += 5) {
            $level = \strtolower($log_data[$i + 2]);
            $log[] = [
                'context' => $log_data[$i + 1] ?? 'prod',
                'level' => $level,
                'folder' => $this->folder,
                'level_class' => $this->levelCssClass($level),
                'level_img' => $this->levelImg($level),
                'date' => $log_data[$i],
                'text' => \trim($log_data[$i + 3] ?? ''),
                'in_file' => null,
                'stack' => \preg_replace('/\-{4,}\n/', '', \trim($log_data[$i + 4] ?? '')),
            ];
        }

        return \array_reverse($log);
    }

    /**
     * @return array<string>
     */
    public function getFolders(): array
    {
        $folders = \glob($this->storage_path . '/*', \GLOB_ONLYDIR);

        if (\is_array($folders)) {
            $folders = \array_map('basename', $folders);
        }

        return \array_values($folders);
    }

    /**
     * @return array<string>
     */
    public function getFolderFiles(bool $basename = false): array
    {
        return $this->getFiles($basename, $this->folder);
    }

    /**
     * @return array<string>
     */
    public function getFiles(bool $basename = false, string $folder = ''): array
    {
        $pattern = '*.log';
        $files = \glob($this->storage_path . '/' . $folder . '/' . $pattern);

        $files = \array_reverse($files);
        $files = \array_filter($files, 'is_file');
        if ($basename && \is_array($files)) {
            $files = \array_map('basename', $files);
        }

        return \array_values($files);
    }

    public function levelImg(string $level): string
    {
        return $this->levels_imgs[$level] ?? 'debug';
    }

    public function levelCssClass(string $level): string
    {
        return $this->levels_classes[$level] ?? 'debug';
    }
}
