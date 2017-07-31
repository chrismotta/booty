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
    public function actionIndex()
    {
        $model             = new models\Dashboard;

        $totalsProvider    = $model->loadData( 
            null, 
            null, 
            [[ '=', 'date(date)','CURDATE()' ]]
        );

        $byDateProvider    = $model->loadData( 
            ['date(date)'], 
            ['date(date)' => 'ASC'], 
            [['>=', 'date(date)', new \yii\db\Expression('date(NOW() - INTERVAL 7 DAY)')]],
            [ 'date(date) as date', 'imps', 'unique_users']
        );

        $byCountryProvider = $model->loadData( 
            ['country'],
            null, 
            [[ '=', 'date(date)','CURDATE()' ]]
        );

        return $this->render('index', [
            'model'             => $model,
            'totalsProvider'    => $totalsProvider,
            'byDateProvider'    => $byDateProvider,
            'byCountryProvider' => $byCountryProvider
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
