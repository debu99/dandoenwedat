<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

return array (
  'package' =>
  array(
      'type' => 'module',
      'name' => 'sesfeelingactivity',
      'version' => '4.10.3p1',
      'path' => 'application/modules/Sesfeelingactivity',
      'title' => '<span style="color:#DDDDDD">SNS - Feelings & Activities Plugin</span>',
      'description' => '<span style="color:#DDDDDD">SNS - Feelings & Activities Plugin</span>',
      'author' => '<a href="http://www.socialenginesolutions.com" style="text-decoration:underline;" target="_blank">SocialEngineSolutions</a>',
      'callback' => array(
          'path' => 'application/modules/Sesfeelingactivity/settings/install.php',
          'class' => 'Sesfeelingactivity_Installer',
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
          0 => 'application/modules/Sesfeelingactivity',
      ),
      'files' =>
      array(
          0 => 'application/languages/en/sesfeelingactivity.csv',
      ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'sesfeelingactivity_feeling',
    'sesfeelingactivity_feelingicon',
  ),
);
