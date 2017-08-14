<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Campaigns */

$this->title = 'Campaign #'.$model->id.': '.$model->name;
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
            'ext_id',
            'payout',
            'country',
            'connection_type',
            'device_type',
            'os',
            'os_version',            
            'carrier',           
            'landing_url:url',
            'creative_320x50',
            'creative_300x250',
        ],
    ]) ?>

    <?= Html::img($model->creative_320x50, ['alt' => '320x50', 'style' => 'margin-right:10px']) ?>
    <?= Html::img($model->creative_300x250, ['alt' => '300x250', 'style' => 'margin-right:10px']) ?>

</div>
