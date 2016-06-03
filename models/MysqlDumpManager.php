<?php

/**
 * Created by solly [02.06.16 11:11]
 */

namespace bs\dbManager\models;

use yii\helpers\StringHelper;

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
        $arguments = [
            'mysqldump',
            '--host=' . $dbInfo['host'],
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
            $arguments[] = '|gzip';
        }
        $arguments[] = '>' . $path;

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
            $arguments[] = 'gunzip -c ' . $path . ' |';
        }
        $arguments = array_merge($arguments, [
            'mysql',
            '--host=' . $dbInfo['host'],
            '--user=' . $dbInfo['username'],
            '--password=' . $dbInfo['password'],
        ]);
        if ($restoreOptions['preset']) {
            $arguments[] = trim($restoreOptions['presetData']);
        }
        $arguments[] = $dbInfo['dbName'];
        if (!StringHelper::endsWith($path, '.gz', false)) {
            $arguments[] = ' < ' . $path;
        }

        return implode(' ', $arguments);
    }
}
