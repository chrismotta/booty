<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PubidBlacklist */

$this->title = 'Campaign '.$model->Campaigns_id.' Blacklist';
?>
<div class="pubid-blacklist-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
