<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ResolvesStoredMediaUrls
{
    protected function resolveStoredMediaUrl(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $value = trim($value);

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $legacyPublicPath = $this->legacyPublicStoragePath($value);

        if ($legacyPublicPath !== null) {
            return $this->mediaUrlForDisk('public', $legacyPublicPath);
        }

        $disk = (string) config('filesystems.default', 'public');

        return $this->mediaUrlForDisk($disk, ltrim($value, '/'));
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
