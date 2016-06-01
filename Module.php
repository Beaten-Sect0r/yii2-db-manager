<?php

namespace bs\dbManager;

use Yii;
use yii\base\Module as BaseModule;
use yii\helpers\FileHelper;

class Module extends BaseModule
{
    public $driverName;
    public $dsn;
    public $host;
    public $dbName;
    public $username;
    public $password;
    public $tablePrefix;
    public $path;
    public $files;

    public function init()
    {
        parent::init();

        $this->driverName = Yii::$app->getDb()->driverName;
        $this->dsn = Yii::$app->getDb()->dsn;
        $this->host = $this->getDsnAttribute('host', $this->dsn);
        $this->dbName = $this->getDsnAttribute('dbname', $this->dsn);
        $this->username = Yii::$app->getDb()->username;
        $this->password = Yii::$app->getDb()->password;
        $this->tablePrefix = Yii::$app->getDb()->tablePrefix;
        $this->files = FileHelper::findFiles($this->path, ['only' => ['*.sql']]);
    }

    /**
     * @inheritdoc
     */
    public function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        } else {
            return null;
        }
    }
}
