<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model app\models\Clusters */
/* @var $form yii\widgets\ActiveForm */

$staticCampaigns = models\StaticCampaigns::find()->asArray()->all();
$carriers        = models\Carriers::find()->asArray()->all();
?>

<div class="box box-info">
    <div class="box-body">
<div class="clusters-form">

    <?php $form = ActiveForm::begin([
        //'layout' => 'horizontal',
        ]); ?>

    <div class="col-md-6">

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country')->dropDownList($country_list, ['prompt' => '', 'id' => 'countryList']) ?>

    <?= $form->field($model, 'Carriers_id')->dropDownList($carrier_list, ['prompt' => '', 'id' => 'carrierList']) ?>

    <?= $form->field($model, 'connection_type')->dropDownList([ 'Carrier' => 'Carrier', 'wifi' => 'Wifi', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'device_type')->dropDownList([ 'Desktop' => 'Desktop', 'Smartphone' => 'Smartphone', 'Tablet' => 'Tablet', 'Other' => 'Other' ], ['prompt' => '']) ?>

    <?= $form->field($model, 'os')->dropDownList([ 'Android' => 'Android', 'iOS' => 'iOS', 'Windows' => 'Windows', 'BlackBerry' => 'BlackBerry' ], ['prompt' => '']) ?>

    </div>
    <div class="col-md-6">


    <?= $form->field($model, 'os_version')->textInput(['type' => 'text']) ?>

    <?= $form->field($model, 'min_payout')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'assignation_method')->dropDownList([ 'manual' => 'Manual', 'automatic' => 'Automatic', ]) ?>

    <?= 
        '<label class="control-label">Static Campaign</label>';
        echo Select2::widget( [
            'model' => $model,
            'attribute' => 'StaticCampaigns_id',
            'data' => ArrayHelper::map( 
                $staticCampaigns, 
                'id', 
                'name' 
            ),
            'addon' => [
                'contentAfter' => '<div style="height:25px;">&nbsp;</div>',
            ],
            'language' => 'us',
            'options' => [
                'placeholder' => 'Select a static campaign...', 
            ],
            'pluginOptions' => [
                'maximumInputLength' => 50
            ],
        ]);           
    ?>

    <?= $form->field($model, 'autostop_limit')->textInput(['maxlength' => true]) ?> 

    </div>
    <div class="col-md-12">

    <?=         
        Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) 
    ?>

    <div class="form-group">
    </div>

    <?php ActiveForm::end(); ?>

</div>
</div>
