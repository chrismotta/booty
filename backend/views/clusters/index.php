<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClustersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Clusters';
$this->params['breadcrumbs'][] = $this->title;

$carriers   = models\Carriers::find()->asArray()->all();
$filterByCarrier = ArrayHelper::map( 
    $carriers, 
    'id', 
    'carrier_name' 
);

?>
<div class="clusters-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Clusters', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => \Yii::$app->params['gridDefalutLayout'],
        'columns' => [

            'id',
            'name',
            'country',
            'connection_type',
            'device_type',
            'os',
            'os_version',            
            // 'StaticCampaigns_id',
            [
                'attribute' => 'carrier',
                'label'     => 'Carrier',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'Carriers_id',
                    'data' => $filterByCarrier,
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'options' => [
                        'placeholder' => 'Select a carrier...',
                    ]
                ]),                
            ],
            [
                'class' => '\kartik\grid\ActionColumn',
                'width' => '90px',
                // 'dropdown' => true,
                // 'dropdownOptions' => ['class' => 'pull-right'],
                'template' => '{view} {update} {assignment} {delete}',
                'buttons' => [
                    'assignment' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-th-list"></span>', 
                            [
                            'clusters/assignment',
                            'id'=>$key,
                            'CampaignsSearch'=>[
                                'country'=>$model->country,
                                'os'=>$model->os,
                                'connection_type'=>$model->connection_type,
                                ],
                            ]
                            );
                    },
                ],
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
