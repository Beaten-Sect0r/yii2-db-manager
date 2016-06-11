<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use bs\dbManager\models\BaseDumpManager;

/* @var $this yii\web\View */
/* @var \bs\dbManager\models\Restore $model */
/* @var string $file */
/* @var int $id */

$this->title = Yii::t('dbManager', 'Restore');
$this->params['breadcrumbs'][] = ['label' => Yii::t('dbManager', 'DB manager'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dbManager-default-restore">

    <div class="well">
        <h4><?= Yii::t('dbManager', 'Restore') . ': ' .  $file ?></h4>
        <?php $form = ActiveForm::begin([
            'action' => ['restore', 'id' => $id],
            'method' => 'post',
        ]) ?>

        <?= $form->errorSummary($model) ?>

        <?= $form->field($model, 'db')->dropDownList($model->getDBList(), ['prompt' => '']) ?>

        <?php if (!BaseDumpManager::isWindows()) {
            echo $form->field($model, 'runInBackground')->checkbox();
        } ?>

        <?php if ($model->hasPresets()): ?>
            <?= $form->field($model, 'preset')->dropDownList($model->getCustomOptions(), ['prompt' => '']) ?>
        <?php endif ?>

        <?= Html::submitButton(Yii::t('dbManager', 'Restore'), ['class' => 'btn btn-success']) ?>

        <?php ActiveForm::end() ?>
    </div>

</div>
