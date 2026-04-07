<?php 
namespace processor\htmlhelper;

//security
if(!defined('IS_ALLOWED')){
    http_response_code(403);
    exit('Direct access to this script is forbidden.');
}

function key_empty($key,$tag_to_add = null){
    if($key===''||$key==null||strtolower(trim($key))=='none') return '';
    else {
        if($tag_to_add) $key = ' ' . $tag_to_add . '="' . $key . '" ';
        return $key;
        }
}
?>