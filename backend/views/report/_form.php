<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Report */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="report-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'D_Placement_id')->textInput() ?>

    <?= $form->field($model, 'D_Campaign_id')->textInput() ?>

    <?= $form->field($model, 'cluster_id')->textInput() ?>

    <?= $form->field($model, 'session_hash')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imps')->textInput() ?>

    <?= $form->field($model, 'imp_time')->textInput() ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'click_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'click_time')->textInput() ?>

    <?= $form->field($model, 'conv_time')->textInput() ?>

    <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'connection_type')->dropDownList([ '3g' => '3g', 'wifi' => 'Wifi', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'carrier')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'device')->dropDownList([ 'mobile' => 'Mobile', 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'console' => 'Console', 'other' => 'Other', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'device_model')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'device_brand')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'os')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'os_version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'browser')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'browser_version')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
