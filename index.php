<?php
  spl_autoload_register(function($class_name){
    // fix linux file address with slash
    $class_name = str_replace('\\','/', $class_name);
    require $class_name.'.php';
  }
