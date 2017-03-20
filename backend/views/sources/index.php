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
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

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
                                    'data-toggle' => 'control-sidebar',
                                    'onclick' => 'openOnSidebar(this)',
                                ]
                                );
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-pencil"></span>', 
                                $url,
                                [
                                    'data-toggle' => 'control-sidebar',
                                    'onclick' => 'openOnSidebar(this)',
                                ]
                                );
                        },
                        'duplicate' => function ($url, $model, $key) {
                            return Html::a(
                                '<span class="glyphicon glyphicon-duplicate"></span>', 
                                $url,
                                [
                                    'data-toggle' => 'control-sidebar',
                                    'onclick' => 'openOnSidebar(this)',
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
