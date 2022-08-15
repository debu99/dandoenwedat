<?php defined('_ENGINE') or die('Access Denied'); return array (
  'adapter' => 'mysqli',
  'params' => 
  array (
    'host' => getenv('MYSQL_HOST'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'dbname' => getenv('MYSQL_DATABASE'),
    'charset' => 'UTF8',
    'adapterNamespace' => 'Zend_Db_Adapter',
    'port' => NULL,
  ),
  'isDefaultTableAdapter' => true,
  'tablePrefix' => 'engine4_',
  'tableAdapterClass' => 'Engine_Db_Table',
); ?>
