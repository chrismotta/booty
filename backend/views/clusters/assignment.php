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
            'label' => '<i class="glyphicon glyphicon-star"></i> Assigned',
            'encode' => false,
            'active' => true,
        ],
        [
            'label' => '<i class="glyphicon glyphicon-star-empty"></i> Available',
            'encode' => false,
            'url' => ['available', 'id'=>$clusterID],
        ],
    ],
]);

?>

<div class="box box-info">
    <div class="box-body">
<?php // Pjax::begin(); ?>    
<?= GridView::widget([
        'id' => 'assigned',
        'dataProvider' => $assignedProvider,
        'filterModel' => $assignedModel,
        'layout' => '{pager}{items}',
        'condensed' => true,
        'columns' => [
            'id',
            [
                'attribute'=>'affiliateName',
                'value'=>'affiliates.name',
            ],
            'name',

            [
                'class' => 'kartik\grid\EditableColumn',
                'attribute' =>'delivery_freq',
                'editableOptions'=> function ($model, $key, $index, $widget) {
                    return [
                        'id' => 'delivery_freq',
                        'formOptions' => [
                            'action' => ['changefreq', 
                            'Clusters_id'   => $model->clusters_id,
                        ]],
                    ];
                },
                'refreshGrid' => true,
                // 'value'     =>function($model, $key, $index){
                //     return $model->getDeliveryFreq();
                // },
            ],

            'payout',
            [
                'attribute'=>'country',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('country', 'success');
                }
            ],
            [
                'attribute'=>'connection_type',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('connection_type', 'primary');
                }
            ],
            [
                'attribute'=>'device_type',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('device_type', 'danger');
                }
            ],
            [
                'attribute'=>'os',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('os', 'info');
                }
            ],
            [
                'attribute'=>'os_version',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('os_version', 'default');
                }
            ],
            [
                'attribute'=>'carrier',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('carrier', 'warning');
                }
            ],

            [
                'class' => '\kartik\grid\DataColumn',
                'header' => '<span class="glyphicon glyphicon-alert text-success"></span>',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    
                    if($model->status!='active')
                        $return = '<span class="glyphicon glyphicon-alert text-success" data-toggle="tooltip" title="PAUSED BY AFF"></span>';
                    else 
                        $return = '';

                    return $return;
                },
                'vAlign' => 'middle',
                'mergeHeader' => true,
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{unassigncampaign}',

                'buttons' => [
                    'unassigncampaign' => function ($url, $model, $key) use ($clusterID) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-minus"></span>', 
                            ['unassigncampaign', 'cid'=>$key, 'id'=>$clusterID]
                            );
                    }
                ]
            ]
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
