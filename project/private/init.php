<?php 
//file contain all constant like IS_ALLOWED
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
    }

  $top_dir = realpath(__DIR__.'/../'); //project

  define('PRIVATE_PATH', $top_dir .'/private/');
  define('DOCUMENT_ROOT', $top_dir .'/pubic_html/');
  //const for entry point, avoinding user enter directly
  define('IS_ALLOWED',true);

?>