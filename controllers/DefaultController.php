<?php

namespace bs\dbManager\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use PDO;

/**
 * Default controller.
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        // MySQL connection test
        try {
            new PDO($this->module->dsn, $this->module->username, $this->module->password);

            $dataArray = [];

            foreach ($this->module->files as $id => $file) {
                $columns = [];
                $columns['id'] = $id;
                $columns['name'] = basename($file);
                $columns['size'] = Yii::$app->formatter->asSize(filesize($file));
                $columns['create_at'] = Yii::$app->formatter->asDatetime(filectime($file));
                $dataArray[] = $columns;
            }

            ArrayHelper::multisort($dataArray, ['create_at'], [SORT_DESC]);
            $dataProvider = new ArrayDataProvider([
                'allModels' => $dataArray,
                'pagination' => [
                    'pageSize' => 30,
                ],
            ]);

            return $this->render('index', ['dataProvider' => $dataProvider]);
        } catch (PDOException $e) {
            echo 'Error connect to MySQL: ' . $e->getMessage();
        }
    }

    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $dump = $this->module->path . $this->module->dbName . '_' . date('Y-m-d-H-i-s') . '.sql';
        $command = 'mysqldump --host=' . $this->module->host . ' --user=' . $this->module->username . ' --password=' . $this->module->password . ' --force ' . $this->module->dbName . ' > ' . $dump;

        if (!shell_exec($command)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('dbManager', 'Dump successfully created.'),
                'options' => ['class' => 'alert-success'],
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('dbManager', 'Error creating dump.'),
                'options' => ['class' => 'alert-error'],
            ]);
        }

        return $this->redirect(['index']);
    }

    /**
     * @inheritdoc
     */
    public function actionDownload($id)
    {
        $dump = $this->module->path . basename($this->module->files[$id]);

        return Yii::$app->response->sendFile($dump);
    }

    /**
     * @inheritdoc
     */
    public function actionRestore($id)
    {
        $dump = $this->module->path . basename($this->module->files[$id]);
        $command = 'mysql --host=' . $this->module->host . ' --user=' . $this->module->username . ' --password=' . $this->module->password . ' --force ' . $this->module->dbName . ' < ' . $dump;

        if (!shell_exec($command)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('dbManager', 'Dump successfully restored.'),
                'options' => ['class' => 'alert-success'],
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('dbManager', 'Error restoring dump.'),
                'options' => ['class' => 'alert-error'],
            ]);
        }

        return $this->redirect(['index']);
    }

    /**
     * @inheritdoc
     */
    public function actionDelete($id)
    {
        $dump = $this->module->path . basename($this->module->files[$id]);

        if (unlink($dump)) {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('dbManager', 'Dump deleted successfully.'),
                'options' => ['class' => 'alert-success'],
            ]);
        } else {
            Yii::$app->session->setFlash('alert', [
                'body' => Yii::t('dbManager', 'Error deleting dump.'),
                'options' => ['class' => 'alert-error'],
            ]);
        }

        return $this->redirect(['index']);
    }

    /**
     * @inheritdoc
     */
    public function actionDeleteAll()
    {
        if (file_exists($this->module->path)) {
            foreach ($this->module->files as $file) {
                if (unlink($file)) {
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t('dbManager', 'All dumps successfully removed.'),
                        'options' => ['class' => 'alert-success'],
                    ]);
                } else {
                    Yii::$app->session->setFlash('alert', [
                        'body' => Yii::t('dbManager', 'Error deleting dumps.'),
                        'options' => ['class' => 'alert-error'],
                    ]);
                }
            }
        }

        return $this->redirect(['index']);
    }
}
