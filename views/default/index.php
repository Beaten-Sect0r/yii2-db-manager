<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */

$this->title = Yii::t('dbManager', 'DB manager');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dbManager-default-index">

    <p>
        <?= Html::a(Yii::t('dbManager', 'Create dump'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('dbManager', 'Delete all'), ['delete-all'], ['class' => 'btn btn-danger']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'label' => Yii::t('dbManager', 'Name'),
            ],
            [
                'attribute' => 'size',
                'label' => Yii::t('dbManager', 'Size'),
            ],
            [
                'attribute' => 'create_at',
                'label' => Yii::t('dbManager', 'Create time'),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{download} {restore} {delete}',
                'buttons' => [
                    'download' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-download"></span>',
                        [
                            'download',
                            'id' => $model['id'],
                        ],
                        [
                            'title' => Yii::t('dbManager', 'Download'),
                        ]);
                    },
                    'restore' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-import"></span>',
                        [
                            'restore',
                            'id' => $model['id'],
                        ],
                        [
                            'title' => Yii::t('dbManager', 'Restore'),
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>',
                        [
                            'delete',
                            'id' => $model['id'],
                        ],
                        [
                            'title' => Yii::t('dbManager', 'Delete'),
                        ]);
                    },
                ],
            ],
        ],
    ]) ?>

</div>
