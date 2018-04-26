<?php

namespace bs\dbManager\models;

use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Class MysqlDumpManager.
 */
class MysqlDumpManager extends BaseDumpManager
{
    /**
     * @param $path
     * @param array $dbInfo
     * @param array $dumpOptions
     * @return mixed
     */
    public function makeDumpCommand($path, array $dbInfo, array $dumpOptions)
    {
        // default port
        if (empty($dbInfo['port'])) {
            $dbInfo['port'] = '3306';
        }
        $arguments = [
            'mysqldump',
            '--host=' . $dbInfo['host'],
            '--port=' . $dbInfo['port'],
            '--user=' . $dbInfo['username'],
            '--password=' . $dbInfo['password'],
        ];
        if ($dumpOptions['schemaOnly']) {
            $arguments[] = '--no-data';
        }
        if ($dumpOptions['preset']) {
            $arguments[] = trim($dumpOptions['presetData']);
        }
        $arguments[] = $dbInfo['dbName'];
        if ($dumpOptions['isArchive']) {
            $arguments[] = '|';
            $arguments[] = 'gzip';
        }
        $arguments[] = '>';
        $arguments[] = $path;

        return implode(' ', $arguments);
    }

    /**
     * @param $path
     * @param array $dbInfo
     * @param array $restoreOptions
     * @return mixed
     */
    public function makeRestoreCommand($path, array $dbInfo, array $restoreOptions)
    {
        $arguments = [];
        if (StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = 'gunzip -c';
            $arguments[] = $path;
            $arguments[] = '|';
        }
        // default port
        if (empty($dbInfo['port'])) {
            $dbInfo['port'] = '3306';
        }
        $arguments = ArrayHelper::merge($arguments, [
            'mysql',
            '--host=' . $dbInfo['host'],
            '--port=' . $dbInfo['port'],
            '--user=' . $dbInfo['username'],
            '--password=' . $dbInfo['password'],
        ]);
        if ($restoreOptions['preset']) {
            $arguments[] = trim($restoreOptions['presetData']);
        }
        $arguments[] = $dbInfo['dbName'];
        if (!StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = '<';
            $arguments[] = $path;
        }

        return implode(' ', $arguments);
    }
}
