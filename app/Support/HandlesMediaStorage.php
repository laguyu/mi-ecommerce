<?php

namespace App\Support;

use RuntimeException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesMediaStorage
{
    protected function mediaDisk(): string
    {
        return (string) config('filesystems.default', 'public');
    }

    protected function storeMediaFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, $this->mediaDisk());

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('No se pudo guardar el archivo en el disco '.$this->mediaDisk().'.');
        }

        return $path;
    }

    protected function mediaUrl(string $path): string
    {
        return $this->mediaUrlForDisk($this->mediaDisk(), $path);
    }

    protected function deleteMediaByUrl(?string $url): void
    {
        if (! is_string($url) || trim($url) === '') {
            return;
        }

        $disk = $this->mediaDisk();
        $path = $this->storagePathFromDiskUrl($url, $disk);

        if ($path !== null) {
            Storage::disk($disk)->delete($path);
        }

        if ($disk !== 'public') {
            $legacyPublicPath = $this->storagePathFromDiskUrl($url, 'public');

            if ($legacyPublicPath !== null) {
                Storage::disk('public')->delete($legacyPublicPath);
            }
        }
    }

    private function storagePathFromDiskUrl(string $url, string $disk): ?string
    {
        $normalized = trim($url);

        if ($normalized === '') {
            return null;
        }

        if ($disk === 'public') {
            $legacyPublicPath = $this->legacyPublicStoragePath($normalized);

            if ($legacyPublicPath !== null) {
                return $legacyPublicPath;
            }
        }

        $diskBaseUrl = rtrim($this->mediaUrlForDisk($disk, ''), '/');

        if ($diskBaseUrl !== '' && Str::startsWith($normalized, $diskBaseUrl.'/')) {
            return ltrim((string) Str::after($normalized, $diskBaseUrl.'/'), '/');
        }

        if (! Str::startsWith($normalized, ['http://', 'https://'])) {
            return ltrim($normalized, '/');
        }

        return null;
    }

    private function legacyPublicStoragePath(string $value): ?string
    {
        $trimmed = ltrim($value, '/');

        if (! Str::startsWith($trimmed, 'storage/')) {
            return null;
        }

        return ltrim((string) Str::after($trimmed, 'storage/'), '/');
    }

    private function mediaUrlForDisk(string $disk, string $path): string
    {
        $adapter = Storage::disk($disk);

        if (is_object($adapter) && method_exists($adapter, 'url')) {
            /** @var mixed $adapter */
            return (string) $adapter->url($path);
        }

        return Storage::url($path);
    }
}
