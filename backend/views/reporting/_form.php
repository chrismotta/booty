<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CampaignLogs */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campaign-logs-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'click_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'D_Campaign_id')->textInput() ?>

    <?= $form->field($model, 'session_hash')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'click_time')->textInput() ?>

    <?= $form->field($model, 'conv_time')->textInput() ?>

    <?= $form->field($model, 'revenue')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
