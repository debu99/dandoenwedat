<?php return array (
  'package' => 
  array (
    'type' => 'module',
    'name' => 'dummy-users',
    'version' => '4.0.0',
    'sku' => '0.1',
    'path' => 'application/modules/Dummyusers',
    'title' => 'Dummy Users',
    'description' => 'Creates 1000 dummy users with a profile.',
    'author' => 'tech-savvy',
    'callback' => 
    array (
      'class' => 'Engine_Package_Installer_Module',
    ),
    'actions' => 
    array (
      0 => 'install',
      1 => 'upgrade',
      2 => 'refresh',
      3 => 'enable',
      4 => 'disable',
    ),
    'directories' => 
    array (
      0 => 'application/modules/Dummyusers',
    ),
    'files' => 
    array (
      0 => 'application/languages/en/dummy-users.csv',
    ),
  ),
); ?>