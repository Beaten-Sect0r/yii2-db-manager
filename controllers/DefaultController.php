<?php

namespace bs\dbManager\controllers;

use bs\dbManager\Module;
use PDO;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller.
 */
class DefaultController extends Controller
{
	/**
	 * @return Module
	 **/
	public function getModule()
	{
		return $this->module;
	}

	public function behaviors()
	{
		return [
			'verbs' => [
				'class'   => VerbFilter::class,
				'actions' => [
					'create'    => ['post'],
					'delete'    => ['post'],
					'deleteAll' => ['post'],
					'restore'   => ['post'],
					'*'         => ['get'],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actionIndex()
	{

		$dataArray = $this->prepareFileData();
		$dbList = $this->getModule()->dbList;
		$dataProvider = new ArrayDataProvider([
			'allModels'  => $dataArray,
			'pagination' => [
				'pageSize' => 30,
			],
		]);

		return $this->render('index', ['dataProvider' => $dataProvider, 'dbList' => $dbList]);

	}

	public function actionTestConnection($dbname)
	{
		$info = $this->getModule()->getDbInfo($dbname);
		try
		{
			new PDO($info['dsn'], $info['username'], $info['password']);
			\Yii::$app->session->setFlash('sussess', 'Connection success:');
		}
		catch (\PDOException $e)
		{
			\Yii::$app->session->setFlash('error', 'Connection failed: ' . $e->getMessage());
		}
		return $this->redirect('index');
	}

	/**
	 * @inheritdoc
	 */
	public function actionCreate()
	{
		$dump = $this->module->path . $this->module->dbName . '_' . date('Y-m-d-H-i-s') . '.sql';
		//MySQL
		if ($this->module->driverName === 'mysql')
		{
			$command = 'mysqldump --host=' . $this->module->host . ' --user=' . $this->module->username . ' --password='
				. $this->module->password . ' --force ' . $this->module->dbName . ' > ' . $dump;
		}
		//PostgreSQL
		if ($this->module->driverName === 'pgsql')
		{
			$command = 'PGPASSWORD=' . $this->module->password . ' pg_dump --host=' . $this->module->host
				. ' --username=' . $this->module->username . ' --no-password ' . $this->module->dbName . ' > ' . $dump;
		}
		shell_exec($command);
		Yii::$app->session->setFlash('alert', [
			'body'    => Yii::t('dbManager', 'Dump successfully created.'),
			'options' => ['class' => 'alert-success'],
		]);

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
		//MySQL
		if ($this->module->driverName === 'mysql')
		{
			$command = 'mysql --host=' . $this->module->host . ' --user=' . $this->module->username . ' --password='
				. $this->module->password . ' --force ' . $this->module->dbName . ' < ' . $dump;
		}
		//PostgreSQL
		if ($this->module->driverName === 'pgsql')
		{
			$command = 'PGPASSWORD=' . $this->module->password . ' psql --host=' . $this->module->host . ' --username='
				. $this->module->username . ' --no-password ' . $this->module->dbName . ' < ' . $dump;
		}
		shell_exec($command);
		Yii::$app->session->setFlash('alert', [
			'body'    => Yii::t('dbManager', 'Dump successfully restored.'),
			'options' => ['class' => 'alert-success'],
		]);

		return $this->redirect(['index']);
	}

	/**
	 * @inheritdoc
	 */
	public function actionDelete($id)
	{
		$dump = $this->module->path . basename($this->module->files[$id]);

		if (unlink($dump))
		{
			Yii::$app->session->setFlash('alert', [
				'body'    => Yii::t('dbManager', 'Dump deleted successfully.'),
				'options' => ['class' => 'alert-success'],
			]);
		}
		else
		{
			Yii::$app->session->setFlash('alert', [
				'body'    => Yii::t('dbManager', 'Error deleting dump.'),
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
		if (!empty($this->getModule()->getFileList()))
		{
			$fail = [];
			foreach ($this->getModule()->getFileList() as $file)
			{
				if (!unlink($file))
				{
					$fail[] = $file;
				}
			}
			if (empty($fail))
			{
				Yii::$app->session->setFlash('alert', [
					'body'    => Yii::t('dbManager', 'All dumps successfully removed.'),
					'options' => ['class' => 'alert-success'],
				]);
			}
			else
			{
				Yii::$app->session->setFlash('alert', [
					'body'    => Yii::t('dbManager', 'Error deleting dumps.'),
					'options' => ['class' => 'alert-error'],
				]);
			}
		}

		return $this->redirect(['index']);
	}


	/**
	 * @return array
	 **/
	protected function prepareFileData()
	{
		$dataArray = [];

		foreach ($this->getModule()->getFileList() as $id => $file)
		{
			$columns = [];
			$columns['id'] = $id;
			$columns['type'] = pathinfo($file, PATHINFO_EXTENSION);
			$columns['name'] = basename($file);
			$columns['size'] = Yii::$app->formatter->asSize(filesize($file));
			$columns['create_at'] = Yii::$app->formatter->asDatetime(filectime($file));
			$dataArray[] = $columns;
		}
		ArrayHelper::multisort($dataArray, ['create_at'], [SORT_DESC]);
		return $dataArray;
	}
}
