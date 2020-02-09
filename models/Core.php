<?php
/**
 * Created by PhpStorm.
 * User: vova
 * Date: 09.08.16
 * Time: 9:43
 */

namespace app\models;


use common\helpers\numToText\ManyToText;
use yii\base\Model;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;


class Core
{
    static function init()
    {
        if (!isset(\yii::$app->params['error'])) \yii::$app->params['error'] = [];
        if (!isset(\yii::$app->params['success_txt'])) \yii::$app->params['success_txt'] = [];
        if (!isset(\yii::$app->params['info'])) \yii::$app->params['info'] = [];
        if (!isset(\yii::$app->params['errorFld'])) \yii::$app->params['errorFld'] = [];
        if (!isset(\yii::$app->params['errorArray'])) \yii::$app->params['errorArray'] = [];
        if (!isset(\yii::$app->params['debug'])) \yii::$app->params['debug'] = [];
    }

    static $month = array(
        'title' => 'месяц',
        'data' => array(
            1 => array('title' => 'январь', 'short' => 'янв', 'title_rod' => 'января', 'id' => 1, 'm' => '01')
        , 2 => array('title' => 'февраль', 'short' => 'фев', 'title_rod' => 'февраля', 'id' => 2, 'm' => '02')
        , 3 => array('title' => 'март', 'short' => 'мар', 'title_rod' => 'марта', 'id' => 3, 'm' => '03')
        , 4 => array('title' => 'апрель', 'short' => 'апр', 'title_rod' => 'апреля', 'id' => 4, 'm' => '04')
        , 5 => array('title' => 'май', 'short' => 'май', 'title_rod' => 'мая', 'id' => 5, 'm' => '05')
        , 6 => array('title' => 'июнь', 'short' => 'июн', 'title_rod' => 'июня', 'id' => 6, 'm' => '06')
        , 7 => array('title' => 'июль', 'short' => 'июл', 'title_rod' => 'июля', 'id' => 7, 'm' => '07')
        , 8 => array('title' => 'август', 'short' => 'авг', 'title_rod' => 'августа', 'id' => 8, 'm' => '08')
        , 9 => array('title' => 'сентябрь', 'short' => 'сен', 'title_rod' => 'сентября', 'id' => 9, 'm' => '09')
        , 10 => array('title' => 'октябрь', 'short' => 'окт', 'title_rod' => 'октября', 'id' => 10, 'm' => '10')
        , 11 => array('title' => 'ноябрь', 'short' => 'ноя', 'title_rod' => 'ноября', 'id' => 11, 'm' => '11')
        , 12 => array('title' => 'декабрь', 'short' => 'дек', 'title_rod' => 'декабря', 'id' => 12, 'm' => '12')
        )
    );

    public static function dump($var, $depth = 10, $highlight = true, $echo = true)
    {
        $vard = new \yii\helpers\VarDumper;

        if ($echo === true) {
            echo $vard->dumpAsString($var, $depth, $highlight);
        } else
            return $vard->dumpAsString($var, $depth, $highlight);
    }

    public static function dumpC($var)
    {
        Core::dump($var,10,0);
    }

    static function pData()
    {
        $data = json_decode(file_get_contents('php://input'),true);
        $data = ArrayHelper::merge((array)$_GET, (array)$data);
        return $data;
    }

    static function encodeRest($ar = [])
    {
        self::init();
        $error = \yii::$app->params['error'];
        $info = \yii::$app->params['info'];
        $success_txt = \yii::$app->params['success_txt'];
        $errorFld = \yii::$app->params['errorFld'];
        $debug = \yii::$app->params['debug'];

        if (!isset($ar['success']))
            $ar['success'] = true;

        if (count($error) > 0) {
            $i = 0;
            $ar['error'] = $error;
            $ar['success'] = false;
        }

        if (count($info) > 0) {
            $ar['info'] = $info;
        }
        if (count($success_txt) > 0) {
            $ar['success_txt'] = $success_txt;
        }

        return $ar;
    }

