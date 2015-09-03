<?php
/**
 * Created by PhpStorm.
 * User: Khadeev Fanis
 * Date: 03/09/15
 * Time: 22:38
 */

namespace Fandom\Lotinfo;


class Curl
{
    static public $ERROR;

    static function getFile($file = false, $dir, $objectType, $url = false, $arGet = array()){

        if(!$file && !$url){
            self::$ERROR = \Helper::boldColorText("Не задано имя файла или URL", "red");
            return false;
        }

        $file = $dir.'/'.str_replace('%ID', $objectType, $file);

        if(!empty($arGet)){
            $arGet['getData'] .= $objectType;
            $url .= '?'.http_build_query($arGet);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0" .
            "; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR" .
            " 3.0.04506.30)");

        $content = curl_exec($ch);

        if(curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200){
            $status = true;
        }
        else{
            $status = false;
        }

        curl_close($ch);
        unset($$ch, $cookie);

        if($status){
            file_put_contents($file, $content);
            return $file;
        }else{
            self::$ERROR = '<b style="color: red">Ошибка обращения к серверу - '.CURLINFO_HTTP_CODE.'</b>';
            return false;
        }
    }
}