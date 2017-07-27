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

$filterByAffiliate = ArrayHelper::map( 
    $affiliates, 
    'id', 
    'name' 
);
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
            'landing_url:url',
            // 'creative_320x50',
            // 'creative_300x250',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
            ]
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
