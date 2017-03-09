<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SourcesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Sources';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sources-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Sources', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
    <div class="box">
        <div class="box-header">
          <h3 class="box-title">Traffic Sources</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
        <?php Pjax::begin(); ?>    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'id' => 'currentGrid',
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'name',
                'short_name',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-eye-open"></span>', 
                                $url,
                                [
                                    'data-toggle' => 'control-sidebar',
                                    'class' => 'grid-button',
                                ]
                                );
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-pencil"></span>', 
                                $url,
                                [
                                    'data-toggle' => 'control-sidebar',
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
