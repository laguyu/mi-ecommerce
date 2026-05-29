<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseEngine
{
    private static ?bool $supportsFullText = null;

    public static function supportsFullText(): bool
    {
        if (self::$supportsFullText !== null) {
            return self::$supportsFullText;
        }

        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return self::$supportsFullText = false;
        }

        try {
            $versionRow = DB::selectOne('select version() as version');
            $version = is_object($versionRow) ? (string) ($versionRow->version ?? '') : '';

            if (stripos($version, 'TiDB') !== false) {
                return self::$supportsFullText = false;
            }
        } catch (\Throwable) {
            return self::$supportsFullText = false;
        }

        return self::$supportsFullText = true;
    }
}