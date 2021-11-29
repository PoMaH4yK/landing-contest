<?php

namespace app\controllers;

use app\behaviors\LogBehavior;
use app\models\ActivitySearch;
use app\utils\JsonRpcDataProvider;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'admin'],
                'rules' => [
                    [
                        'actions' => ['logout', 'admin'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'log' => [
                'class' => LogBehavior::class,
                'url' => Yii::$app->params['activityUrl'],
                'login' => Yii::$app->params['activityLogin'],
                'password' => Yii::$app->params['activityPassword'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionAdmin()
    {
        $provider = new JsonRpcDataProvider([
            'url' => Yii::$app->params['activityUrl'],
            'login' => Yii::$app->params['activityLogin'],
            'password' => Yii::$app->params['activityPassword'],
            'pagination' => ['pageSize' => 5],
            'sort' => [
                'attributes' => ['url', 'lastVisit', 'count']
            ]
        ]);

        return $this->render('admin', [
            'provider' => $provider,
        ]);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['admin']);
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
