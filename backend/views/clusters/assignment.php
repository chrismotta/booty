<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\grid\EditableColumn;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClustersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$clusterID = $clustersModel->id;
$this->title = 'Cluster #'.$clusterID.' "'.$clustersModel->name.'": Assignment';
$this->params['breadcrumbs'][] = ['label' => 'Clusters', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Assigned Campaigns</h3>
        <div class="box-tools">
          <!-- This will cause the box to collapse when clicked -->
          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
<?php // Pjax::begin(); ?>    
<?= GridView::widget([
        'id' => 'assigned',
        'dataProvider' => $assignedProvider,
        // 'filterModel' => $assignedModel,
        'layout' => '{items}',
        'columns' => [
            'id',
            [
                'attribute'=>'affiliateName',
                'value'=>'affiliates.name',
            ],
            'name',

            [
                'class' => 'kartik\grid\EditableColumn',
                'editableOptions'=> function ($model, $key, $index, $widget) {
                    return [
                        'id' => 'delivery_freq',
                        'formOptions' => [
                            'action' => ['changefreq', 
                            'Clusters_id'   => $model->clusters_id,
                        ]],
                    ];
                },
                'attribute' =>'delivery_freq',
                'value'     =>function($model, $key, $index){
                    return $model->getDeliveryFreq();
                },
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

<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">Available Campaigns</h3>
        <div class="box-tools">
          <!-- This will cause the box to collapse when clicked -->
          <button class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">

<?php // Pjax::begin(); ?>    
<?= GridView::widget([
        'id' => 'available',
        'dataProvider' => $availableProvider,
        'filterModel' => $availableModel,
        'layout' => '{items}{pager}',
        'columns' => [
            'id',
            [
                'attribute'=>'affiliateName',
                'value'=>'affiliates.name',
            ],
            'name',
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
            [
                'attribute' => 'carrier',
                'filterOptions' => [
                    'class' => isset($clustersModel->carriers->carrier_name) ? 'filter-disabled' : '',
                ],
                'format'=>'html',
                'value' => function($model, $key, $index){
                    return $model->formatValues('carrier', 'warning');
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{assigncampaign}',

                'buttons' => [
                    'assigncampaign' => function ($url, $model, $key) use ($clusterID) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-plus"></span>', 
                            ['assigncampaign', 'cid'=>$key, 'id'=>$clusterID]
                            );
                    },
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
