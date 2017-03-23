<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PlacementsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="placements-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'Sources_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'frequency_cap') ?>

    <?= $form->field($model, 'payout') ?>

    <?php // echo $form->field($model, 'model') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'size') ?>

    <?php // echo $form->field($model, 'health_check_imps') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
