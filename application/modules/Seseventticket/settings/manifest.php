<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return
array (
  'package' => array(
      'type' => 'module',
      'name' => 'seseventticket',
      'version' => '5.0.0',
      'path' => 'application/modules/Seseventticket',
      'title' => 'SES - Advanced Events - Events Tickets Selling & Booking System',
      'description' => 'SES - Advanced Events - Events Tickets Selling & Booking System',
      'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
      'actions' => array(
          'install',
          'upgrade',
          'refresh',
          'enable',
          'disable',
      ),
      'callback' => array(
          'path' => 'application/modules/Seseventticket/settings/install.php',
          'class' => 'Seseventticket_Installer',
      ),
      'directories' => array(
          'application/modules/Seseventticket',
      ),
      'files' => array(
          'application/languages/en/seseventticket.csv',
      ),
  ),
);
