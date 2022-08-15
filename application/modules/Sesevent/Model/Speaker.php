<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Speaker.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Speaker extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {

    $slug = $this->getSlug();
    $params = array_merge(array(
        'route' => 'sesevent_speakers',
        'reset' => true,
        'speaker_id' => $this->speaker_id,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, $reset);
  }

  public function getTitle() {
    return $this->name;
  }

  public function getPhotoUrl() {

    $photo_id = $this->photo_id;
    if (empty($photo_id)) {
			$settings = Engine_Api::_()->getApi('settings', 'core');
		 	$defaultPhoto = $settings->getSetting('sesevent_speaker_default_photo', 'application/modules/Sesspeaker/externals/images/nophoto_speaker_thumb_profile.png');
     	return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);
    } elseif ($photo_id) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, '');
			if(!$file){
				$settings = Engine_Api::_()->getApi('settings', 'core');
		 		$defaultPhoto = $settings->getSetting('sesevent_speaker_default_photo', 'application/modules/Sesspeaker/externals/images/nophoto_speaker_thumb_profile.png');
     	return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);	
			}
      return $file->map();
    }
  }

}
