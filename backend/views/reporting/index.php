<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\CampaignLogsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reporting';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-logs-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

<div class="post-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($searchModel, 'click_time') ?>

    <?= $form->field($searchModel, 'conv_time') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php Pjax::begin(); ?>    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            //'click_id',
            //'D_Campaign_id',
            //'session_hash',
            //'click_time',
            //'conv_time',
             [
             'attribute' => 'campaign',
             'value' => 'campaign.name'
             ],
             [
             'attribute' => 'affiliate',
             'value' => 'campaign.Affiliates_name'
             ],               
             [
             'attribute' => 'publisher',
             'value' => 'clusterLog.placement.publisher'
             ],
             [
             'attribute' => 'model',
             'value' => 'clusterLog.placement.model'
             ],
             [
             'attribute' => 'status',
             'value' => 'clusterLog.placement.status'
             ],
             [
             'attribute' => 'country',
             'value' => 'clusterLog.country'
             ],
             [
             'attribute' => 'connection_type',
             'value' => 'clusterLog.connection_type'
             ],
             [
             'attribute' => 'carrier',
             'value' => 'clusterLog.carrier'
             ],             
             [
             'attribute' => 'device',
             'value' => 'clusterLog.device'
             ],
             [
             'attribute' => 'device_brand',
             'value' => 'clusterLog.device_brand'
             ],             
             [
             'attribute' => 'device_model',
             'value' => 'clusterLog.device_model'
             ],
             [
             'attribute' => 'os',
             'value' => 'clusterLog.os'
             ],
             [
             'attribute' => 'os_version',
             'value' => 'clusterLog.os_version'
             ],
             [
             'attribute' => 'browser',
             'value' => 'clusterLog.browser'
             ],
             [
             'attribute' => 'browser_version',
             'value' => 'clusterLog.browser_version'
             ],
             [
             'attribute' => 'cost',
             'value' => 'clusterLog.cost'
             ],
             [
             'attribute' => 'imps',
             'value' => 'clusterLog.imps'
             ],
            'revenue',
            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
<?php Pjax::end(); ?></div>
