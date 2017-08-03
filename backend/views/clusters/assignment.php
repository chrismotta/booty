<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClustersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$clusterID = $clustersModel->id;
$this->title = 'Cluster #'.$clusterID.' "'.$clustersModel->name.'": Assignment';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Assigned Campaigns</h3>
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
            [
                'attribute' => 'country',
                'filterOptions' => [
                    'class' => isset($clustersModel->country) ? 'filter-disabled' : '',
                ]
            ],
            [
                'attribute' => 'os',
                'filterOptions' => [
                    'class' => isset($clustersModel->os) ? 'filter-disabled' : '',
                ]
            ],
            [
                'attribute' => 'connection_type',
                'label' => 'Conn. Type',
                'filterOptions' => [
                    'class' => isset($clustersModel->connection_type) ? 'filter-disabled' : '',
                ]
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
