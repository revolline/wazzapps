<?php
/**
 * Created by PhpStorm.
 * User: nikki
 * Date: 07.02.2020
 * Time: 22:40
 */

namespace app\models;


use yii\base\Model;
use yii\helpers\Json;

class Purchases extends Model
{

    public function getPurchases()
    {
        $data = file('http://test123.wazzapps.ru/index.php', FILE_IGNORE_NEW_LINES);
        $ar = [];
        //делаем разбивку строки на элементы массива по пробелам
        foreach ($data as $datum) {
            $ar[] = preg_split("/[\s]+/", $datum);
        }
        //удаляем заголовки
        unset($ar[0]);

        //запрашиваем валюты за сегодняшний день
        $all_currency_from_euro = $this->getIsAvalibleFromApi();
        $ar_itog = [];
        $i = 0;
        foreach ($ar as $item) {
            // если нет валюты или цены  - тогда пропускаем такую запись
            if ($item[4] == '' || $item[3] == '') continue;
            //  начинаем перевод все в евро, а потом умножаем на доллар
            if (array_key_exists($item[4], $all_currency_from_euro)) {
//                $ar_from_euro[$i][5] =  $all_currency_from_euro[$item[4]];
                $ar_itog[$i] = $item;
                $ar_itog[$i][0] = $item[0] . " " . $item[1];
                $ar_itog[$i][5] = round(($item[3] / $all_currency_from_euro[$item[4]]) * $all_currency_from_euro['USD'], 2);
            } else continue;
            $i++;
        }

        // проверка на уже существующую запись в БД
        foreach ($ar_itog as $item) {
            $sql = "select id
                from purchases
                where dt = :dt and bundle = :bundle and amount_buyer = :amount_buyer and currency = :currency";
            $params = [];
            $params['dt'] = $item[0];
            $params['bundle'] = $item[2];
            $params['amount_buyer'] = $item[3];
            $params['currency'] = $item[4];
            $res = \yii::$app->db->createCommand($sql, $params)->queryScalar();
            if ($res !== false) continue;
            else {
                //вставляем новую
                $fld = [];
                $fld['dt'] = $item[0];
                $fld['bundle'] = $item[2];
                $fld['amount_buyer'] = $item[3];
                $fld['currency'] = $item[4];
                $fld['amount_usd'] = $item[5];
                \yii::$app->db->createCommand()->insert('purchases', $fld)->execute();
            }

        }
    }

    /**
     * чтобы не тратить запросы API, записываем инфу о валютах в файл
     *
     */
    public function getIsAvalibleFromApi()
    {
        $file_name = \Yii::getAlias('@app') . '/runtime/' . date("Y-m-d");
        if (!file_exists($file_name)) {
            $FixerApi = new FixerApi('5b66dd8fbb3f40a2e60907f561926c05');
            $params = [];
            $data = $FixerApi->latest($params);
            $data_j = Json::decode($data);
            if ($data_j['success'] == true) {
                $contezt = serialize($data_j['rates']);
                file_put_contents($file_name, $contezt);
            }
        }

        $res = file_get_contents($file_name);
        $res_itog = unserialize($res);
        return $res_itog;

    }

    /**
     * @return array
     * @throws \yii\db\Exception
     * запрос 1
     */
    public function getAmountUsdForDay()
    {
        $sql = "select sum(amount_usd) as cnt, date_format(dt, '%d.%m.%Y') as dt
from purchases
group by date_format(dt, '%Y-%m-%d')
order by date_format(dt, '%Y-%m-%d')";
        $all = \yii::$app->db->createCommand($sql)->queryAll();
        return $all;
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     * запрос 2
     */
    public function getShareForCurrency()
    {

        /*
         * более быстрая версия с группировкой по валютам
         */
        $sql = "select dt, currency, sum(amount_buyer),
       ( sum(amount_buyer) /
       (select sum(pur.amount_buyer)
       from purchases pur
       where date_format(pur.dt, '%Y-%m-%d') =  date_format(purchases.dt, '%Y-%m-%d')) * 100)
from purchases
group by date_format(dt, '%Y-%m-%d'), currency;";


        $sql = "select date_format(dt, '%Y-%m-%d') dt,
       (select sum(pur.amount_buyer)  from purchases pur
        where date_format(pur.dt, '%Y-%m-%d') =  date_format(purchases.dt, '%Y-%m-%d') ) sum_all,

       (select (sum(pur_usd.amount_buyer) /  sum_all) * 100
        from purchases as pur_usd
        where pur_usd.currency = 'USD' and date_format(pur_usd.dt, '%Y-%m-%d') = date_format(purchases.dt, '%Y-%m-%d')) USD,
       (select (sum(pur_rub.amount_buyer) /  sum_all) * 100
        from purchases as pur_rub
        where pur_rub.currency = 'RUB' and date_format(pur_rub.dt, '%Y-%m-%d') = date_format(purchases.dt, '%Y-%m-%d')) RUB,

       (select (sum(pur_idr.amount_buyer) /  sum_all) * 100
        from purchases as pur_idr
        where pur_idr.currency = 'IDR' and date_format(pur_idr.dt, '%Y-%m-%d') = date_format(purchases.dt, '%Y-%m-%d')) IDR,

       (select (sum(pur_zar.amount_buyer) /  sum_all) * 100
        from purchases as pur_zar
        where pur_zar.currency = 'ZAR' and date_format(pur_zar.dt, '%Y-%m-%d') = date_format(purchases.dt, '%Y-%m-%d')) ZAR
from purchases
where 
group by date_format(dt, '%Y-%m-%d');";
        $all = \yii::$app->db->createCommand($sql)->queryAll();
        return $all;
    }
}