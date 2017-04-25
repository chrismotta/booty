<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\StaticCampaigns */

$this->title = 'Create Static Campaigns';
$this->params['breadcrumbs'][] = ['label' => 'Static Campaigns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="static-campaigns-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
