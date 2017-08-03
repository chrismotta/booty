<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Clusters */

$this->title = 'Cluster #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Clusters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="clusters-view">

    <p>
        <?= Html::a('Admin', ['clusters/'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Assign', ['assignment', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
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
            'country',
            'connection_type',
            'os',
            [
                'label'     => 'Static Campaign',
                'attribute' => 'staticCampaigns.name'
            ]
        ],
    ]) ?>

</div>
