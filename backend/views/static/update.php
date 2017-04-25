<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\StaticCampaigns */

$this->title = 'Update Static Campaigns: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Static Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="static-campaigns-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
