<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Placements */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="placements-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'Publishers_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'frequency_cap')->textInput() ?>

    <?= $form->field($model, 'payout')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'model')->dropDownList([ 'CPM' => 'CPM', 'RS' => 'RS', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'status')->dropDownList([ 'health_check' => 'Health check', 'active' => 'Active', 'testing' => 'Testing', 'paused' => 'Paused', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'size')->textInput(['maxlength' => true]) ?>

    <!-- <?= $form->field($model, 'health_check_imps')->textInput() ?> -->

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
