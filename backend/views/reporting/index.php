<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampaignLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reporting';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-logs-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'click_id',
            'D_Campaign_id',
            //'session_hash',
            //'click_time',
            //'conv_time',
            'revenue',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
