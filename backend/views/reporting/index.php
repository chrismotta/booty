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


$this->title = 'Reporting';
$this->params['breadcrumbs'][] = $this->title;


$searchModel->date_start = isset($_GET['CampaignLogsSearch']['date_start']) ? $_GET['CampaignLogsSearch']['date_start'] : date( 'd-m-Y' );
$searchModel->date_end = isset($_GET['CampaignLogsSearch']['date_end']) ? $_GET['CampaignLogsSearch']['date_end'] : date( 'd-m-Y' );

$DPlacement       = models\DPlacement::find()->asArray()->all();
$DCampaign        = models\DCampaign::find()->asArray()->all();

$clusterNames     = components\MapHelper::redisToMap(\Yii::$app->redis->hgetall( 'clusternames' ));
$devices          = \Yii::$app->redis->smembers( 'devices' );
$deviceBrands     = \Yii::$app->redis->smembers( 'device_brands' );
$deviceModels     = \Yii::$app->redis->smembers( 'device_models' );
$os               = \Yii::$app->redis->smembers( 'os' );
$osVersions       = \Yii::$app->redis->smembers( 'os_versions' );
$browsers         = \Yii::$app->redis->smembers( 'browsers' );
$browserVersions  = \Yii::$app->redis->smembers( 'browser_versions' );
$countries        = \Yii::$app->redis->smembers( 'countries' );
$carriers         = \Yii::$app->redis->smembers( 'carriers' );
$pubIds           = \Yii::$app->redis->smembers( 'pub_ids' );
$exchangeIds      = \Yii::$app->redis->smembers( 'exchange_ids' );
$subpubIds        = \Yii::$app->redis->smembers( 'subpub_ids' );
$deviceIds        = \Yii::$app->redis->smembers( 'device_ids' );

$params = Yii::$app->request->get();
$onloadJs = '
    $("document").ready(function(){ 
        $("#filtersform").on("pjax:end", function() {
            $.pjax.reload({container:"#results"});  //Reload GridView
        });
    });
';

if ( isset($params['CampaignLogsSearch']['fields_group1']) && !empty($params['CampaignLogsSearch']['fields_group1']) )
    $columns = $params['CampaignLogsSearch']['fields_group1'];
else
    $columns = ['campaign'];


if ( isset($params['CampaignLogsSearch']['fields_group2']) && !empty($params['CampaignLogsSearch']['fields_group2']) )
    $columns = array_merge( $columns, $params['CampaignLogsSearch']['fields_group2'] );


if ( isset($params['CampaignLogsSearch']['fields_group3']) && !empty($params['CampaignLogsSearch']['fields_group3']) )
    $columns =  array_merge( $columns, $params['CampaignLogsSearch']['fields_group3'] );
else
    $columns = array_merge( $columns, ['imps'] );

foreach ( $columns as $column )
{
    $onloadJs .= '
        $("#chkbut_'.$column.'").addClass("active");
        $("#chkinp_'.$column.'").prop("checked", true);
    ';
}

$this->registerJs(
   '$("document").ready(function(){ '.$onloadJs.'});'
);
?>

<div class="campaign-logs-index">

    <?= $this->render('_form', [
        'model'       => $model,
        'searchModel' => $searchModel,
        'DPlacement'  => $DPlacement,
        'DCampaign'   => $DCampaign,
        'clusterNames'=> $clusterNames,
        'countries'   => $countries,
        'carriers'    => $carriers,
        'devices'     => $devices,
        'deviceBrands'=> $deviceBrands,
        'deviceModels'=> $deviceModels,
        'os'          => $os,
        'osVersions'  => $osVersions,
        'browsers'    => $browsers,
        'browserVersions' => $browserVersions,
        'pubIds'      => $pubIds,
        'exchangeIds' => $exchangeIds,
        'subpubIds'   => $subpubIds,
        'deviceIds'   => $deviceIds,
        'params'      => $params
    ]) ?>


<?php
// only after submit
if(isset($dataProvider)){
    $totals = $totalsProvider->getModels();
?>
    
    <div>
    <?=
        ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'fontAwesome' => true,
            'exportConfig'  => [
                ExportMenu::FORMAT_EXCEL     => false,
            ]
        ]);
    ?>
    </div>


    <div style="overflow-x:scroll;">
    <?php 
        Pjax::begin( ['id' => 'results'] );

        foreach ( $columns as $p => $column )
        {
            switch ($column)
            {
                case 'imps':
                case 'convs':
                case 'clicks':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? $totals[0][$column] : null,
                    ];
                break;                   
                case 'revenue':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$ '.number_format($totals[0][$column],6) : null,
                        'value' => function($model, $key, $index, $widget) {
                          return '$ '.number_format($model->revenue,6);
                        },
                    ];
                break;            
                case 'cost':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$ '.number_format($totals[0][$column],6) : null,
                        'value' => function($model, $key, $index, $widget) {
                          return '$ '.number_format($model->cost,6);
                        },
                    ];
                break;
            }
        }
    ?>    
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'showFooter' => true,
        ]); ?>
    <?php Pjax::end(); ?>
        
    </div>

<?php } //end if ?>

</div>