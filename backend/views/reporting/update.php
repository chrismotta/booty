<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\CampaignLogs */

$this->title = 'Update Campaign Logs: ' . $model->click_id;
$this->params['breadcrumbs'][] = ['label' => 'Campaign Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->click_id, 'url' => ['view', 'id' => $model->click_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="campaign-logs-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
