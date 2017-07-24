<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="placements-view">

    <h1><?= Html::encode($this->title) ?></h1>

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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label'     => 'Publisher',
                'attribute' => 'publishers.name'
            ],
            'name',
            'frequency_cap',
            'payout',
            'model',
            'status',
            'size',
            'health_check_imps',
        ],
    ]) ?>

</div>
