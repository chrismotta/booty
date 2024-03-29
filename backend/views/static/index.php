<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\StaticCampaignsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Static Campaigns';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="static-campaigns-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Static Campaigns', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => \Yii::$app->params['gridDefalutLayout'],
        'columns' => [
            'id',
            'name',
            'landing_url:url',
            [
                'label'  => '320x50',
                'format' => 'image',
                'value'  => function($data) { return $data->creative_320x50; },
                'contentOptions'  => [
                    'class' =>'img-column-320x50',
                ],
            ],
            [
                'label'  => '300x250',
                'format' => 'image',
                'value'  => function($data) { return $data->creative_300x250; },
                'contentOptions'  => [
                    'class' =>'img-column-300x250',
                ],
            ],
            // [
            //     'attribute' => 'creative_300x250',
            //     'contentOptions'  => [
            //         'style' =>'max-height:125px;max-width:150px;overflow:hidden;'
            //     ],
            //     'headerOptions'  => [
            //         'style' => 'max-height:125px;max-width:150px;overflow:hidden;'
            //     ],              
            // ],
            // [
            //     'attribute' => 'creative_320x50',
            //     'contentOptions'  => [
            //         'style' =>'max-height:125px;max-width:150px;overflow:hidden;'
            //     ],
            //     'headerOptions'  => [
            //         'style' => 'max-height:125px;max-width:150px;overflow:hidden;'
            //     ],              
            // ],            

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
