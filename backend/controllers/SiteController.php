<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use app\models;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex($maintenance=false)
    {
        if($maintenance){
            return $this->renderPartial('maintenance');
        }

        $model             = new models\Dashboard;

        $totalsProvider    = $model->loadData( 
            ['date(date)'], 
            null, 
            [[ '=', 'date(date)',new \yii\db\Expression( 'CURDATE()' ) ]],
            [ 'sum(imps) AS imps', 'sum(unique_users) AS unique_users', 'sum(installs) AS installs', 'sum(cost) AS cost', 'sum(revenue) AS revenue']
        );

        $yesterdayProvider = $model->loadData( 
            ['date(date)'], 
            null, 
            [[ '=', 'date(date)',new \yii\db\Expression( 'DATE(NOW()- INTERVAL 1 DAY)' ) ]],
            [ 'sum(imps) AS imps', 'sum(unique_users) AS unique_users', 'sum(installs) AS installs']
        );        

        $byDateProvider    = $model->loadData( 
            ['date(date)'], 
            ['date(date)' => 'ASC'], 
            [['>=', 'date(date)', new \yii\db\Expression('date(NOW() - INTERVAL 7 DAY)')]],
            [ 'date(date) AS date', 'sum(imps) AS imps', 'sum(unique_users) AS unique_users', 'sum(cost) AS cost', 'sum(revenue) AS revenue']
        );
/*
        $byCountryProvider = $model->loadData( 
            ['country'],
            null, 
            [[ '=', 'date(date)',new \yii\db\Expression( 'CURDATE()' ) ]],
            ['country AS country', 'sum(imps) AS imps']
        );
*/
        return $this->render('index', [
            'model'             => $model,
            'totalsProvider'    => $totalsProvider,
            'yesterdayProvider' => $yesterdayProvider,
            'byDateProvider'    => $byDateProvider,
//            'byCountryProvider' => $byCountryProvider
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
