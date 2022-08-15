<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Instagram.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

require_once(APPLICATION_PATH . '/application/modules/Sessociallogin/Api/Instagram/Instagram.php');

class Sessociallogin_Model_DbTable_Instagram extends Engine_Db_Table {

    protected $_name = 'user_instagram';
    protected $_api;

    public function enable() {
        $settings['instagram_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.clientid', '');
        $settings['instagram_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.clientsecret', '');
        $enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.enable', 1);
        if (empty($settings['instagram_client']) ||
                empty($settings['instagram_secret']) || !$enable) {
            return false;
        }
        return true;
    }

    public static function getInInstance() {
        return Engine_Api::_()->getDbtable('instagram', 'sessociallogin')->getApi();
    }

    public function getApi($auth = false) {
        // Already initialized
        if (null !== $this->_api) {
            return $this->_api;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        // Need to initialize
        $settings['instagram_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.clientid', '');
        $settings['instagram_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.clientsecret', '');
        $settings['instagram_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.enable', 0);
        if (empty($settings['instagram_client']) ||
                empty($settings['instagram_secret']) || empty($settings['instagram_enable'])) {
            $this->_api = null;
            Zend_Registry::set('Instagram_Api', $this->_api);
            return false;
        }

        $this->_api = new Instagram(array(
            'apiKey' => $settings['instagram_client'],
            'apiSecret' => $settings['instagram_secret'],
            'apiCallback' => (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) . Zend_Registry::get('StaticBaseUrl') . 'sessociallogin/auth/instagram'
        ));
        Zend_Registry::set('Instagram_Api', $this->_api);
        if ($auth)
            return $this->_api;
        // Try to log viewer in?
        if (!empty($_SESSION['sessociallogin_instagram'])) {
            $_SESSION['instagram_lock'] = true;
            $inst_uid = Engine_Api::_()->getDbtable('instagram', 'sessociallogin')
                    ->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
            if ($inst_uid) {
                $this->_api->setAccessToken($inst_uid->access_token);
                $user = $this->_api->getUser();
                if (empty($user->data->username))
                    return false;
            }
        } else
            $_SESSION['instagram_lock'] = '';

        return $this->_api;
    }

    public function isConnected() {
        // Need to initialize
        $settings['instagram_client'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.clientid', '');
        $settings['instagram_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.clientsecret', '');
        $settings['instagram_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.instagram.enable', 0);
        if (empty($settings['instagram_client']) || empty($settings['instagram_secret']) || empty($settings['instagram_enable']))
            return false;
        return true;
    }

}