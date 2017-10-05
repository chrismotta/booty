<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = 'Update Placement #'.$model->id.': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Publishers', 'url' => ['/publishers']];
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="placements-update">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
        'filterByPublisher' => $filterByPublisher,
    ]) ?>

</div>
