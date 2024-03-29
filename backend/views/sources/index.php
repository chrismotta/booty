<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SourcesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Trafic Sources';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sources-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Sources', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
    <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
        <?php Pjax::begin(['id' => 'pjax-id']); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'id' => 'currentGrid',
            'rowOptions' => function ($model, $key, $index, $grid){
                return array(
                    'class' => 'deep-link',
                    'data-child' => Yii::$app->urlManager->createUrl(['placements/index', 'PlacementsSearch[Sources_id]' => $key]),
                    );
            },
            'columns' => [
                // ['class' => 'yii\grid\SerialColumn'],

                'id',
                'name',
                'short_name',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {duplicate} {delete}',

                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-eye-open"></span>', 
                                $url,
                                [
                                    'class' => 'grid-button',
                                ]
                                );
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-pencil"></span>', 
                                $url,
                                [
                                    'class' => 'grid-button',
                                ]
                                );
                        },
                        'duplicate' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-duplicate"></span>', 
                                ['duplicate', 'id'=>$key],
                                [
                                    'class' => 'grid-button',
                                ]
                                );
                        },
                    ],
                ],
            ],
        ]); ?>
        </div>
    </div>
<?php Pjax::end(); ?></div>
