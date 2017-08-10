<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\CampaignsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Campaigns';
$this->params['breadcrumbs'][] = $this->title;

$affiliates = models\Affiliates::find()->asArray()->all();
// $carriers   = models\Carriers::find()->asArray()->all();

$filterByAffiliate = ArrayHelper::map( 
    $affiliates, 
    'id', 
    'name' 
);

// $filterByCarrier = ArrayHelper::map( 
//     $carriers, 
//     'id', 
//     'carrier_name' 
// );

?>
    

<div class="campaigns-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Campaigns', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '<div class="box box-info"><div class="box-header">{summary}</div><div class="box-body">{items}</div><div class="box-footer">{pager}</div></div>',
        'columns' => [

            'id',
            [
                'attribute' => 'affiliate',
                'label'     => 'Affiliate',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'Affiliates_id',
                    'data' => $filterByAffiliate,
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'options' => [
                        'placeholder' => 'Select an Affiliate...',
                    ]
                ]),                
            ],
            'name',
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
            // 'landing_url:url',
            // 'creative_320x50',
            // 'creative_300x250',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
            ]
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
