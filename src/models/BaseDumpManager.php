<?php

namespace bs\dbManager\models;

use bs\dbManager\contracts\IDumpManager;

/**
 * Class BaseDumpManager.
 */
abstract class BaseDumpManager implements IDumpManager
{
    /**
     * @param $basePath
     * @param array $dbInfo
     * @param array $dumpOptions
     * @return string
     */
    public function makePath($basePath, array $dbInfo, array $dumpOptions)
    {
        return sprintf('%s%s_%s_%s_%s.%s',
            $basePath,
            $dbInfo['dbName'],
            ($dumpOptions['schemaOnly'] ? 'schema' : 'full'),
            ($dumpOptions['preset'] ? $dumpOptions['preset'] : 'default'),
            date('Y-m-d_H-i-s'),
            ($dumpOptions['isArchive'] ? 'sql.gz' : 'sql')
        );
    }

    /**
     * @param $path
     * @param array $dbInfo
     * @param array $dumpOptions
     * @return string
     */
    abstract public function makeDumpCommand($path, array $dbInfo, array $dumpOptions);

    /**
     * @param $path
     * @param array $dbInfo
     * @param array $restoreOptions
     * @return string
     */
    abstract public function makeRestoreCommand($path, array $dbInfo, array $restoreOptions);

    /**
     * Windows or not windows.
     *
     * @return bool
     */
    public static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
