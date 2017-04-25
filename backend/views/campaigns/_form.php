<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Campaigns */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campaigns-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'Affiliates_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'payout')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'landing_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'creative_320x50')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'creative_300x250')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
