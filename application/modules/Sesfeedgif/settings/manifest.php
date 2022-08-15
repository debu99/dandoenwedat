<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array (
  'package' =>
  array(
    'type' => 'module',
    'name' => 'sesfeedgif',
    'version' => '4.10.3p1',
    'path' => 'application/modules/Sesfeedgif',
    'title' => '<span style="color:#DDDDDD">SNS - GIF Images & Giphy Integration with GIF Player Plugin</span>',
    'description' => '<span style="color:#DDDDDD">SNS - GIF Images & Giphy Integration with GIF Player Plugin</span>',
    'author' => '<a href="http://www.socialenginesolutions.com" style="text-decoration:underline;" target="_blank">SocialEngineSolutions</a>',
    'callback' => array(
      'path' => 'application/modules/Sesfeedgif/settings/install.php',
      'class' => 'Sesfeedgif_Installer',
    ),
    'actions' =>
    array(
      0 => 'install',
      1 => 'upgrade',
      2 => 'refresh',
      3 => 'enable',
      4 => 'disable',
    ),
    'directories' =>
    array(
      0 => 'application/modules/Sesfeedgif',
    ),
    'files' =>
    array(
      0 => 'application/languages/en/sesfeedgif.csv',
    ),
  ),
  //Items
  'items' => array(
    'sesfeedgif_image'
  ),
);
