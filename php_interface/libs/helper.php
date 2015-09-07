<?
class Helper {

    static function pR($arr = array(), $display = true){
        global $USER;

        $none = '';

        if($display){
            if(!$USER->IsAdmin())
                $none = 'none';
        }

        echo "<noindex><pre style='display:".$none."'>"; print_r($arr); echo "</pre></noindex>";
    }

    static function includeFile($file, $path = false){
        if(!$path)
        {
            $dir = $_SERVER['DOCUMENT_ROOT'].'/include/';
            $includeDir = SITE_DIR."include/";
        }
        else
        {
            $dir = $_SERVER['DOCUMENT_ROOT'].'/'.$path.'/';
            $includeDir = SITE_DIR.$path."/";
        }

        if(!file_exists($dir.$file.".php")){
            $newFile = fopen($dir.$file.".php", 'w');
            fclose($newFile);
        }else{
            $GLOBALS['APPLICATION']->IncludeFile(
                $includeDir.$file.".php",
                Array(),
                Array("MODE"=>"html")
            );
        }
    }

    static function getParam($link){
        if(!empty($_SERVER['QUERY_STRING']))
            $get = '?'.$_SERVER['QUERY_STRING'].'&'.$link;
        else
            $get = '?'.$link;

        return $get;
    }


    public static function arrayPluck($array, $value, $key = null) {
        $results = array();

        if(!empty($array)){
            foreach ($array as $item) {
                $itemValue = is_object($item) ? $item->{$value} : $item[$value];

                // If the key is "null", we will just append the value to the array and keep
                // looping. Otherwise we will key the array using the value of the key we
                // received from the developer. Then we'll return the final array form.
                if (is_null($key)) {
                    $results[] = $itemValue;
                } else {
                    $itemKey = is_object($item) ? $item->{$key} : $item[$key];

                    $results[$itemKey] = $itemValue;
                }
            }
        }

        return $results;
    }

    public static function depthUrl($url)
    {
        $ar = explode('/', $url);

        $depth = count($ar) - 2;

        return $depth;
    }

    public static function inObject($val, $obj)
    {
        if($val == ""){
            return false;
        }
        if(!is_object($obj)){
            $obj = (object)$obj;
        }

        foreach($obj as $key => $value){
            if(!is_object($value) && !is_array($value)){
                if($value == $val){
                    return true;
                }
            }else{
                return self::inObject($val, $value);
            }
        }
        return false;
    }

    public static function boldColorText ($msg = false, $color = "") {
        $response = "";

        if ($msg) {
            $response = "<b style='color:{$color}'>{$msg}</b><br />";
        }

        return $response;
    }
}