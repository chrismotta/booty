<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model app\models\Placements */
/* @var $form yii\widgets\ActiveForm */

$publishers       = models\Publishers::find()->asArray()->all();
$clusters         = models\Clusters::find()->asArray()->all();
?>

<div class="placements-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= 
        '<label class="control-label">Publisher</label>';
        echo Select2::widget( [
            'model' => $model,
            'attribute' => 'Publishers_id',
            'name' => 'publisher',
            'data' => ArrayHelper::map( 
                $publishers, 
                'id', 
                'name' 
            ),
            'language' => 'us',
            'options' => [
                'placeholder' => 'Select a publisher...', 
            ],
            'pluginOptions' => [
                'maximumInputLength' => 50
            ],
        ]);            
    ?>

    <?= 
        '<label class="control-label">Cluster</label>';
        echo Select2::widget( [
            'model' => $model,
            'attribute' => 'Clusters_id',
            'name' => 'cluster',
            'data' => ArrayHelper::map( 
                $clusters, 
                'id', 
                'name' 
            ),
            'language' => 'us',
            'options' => [
                'placeholder' => 'Select a cluster...', 
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'maximumInputLength' => 50
            ],
        ]);            
    ?>    

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'frequency_cap')->textInput() ?>

    <?= $form->field($model, 'payout')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'model')->dropDownList([ 'CPM' => 'CPM', 'RS' => 'RS', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'size')->dropDownList([ '300x250' => '300x250', '320x50' => '320x50', ], ['prompt' => '']) ?>

    <?php 
        $model->health_check_imps = 10000;
        $model->status = 'health_check';
    ?>

    <?= $form->field($model, 'status')->dropDownList([ 'health_check' => 'Health check', 'active' => 'Active', 'testing' => 'Testing', 'paused' => 'Paused', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'health_check_imps')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
