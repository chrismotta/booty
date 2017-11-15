<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PubidBlacklist */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pubid-blacklist-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'Campaigns_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'blacklist')->textarea(['rows' => 18])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
