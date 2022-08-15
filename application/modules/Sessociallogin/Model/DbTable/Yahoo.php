<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Yahoo.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Model_DbTable_Yahoo extends Engine_Db_Table {

    protected $_name = 'user_yahoo';

    public function isConnected() {
        // Need to initialize
        $settings['yahoo_consumerKey'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.yahooconsumerkey', '');
        $settings['yahoo_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.yahooconsumersecret', '');
        $settings['yahoo_appid'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.yahooappid', '');
        $settings['yahoo_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.yahoo.enable', '0');
       
        if (!$settings['yahoo_appid'] || !$settings['yahoo_consumerKey'] || !$settings['yahoo_secret'] || !$settings['yahoo_enable'])
            return false;
        return true;
    }

}
