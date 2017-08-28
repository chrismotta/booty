<?php

namespace backend\controllers;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models;
use Yii;
use yii\data\ArrayDataProvider;

class TesttrafficreportController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex( $date = null )
    {
       	$cache = new \Predis\Client( \Yii::$app->params['predisConString'] );

       	if ( !$date )
       		$date = date( 'Y-m-d' );

       	$cache->select(8);

        $clickIds 	  = $cache->zrange( 'testclickids', 0, -1 );
        $counters 	  = [];
        $rows 		  = [];
        $campaignIds  = [];
        $data         = [];
        $c=0;
        foreach ( $clickIds AS $clickId )
        {
        	$click = $cache->hgetall( 'campaignlog:'.$clickId );

        	if ( $click )
        	{
	        	$campaignId = $click['campaign_id'];

	        	if ( isset( $counters[$campaignId] ) )
	        	{
					$counters[$campaignId]['clicks']++;
	        	}
				else
					$counters[$campaignId]['clicks'] = 1;

				$convTime = $cache->get( 'conv:'.$clickId );

				if ( $convTime )
				{
		        	if ( isset( $counters[$campaignId]['convs'] ) )
						$counters[$campaignId]['convs']++;
					else
                        $counters[$campaignId]['convs'] = 1;            
                }
                else
					$counters[$campaignId]['convs'] = 0;			

	        	if ( !in_array( $campaignId, $campaignIds ))
	        		$campaignIds[] = (int)$campaignId;
        	}
        }

        $campaigns = models\Campaigns::find()->where(['in', 'id', $campaignIds])->all();

        foreach ( $campaigns as $campaign )
        {
        	$cid = $campaign->id;
        	
        	$data[] = [
        		'affiliate'	=> $campaign->affiliates->name . ' ('.$campaign->affiliates->id.')',
        		'campaign'  => $campaign->name . ' ('.$cid.')',
        		'clicks'	=> $counters[$cid]['clicks'],
        		'convs'		=> $counters[$cid]['convs']
        	];        	
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

        return $this->render('index', [
        	'dataProvider' => $dataProvider,
        ]);
    }

}
