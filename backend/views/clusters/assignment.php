<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClustersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Cluster #'.$clustersModel->id.' "'.$clustersModel->name.'": Assaignment';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">

<div class="clusters-index col-sm-12 col-md-8">
<h4>Available</h4>
<?php Pjax::begin(); ?>    <?= GridView::widget([
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
                'class' => 'yii\grid\ActionColumn',
                'template' => '{add}',

                'buttons' => [
                    'add' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-plus"></span>', 
                            ['add', 'id'=>$key],
                            [
                                'class' => 'grid-button',
                            ]
                            );
                    },
                ]
            ]
        ],
    ]); ?>
<?php Pjax::end(); ?>
</div>


<div class="clusters-index col-sm-12 col-md-4">
<h4>Assigned</h4>
<?php Pjax::begin(); ?>    <?= GridView::widget([
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
                'template' => '{remove}',

                'buttons' => [
                    'remove' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-minus"></span>', 
                            ['remove', 'id'=>$key],
                            [
                                'class' => 'grid-button',
                            ]
                            );
                    },
                ]
            ]
        ],
    ]); ?>
<?php Pjax::end(); ?></div>

</div>
