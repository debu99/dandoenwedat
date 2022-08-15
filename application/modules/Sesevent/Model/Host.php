<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Host.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Host extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  public function getHref($params = array()) {
    
    $sitehostredirect = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1); 
		if($sitehostredirect && $this->user_id) {
		  $user = Engine_Api::_()->getItem('user', $this->user_id);
		  return $user->getHref();
		} else {
			$params = array_merge(array(
	        'route' => 'sesevent_viewhost',
	        'controller' => 'index',
	        'action' => 'viewhost',
	        'host_id' => $this->host_id,
	            ), $params);
	    $route = @$params['route'];
	    unset($params['route']);
		}
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
  }

  public function getTitle() {
    $user = Engine_Api::_()->getItem('user', $this->user_id);

    return $user->getTitle();
  }

  /**
   * Gets a url to the current photo representing this item. Return null if none
   * set
   *
   * @param string The photo type (null -> main, thumb, icon, etc);
   * @return string The photo url
   */
  public function getPhotoUrl($type = null ,$test = null) { 
    $photo_id = $this->photo_id;
		$settings = Engine_Api::_()->getApi('settings', 'core');
    if (!$photo_id) {
			$defaultPhoto = $settings->getSetting('sesevent.host.default.photo', 'application/modules/Sesevent/externals/images/nophoto_host_thumb_icon.png');
      return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);
    }
    $file = Engine_Api::_()->getItemTable('storage_file')->getFile($photo_id, $type);
    if (!$file) {
      $defaultPhoto = Engine_Api::_()->sesevent()->getFileUrl($settings->getSetting('sesevent_host_default_photo', 'application/modules/Sesevent/externals/images/nophoto_host_thumb_icon.png'));
      return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);
    }
    return $file->map();
  }

}
