<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Pinterest.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Model_DbTable_Pinterest extends Engine_Db_Table {

    protected $_name = 'user_pinterest';

    public function isConnected() {
        // Need to initialize
        $settings['pinterest_appid'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.pinterest.appid', '');
        $settings['pinterest_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.pinterest.secret', '');
        $settings['pinterest_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.pinterest.enable', '0');
        if (!$settings['pinterest_appid'] || !$settings['pinterest_secret'] || !$settings['pinterest_enable'])
            return false;
        return true;
    }

}
