<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Hotmail.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Model_DbTable_Hotmail extends Engine_Db_Table {

    protected $_name = 'user_hotmail';

    public function isConnected() {
        // Need to initialize
        $settings['hotmail_appid'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmailclientid', '');
        $settings['hotmail_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmailclientsecret', '');
        $settings['hotmail_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.enable', '0');
        if (!$settings['hotmail_appid'] || !$settings['hotmail_secret'] || !$settings['hotmail_enable'])
            return false;
        return true;
    }

}
