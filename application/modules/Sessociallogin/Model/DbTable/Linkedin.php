<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Linkedin.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

require_once(APPLICATION_PATH . '/application/modules/Sessociallogin/Api/Linkedin/LinkedIn.php');

class Sessociallogin_Model_DbTable_Linkedin extends Engine_Db_Table {

    protected $_name = 'user_linkedin';
    protected $_api;

    public static function getLIInstance() {
        return Engine_Api::_()->getDbtable('linkedin', 'sessociallogin')->getApi();
    }

    public function getApi() {
        // Already initialized
        if (null !== $this->_api) {
            return $this->_api;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        // Need to initialize
        $settings['linkedin_access'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.access', '');
        $settings['linkedin_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.secret', '');
        $settings['linkedin_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.enable',false);
        if (empty($settings['linkedin_access']) ||
                empty($settings['linkedin_secret']) || empty($settings['linkedin_enable'])) {
            $this->_api = null;
            Zend_Registry::set('Linkedin_Api', $this->_api);
            return false;
        }

        $this->_api = new LinkedIn(array('appKey' => $settings['linkedin_access'], 'appSecret' => $settings['linkedin_secret']));
        Zend_Registry::set('Linkedin_Api', $this->_api);

        // Try to log viewer in?
        if (!empty($_SESSION['linkedin_uid'])) {
            $_SESSION['linkedin_lock'] = true;
            $lin_uid = Engine_Api::_()->getDbtable('linkedin', 'sessociallogin')
                    ->fetchRow(array('user_id = ?' => $viewer->getIdentity()));
            if ($lin_uid) {
                $_SESSION['linkedin_uid'] = $lin_uid['linkedin_uid'];
                $_SESSION['linkedin_secret'] = $lin_uid['code'];
                $_SESSION['linkedin_token'] = $lin_uid['access_token'];
                $this->_api->setTokenAccess($_SESSION['linkedin_access']);
            }
        } else
            $_SESSION['linkedin_lock'] = '';

        return $this->_api;
    }

    public function isConnected() {
        // Need to initialize
        $settings['linkedin_access'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.access', '');
        $settings['linkedin_secret'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.secret', '');
        $settings['linkedin_enable'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.linkedin.enable','0');
        if (!$settings['linkedin_access'] || !$settings['linkedin_secret'] || !$settings['linkedin_enable'])
            return false;
        return true;
    }

}
