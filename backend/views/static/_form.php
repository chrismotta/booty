<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StaticCampaigns */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-info">
    <div class="box-body">
<div class="static-campaigns-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'landing_url')->textInput(['maxlength' => true]) ?>

    </div>
    <div class="col-md-6">

    <?= $form->field($model, 'creative_300x250')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'creative_320x50')->textInput(['maxlength' => true]) ?>

    </div>
    <div class="col-md-12">

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    </div>
    
    <?php ActiveForm::end(); ?>

</div>
</div>
