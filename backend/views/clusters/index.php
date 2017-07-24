<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\ClustersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Clusters';
$this->params['breadcrumbs'][] = $this->title;
$placements = models\Placements::find()->asArray()->all();

$filterByPlacement = ArrayHelper::map( 
    $placements, 
    'id', 
    'name' 
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
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'placement',
                'label'     => 'Placement',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'Placements_id',
                    'data' => $filterByPlacement,
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'options' => [
                        'placeholder' => 'Select placement...',
                    ]
                ]),                
            ],
            'country',
            'connection_type',
            // 'carrier',
            // 'StaticCampaigns_id',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {assignment} {delete}',
                'buttons' => [
                    'assignment' => function ($url, $model, $key) {
                        return Html::a(
                            '<span class="glyphicon glyphicon-th-list"></span>', 
                            $url,
                            []
                            );
                    },
                ],
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
