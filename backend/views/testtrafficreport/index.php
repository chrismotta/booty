<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use backend\models;
use backend\components;
use yii\bootstrap;
use kartik\daterange\DateRangePicker;
use kartik\export\ExportMenu;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampaignLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Test Traffic Reporting';
$this->params['breadcrumbs'][] = $this->title;



$params = Yii::$app->request->get();

$onloadJs = '
    $("document").ready(function(){ 
        $("#filtersform").on("pjax:end", function() {
            $.pjax.reload({container:"#results"});  //Reload GridView
        });
    });
';


$this->registerJs(
   '$("document").ready(function(){ '.$onloadJs.'});'
);
?>

<div class="campaign-logs-index">


<?php
// only after submit
if(isset($dataProvider)){
    //$totals = $totalsProvider->getModels();
?>

    <div style="overflow-x:scroll;">
    <?php 
        Pjax::begin( ['id' => 'results'] );
    ?>    
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [ 'affiliate', 'campaign', 'clicks', 'convs' ],
            //'showFooter' => true,
        ]); ?>
    <?php Pjax::end(); ?>
        
    </div>

<?php } //end if ?>

</div>