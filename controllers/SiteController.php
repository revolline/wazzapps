<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $Purchases = new \app\models\Purchases();
        $getAmountUsdForDay = $Purchases->getAmountUsdForDay();
        $getShareForCurrency = $Purchases->getShareForCurrency();
        return $this->render('index', [
            'getAmountUsdForDay' => $getAmountUsdForDay,
            'getShareForCurrency' => $getShareForCurrency,
        ]);
    }

}
