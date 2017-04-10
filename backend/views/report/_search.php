<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ReportSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="report-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'D_Placement_id') ?>

    <?= $form->field($model, 'D_Campaign_id') ?>

    <?= $form->field($model, 'cluster_id') ?>

    <?= $form->field($model, 'session_hash') ?>

    <?php // echo $form->field($model, 'imps') ?>

    <?php // echo $form->field($model, 'imp_time') ?>

    <?php // echo $form->field($model, 'cost') ?>

    <?php // echo $form->field($model, 'click_id') ?>

    <?php // echo $form->field($model, 'click_time') ?>

    <?php // echo $form->field($model, 'conv_time') ?>

    <?php // echo $form->field($model, 'country') ?>

    <?php // echo $form->field($model, 'connection_type') ?>

    <?php // echo $form->field($model, 'carrier') ?>

    <?php // echo $form->field($model, 'device') ?>

    <?php // echo $form->field($model, 'device_model') ?>

    <?php // echo $form->field($model, 'device_brand') ?>

    <?php // echo $form->field($model, 'os') ?>

    <?php // echo $form->field($model, 'os_version') ?>

    <?php // echo $form->field($model, 'browser') ?>

    <?php // echo $form->field($model, 'browser_version') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
