<?php
/**
 * Created by PhpStorm.
 * User: nikki
 * Date: 07.02.2020
 * Time: 22:38
 */

namespace app\commands;


use app\models\Purchases;
use yii\console\Controller;

class GetDataController extends Controller
{

    public function actionPurchases()
    {
        $Purchases = new Purchases();
        $Purchases->getPurchases();
    }
}