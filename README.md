# Yii2 DB manager

Click on a :star:!

[![Total Downloads](https://poser.pugx.org/beaten-sect0r/yii2-db-manager/downloads?format=flat-square)](https://packagist.org/packages/beaten-sect0r/yii2-db-manager)
[![Latest Stable Version](https://poser.pugx.org/beaten-sect0r/yii2-db-manager/v/stable?format=flat-square)](https://packagist.org/packages/beaten-sect0r/yii2-db-manager)
[![Latest Unstable Version](https://poser.pugx.org/beaten-sect0r/yii2-db-manager/v/unstable?format=flat-square)](https://packagist.org/packages/beaten-sect0r/yii2-db-manager)
[![License](https://poser.pugx.org/beaten-sect0r/yii2-db-manager/license?format=flat-square)](https://packagist.org/packages/beaten-sect0r/yii2-db-manager)

MySQL/PostgreSQL Database Backup and Restore functionality

![screenshot](screenshot.png)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require --prefer-dist beaten-sect0r/yii2-db-manager "*"
```

or add

```
"beaten-sect0r/yii2-db-manager": "*"
```

to the require section of your `composer.json` file.

## Configuration

Once the extension is installed, simply add it in your config by:

Basic ```config/web.php```

Advanced ```backend/config/main.php```

## Simple config

```php
    'modules' => [
        'db-manager' => [
            'class' => 'bs\dbManager\Module',
            // path to directory for the dumps
            'path' => '@app/backups',
            // list of registerd db-components
            'dbList' => ['db'],
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
            ],
        ],
    ],
```

## Advanced config

```php
    'modules' => [
        'db-manager' => [
            'class' => 'bs\dbManager\Module',
            // path to directory for the dumps
            'path' => '@app/backups',
            // list of registerd db-components
            'dbList' => ['db', 'db1', 'db2'],
            // additional mysqldump/pg_dump presets (available for choosing in dump and restore forms)
            'customDumpOptions' => [
                'mysqlForce' => '--force',
                'somepreset' => '--triggers --single-transaction',
                'pgCompress' => '-Z2 -Fc',
            ],
            'customRestoreOptions' => [
                'mysqlForce' => '--force',
                'pgForce' => '-f -d',
            ],
            // options for full customizing default command generation
            'mysqlManagerClass' => 'CustomClass',
            'postgresManagerClass' => 'CustomClass',
            // option for add additional DumpManagers
            'createManagerCallback' => function($dbInfo) {
                if ($dbInfo['dbName'] == 'exclusive') {
                    return new MyExclusiveManager;
                } else {
                    return false;
                }
            }
            'as access' => [
                'class' => 'yii\filters\AccessControl',
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
            ],
        ],
    ],
```

Make sure you create a writable directory named backup on app root directory.

## Usage

Pretty url's ```/db-manager```

No pretty url's ```index.php?r=db-manager```

## Changelog

- Multiple database management
- Ability for customize dump and restore options; dump and restore processors
- Ability for run operations asynchronously
- Ability for compressing dumps
