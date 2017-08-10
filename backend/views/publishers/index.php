<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\PublishersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Publishers';
$this->params['breadcrumbs'][] = $this->title;

$users = models\User::find()->asArray()->all();

$filterByUser = ArrayHelper::map( 
    $users, 
    'id', 
    'username' 
);
?>
<div class="publishers-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Publishers', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => \Yii::$app->params['gridDefalutLayout'],
        'rowOptions' => function ($model, $key, $index, $grid) {
                return [
                'class' => 'deep-link',
                'data-child' => Url::to([
                    '/placements', 
                    'PlacementsSearch[Publishers_id]'=>$key
                    ]),
                ];
            },
        'columns' => [

            'id',
            'name',
            'short_name',
            [
                'attribute' => 'username',
                'label'     => 'Admin User',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'admin_user',
                    'data' => $filterByUser,
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'options' => [
                        'placeholder' => 'Select user...',
                    ]
                ]),                
            ],

            [
            'class' => 'yii\grid\ActionColumn',
            'contentOptions' => ['class'=>'prevent-deep-link'],
            ],

        ],
    ]); ?>
<?php Pjax::end(); ?></div>
