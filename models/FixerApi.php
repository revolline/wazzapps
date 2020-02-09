<?php
/**
 * Created by PhpStorm.
 * User: nikki
 * Date: 08.02.2020
 * Time: 0:24
 */

namespace app\models;


class FixerApi
{

    /**
     * @var
     *
     * @link https://fixer.io/documentation
     *
     * @method latest
     */


    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function __call($name, $arguments)
    {
        // определяем, что мы передаем. чтобы при вызове callMethod всегда был $params
        if (!is_array($arguments) || 0 === count($arguments)) {
            $params = [];
        } else {
            $params = $arguments[0];
        }

        return $this->callMethod($name, $params);
    }

    /*
     * для удобства вызова любого метода с апи
     */
    protected function callMethod($methodName, $params = [])
    {
        $url = $methodName . '?';
        // объединяем наш ключ и передаваемые параметры
        $params = array_merge((array)$params, ['access_key' => $this->apiKey]);
        // Генерируем URL-кодированную строку запроса
        $par_itog = http_build_query($params);
        $host = $this->getApiHost();
        //обращаемся к апи
        $result = file_get_contents($host . $url.$par_itog, false);
        return $result;
    }


    protected function getApiHost()
    {
        return 'http://data.fixer.io/api/';
    }
}