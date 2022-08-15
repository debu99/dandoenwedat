<?php defined('_ENGINE') or die('Access Denied'); return array (
  'class' => 'Zend_Mail_Transport_Smtp',
  'args' => 
  array (
    0 =>  getenv('SMTP_SERVER_ADDRESS'),
    1 => 
    array (
      'port' => getenv('SMTP_PORT'),
      'ssl' => 'tls',
      'auth' => 'login',
      'username' => getenv('SMTP_USERNAME'),
      'password' => getenv('SMTP_PASSWORD'),
    ),
  ),
); ?>