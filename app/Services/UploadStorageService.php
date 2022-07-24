<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @method static bool exists(string $path)
 * @method static string get(string $path)
 * @method static resource|null readStream(string $path)
 * @method static bool put(string $path, string|resource $contents, mixed $options = [])
 * @method static bool writeStream(string $path, resource $resource, array $options = [])
 * @method static string getVisibility(string $path)
 * @method static bool setVisibility(string $path, string $visibility)
 * @method static bool prepend(string $path, string $data)
 * @method static bool append(string $path, string $data)
 * @method static bool delete(string|array $paths)
 * @method static bool copy(string $from, string $to)
 * @method static bool move(string $from, string $to)
 * @method static int size(string $path)
 * @method static int lastModified(string $path)
 * @method static array files(string|null $directory = null, bool $recursive = false)
 * @method static array allFiles(string|null $directory = null)
 * @method static array directories(string|null $directory = null, bool $recursive = false)
 * @method static array allDirectories(string|null $directory = null)
 * @method static bool makeDirectory(string $path)
 * @method static bool deleteDirectory(string $directory)
 * @method static string url(string $path)
 * @method static string path(string $path)
 *
 * @see \Illuminate\Filesystem\FilesystemManager
 */
class UploadStorageService
{
    public const DISK_NAME = 'uploads';

    protected static function getDisk(): string
    {
        static $disk = null;
        if ($disk === null) {
            $disk = \config('filesystems.default');
        }

        if ($disk === 'local') {
            return self::DISK_NAME;
        }

        return $disk;
    }

    /**
     * Store uploaded file in public uploads disk
     *
     * @return string|false       Path of stored file
     */
    public static function storeUploadedFile(UploadedFile $file, string $path, ?string $name = null): string|false
    {
        if (empty($name)) {
            return $file->store($path, self::getDisk());
        }

        return $file->storeAs($path, $name, self::getDisk());
    }

    /**
     * @param  array<mixed> $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $disk = Storage::disk(self::getDisk());

        return \call_user_func_array([$disk, $method], $arguments);
    }
}
