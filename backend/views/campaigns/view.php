<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Campaigns */

$this->title = 'Campaign #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Affiliates', 'url' => ['/affiliates']];
$this->params['breadcrumbs'][] = ['label' => 'Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaigns-view">
    <!-- 
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
     -->
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label'     => 'Affiliate',
                'attribute' => 'affiliates.name'
            ],
            'name',
            'info:ntext',
            'ext_id',
            'payout',
            'status',
            [
                'attribute'=>'country',
                'format'=>'html',
                'value' => $model->formatValues('country', 'success'),
            ],
            [
                'attribute'=>'connection_type',
                'format'=>'html',
                'value' => $model->formatValues('connection_type', 'primary'),
            ],
            [
                'attribute'=>'device_type',
                'format'=>'html',
                'value' => $model->formatValues('device_type', 'danger'),
            ],
            [
                'attribute'=>'os',
                'format'=>'html',
                'value' => $model->formatValues('os', 'info'),
            ],
            [
                'attribute'=>'os_version',
                'format'=>'html',
                'value' => $model->formatValues('os_version', 'default'),
            ],
            [
                'attribute'=>'carrier',
                'format'=>'html',
                'value' => $model->formatValues('carrier', 'warning'),
            ],  
            'app_id',
            'landing_url:url',
            'creative_320x50:url',
            'creative_300x250:url',
            [
                'attribute'=>'Test URL',
                'format'=>'url',
                'value'=> function($model, $widget){
                    return 'http://ad.spdx.co/click/test/'.$model->id;
                }
            ]
        ],
    ]) ?>

    <?= Html::img($model->creative_320x50, ['alt' => '320x50', 'style' => 'margin-right:10px']) ?>
    <?= Html::img($model->creative_300x250, ['alt' => '300x250', 'style' => 'margin-right:10px']) ?>

</div>
