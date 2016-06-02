<?php

namespace bs\dbManager;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\base\UserException;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class Module
 *
 * @package bs\dbManager
 */
class Module extends BaseModule
{
	/**
	 * Array of available db-components for dump
	 *
	 * @var array $db
	 **/
	public $dbList = ['db'];
	/**
	 * Path for backup directory
	 *
	 * @var string $path
	 **/
	public $path;

	/**
	 * You can setup favorite dump options presets foreach db
     * @example
	 * 'customDumpOptions'=>[
	 *    'preset1' => '--triggers --single-transaction',
	 *    'preset2' => ' --replace --lock-all-tables '
	 * ]
	 * @var array $customDumpOptions
	**/
	public $customDumpOptions = [];

	/**
	 * @see $customDumpOptions
	 * @var array $customRestoreOptions
	 **/
	public $customRestoreOptions = [];

	/**
	 * @var array
	 */
	protected $dbInfo = [];
	/**
	 * @var array
	 */
	protected $fileList = [];

	/**
	 * @throws InvalidConfigException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		parent::init();
		if (!empty($this->dbList))
		{
			foreach ($this->dbList as $db)
			{
				/**
				 * @var Connection $db
				 **/
				$db = Instance::ensure($db, Connection::class);
				$this->dbInfo['db']['driverName'] = $db->driverName;
				$this->dbInfo['db']['dsn'] = $db->dsn;
				$this->dbInfo['db']['host'] = $this->getDsnAttribute('host', $db->dsn);
				$this->dbInfo['db']['dbName'] = $this->getDsnAttribute('dbName', $db->dsn);
				$this->dbInfo['db']['username'] = $db->username;
				$this->dbInfo['db']['password'] = $db->password;
				$this->dbInfo['db']['prefix'] = $db->tablePrefix;
			}
		}
		$this->path = Yii::getAlias($this->path);
		if (!is_dir($this->path))
		{
			throw new InvalidConfigException('Path is not directory');
		}
		if (!is_writable($this->path))
		{
			throw new InvalidConfigException('Path is not writeable! Check chmod!');
		}
		$this->fileList = FileHelper::findFiles($this->path, ['only' => ['*.sql', '*.gz']]);
	}

	/**
	 * Get info for selected database
	 *
	 * @param $db
	 *
	 * @return array
	 * @throws UserException
	 */
	public function getDbInfo($db)
	{
		$info = ArrayHelper::getValue($this->dbList, $db, null);
		if (!$info)
		{
			throw new UserException('Db with name ' . $db . ' not configured for dump');
		}
		return $info;
	}

	/**
	 * @return array
	 **/
	public function getFileList()
	{
		return $this->fileList;
	}

	/**
	 * @param $name
	 * @param $dsn
	 *
	 * @return null
	 */
	protected function getDsnAttribute($name, $dsn)
	{
		if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match))
		{
			return $match[1];
		}
		else
		{
			return null;
		}
	}

}
