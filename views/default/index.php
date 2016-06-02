<?php

/* @var $this yii\web\View */
/* @var array $dbList */
/* @var \bs\dbManager\models\Dump $model */
/* @var $dataProvider yii\data\ArrayDataProvider */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\grid\GridView;

$this->title = Yii::t('dbManager', 'DB manager');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dbManager-default-index">


	<?php $form = ActiveForm::begin([
		'action' => ['create'],
		'method' => 'post',
		'layout' => 'inline',
	]) ?>
	<?= $form->field($model, 'db')->dropDownList(array_combine($dbList, $dbList)) ?>
	<?= $form->field($model, 'isArchive')->checkbox() ?>
	<?= $form->field($model, 'schemaOnly')->checkbox() ?>
	<?php if ($model->hasPresets()): ?>
		<?= $form->field($model, 'preset')->dropDownList($model->getCustomOptions()) ?>
	<?php endif; ?>
	<?= Html::submitButton(Yii::t('dbManager', 'Create dump'), ['class' => 'btn btn-success']) ?>
	<?php ActiveForm::end(); ?>


	<?= Html::a(Yii::t('dbManager', 'Delete all'),
		['delete-all'],
		[
			'class'        => 'btn btn-danger pull-right',
			'data-method'  => 'post',
			'data-confirm' => Yii::t('dbManager', 'Are you sure?'),
		]) ?>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns'      => [
			[
				'attribute' => 'type',
				'label'     => Yii::t('dbManager', 'Type'),
			],
			[
				'attribute' => 'name',
				'label'     => Yii::t('dbManager', 'Name'),
			],
			[
				'attribute' => 'size',
				'label'     => Yii::t('dbManager', 'Size'),
			],
			[
				'attribute' => 'create_at',
				'label'     => Yii::t('dbManager', 'Create time'),
			],
			[
				'class'    => 'yii\grid\ActionColumn',
				'template' => '{download} {restore} {delete}',
				'buttons'  => [
					'download' => function($url, $model)
					{
						return Html::a('<span class="glyphicon glyphicon-download"></span>',
							[
								'download',
								'id' => $model['id'],
							],
							[
								'title' => Yii::t('dbManager', 'Download'),
							]);
					},
					'restore'  => function($url, $model)
					{
						return Html::a('<span class="glyphicon glyphicon-import"></span>',
							[
								'restore',
								'id' => $model['id'],
							],
							[
								'data-method'  => 'post',
								'data-confirm' => Yii::t('dbManager', 'Are you sure?'),
								'title'        => Yii::t('dbManager', 'Restore'),
							]);
					},
					'delete'   => function($url, $model)
					{
						return Html::a('<span class="glyphicon glyphicon-trash"></span>',
							[
								'delete',
								'id' => $model['id'],
							],
							[
								'title' => Yii::t('dbManager', 'Delete'),
								'data-method'  => 'post',
								'data-confirm' => Yii::t('dbManager', 'Are you sure?'),
							]);
					},
				],
			],
		],
	]) ?>

</div>
