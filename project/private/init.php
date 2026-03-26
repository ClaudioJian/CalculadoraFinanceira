<?php 
//file contain all constant like IS_ALLOWED
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
    }

  define('PRIVATE_PATH', __DIR__ .'/../private/');
  define('DOCUMENT_ROOT',__DIR__);
  //const for entry point, avoinding user enter directly
  define('IS_ALLOWED',true);

?>