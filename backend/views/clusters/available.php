<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\grid\EditableColumn;
use yii\widgets\Pjax;
use yii\web\View;
use yii\helpers\Url;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClustersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$clusterID = $clustersModel->id;
$this->title = 'Cluster #'.$clusterID.' "'.$clustersModel->name.'": Assignment';
$this->params['breadcrumbs'][] = ['label' => 'Clusters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(
    '$("#bulkAssignment").on("click", function() { 
        var selected = $("#available").yiiGridView("getSelectedRows");
        var href = "assigncampaign?id='.$clusterID.'&cid=["+selected.toString()+"]";
        // console.log(href);
        window.location.href = href;
    });',
    View::POS_READY,
    'bulkAssignment'
);

?>

<?php

echo Tabs::widget([
    'items' => [
        [
            'label' => '<i class="glyphicon glyphicon-star"></i><span class="tag-title">Assigned</span>',
            'encode' => false,
            'url' => ['assignment', 'id'=>$clusterID],
        ],
        [
            'label' => '<i class="glyphicon glyphicon-star-empty"></i><span class="tag-title">Available</span>',
            'encode' => false,
            'active' => true,
        ],
    ],
]);

?>

<div class="box box-danger">
    <div class="box-body">

<?php // Pjax::begin(); ?>    
<?= GridView::widget([
        'id' => 'available',
        'dataProvider' => $availableProvider,
        'filterModel' => $availableModel,

        'condensed' => true,
        'showPageSummary' => true,
        'layout' => '<div style="float:left;">{pager} &nbsp; </div>
            <div style="float:right;margin: 20px">'.
            Html::button('Assign All Selected', [
                'class' => 'btn',
                'id'    => 'bulkAssignment',
            ]).'
            </div>
            <div style="clear:both;">{items}</div>',
        // 'layout' => '{pager}{items}',
        
        'columns' => [
            'id',
            [
                'attribute'=>'ext_id',
                'format'=>'html',
                'value' => function ($model, $key, $index, $column){
                    return '<div class="ext_id_column" title="'.$model->ext_id.'">'.$model->ext_id.'</div>';
                }
            ],
            [
                'attribute'=>'affiliateName',
                'value'=>'affiliates.name',
            ],
            [
                'attribute'=>'name',
                'contentOptions' => [
                    'class'=>'wrap-long-names',
                    'style'=>['max-width'=>'120px']
                ],
            ],
            [
                'attribute'=>'app_id',
                'contentOptions' => [
                    'class'=>'wrap-long-names',
                    'style'=>['max-width'=>'120px'],
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('app_id', 'default');
                }
            ],
            'payout',
            [
                'attribute' => 'country',
                'filterOptions' => [
                    'class' => isset($clustersModel->country) ? 'filter-disabled' : '',
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('country', 'success');
                }
            ],
            [
                'attribute' => 'connection_type',
                'label' => 'Conn. Type',
                'filterOptions' => [
                    'class' => isset($clustersModel->connection_type) ? 'filter-disabled' : '',
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('connection_type', 'primary');
                }
            ],
            [
                'attribute' => 'device_type',
                'filterOptions' => [
                    'class' => isset($clustersModel->device_type) ? 'filter-disabled' : '',
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('device_type', 'danger');
                }
            ],
            [
                'attribute' => 'os',
                'filterOptions' => [
                    'class' => isset($clustersModel->os) ? 'filter-disabled' : '',
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('os', 'info');
                }
            ],
            [
                'attribute' => 'os_version',
                'filterOptions' => [
                    'class' => isset($clustersModel->os_version) ? 'filter-disabled' : '',
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('os_version', 'default');
                }
            ],
            // [
            //     'attribute' => 'carrier',
            //     'filterOptions' => [
            //         'class' => isset($clustersModel->carriers->carrier_name) ? 'filter-disabled' : '',
            //     ],
            //     'format'=>'html',
            //     'value' => function($model, $key, $index){
            //         return $model->formatValues('carrier', 'warning');
            //     }
            // ],

            // [
            //     'class' => '\kartik\grid\DataColumn',
            //     'header' => '<span class="glyphicon glyphicon-alert text-warning"></span>',
            //     'format'=>'html',
            //     'value' => function($model, $key, $index){
                    
            //         if(!isset($model->app_id))
            //             $return = '<span class="glyphicon glyphicon-alert text-warning" data-toggle="tooltip" title="NO APP_ID SET"></span>';
            //         else 
            //             $return = '';

            //         return $return;
            //     },
            //     'vAlign' => 'middle',
            //     'mergeHeader' => true,
            // ],

            [
                'class' => '\kartik\grid\ActionColumn',
                'width'  => '40px',
                'template' => '{assigncampaign}',
                'header' => '<span class="glyphicon glyphicon-plus"></span>',

                'buttons' => [
                    'assigncampaign' => function ($url, $model, $key) use ($clusterID) {
                            
                        if(!isset($model->app_id)){
                            
                            return '<span class="glyphicon glyphicon-alert text-danger" data-toggle="tooltip" title="APP_ID NOT SET"></span>';

                        
                        }else if(!json_decode($model->app_id)){

                            return '<span class="glyphicon glyphicon-alert text-warning" data-toggle="tooltip" title="APP_ID NOT VALID"></span>';

                        }else{

                            return Html::a(
                                '<span class="glyphicon glyphicon-plus"></span>', 
                                ['assigncampaign', 'cid'=>$key, 'id'=>$clusterID]
                                );
                       
                        }
                    },
                ]
            ],

            [
                'class' => '\kartik\grid\CheckboxColumn',
                'width'  => '40px',
                'checkboxOptions' => 
                function($model, $key, $index, $column){
                    if(!isset($model->app_id))
                        return ['disabled'=>'disabled'];           
                    else if(!json_decode($model->app_id))
                        return ['disabled'=>'disabled'];           
                    else
                        return [];
                },
            ],
        ],
    ]); ?>
<?php // Pjax::end(); ?>

</div></div>


<div class="row">

<div class="clusters-index col-sm-12 col-md-8">

</div>

<div class="clusters-index col-sm-12 col-md-4">

</div>

</div>
