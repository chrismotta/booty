<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Placements */

$this->title = 'Create Placement';
$this->params['breadcrumbs'][] = ['label' => 'Placements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$model->status = 'health_check';
$model->health_check_imps = 10000;
?>
<div class="placements-create">

    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
