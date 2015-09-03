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
}