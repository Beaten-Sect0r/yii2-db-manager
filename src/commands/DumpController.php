<?php

namespace bs\dbManager\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use bs\dbManager\models\Dump;
use PDO;
use PDOException;
use Symfony\Component\Process\Process;

/**
 * Database backup manager.
 */
class DumpController extends Controller
{
    public $defaultAction = 'create';

    /**
     * Create database dump.
     *
     * @param string $db and $isArchive.
     */
    public function actionCreate($db, $isArchive = false)
    {
        $module = Yii::$app->getModule('db-manager');
        $model = new Dump($module->dbList, $module->customDumpOptions);
        if (ArrayHelper::isIn($db, $module->dbList)) {
            $dbInfo = $module->getDbInfo($db);
            $dumpOptions = $model->makeDumpOptions();
            if ($isArchive == 'gzip') {
                $dumpOptions['isArchive'] = true;
            }
            $manager = $module->createManager($dbInfo);
            $dumpPath = $manager->makePath($module->path, $dbInfo, $dumpOptions);
            $dumpCommand = $manager->makeDumpCommand($dumpPath, $dbInfo, $dumpOptions);
            Yii::trace(compact('dumpCommand', 'dumpPath', 'dumpOptions'), get_called_class());
            $process = new Process($dumpCommand);
            $process->run();
            if ($process->isSuccessful()) {
                Console::output('Dump successfully created.');
            } else {
                Console::output('Dump failed.');
            }
        } else {
            Console::output('Database configuration not found.');
        }
    }

    /**
     * Test connection to database.
     *
     * @param string $db.
     */
    public function actionTestConnection($db)
    {
        $module = Yii::$app->getModule('db-manager');
        if (ArrayHelper::isIn($db, $module->dbList)) {
            $info = $module->getDbInfo($db);
            try {
                new PDO($info['dsn'], $info['username'], $info['password']);
                Console::output('Connection success.');
            } catch (PDOException $e) {
                Console::output('Connection failed: ' . $e->getMessage());
            }
        } else {
            Console::output('Database configuration not found.');
        }
    }
}
