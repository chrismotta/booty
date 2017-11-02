<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\KeywordBlacklist */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="keyword-blacklist-form">

    <?php $form = ActiveForm::begin([
    	'options' => ['class' => 'form-inline'],
	]); ?>

    <?= $form->field($model, 'keyword')->textInput(['maxlength' => true]) ?>

    <div class="form-group" style="vertical-align: top;">
        <?= Html::submitButton($model->isNewRecord ? 'Add' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
