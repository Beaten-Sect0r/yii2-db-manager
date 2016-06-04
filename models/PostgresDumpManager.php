<?php

/**
 * Created by solly [02.06.16 11:12]
 */

namespace bs\dbManager\models;

use yii\helpers\StringHelper;

class PostgresDumpManager extends BaseDumpManager
{
    /**
     * @param $path
     * @param array $dbInfo
     * @param array $dumpOptions
     * @return string
     */
    public function makeDumpCommand($path, array $dbInfo, array $dumpOptions)
    {
        $arguments = [
            'PGPASSWORD=' . $dbInfo['password'],
            'pg_dump',
            '--host=' . $dbInfo['host'],
            '--port=' . $dbInfo['port'],
            '--username=' . $dbInfo['username'],
            '--no-password',
        ];
        if ($dumpOptions['schemaOnly']) {
            $arguments[] = '--schema-only';
        }
        if ($dumpOptions['preset']) {
            $arguments[] = trim($dumpOptions['presetData']);
        }
        $arguments[] = $dbInfo['dbName'];
        if ($dumpOptions['isArchive']) {
            $arguments[] = '|gzip';
        }
        $arguments[] = '> ' . $path;

        return implode(' ', $arguments);
    }

    /**
     * @param $path
     * @param array $dbInfo
     * @param array $restoreOptions
     * @return string
     */
    public function makeRestoreCommand($path, array $dbInfo, array $restoreOptions)
    {
        $arguments = [];
        if (StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = 'gunzip -c ' . $path;
            $arguments[] = '|';
        }
        $arguments = array_merge($arguments, [
            'PGPASSWORD=' . $dbInfo['password'],
            'psql',
            '--host=' . $dbInfo['host'],
            '--port=' . $dbInfo['port'],
            '--username=' . $dbInfo['username'],
            '--password=' . $dbInfo['password'],
        ]);
        if ($restoreOptions['preset']) {
            $arguments[] = trim($restoreOptions['presetData']);
        }
        $arguments[] = $dbInfo['dbName'];
        if (!StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = '< ' . $path;
        }

        return implode(' ', $arguments);
    }
}
