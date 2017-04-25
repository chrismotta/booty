<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Publishers */

$this->title = 'Create Publishers';
$this->params['breadcrumbs'][] = ['label' => 'Publishers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="publishers-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
