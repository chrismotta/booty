<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\CampaignLogs */

$this->title = 'Create Campaign Logs';
$this->params['breadcrumbs'][] = ['label' => 'Campaign Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="campaign-logs-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
