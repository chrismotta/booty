<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Publishers */

$this->title = 'Publisher #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Publishers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="publishers-view">

    <p>
        <?= Html::a('Admin', ['publishers/'], ['class' => 'btn btn-success']) ?>
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
            [
                'label'     => 'Admin User',
                'attribute' => 'adminUser.username'
            ]
        ],
    ]) ?>

</div>
