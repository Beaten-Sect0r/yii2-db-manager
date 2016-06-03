<?php

/* @var $this yii\web\View */
/* @var \bs\dbManager\models\Restore $model */
/* @var string $file */
/* @var int $id */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$this->title = Yii::t('dbManager', 'DB manager') . ' - ' . Yii::t('dbManager', 'Restore');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dbManager-default-restore">

	<div class="well">
		<h4><?=Yii::t('dbManager', 'Restore')?> <?=$file?></h4>
		<?php $form = ActiveForm::begin([
            'action' => ['restore','id'=>$id],
            'method' => 'post',
            'layout' => 'inline',
        ]) ?>
		<?= $form->errorSummary($model) ?>
		<?= $form->field($model, 'db')->dropDownList($model->getDBList(), ['prompt' => '----']) ?>
		<?= $form->field($model, 'runInBackground')->checkbox() ?>
		<?php if ($model->hasPresets()): ?>
			<?= $form->field($model, 'preset')->dropDownList($model->getCustomOptions(), ['prompt' => '----']) ?>
		<?php endif ?>
		<?= Html::submitButton(Yii::t('dbManager', 'Restore'), ['class' => 'btn btn-success']) ?>
		<?php ActiveForm::end() ?>
	</div>

</div>
