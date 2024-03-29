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

$disabled = isset($_GET['dev']) && $_GET['dev']==1 ? false : true;
?>

<div class="box box-info">
    <div class="box-body">
<div class="campaigns-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

    <?= 
        '<label class="control-label">Affiliate</label>';
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
                'disabled' => $disabled
            ],
            'pluginOptions' => [
                'maximumInputLength' => 50
            ],
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'payout')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'aff_daily_cap')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'daily_cap')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'connection_type')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'device_type')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'ext_id')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    </div>
    <div class="col-md-6">
    
    <?= $form->field($model, 'os')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>
    
    <?= $form->field($model, 'os_version')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'carrier')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'landing_url')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'creative_320x50')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'creative_300x250')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

    <?= $form->field($model, 'app_id')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>
    </div>


    <div class="form-group col-md-12">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
</div>