    static function encode($ar = array(), $with_debug = false)
    {
        self::init();
        $error = \yii::$app->params['error'];
        $errorArray = \yii::$app->params['errorArray'];
        $info = \yii::$app->params['info'];
        $success_txt = \yii::$app->params['success_txt'];
        $errorFld = \yii::$app->params['errorFld'];
        $debug = \yii::$app->params['debug'];

        if(!isset($ar['success']) || empty($ar['success'])) $ar['success'] = true;

        if (count($error) > 0) {
            $i = 0;
            $ar['error'] = '';
            foreach ((array)$error as $er) {
                if (!is_string($er)) continue;
                if ($i > 0) $ar['error'] .= '<br />';
                $ar['error'] .= $er;
                $i++;
            }
            $ar['errorFld'] = $errorFld;
            $ar['success'] = false;
        }

        if (count($errorArray) > 0) {
            $i = 0;
            $ar['errorArray'] = [];
            foreach ((array)$errorArray as $fld=>$ers) {
                $i = 0;
                foreach ($ers as $er) {
                    if (!is_string($er)) continue;
                    if ($i > 0) $ar['errorArray'][$fld] = "<br />";
                    $ar['errorArray'][$fld] = $er;
                    $i++;
                }
            }
            $ar['errorFld'] = $errorFld;
            $ar['success'] = false;
        }

        if (count($info) > 0) {
            $ar['info'] = implode('<br />', (array) $info);
        }
        if (count($success_txt) > 0) {
            $ar['success_txt'] = implode('<br />', (array) $success_txt);
        }

        if ($with_debug === true) {
            $dbStats = Yii::getLogger()->getLogs('profile');
            $ar['debug'] = Core::dump($debug, 10, true, false);
            $ar['debug'] .= '<br /><br />SQL:';
            foreach ($dbStats as $item) {
                $ar['debug'] .= '<br />' . $item[0];
            }
        }

        $js = \yii\helpers\Json::encode($ar);
        return $js;
    }


    static function encode_echo($ar = array(), $with_debug = false)
    {
        $str = self::encode($ar, $with_debug);
        header('Content-Type: application/json;charset=utf-8');
        echo $str;
        exit;
        //\yii::$app->end();
    }




