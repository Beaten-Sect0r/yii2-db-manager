<?php

namespace bs\dbManager;

use yii\base\BootstrapInterface;

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
        if (!isset($app->i18n->translations['dbManager'])) {
            $app->i18n->translations['dbManager'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@bs/dbManager/messages',
            ];
        }
    }
}
