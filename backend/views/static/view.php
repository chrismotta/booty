<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\StaticCampaigns */

$this->title = 'Static campaign #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Static Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="static-campaigns-view">

    <p>
        <?= Html::a('Admin', ['static/'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'landing_url:url',
            'creative_300x250',
            'creative_320x50',
        ],
    ]) ?>

    <?= Html::img($model->creative_320x50, ['alt' => '320x50', 'style' => 'margin-right:10px']) ?>
    <?= Html::img($model->creative_300x250, ['alt' => '300x250', 'style' => 'margin-right:10px']) ?>
</div>
