<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = 'Placement #'.$model->id.': '.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="placements-view">

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
    <p>
        <hr/>
        <h4>Iframe Tag</h4>
        <?= Html::textarea('iframeTag', '<iframe src="" frameborder="0" scrolling="no" width="" height=""></iframe>', ['class' => 'form-control']) ?>
        <h4>Javascript Tag</h4>
        <?= Html::textarea('javascriptTag', '<script type="text/javascript" src=""></script>', ['class' => 'form-control']) ?>
        <hr/>
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
