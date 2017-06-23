<?php

namespace bs\dbManager\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use bs\dbManager\models\Dump;
use Symfony\Component\Process\Process;

class DumpController extends Controller
{
    public function actionIndex($db, $isArchive = false)
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
            Console::output('Data base configuration not found.');
        }
    }
}
