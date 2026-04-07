<?php 

if(!defined('IS_ALLOWED')){
    http_response_code(403);
    exit('Direct access to this script is forbidden.');
}

require_once PRIVATE_PATH . "processor/elements.php";
require_once PRIVATE_PATH . "processor/htmlhelper.php";

require_once __DIR__."/selector.php";
require_once __DIR__."/calculator.php";
require_once __DIR__."/resizer.php";

?>