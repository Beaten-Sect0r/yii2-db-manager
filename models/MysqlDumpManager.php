<?php
/**
 * Created by solly [02.06.16 11:11]
 */

namespace bs\dbManager\models;


use bs\dbManager\service\BaseDumpManager;
use Symfony\Component\Process\ProcessBuilder;

class MysqlDumpManager extends BaseDumpManager
{
	/**
	 * @param       $path
	 * @param array $dbInfo
	 * @param array $dumpOptions
	 *
	 * @return mixed
	 */
	public function makeDumpCommand($path, array $dbInfo, array $dumpOptions)
	{
		$builder = new ProcessBuilder();
		$builder->setPrefix('mysqldump');
		$arguments = [
			'--host='.$dbInfo['host'],
			'--user='.$dbInfo['username'],
			'--password='.$dbInfo['password']
		];
		$builder->setArguments($arguments);
		if($dumpOptions['schemaOnly']){
			$builder->add('--no-data');
		}
		if($dumpOptions['preset']){
			$builder->add($dumpOptions['presetData']);
		}
		$builder->add($dbInfo['dbName']);

		if($dumpOptions['isArchive']){
			$builder->add('|gzip');
		}
		$builder->add('>'.$path);
		return $builder->getProcess()->getCommandLine();
	}

	/**
	 * @param       $path
	 * @param array $dbInfo
	 * @param array $restoreOptions
	 *
	 * @return mixed
	 */
	public function makeRestoreCommand($path, array $dbInfo, array $restoreOptions)
	{
		// TODO: Implement makeRestoreCommand() method.
	}

}