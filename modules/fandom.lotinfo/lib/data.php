<?php
/**
 * Created by PhpStorm.
 * User: fans
 * Date: 02.09.15
 * Time: 15:41
 */

namespace Fandom\Lotinfo;

class Data
{
    $tmpDir = $_SERVER["DOCUMENT_ROOT"].$config['tmpDir'];
    $config['xmlDir'] = $_SERVER["DOCUMENT_ROOT"].$config['xmlDir'];
    $xml_file = $config['xmlFile'];
    $log_file = $_SERVER["DOCUMENT_ROOT"].$config['logFile'];
    $apiUrl = $config['apiUrl'];
    $errors = '';
    $message = '';
    $email_text = '';

    public function __construct() {

    }
}