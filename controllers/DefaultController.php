<?php

namespace bs\dbManager\controllers;

use bs\dbManager\models\Dump;
use bs\dbManager\Module;
use PDO;
use Symfony\Component\Process\Process;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
					'create'     => ['post'],
					'delete'     => ['post'],
					'delete-all' => ['post'],
					'restore'    => ['post'],
					'*'          => ['get'],
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
		$model = new Dump($dbList, $this->getModule()->customDumpOptions);
		$dataProvider = new ArrayDataProvider([
			'allModels'  => $dataArray,
			'pagination' => [
				'pageSize' => 30,
			],
		]);

		return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model, 'dbList' => $dbList]);

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
		$model = new Dump($this->getModule()->dbList, $this->getModule()->customDumpOptions);
		if ($model->load(Yii::$app->request->post()) && $model->validate())
		{
			$dbInfo = $this->getModule()->getDbInfo($model->db);
			$dumpOptions = $model->makeDumpOptions();
			$manager = $this->getModule()->createManager($dbInfo['driverName']);
			$dumpPath = $manager->makePath($this->getModule()->path, $dbInfo, $dumpOptions);
			$dumpCommand = $manager->makeDumpCommand($dumpPath, $dbInfo, $dumpOptions);
			if ($model->runInBackground)
			{
				$this->runDumpAsync($dumpCommand);
			}
			else
			{
				$this->runDump($dumpCommand);
			}

		}
		else
		{
			Yii::$app->session->setFlash('error', Yii::t('dbManager','Dump request invalid') . '\n' . Html::errorSummary
			($model));
		}
		return $this->redirect(['index']);
	}

	/**
	 * @inheritdoc
	 */
	public function actionDownload($id)
	{
		$dumpPath = $this->getModule()->path . basename(ArrayHelper::getValue($this->getModule()->getFileList(), $id));
		return Yii::$app->response->sendFile($dumpPath);
	}

	/**
	 * @inheritdoc
	 */
	public function actionRestore($id)
	{
		$dumpPath = $this->getModule()->path . basename(ArrayHelper::getValue($this->getModule()->getFileList(), $id));

		//MySQL
		if ($this->module->driverName === 'mysql')
		{
			$command = 'mysql --host=' . $this->module->host . ' --user=' . $this->module->username . ' --password='
				. $this->module->password . ' --force ' . $this->module->dbName . ' < ' . $dumpPath;
		}
		//PostgreSQL
		if ($this->module->driverName === 'pgsql')
		{
			$command = 'PGPASSWORD=' . $this->module->password . ' psql --host=' . $this->module->host . ' --username='
				. $this->module->username . ' --no-password ' . $this->module->dbName . ' < ' . $dumpPath;
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

	protected function runDump($command)
	{
		$process = new Process($command);
		$process->run();
		if ($process->isSuccessful())
		{
			Yii::$app->session->addFlash('success', Yii::t('dbManager', 'Dump successfully created.'));
		}
		else
		{
			Yii::$app->session->addFlash('error', Yii::t('dbManager', 'Dump failed') . '\n'
				. $process->getOutput());
			Yii::error('Dump failed' . '\n' . $process->getOutput());
		}

	}

	protected function runDumpAsync($command)
	{
		$process = new Process($command);
		$process->start();
		$pid = $process->getPid();
		$activePids = Yii::$app->session->get('backupPids', []);
		Yii::$app->session->set('backupPids', array_merge($activePids, [$pid => $command]));
		Yii::$app->session->addFlash('info', Yii::t('dbManager', 'Dump process running with pid={pid}', $pid)
			. '\n' . $command);
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
