<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Vk.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
include(APPLICATION_PATH.'/application/modules/Sessociallogin/Api/Vk/Vkontakte.php');

class Sessociallogin_Model_DbTable_Vk extends Engine_Db_Table {
    protected $_name = 'user_vk';
    public function isConnected() {
        // Need to initialize
        $settings['sessociallogin_vkkey'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.vkkey', '');
        $settings['sessociallogin_vksecret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.vksecret', '');
        $settings['sessociallogin_vk_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.vk.enable', '0');
        if (!$settings['sessociallogin_vkkey'] || !$settings['sessociallogin_vksecret'] || !$settings['sessociallogin_vk_enable'])
            return false;
        return true;
    }
}