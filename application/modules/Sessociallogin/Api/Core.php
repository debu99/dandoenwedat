<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Api_Core extends Core_Api_Abstract {

  public function iconStyle() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $count = 0;
    $googleTable = Engine_Api::_()->getDbTable('google', 'sessociallogin');
    if ($googleTable->isConnected())
      $count++;
    $yahooTable = Engine_Api::_()->getDbTable('yahoo', 'sessociallogin');
    if ($yahooTable->isConnected())
      $count++;
    $instagramTable = Engine_Api::_()->getDbTable('instagram', 'sessociallogin');
    if ($instagramTable->isConnected())
      $count++;
    $pinterestTable = Engine_Api::_()->getDbTable('pinterest', 'sessociallogin');
    if ($pinterestTable->isConnected())
      $count++;
    $linkedinTable = Engine_Api::_()->getDbTable('linkedin', 'sessociallogin');
    if ($linkedinTable->isConnected())
      $count++;
    $hotmailTable = Engine_Api::_()->getDbTable('hotmail', 'sessociallogin');
    if ($hotmailTable->isConnected())
      $count++;
    $flickrTable = Engine_Api::_()->getDbTable('flickr', 'sessociallogin');
    if ($flickrTable->isConnected())
      $count++;
    $flickrTable = Engine_Api::_()->getDbTable('flickr', 'sessociallogin');
    if ($flickrTable->isConnected())
      $count++;
    $vkTable = Engine_Api::_()->getDbTable('vk', 'sessociallogin');
    if ($vkTable->isConnected())
      $count++;
    if ('none' != $settings->getSetting('core_facebook_enable', 'none') && $settings->core_facebook_secret && Engine_Api::_()->getDbtable('facebook', 'user')->getApi())
      $count++;
    if ('none' != $settings->getSetting('core_twitter_enable', 'none') && $settings->core_twitter_secret)
      $count++;
    return $count;
  }
}
