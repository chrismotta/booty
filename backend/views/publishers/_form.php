<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Publishers */
/* @var $form yii\widgets\ActiveForm */

$users = models\User::find()->asArray()->all();
?>

<div class="publishers-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'short_name')->textInput(['maxlength' => true]) ?>

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

    <div class="form-group">

        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
