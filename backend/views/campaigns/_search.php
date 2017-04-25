<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CampaignsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campaigns-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'Affiliates_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'payout') ?>

    <?= $form->field($model, 'landing_url') ?>

    <?php // echo $form->field($model, 'creative_320x50') ?>

    <?php // echo $form->field($model, 'creative_300x250') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
