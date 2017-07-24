<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model app\models\Campaigns */
/* @var $form yii\widgets\ActiveForm */

$affiliates = models\Affiliates::find()->asArray()->all();
?>

<div class="campaigns-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= 
        '<label class="control-label">Admin User</label>';
        echo Select2::widget( [
            'model' => $model, 
            'attribute' => 'Affiliates_id',
            'data' => ArrayHelper::map( 
                $affiliates, 
                'id', 
                'name' 
            ),
            'addon' => [
                'contentAfter' => '<div style="height:25px;">&nbsp;</div>',
            ],            
            'language' => 'us',
            'options' => [
                'placeholder' => 'Select an affiliate...', 
            ],
            'pluginOptions' => [
                'maximumInputLength' => 50
            ],
        ]);            
    ?>

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
