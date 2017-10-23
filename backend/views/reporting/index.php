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

// debugeando!

// switch to ajax

$clusters     = [];//components\MapHelper::filtersFromRedisToSelectWidget( \Yii::$app->redis->zrange( 'clusters', 0, -1 ) );
$campaigns     = [];//components\MapHelper::filtersFromRedisToSelectWidget( \Yii::$app->redis->zrange( 'campaigns', 0, -1 ) );
$affiliates     = [];//components\MapHelper::filtersFromRedisToSelectWidget( \Yii::$app->redis->zrange( 'affiliates', 0, -1 ) );
$publishers     = [];//components\MapHelper::filtersFromRedisToSelectWidget( \Yii::$app->redis->zrange( 'publishers', 0, -1 ) );
$placements     = [];//components\MapHelper::filtersFromRedisToSelectWidget( \Yii::$app->redis->zrange( 'placements', 0, -1 );


// redis
$impStatus = [ 
    'no_offer',
    'health_check',
    'testing',
    'banned',
    'inc_targeting',
    'print'
];

$devices          = \Yii::$app->redis->zrange( 'devices', 0, -1 );
$deviceBrands     = \Yii::$app->redis->zrange( 'device_brands', 0, -1 );
$deviceModels     = \Yii::$app->redis->zrange( 'device_models', 0, -1 );
$os               = \Yii::$app->redis->zrange( 'os', 0, -1 );
$osVersions       = \Yii::$app->redis->zrange( 'os_versions', 0, -1 );
$browsers         = \Yii::$app->redis->zrange( 'browsers', 0, -1 );
$browserVersions  = \Yii::$app->redis->zrange( 'browser_versions', 0, -1 );
$countries        = \Yii::$app->redis->zrange( 'countries', 0, -1 );
$carriers         = \Yii::$app->redis->zrange( 'carriers', 0, -1 );
$pubIds           = [];//\Yii::$app->redis->zrange( 'pub_ids', 0, \Yii::$app->redis->zcard('pub_ids') );
$exchangeIds      = [];//\Yii::$app->redis->zrange( 'exchange_ids', 0, \Yii::$app->redis->zcard('exchange_ids') );
$subpubIds        = [];//\Yii::$app->redis->zrange( 'subpub_ids', 0, \Yii::$app->redis->zcard('subpub_ids') );
$deviceIds        = [];//\Yii::$app->redis->zrange( 'device_ids', 0, \Yii::$app->redis->zcard('device_ids') );


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

if(isset($dataProvider))
    $afterSubmit = true;
else
    $afterSubmit = false;
?>

<div class="campaign-logs-index">

    <?= $this->render('_form', [
        'model'       => $model,
        'searchModel' => $searchModel,
        'affiliates'  => $affiliates,
        'placements'  => $placements,
        'publishers'  => $publishers,
        'campaigns'   => $campaigns,
        'clusters'    => $clusters,
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
        'params'      => $params,
        'afterSubmit' => $afterSubmit,
        'impStatus'   => $impStatus,
    ]) ?>


<?php
// only after submit
if($afterSubmit){
    $totals = $totalsProvider->getModels();
?>
    
    <div>

    <?=
    /*
        ExportMenu::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'fontAwesome' => true,
            'exportConfig'  => [
                ExportMenu::FORMAT_EXCEL     => false,
            ]
        ]);
    */
    '' ?>
    </div>


    <div style="overflow-x:scroll;">
    <?php 
        Pjax::begin( ['id' => 'results'] );

        foreach ( $columns as $p => $column )
        {
            switch ($column)
            {
                case 'cluster':
                    $columns[$p] = [
                        'attribute' => $column,
                        'value' => function($model, $key, $index, $widget) {
                            $value = $model->cluster ? $model->cluster . ' ('.$model->cluster_id.')' : null;
                            return $value;
                        },
                    ];  
                break;                
                case 'publisher':
                    $columns[$p] = [
                        'attribute' => $column,
                        'value' => function($model, $key, $index, $widget) {
                            if($model->publisher){
                                if(in_array('Stakeholder', $model->userroles))
                                    $value = $model->publisher_id;
                                else
                                    $value = $model->publisher . ' ('.$model->publisher_id.')';
                            }else
                                $value = null;

                            return $value;
                        },
                    ];  
                break;                
                case 'affiliate':
                    $columns[$p] = [
                        'attribute' => $column,
                        'value' => function($model, $key, $index, $widget) {
                            $value = $model->affiliate ? $model->affiliate . ' ('.$model->affiliate_id.')' : null;
                            return $value;
                        },
                    ];  
                break;                
                case 'placement':
                    $columns[$p] = [
                        'attribute' => $column,
                        'value' => function($model, $key, $index, $widget) {
                            if($model->placement){
                                if(in_array('Stakeholder', $model->userroles))
                                    $value = $model->placement_id;
                                else
                                    $value = $model->placement . ' ('.$model->placement_id.')';
                            }else
                                $value = null;
                            // $value = $model->placement ? $model->placement . ' ('.$model->placement_id.')' : null;
                            return $value;
                        },
                    ];                
                break;                
                case 'campaign':
                    $columns[$p] = [
                        'attribute' => $column,
                        'value' => function($model, $key, $index, $widget) {
                            $value = $model->campaign ? $model->campaign . ' ('.$model->campaign_id.')' : null;
                            return $value;
                        },
                    ];                
                break;
                case 'imps':
                case 'convs':
                case 'clicks':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? $totals[0][$column] : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                    ];
                break; 
                case 'profit':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                        'value' => function($model, $key, $index, $widget) {
                          return '% '.number_format($model->profit,2);
                        },
                    ];
                break;                
                case 'profit_ecpm':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                        'value' => function($model, $key, $index, $widget) {
                          return '% '.number_format($model->profit_ecpm,2);
                        },
                    ];
                break;                  
                case 'cost_ecpm':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                        'value' => function($model, $key, $index, $widget) {
                          return '% '.number_format($model->cost_ecpm,2);
                        },
                    ];
                break;                 
                case 'revenue_ecpm':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                        'value' => function($model, $key, $index, $widget) {
                          return '% '.number_format($model->revenue_ecpm,2);
                        },
                    ];
                break;                                   
                case 'conv_rate':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '%&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                        'value' => function($model, $key, $index, $widget) {
                          return '% '.number_format($model->conv_rate,2);
                        },
                    ];
                break; 
                case 'revenue':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],                        
                        'value' => function($model, $key, $index, $widget) {
                          return '$ '.number_format($model->revenue,2);
                        },
                    ];
                break;                             
                case 'cost':
                    $columns[$p] = [
                        'attribute' => $column,
                        'footer'    => isset($totals[0]) ? '$&nbsp'.number_format($totals[0][$column],2) : null,
                        'footerOptions' => [
                            'style' => 'font-weight:bold;'
                        ],
                        'value' => function($model, $key, $index, $widget) {
                          return '$ '.number_format($model->cost,2);
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

    <p style="with:100%;text-align: right;margin-top:10px;">Time Elapsed: <?php echo time() - $startTime; ?> sec.</p>
<?php } //end if ?>

</div>