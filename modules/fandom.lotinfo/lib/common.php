<?php
/**
 * Created by PhpStorm.
 * User: Khadeev Fanis
 * Date: 03/09/15
 * Time: 01:04
 */

namespace Fandom\Lotinfo;


class Common
{
    const TO = 'mf4564@gmail.com';
    const TO_TEST = 'fans7288@gmail.com';
    const FROM = 'noreply@alfa74.fans-site.ru.ru';

    public static function recRMDir($path){
        $path .= '/';
        $spisok = scandir($path);
        unset($spisok[0], $spisok[1]);
        $spisok = array_values($spisok);

        foreach ($spisok as $failik) :
            if ( is_dir($path. $failik) ) :
                self::recRMDir($path. $failik .'/');
                rmdir($path. $failik);
            else :
                unlink($path. $failik);
            endif;
        endforeach;
    }

    static function sendMail($text, $theme, $debug){
        $message = "
            <htlm>
                <head>
                    <title>Результат парсинга</title>
                </head>
                <body>
                    {$text}
                </body>
            </html>
        ";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        if($debug)
            $to = self::TO_TEST;
        else
            $to = self::TO . ', '.self::TO_TEST;
        // Дополнительные заголовки
        $headers .= "To: {$to}\r\n";
        $headers .= "From: {self::FROM}}\r\n";

        mail($to, $theme, $message, $headers);
    }
}