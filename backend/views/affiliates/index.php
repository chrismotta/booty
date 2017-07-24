<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel app\models\AffiliatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Affiliates';
$this->params['breadcrumbs'][] = $this->title;
$users = models\User::find()->asArray()->all();

$filterByUser = ArrayHelper::map( 
    $users, 
    'id', 
    'username' 
);

?>
<div class="affiliates-index">

    <!-- <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Affiliates', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'short_name',
            //'user_id',
            //'api_key',
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

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
