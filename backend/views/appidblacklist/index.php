<?php

use yii\helpers\Html;
// use yii\grid\GridView;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Tabs;
/* @var $this yii\web\View */
/* @var $searchModel app\models\AppidBlacklistSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Campaigns Blacklist';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php

echo Tabs::widget([
    'items' => [
        [
            'label' => '<i class="glyphicon glyphicon-phone"></i><span class="tag-title"> by App ID</span>',
            'encode' => false,
            'active' => true,
        ],
        [
            'label' => '<i class="glyphicon glyphicon-search"></i><span class="tag-title"> by Keyword</span>',
            'encode' => false,
            'url' => ['/keywordblacklist'],
        ],
    ],
]);

?>

<div class="box box-info">
    <div class="box-body">

<div class="appid-blacklist-create" style="margin: 10px 0 -10px 5px;">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<hr/>

<div class="appid-blacklist-index">

<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'condensed' => true,
        'bordered' => false,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            'app_id',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
            ]
        ],
    ]); ?>
<?php Pjax::end(); ?></div>

</div></div>