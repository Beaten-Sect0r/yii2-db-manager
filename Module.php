<?php

namespace bs\dbManager;

use Yii;
use yii\base\Module as BaseModule;
use yii\helpers\FileHelper;

class Module extends BaseModule
{
    public $dsn;
    public $username;
    public $password;
    public $tablePrefix;
    public $path;

    public $host;
    public $dbName;
    public $files;

    public function init()
    {
        parent::init();

        $dsn = $this->parseDSN($this->dsn);
        $this->host = $dsn['host'];
        $this->dbName = $this->tablePrefix . $dsn['dbname'];
        $this->files = FileHelper::findFiles($this->path, ['only' => ['*.sql']]);
    }

    public function parseDSN($dsn)
    {
        if (is_array($dsn)) {
            return $dsn;
        }
        $parsed = @parse_url($dsn);
        if (!$parsed) {
            return;
        }
        $params = null;
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $parsed += $params;
        }
        $parsed['dsn'] = $dsn;

        $path = explode(';', $parsed['path']);
        foreach ($path as $p) {
            $x = explode('=', $p);
            $parsed[$x[0]] = $x[1];
        }

        return $parsed;
    }
}
