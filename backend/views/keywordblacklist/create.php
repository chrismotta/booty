<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\KeywordBlacklist */

$this->title = 'Create Keyword Blacklist';
$this->params['breadcrumbs'][] = ['label' => 'Keyword Blacklists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="keyword-blacklist-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
