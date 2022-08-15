<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Flickr.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
require_once(APPLICATION_PATH.'/application/modules/Sessociallogin/Api/Flickr/Flickr.php');
class Sessociallogin_Model_DbTable_Flickr extends Engine_Db_Table {

    protected $_name = 'user_flickr';

    public function isConnected() {
        // Need to initialize
        $settings['sessociallogin_flickrkey'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.flickrkey', '');
        $settings['sessociallogin_flickrsecret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.flickrsecret', '');
        $settings['sessociallogin_flickr_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.flickr.enable', '0');
        if (!$settings['sessociallogin_flickrkey'] || !$settings['sessociallogin_flickrsecret'] || !$settings['sessociallogin_flickr_enable'])
            return false;
        return true;
    }

}
