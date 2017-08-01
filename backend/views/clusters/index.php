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

            'id',
            'name',
            'country',
            'os',
            'connection_type',
            // 'StaticCampaigns_id',

            [
                'class' => 'yii\grid\ActionColumn',
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
                            ],
                            []
                            );
                    },
                ],
            ],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