    /**
     * пишем текст в массив ошибок
     * @param type $str
     */
    static function error($str, $fldName = null, $session = false)
    {
        self::init();
        if ($str instanceof Model) {
            $class = get_class($str);
            $tmp = explode('\\', $class);
            $class_name = mb_strtolower($tmp[count($tmp)-1]);
            if ($str->errors) {
                foreach ($str->errors as $fld => $errors) {
                    foreach ($errors as $error) {
                        $ar = \yii::$app->params['error'];
                        $ar[] = $error;
                        \yii::$app->params['error'] = $ar;

                        $arArray = \yii::$app->params['errorArray'];
                        $arArray[$fld][] = $error;
                        \yii::$app->params['errorArray'] = $arArray;

                    }
                    $ar = \yii::$app->params['errorFld'];
                    $ar[] = $fld;
                    $ar[] = $class_name.'-'.$fld;
                    \yii::$app->params['errorFld'] = $ar;
                }

                if ($session === true) {
                    $session = \Yii::$app->session;
                    foreach ((array)$str->errors as $val) {
                        $session->setFlash('error', $val);
                    }
                }
            }
            return;
        }

        if (is_array($str)) {
            foreach ($str as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $ar = \yii::$app->params['error'];
                        $ar[] = $v;
                        \yii::$app->params['error'] = $ar;
                        $ar = \yii::$app->params['errorFld'];
                        $ar[] = $key;
                        \yii::$app->params['errorFld'] = $ar;
                    }
                } else {
                    $ar = \yii::$app->params['error'];
                    $ar[] = $val;
                    \yii::$app->params['error'] = $ar;
                    $ar = \yii::$app->params['errorFld'];
                    $ar[] = $key;
                    \yii::$app->params['errorFld'] = $ar;
                }
            }
            if ($session === true) {
                $session = \Yii::$app->session;
                foreach ((array)$ar as $val) {
                    $session->setFlash('error', $val);
                }
            }
            return;
        }

        $ar = \yii::$app->params['error'];
        $ar[] = $str;
        \yii::$app->params['error'] = $ar;
        if ($fldName !== null) {
            $ar = \yii::$app->params['errorFld'];
            $ar[] = $fldName;
            \yii::$app->params['errorFld'] = $ar;
        }
    }

    static function info($str)
    {
        self::init();
        $ar = \yii::$app->params['error'];
        $ar[] = $str;
        \yii::$app->params['info'] = $ar;
    }

    static function success_txt($str)
    {
        self::init();
        $ar = \yii::$app->params['success_txt'];
        $ar[] = $str;
        \yii::$app->params['success_txt'] = $ar;
    }

    /**
     * пишем текст в файл
     * @param type $str
     */
    static function errorSystem($str)
    {
        $fname = YiiBase::getPathOfAlias('application') . '/tmp/err.txt';
        $h = fopen($fname, "a");
        $dt = date('d.m.Y H:i:s') . '/' . rand(10, 99);
        $text = $dt . ' - ' . $_SERVER["REQUEST_URI"] . "\n" . $str . "\n\n";
        self::error('Ошибка системы<br />идентификатор ошибки - ' . $dt);
        if (fwrite($h, $text))
            fclose($h);
    }

    static function hasInfo()
    {
        self::init();
        if (count(\yii::$app->params['info']) == 0)
            return false;
        return true;
    }

    static function hasError()
    {
        self::init();
        if (count(\yii::$app->params['error']) == 0)
            return false;
        return true;
    }

    static function viewError($sep = '<br />', $clearEr = true)
    {
        self::init();
        $errors = \yii::$app->params['error'];
        if (!is_array($errors))
            return;
        $str = '';
        foreach ($errors as $er) {
            if ($str != '')
                $str .= $sep;
            $str .= $er;
        }
        if ($clearEr === true) {
            \yii::$app->params['error'] = array();
        }
        return $str;
    }




    static function img($pathUrl = '', $razmer, $watermark = null, $attribute = ['align' => 'absmiddle'])
    {
        $format = '';

        if (is_array($razmer)) {
            $width = $razmer[0];
            $height = $razmer[1];
        } else {
            $tmp = \Yii::$app->params['img']['all']['razmer'][$razmer];
            if (!$tmp) return;
            $width = $tmp[0];
            $height = $tmp[1];
            $format = $tmp['format'] ?? null;
        }
        $ar = ['full_in'];
        if (!in_array($format, $ar)) $format = null;


        $path = \Yii::getAlias('@storage') . $pathUrl;

        $fName = basename($path);



        try {
            if (!is_file($path)) {
                $dir = dirname($path) . '/../src';
                if (is_file($dir . '/' . $fName)) {

                    $Image = new Image();

                    $image = $Image::getImagine()->open($dir . '/' . $fName, ImageInterface::FILTER_LANCZOS);

                    $size = $image->getSize();

                    $w = $size->getWidth();
                    $h = $size->getHeight();


                    if ($format == 'full_in') {
                        $k_w = $w / $width;
                        $k_h = $h / $height;
                        $k = ($k_w < $k_h) ? $k_h : $k_w;

                        $resize_w = round($w/$k);
                        $resize_h = round($h/$k);

                        if ($width > $resize_w) $width = $resize_w;
                        if ($height > $resize_h) $height = $resize_h;

                        $crop_w = $resize_w - $width;
                        $crop_w = ($crop_w > 0) ? round($crop_w/2) : 0;
                        $crop_h = $resize_h - $height;
                        $crop_h = ($crop_h > 0) ? round($crop_h/2) : 0;
                    } else {
                        $k_w = $w / $width;
                        $k_h = $h / $height;
                        $k = ($k_w > $k_h) ? $k_h : $k_w;

                        $resize_w = round($w/$k);
                        $resize_h = round($h/$k);

                        $crop_w = $resize_w - $width;
                        $crop_w = ($crop_w > 0) ? round($crop_w/2) : 0;
                        $crop_h = $resize_h - $height;
                        $crop_h = ($crop_h > 0) ? round($crop_h/2) : 0;
                    }
                    $image->resize(new Box($resize_w, $resize_h))
                        ->crop(new Point($crop_w, $crop_h), new Box($width, $height))
                        ->save($path);

                }
            }
        } catch (Exception $exc) {
//            echo $exc->getTraceAsString();
        }

        if (is_file($path)) {
            $pathUrl = \Yii::getAlias('@storageUrl') . $pathUrl;
            $ret['img'] = '<img src="' . $pathUrl . '" width="' . $width . '" height="' . $height . '" />';
            $ret['url'] = $pathUrl;

        } else {
            $ret = false;
        }

        return $ret;
    }

    static function img_del($pathUrl)
    {
        $path = \Yii::getAlias('@storage') . $pathUrl;
        $tmp = explode('/', $path);
        $f_name = $tmp[count($tmp) - 1];

        $ar_dir = ['sm', 'md', 'lg', 'src'];
        foreach ($ar_dir as $dirName) {
            $dir = dirname($path) . '/../'.$dirName.'/';
            if (is_file($dir.$f_name)) unlink($dir.$f_name);
        }
    }


    public static function downLoad($path, $fileName, $fileType)
    {
        ob_start();
        ob_clean();
//        $content = file_get_contents($path);

        $fp = fopen($path, 'rb');

        switch ($fileType) {
            case 'tiff':
                $fileType_str = 'Content-type: image/tiff';
                break;

            case 'png':
                $fileType_str = 'Content-type: image/png';
                break;

            case 'gif':
                $fileType_str = 'Content-type: image/gif';
                break;

            case 'jpg':
            case 'jpeg':
                $fileType_str = 'Content-type: image/jpeg';
                break;

            case 'xml':
                $fileType_str = 'Content-type: plaintext/xml';
                break;

            case 'rtf':
                $fileType_str = 'Content-type: plaintext/rtf';
                break;

            case 'docx':
                $fileType_str = 'Content-type: application/octet-stream';
                break;

            case 'pdf':
                $fileType_str = 'Content-type: application/pdf';
                break;

            case 'zip':
                $fileType_str = 'Content-type: application/zip';
                break;

            default:
                $fileType_str = 'Content-type: plaintext/octet-stream';
                break;
        }

        if ($fileType == 'docx') {
            header('Content-Description: File Transfer');
            header(
                'Content-Type: application/vnd.openxmlformats-officedocument.' .
                'wordprocessingml.document'
            );
            header(
                'Content-Disposition: attachment; filename="' . $fileName . '"'
            );
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');

//            header('Content-Length: ' . strlen($content));
//            echo $content;

            header('Content-Length: ' . filesize($path));
            fpassthru($fp);

            \Yii::$app->end();
        }

        $fileName = str_replace(',','-',$fileName);

        header($fileType_str);
        header('Content-Description: File Transfer');
//        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=$fileName");
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
//        header('Content-Length: ' . strlen($content));
//        echo $content;

        header('Content-Length: ' . filesize($path));
        fpassthru($fp);

        \Yii::$app->end();
    }

    static function ruToEn($str)
    {
        $pattern = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И',
            'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С',
            'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ',
            'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з',
            'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р',
            'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ',
            'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            ' ');
        $replace = array(
            'A', 'B', 'V', 'G', 'D', 'E', 'J', 'Z', 'I',
            'I', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S',
            'T', 'U', 'F', 'KS', 'C', 'CH', 'SH', 'CH', '-',
            'A', '-', 'E', 'YO', 'YA',
            'a', 'b', 'v', 'g', 'd', 'e', 'j', 'z', 'i',
            'i', 'i', 'k', 'l', 'm', 'n', 'o', 'p', 'r',
            's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'ch', '-',
            'a', '-', 'e', 'yo', 'ya',
            '-'
        );
        $str = str_replace('З', 'Z', $str);
        $str = str_replace('з', 'z', $str);
        $str = str_replace($pattern, $replace, $str);
        return $str;
    }


    /**
     * Функция, которая возвращает строку для url
     *
     * @param $string
     * @return null|string|string[]
     */
    static function slugable($string){
        $string = static::ruToEn($string);
        $slag = preg_replace('/[^A-Za-z0-9-]+/', '-', $string); // заменяет все символы и пробелы на "-"
        $slag = mb_strtolower($slag);
        return $slag;
    }


    static function dt_diff ($date_from, $date_till) {
        $date_from = explode('-', $date_from);
        $date_till = explode('-', $date_till);

        $time_from = mktime(0, 0, 0, $date_from[1], $date_from[2], $date_from[0]);
        $time_till = mktime(0, 0, 0, $date_till[1], $date_till[2], $date_till[0]);

        $diff = ($time_till - $time_from)/60/60/24;
        //$diff = date('d', $diff); - как делал))

        return $diff;
    }

    static function ManyToText()
    {
        return new ManyToText();
    }

    static function phoneFormatting($phone){
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if(strlen($phone) === 10 and ($phone[0] != '7' or $phone[0] != '8')){
                $phone = '7'.$phone;
            }
            if(strlen($phone) === 11 and $phone[0] == '8'){
                $phone[0] = '7';
            }
            return  $phone;
    }

    static function convertDateForDoc($date){
        if(empty($date)) return null;
        $d = date('d', strtotime($date));
        $d = '&laquo;'.$d.'&raquo;';
        $m = date('n', strtotime($date));
        $m = self::$month['data'][$m]['title_rod'];
        $Y = date('Y', strtotime($date)).' г.';

        return $d.' '.$m.' '.$Y;
    }

    /**
     * конвертирует сумму из рублей в копейки
     * @param $value
     * @return float|int|mixed|null
     */
    public static function convertSum($value){
        if ($value != '') {
            $value = str_replace(',', '.', $value);
            $value = $value * 100;
        } else $value = null;
        return $value;
    }

    /**
     * конвертирует количество из дробных в целые
     * @param $value
     * @return float|int|mixed
     */
    public static function convertKol($value){
        if ($value != '') {
            $value = str_replace(',', '.', $value);
            $value = $value * 1000;
        } else $value = 0;
        return $value;
    }

    /**
     * конвертирует проценты избавляя от дробной части
     * @param $value
     * @return float|int|mixed|null
     */
    public static function convertPercent($value){
        if ($value != '') {
            $value = str_replace(',', '.', $value);
            $value = $value * 100;
        } else $value = null;
        return $value;
    }

    /**
     * конвертирует сумму из копеек в рубли
     * @param $value
     * @return float|int|mixed|null
     */
    public static function reconvertSum($value){
        if ($value != '') {
            $value = $value / 100;
            $value = str_replace('.', ',', $value);
        } else $value = null;
        return $value;
    }

    /**
     * конвертирует количество из целых в дробные
     * @param $value
     * @return float|int|mixed
     */
    public static function reconvertKol($value){
        if ($value != '') {
            $value = $value / 1000;
            $value = str_replace('.', ',', $value);
        } else $value = null;
        return $value;
    }

    /**
     * конвертирует проценты добавляя дробную часть
     * @param $value
     * @return float|int|mixed|null
     */
    public static function reconvertPercent($value){
        if ($value != '') {
            $value = $value / 100;
            $value = str_replace('.', ',', $value);
        } else $value = null;
        return $value;
    }

    /**
     * формирует по умолчанию сео описание исходя из хлебной крошки
     */
    public static function showSeoDesc($breadcrumbs = [])
    {
//            Core::dump($breadcrumbs);die;
        $desc = '';
        if (count($breadcrumbs) > 0) {
            foreach ($breadcrumbs as $item) {
                $url = $item['url'] ?? null;
                if ($url !== null) {
                    $desc = $item['label'];
                }else $desc .= ". $item. Pharmznanie - Онлайн обучение фармацевтов и провизоров.";
            }
        }

        //Core::dump($desc);die;
        return $desc;
    }

    static function isDate($value)
    {
        if (!$value) {
            return false;
        }

        try {
            new \DateTime($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


}




/*

class NumToText
{
    public $onlyWord = false;

    public $Mant = array(); // описания мантисс
// к примеру ('рубль', 'рубля', 'рублей')
// или ('метр', 'метра', 'метров')
    public $Expon = array(); // описания экспонент

// к примеру ('копейка', 'копейки', 'копеек')

    public function __construct()
    {

    }

// установка описания мантисс
    public function SetMant($mant)
    {
        $this->Mant = $mant;
    }

// установка описания экспонент
    public function SetExpon($expon)
    {
        $this->Expon = $expon;
    }

// функция возвращает необходимый индекс описаний разряда
// ('миллион', 'миллиона', 'миллионов') для числа $ins
// например для 29 вернется 2 (миллионов)
// $ins максимум два числа
    public function DescrIdx($ins)
    {
        if (intval($ins / 10) == 1) // числа 10 - 19: 10 миллионов, 17 миллионов
            return 2;
        else {
// для остальных десятков возьмем единицу
            $tmp = $ins % 10;
            if ($tmp == 1) // 1: 21 миллион, 1 миллион
                return 0;
            else if ($tmp >= 2 && $tmp <= 4)
                return 1; // 2-4: 62 миллиона
            else
                return 2; // 5-9 48 миллионов
        }
    }

// IN: $in - число,
// $raz - разряд числа - 1, 1000, 1000000 и т.д.
// внутри функции число $in меняется
// $ar_descr - массив описаний разряда ('миллион', 'миллиона', 'миллионов') и т.д.
// $fem - признак женского рода разряда числа (true для тысячи)
    public function DescrSot(&$in, $raz, $ar_descr, $fem = false)
    {
        $ret = '';

        $conv = intval($in / $raz);
        $in %= $raz;

        $descr = $ar_descr[$this->DescrIdx($conv % 100)];

        if ($conv >= 100) {
            $Sot = array('сто', 'двести', 'триста', 'четыреста', 'пятьсот',
                'шестьсот', 'семьсот', 'восемьсот', 'девятьсот');
            $ret = $Sot[intval($conv / 100) - 1] . ' ';
            $conv %= 100;
        }

        if ($conv >= 10) {
            $i = intval($conv / 10);
            if ($i == 1) {
                $DesEd = array('десять', 'одиннадцать', 'двенадцать', 'тринадцать',
                    'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать',
                    'восемнадцать', 'девятнадцать');
                $ret .= $DesEd[$conv - 10] . ' ';
                if (count($ar_descr) > 0)
                    $ret .= $descr;
// возвращаемся здесь
                return $ret;
            }
            $Des = array('двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят',
                'семьдесят', 'восемьдесят', 'девяносто');
            $ret .= $Des[$i - 2] . ' ';
        }

        $i = $conv % 10;
        if ($i > 0) {
            if ($fem && ($i == 1 || $i == 2)) {
// для женского рода (сто одна тысяча)
                $Ed = array('одна', 'две');
                $ret .= $Ed[$i - 1] . ' ';
            } else {
                $Ed = array('один', 'два', 'три', 'четыре', 'пять',
                    'шесть', 'семь', 'восемь', 'девять');
                $ret .= $Ed[$i - 1] . ' ';
            }
        }
        if (count($ar_descr) > 0)
            $ret .= $descr;
        return $ret;
    }

// IN: $sum - число, например 1256.18
    public function Convert($sum)
    {
        $ret = '';

// имена данных перменных остались от предыдущей версии
// когда скрипт конвертировал только денежные суммы
        $Kop = 0;
        $Rub = 0;

        $sum = trim($sum);
// удалим пробелы внутри числа
        $sum = str_replace(' ', '', $sum);

// флаг отрицательного числа
        $sign = false;
        if ($sum[0] == '-') {
            $sum = substr($sum, 1);
            $sign = true;
        }

// заменим запятую на точку, если она есть
        $sum = str_replace(',', '.', $sum);

        $Rub = intval($sum);
        $Kop = round($sum * 100 - $Rub * 100);

        if ($Rub) {
// значение $Rub изменяется внутри функции DescrSot
// новое значение: $Rub %= 1000000000 для миллиарда
            if ($Rub >= 1000000000)
                $ret .= $this->DescrSot($Rub, 1000000000, array('миллиард', 'миллиарда', 'миллиардов')) . ' ';
            if ($Rub >= 1000000)
                $ret .= $this->DescrSot($Rub, 1000000, array('миллион', 'миллиона', 'миллионов')) . ' ';
            if ($Rub >= 1000)
                $ret .= $this->DescrSot($Rub, 1000, array('тысяча', 'тысячи', 'тысяч'), true) . ' ';

            $ret .= $this->DescrSot($Rub, 1, $this->Mant) . ' ';

// если необходимо поднимем регистр первой буквы
            $ret = iconv('utf-8', 'windows-1251', $ret);
            $ret[0] = chr(ord($ret[0]) + ord('A') - ord('a'));
            $ret = iconv('windows-1251', 'utf-8', $ret);
// для корректно локализованных систем можно закрыть верхнюю строку
// и раскомментировать следующую (для легкости сопровождения)
// $ret[0] = strtoupper($ret[0]);
        }
        if ($this->onlyWord !== true) {
            if ($Kop < 10)
                $ret .= '0';
            $ret .= $Kop . ' ' . $this->Expon[$this->DescrIdx($Kop)];
        }

// если число было отрицательным добавим минус
        if ($sign)
            $ret = '-' . $ret;
        return $ret;
    }

}

class ManyToText extends NumToText
{

    public function __construct()
    {
        $this->SetMant(array('рубль', 'рубля', 'рублей'));
        $this->SetExpon(array('копейка', 'копейки', 'копеек'));
    }

}

class MetrToText extends NumToText
{

    public function __construct()
    {
        $this->SetMant(array('метр', 'метра', 'метров'));
        $this->SetExpon(array('сантиметр', 'сантиметра', 'сантиметров'));
    }

}
*/




