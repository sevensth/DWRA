<?php

//debug
define('DEBUG', 0);
define('DISABLE_ERROR_RESPONSE', 0);
ini_set("error_reporting", E_ALL);
ini_set("display_errors", DEBUG);

//global defines
define('WP_IDENTICON_ROOT_PATH', dirname(__FILE__));
define('DW_IDENTICON_SUBSITE_DIR',  '');
define('DW_IDENTICON_CACHE_DIR', 'identicon');
define('DW_IDENTICON_STATIC_DIR', 'static');
define('DW_IDENTICON_MIN_SIZE', 8);
define('DW_IDENTICON_MAX_SIZE', 1024);
define('DW_IDENTICON_SEED_MIN_LEN', 10);
define('DW_IDENTICON_SEED_MAX_LEN', 512);

define('DW_IDENTICON_ERR_INVALID_SEED', -1);
define('DW_IDENTICON_ERR_INVALID_SIZE', -2);
define('DW_IDENTICON_ERR_INVALID_AUTH_INFO', -3);
define('DW_IDENTICON_ERR_AUTH_FAILED', -101);

//auth keys
$AuthCheckPool = [
    'e15b48f0' => '7edb2d4a6fb17fbf5a3452ca4e34161e87398c55b9e595948589be8c8cd38340',
];

//util functions
function ResponseErrorToClientAndExit($errNo)
{
    $httpCode = 0;
    $message = '';
    switch ($errNo)
    {
        case DW_IDENTICON_ERR_INVALID_SEED:
            $httpCode = 404;
            $message = 'Not Found';
            break;
        case DW_IDENTICON_ERR_INVALID_SIZE:
            $httpCode = 404;
            $message = 'Not Found';
            break;
        case DW_IDENTICON_ERR_INVALID_AUTH_INFO:
            $httpCode = 422;
            $message = 'Unprocessable Entity';
            break;
        case DW_IDENTICON_ERR_AUTH_FAILED:
            $httpCode = 403;
            $message = 'Forbidden';
            break;

        default:
            break;
    }

    //header("HTTP/1.0 404 Not Found");
    $headerString = $_SERVER['SERVER_PROTOCOL'] . " $httpCode $message";
    if (!DISABLE_ERROR_RESPONSE)
    {
        header($headerString);
        exit;
    }
}

//preprocess options
//seed
$seed = $_REQUEST['seed'];
if (!$seed)
{
    $uri = strtok($_SERVER["REQUEST_URI"], '?');
    list($empty, $seed, $rest) = explode(DW_IDENTICON_SUBSITE_DIR . DIRECTORY_SEPARATOR, $uri, 3);
}
$seedLength = strlen($seed);
if ($seedLength < DW_IDENTICON_SEED_MIN_LEN || $seedLength > DW_IDENTICON_SEED_MAX_LEN)
{
    ResponseErrorToClientAndExit(DW_IDENTICON_ERR_INVALID_SEED);
}

//authenticate
$auth = $_REQUEST['auth'];
$authCheckKey = $_REQUEST['ak'];
$authCheck = $AuthCheckPool[$authCheckKey];
if (strlen($auth) > 0 && strlen($authCheck) > 0)
{
    include "auth.lib.php";
    $targetHash = DWIdenticonAuthHash($seed, $authCheck);
    if ($targetHash != $auth)
    {
        ResponseErrorToClientAndExit(DW_IDENTICON_ERR_AUTH_FAILED);
    }
}
else
{
    ResponseErrorToClientAndExit(DW_IDENTICON_ERR_INVALID_AUTH_INFO);
}

//size
$size = intval($_REQUEST['s']);
if ($size < DW_IDENTICON_MIN_SIZE || $size > DW_IDENTICON_MAX_SIZE)
{
    $size = 80;
}

//gravatar redirect
$gravatar = strtolower($_REQUEST['gr']);
$gravatar = $gravatar == 'true' || $gravatar = 1;

//get static
$avatarList = glob(WP_IDENTICON_ROOT_PATH . DIRECTORY_SEPARATOR . DW_IDENTICON_STATIC_DIR . DIRECTORY_SEPARATOR . $seed . '.*', GLOB_NOSORT);
$avatar = $avatarList[0];
if (!$avatar)
{
    //generate avatar
    include 'wp_identicon.php';
    $idc = new identicon();
    //function identicon_build($seed='',$altImgText='',$img=true,$outsize='',$write=true,$random=true,$displaysize='',$gravataron=true)
    $avatar = $idc->identicon_build($seed, '', false, $size, true, true, $size, $gravatar);
}

//output image
$filename = basename($avatar);
$file_extension = strtolower(substr(strrchr($filename,"."),1));
$contentType = 'application/octet-stream';
switch($file_extension)
{
    case "gif": $contentType="image/gif"; break;
    case "png": $contentType="image/png"; break;
    case "jpeg":
    case "jpg": $contentType="image/jpeg"; break;
    default:
}

if (DEBUG)
{
    echo WP_IDENTICON_ROOT_PATH . DIRECTORY_SEPARATOR . DW_IDENTICON_STATIC_DIR . DIRECTORY_SEPARATOR . $seed . '.*', GLOB_NOSORT;
    $avatarList = glob(WP_IDENTICON_ROOT_PATH . DIRECTORY_SEPARATOR . DW_IDENTICON_STATIC_DIR . DIRECTORY_SEPARATOR . $seed . '.*', GLOB_NOSORT);
    var_dump($avatarList);
    echo "seed:$seed, len:$seedLength";
    echo '<pre>';
    echo "[$seed]";
    print_r($_SERVER);
    echo '</pre>';
    echo $avatar;
    echo filesize($avatar);
    echo "<img src=\"data:image/png;base64," . base64_encode(file_get_contents($avatar)) . "\" width=\"$size\" height=\"$size\" />";
    var_dump($idc->limitCache());
    exit;
}

header('Content-type: ' . $contentType);
header('Content-Length: ' . filesize($avatar));
@readfile($avatar);

exit;