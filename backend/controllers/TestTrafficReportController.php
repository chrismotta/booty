<?php

namespace backend\controllers;

class TestTrafficReportController extends \yii\web\Controller
{
    public function actionIndex( $date = null )
    {
       	$cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

       	if ( !$date )
       		$date = date( 'Y-m-d' );

       	$cache->select(8);

        $clickIds = $cache->zrange( 'convs', 0, -1 );

        foreach ( $clickIds AS $clickId )
        {
        	$data[] = $cache->hgetall( 'campaignlog:'.$clickId );
        }

		$dataProvider = new ArrayDataProvider([
		    'allModels' => $data,
		    'pagination' => [
		        'pageSize' => 50,
		    ],
		    'sort' => [
		        'attributes' => ['id', 'name'],
		    ],
		]);        

        return $this->render('index');
    }

}
