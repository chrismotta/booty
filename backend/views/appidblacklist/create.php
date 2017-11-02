<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\AppidBlacklist */

$this->title = 'Create Appid Blacklist';
$this->params['breadcrumbs'][] = ['label' => 'Appid Blacklists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="appid-blacklist-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
