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

$this->title = 'Campaigns Assigned by Cluster';
$this->params['breadcrumbs'][] = ['label' => 'Affiliates', 'url' => ['/affiliates']];
$this->params['breadcrumbs'][] = $this->title;

// $affiliates = models\Affiliates::find()->asArray()->all();
// $carriers   = models\Carriers::find()->asArray()->all();

// $filterByAffiliate = ArrayHelper::map( 
//     $affiliates, 
//     'id', 
//     'name' 
// );

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
        'layout' => \Yii::$app->params['gridDefalutLayout'],
        'columns' => [
            'name',
            'affiliate',
            'available',
        /*
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
                        'placeholder' => 'Select',
                    ]
                ]),                
            ],
            'name',
            [
                'attribute' => 'ext_id',
                'contentOptions' => [
                    'class'=>'wrap-long-hashes'
                ],
            ],
            'payout',
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
            // [
            //     'attribute'=>'os_version',
            //     'format'=>'html',
            //     'value' => function($model, $key, $index){
            //         return $model->formatValues('os_version', 'default');
            //     }
            // ],
            // [
            //     'attribute'=>'carrier',
            //     'format'=>'html',
            //     'value' => function($model, $key, $index){
            //         return $model->formatValues('carrier', 'warning');
            //     }
            // ],
            [
                'attribute' => 'status',
                'filter' => [
                        'active' => 'active',
                        'archived' => 'archived',
                        'paused' => 'paused',
                        'aff_paused' => 'aff_paused',
                        'affiliate_off' => 'affiliate_off',
                        'blacklisted' => 'blacklisted',
                        'no_appid' => 'no_appid',
                        'no_payout' => 'no_payout',                        
                        'no_url' => 'no_url',
                    ],
                'contentOptions' => function($model, $key, $index){
                    switch ($model->status) {
                        case 'active':
                            $options = ['class'=>'text-success'];
                            break;
                        case 'paused':
                            $options = ['class'=>'text-info'];
                            break;
                        case 'aff_paused':
                            $options = ['class'=>'text-info'];
                            break;
                        case 'blacklisted':
                            $options = ['class'=>'text-danger'];
                            break;
                        case 'archived':
                            $options = ['class'=>'text-muted'];
                            break;
                        case 'affiliate_off':
                            $options = ['class'=>'text-muted'];
                            break;
                        default:
                            $options = [];
                    }

                    $options['style'] = 'font-weight: bold; font-size: 11px';
                    return $options;
                }
            ],
            // 'landing_url:url',
            // 'creative_320x50',
            // 'creative_300x250',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}{blacklist}{delete}',
                'buttons' => [
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
                ],
            ]
        */
        ],
    ]); ?>
<?php Pjax::end(); ?></div>

<div class="modal remote fade" id="blacklist" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content loader-lg"></div>
        </div>
</div>
