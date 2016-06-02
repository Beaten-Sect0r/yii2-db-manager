<?php
/**
 * Created by solly [02.06.16 11:12]
 */

namespace bs\dbManager\models;


use bs\dbManager\service\BaseDumpManager;

class PostgresDumpManager extends BaseDumpManager
{
	/**
	 * @param       $path
	 * @param array $dbInfo
	 * @param array $dumpOptions
	 *
	 * @return string
	 */
	public function makeDumpCommand($path, array $dbInfo, array $dumpOptions)
	{
		$builder = new ProcessBuilder();
		$builder->setPrefix('PGPASSWORD='.$dbInfo['password'].' pg_dump');
		$arguments = [
			'--host='.$dbInfo['host'],
			'--user='.$dbInfo['username'],
			'--no-password'
		];
		$builder->setArguments($arguments);
		if($dumpOptions['schemaOnly']){
			$builder->add('--schema-only');
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
	 * @return string
	 */
	public function makeRestoreCommand($path, array $dbInfo, array $restoreOptions)
	{
		// TODO: Implement makeRestoreCommand() method.
	}

}