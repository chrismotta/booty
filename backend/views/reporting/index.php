<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\jui\DatePicker;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampaignLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->registerJs(
   '$("document").ready(function(){ 
        $("#filters_form").on("pjax:end", function() {
            $.pjax.reload({container:"#results"});  //Reload GridView
        });
    });'
);
$this->title = 'Reporting';
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="campaign-logs-index">

 
<?php yii\widgets\Pjax::begin(['id' => 'filters_form']) ?>
<?php $form = ActiveForm::begin(['options' => ['data-pjax' => true ]]); ?>

 
    <?=
        $form->field($searchModel, 'date_start')->widget(\yii\jui\DatePicker::classname(), [
        //'language' => 'ru',
        //'dateFormat' => 'yyyy-MM-dd',
        ]);            
    ?>
 
     <?=
        $form->field($searchModel, 'date_end')->widget(\yii\jui\DatePicker::classname(), [
        //'language' => 'ru',
        //'dateFormat' => 'yyyy-MM-dd',
        ]);            
    ?>
 
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
 
<?php ActiveForm::end(); ?>
<?php yii\widgets\Pjax::end() ?>

</div>


<?php Pjax::begin( ['id' => 'results'] ); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            //'click_id',
            //'D_Campaign_id',
            //'session_hash',
            //'click_time',
            //'conv_time',
             [
             'attribute' => 'campaign',
             'value' => 'campaign.name'
             ],
             [
             'attribute' => 'affiliate',
             'value' => 'campaign.Affiliates_name'
             ],               
             [
             'attribute' => 'publisher',
             'value' => 'publisher'
             ],
             [
             'attribute' => 'model',
             'value' => 'model'
             ],
             [
             'attribute' => 'status',
             'value' => 'status'
             ],
             [
             'attribute' => 'country',
             'value' => 'clusterLog.country'
             ],
             [
             'attribute' => 'connection_type',
             'value' => 'clusterLog.connection_type'
             ],
             [
             'attribute' => 'carrier',
             'value' => 'clusterLog.carrier'
             ],
             /*             
             [
             'attribute' => 'device',
             'value' => 'clusterLog.device'
             ],
             [
             'attribute' => 'device_brand',
             'value' => 'clusterLog.device_brand'
             ],             
             [
             'attribute' => 'device_model',
             'value' => 'clusterLog.device_model'
             ],
             [
             'attribute' => 'os',
             'value' => 'clusterLog.os'
             ],
             [
             'attribute' => 'os_version',
             'value' => 'clusterLog.os_version'
             ],
             [
             'attribute' => 'browser',
             'value' => 'clusterLog.browser'
             ],
             [
             'attribute' => 'browser_version',
             'value' => 'clusterLog.browser_version'
             ],
             */
             [
             'attribute' => 'cost',
             'value' => 'clusterLog.cost'
             ],
             [
             'attribute' => 'imps',
             'value' => 'clusterLog.imps'
             ],
            'revenue',
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
