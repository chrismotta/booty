<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Affiliates */

$this->title = 'Affiliate #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Affiliates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="affiliates-view">

    <p>
        <?= Html::a('Admin', ['affiliates/'], ['class' => 'btn btn-success']) ?>
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
            'short_name',
            'user_id',
            'api_key',
            'click_macro',
            [
                'label'     => 'Admin User',
                'attribute' => 'adminUser.username'
            ]
        ],
    ]) ?>

</div>
