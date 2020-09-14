<?php

namespace bs\dbManager;

use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;
use bs\dbManager\commands\DumpController;

/**
 * dbManager module bootstrap class.
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        // add module I18N category
        if ($app instanceof WebApplication) {
            if (!isset($app->i18n->translations['dbManager'])) {
                $app->i18n->translations['dbManager'] = [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@bs/dbManager/messages',
                ];
            }
        }

        // add console command
        if ($app instanceof ConsoleApplication) {
            if (!isset($app->controllerMap['dump'])) {
                $app->controllerMap['dump'] = DumpController::class;
            }
        }
    }
}
