<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Clusters */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="clusters-form">

    <?php $form = ActiveForm::begin([
        'layout' => 'horizontal',
        ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'Placements_id')->textInput() ?>

    <?= $form->field($model, 'country')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'connection_type')->dropDownList([ '3g' => '3g', 'wifi' => 'Wifi', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'carrier')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'StaticCampaigns_id')->textInput() ?>

        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    <div class="form-group">
    </div>

    <?php ActiveForm::end(); ?>

</div>
