<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\grid\EditableColumn;
use kartik\editable\Editable;
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
        var selected = $("#assigned").yiiGridView("getSelectedRows");
        var href = "unassigncampaign?id='.$clusterID.'&cid=["+selected.toString()+"]";
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
            'active' => true,
        ],
        [
            'label' => '<i class="glyphicon glyphicon-star-empty"></i><span class="tag-title">Available</span>',
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
        'layout' => '<div style="float:left;">{pager} &nbsp; </div>
            <div style="float:right;margin: 20px">'.
            Html::button('Unassign All Selected', [
                'class' => 'btn',
                'id'    => 'bulkAssignment',
            ]).'
            </div>
            <div style="clear:both;">{items}</div>',
        'condensed' => true,
        'columns' => [
            'id',
            [
                'attribute'=>'affiliateName',
                'value'=>'affiliates.name',
            ],
            [
                'attribute'=>'name',
                'contentOptions' => [
                    'class'=>'wrap-long-names'
                ],
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
                'class' => 'kartik\grid\EditableColumn',
                'attribute' => 'delivery_freq',
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
                'mergeHeader' => true,
                // 'value'     =>function($model, $key, $index){
                //     return $model->getDeliveryFreq();
                // },
            ],
            
            [
                'class' => '\kartik\grid\DataColumn',
                'header' => '<span class="glyphicon glyphicon-alert text-default"></span>',
                'format'=>'html',
                'value' => function($model, $key, $index){
                    
                    if($model->delivery_freq == 0)
                        $lastLog = $model->getLastLog();

                    if(isset($lastLog) && $lastLog->status='no_conv_limit')
                        $return = '<span class="glyphicon glyphicon-alert" style="color:#f90" data-toggle="tooltip" title="20K LIMIT EXCEEDED"></span>';
                    else if($model->status!='active')
                        $return = '<span class="glyphicon glyphicon-alert" style="color:#0C0" data-toggle="tooltip" title="'.strtoupper($model->status).'"></span>';
                    else 
                        $return = '';

                    return $return;
                },
                'vAlign' => 'middle',
                'mergeHeader' => true,
            ],

            [
                'class' => '\kartik\grid\ActionColumn',
                'width'  => '40px',
                'template' => '{unassigncampaign}{blacklist}',
                'header' => '<span class="glyphicon glyphicon-minus"></span>',

                'buttons' => [
                    'unassigncampaign' => function ($url, $model, $key) use ($clusterID) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-minus"></span>', 
                            ['unassigncampaign', 'cid'=>$key, 'id'=>$clusterID]
                            );
                    },
                    'blacklist' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-ban-circle"></span>', 
                            ['pubidblacklistiframe', 'id'=>$key],
                            [
                                'title' => 'Pubid Blacklist',
                                'data-toggle'=>'modal',
                                'data-target'=>'#blacklist',
                            ]
                        );
                    },
                ]
            ],

            [
                'class' => '\kartik\grid\CheckboxColumn',
                'width'  => '40px',
                // 'checkboxOptions' => 
                // function($model, $key, $index, $column){
                //     if(!isset($model->app_id))
                //         return ['disabled'=>'disabled'];           
                //     else if(!json_decode($model->app_id))
                //         return ['disabled'=>'disabled'];           
                //     else
                //         return [];
                // },
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

<div class="modal remote fade" id="blacklist" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content loader-lg"></div>
        </div>
</div>