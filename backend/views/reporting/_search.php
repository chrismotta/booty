<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CampaignLogsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="campaign-logs-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'click_id') ?>

    <?= $form->field($model, 'D_Campaign_id') ?>

    <?= $form->field($model, 'session_hash') ?>

    <?= $form->field($model, 'click_time') ?>

    <?= $form->field($model, 'conv_time') ?>

    <?php // echo $form->field($model, 'revenue') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
