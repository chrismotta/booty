<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PlacementsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Placements';
$this->params['breadcrumbs'][] = $this->title;

$publishers = models\Publishers::find()->asArray()->all();

$filterByPublisher = ArrayHelper::map( 
    $publishers, 
    'id', 
    'name' 
);

$clusters = models\Clusters::find()->asArray()->all();

$filterByCluster = ArrayHelper::map( 
    $clusters, 
    'id', 
    'name' 
);
?>
<div class="placements-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Placements', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'publisher',
                'label'     => 'Publisher',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'Publishers_id',
                    'data' => $filterByPublisher,
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'options' => [
                        'placeholder' => 'Select publisher...',
                    ]
                ]),                
            ],
            [
                'attribute' => 'cluster',
                'label'     => 'Cluster',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'Clusters_id',
                    'data' => $filterByCluster,
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'options' => [
                        'placeholder' => 'Select cluster...',
                    ]
                ]),                
            ],            
            'name',
            'frequency_cap',
            'payout',
            // 'model',
            // 'status',
            // 'size',
            // 'health_check_imps',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
