<?php

namespace bs\dbManager\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use bs\dbManager\models\Dump;
use bs\dbManager\models\Restore;
use PDO;
use PDOException;
use Symfony\Component\Process\Process;

/**
 * Database backup manager.
 */
class DumpController extends Controller
{
    public $db = 'db';
    public $gzip = false;
    public $storage = false;
    public $file = null;

    public $defaultAction = 'create';

    public function options($actionID)
    {
        return [
            'db',
            'gzip',
            'storage',
            'file',
        ];
    }

    public function optionAliases()
    {
        return [
            'db' => 'db',
            'gz' => 'gzip',
            's' => 'storage',
            'f' => 'file',
        ];
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return Yii::$app->getModule('db-manager');
    }

    private function deleteFiles()
    {
        $lastFiles = $this->getModule()->lastFiles;
        $path = Yii::getAlias($this->getModule()->path);

        $files = FileHelper::findFiles($path, ['only' => ['*.sql', '*.gz'], 'recursive' => FALSE]);

        usort($files, function ($x, $y) {
            return filemtime($x) > filemtime($y);
        });

        $allFiles = count($files);
        foreach ($files as $file) {
            if ($allFiles == $lastFiles) break;
            FileHelper::unlink($file);
            $allFiles--;
        }
    }

    /**
     * Create database dump.
     */
    public function actionCreate()
    {
        $this->deleteFiles();

        $model = new Dump($this->getModule()->dbList);
        if (ArrayHelper::isIn($this->db, $this->getModule()->dbList)) {
            $dbInfo = $this->getModule()->getDbInfo($this->db);
            $dumpOptions = $model->makeDumpOptions();
            if ($this->gzip) {
                $dumpOptions['isArchive'] = true;
            }
            $manager = $this->getModule()->createManager($dbInfo);
            $dumpPath = $manager->makePath($this->getModule()->path, $dbInfo, $dumpOptions);
            $dumpCommand = $manager->makeDumpCommand($dumpPath, $dbInfo, $dumpOptions);
            Yii::trace(compact('dumpCommand', 'dumpPath', 'dumpOptions'), get_called_class());
            $process = new Process($dumpCommand, null, null, null, 60 * 30);
            $process->setTimeout($this->getModule()->timeout);
            $process->run();
            if ($process->isSuccessful()) {
				$uploadResult = true;
                if ($this->storage) {
                    if (Yii::$app->has('backupStorage')) {
						Console::output('Opening: '.$dumpPath);

						$storage = Yii::createObject([
							'class' => 'creocoder\flysystem\LocalFilesystem',
							'path' => dirname($dumpPath),
						]);
                        //$dumpText = fopen($dumpPath, 'r+');
                        $uploadResult = Yii::$app->backupStorage->writeStream(StringHelper::basename($dumpPath), $storage->readStream(StringHelper::basename($dumpPath)));
                        //fclose($dumpText);
						//Console::output(print_r($uploadResult, 1));
                    } else {
                        Console::output('Storage component is not configured.');
                    }
                }
				if ($uploadResult !== false) {
					Console::output('Dump successfully created.');
				}
            } else {
				//Console::output(print_r($process->getErrorOutput(), 1));
                Console::output('Dump failed create.');
            }
        } else {
            Console::output('Database configuration not found.');
        }
    }

    /**
     * Restore database dump.
     */
    public function actionRestore()
    {
        $model = new Restore($this->getModule()->dbList);
        if (is_null($this->file)) {
            if ($this->storage) {
                if (Yii::$app->has('backupStorage')) {
                    foreach (Yii::$app->backupStorage->listContents() as $file) {
                        $fileList[] = [
                            'basename' => $file['basename'],
                            'timestamp' => $file['timestamp'],
                        ];
                    }
                } else {
                    Console::output('Storage component is not configured.');
                }
            } else {
                foreach ($this->getModule()->getFileList() as $file) {
                    $fileList[] = [
                        'basename' => StringHelper::basename($file),
                        'timestamp' => filectime($file),
                    ];
                }
            }
            ArrayHelper::multisort($fileList, ['timestamp'], [SORT_DESC]);
            $this->file = ArrayHelper::getValue(array_shift($fileList), 'basename');
        }
        $runtime = null;
        $dumpFile = null;
        if ($this->storage) {
            if (Yii::$app->has('backupStorage')) {
                if (Yii::$app->backupStorage->has($this->file)) {
                    $runtime = Yii::getAlias('@runtime/backups');
                    if (!is_dir($runtime)) {
                        FileHelper::createDirectory($runtime);
                    }
                    $dumpFile = $runtime . '/' . $this->file;
                    file_put_contents($dumpFile, Yii::$app->backupStorage->read($this->file));
                } else {
                    Console::output('File not found.');
                }
            } else {
                Console::output('Storage component is not configured.');
            }
        } else {
            $fileExists = $this->getModule()->path . $this->file;
            if (file_exists($fileExists)) {
                $dumpFile = $fileExists;
            } else {
                Console::output('File not found.');
            }
        }
        if (ArrayHelper::isIn($this->db, $this->getModule()->dbList)) {
            $dbInfo = $this->getModule()->getDbInfo($this->db);
            $restoreOptions = $model->makeRestoreOptions();
            $manager = $this->getModule()->createManager($dbInfo);
            $restoreCommand = $manager->makeRestoreCommand($dumpFile, $dbInfo, $restoreOptions);
            Yii::trace(compact('restoreCommand', 'dumpFile', 'restoreOptions'), get_called_class());
            $process = new Process($restoreCommand);
            $process->setTimeout($this->getModule()->timeout);
            $process->run();
            if (!is_null($runtime)) {
                FileHelper::removeDirectory($runtime);
            }
            if ($process->isSuccessful()) {
                Console::output('Dump successfully restored.');
            } else {
                Console::output('Dump failed restored.');
            }
        } else {
            Console::output('Database configuration not found.');
        }
    }

    /**
     * Deleting all dumps.
     */
    public function actionDeleteAll()
    {
        Console::output('Do you want to delete all dumps? [yes|no]');
        $answer = trim(fgets(STDIN));
        if (!strncasecmp($answer, 'y', 1)) {
            if (!empty($this->getModule()->getFileList())) {
                $fail = [];
                foreach ($this->getModule()->getFileList() as $file) {
                    if (!unlink($file)) {
                        $fail[] = $file;
                    }
                }
                if (empty($fail)) {
                    Console::output('All dumps successfully removed.');
                } else {
                    Console::output('Error deleting dumps.');
                }
            }
        }
    }

    /**
     * Test connection to database.
     */
    public function actionTestConnection()
    {
        if (ArrayHelper::isIn($this->db, $this->getModule()->dbList)) {
            $info = $this->getModule()->getDbInfo($this->db);
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
