<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Publishers */
/* @var $form yii\widgets\ActiveForm */

$users = User::find()->asArray()->all();
?>

<div class="box box-info">
    <div class="box-body">
<div class="publishers-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-6">

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'short_name')->textInput(['maxlength' => true]) ?>

    </div>
    <div class="col-md-6">
    
    <?= 
        '<label class="control-label">Admin User</label>';
        echo Select2::widget( [
            'model' => $model, 
            'attribute' => 'admin_user',
            'data' => ArrayHelper::map( 
                $users, 
                'id', 
                'username' 
            ),
            'addon' => [
                'contentAfter' => '<div style="height:25px;">&nbsp;</div>',
            ],            
            'language' => 'us',
            'options' => [
                'placeholder' => 'Select a publisher...', 
            ],
            'pluginOptions' => [
                'maximumInputLength' => 50
            ],
        ]);            
    ?>

    <?= $form->field($model, 'macros', [

        ])->textInput(['maxlength' => true]) ?>

    </div>
    <div class="col-md-12">

    <div class="form-group">

        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>
