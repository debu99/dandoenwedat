<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorship.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Sponsorship extends Core_Model_Item_Abstract {
  protected $_searchTriggers = false;
	public function getPhotoUrl($type = NULL) {
    $photo_id = $this->photo_id;
    if ($photo_id) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, $type);
			if(!$file){
				$settings = Engine_Api::_()->getApi('settings', 'core');
		 		$defaultPhoto = $settings->getSetting('sesevent_sponsor_default_photo', 'application/modules/Sesevent/externals/images/nophoto_event_thumb_profile.png');
     		return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);	
			}
      return $file->map();
    } else {
			 $settings = Engine_Api::_()->getApi('settings', 'core');
		 	 $defaultPhoto = $settings->getSetting('sesevent_sponsor_default_photo', 'application/modules/Sesevent/externals/images/nophoto_event_thumb_profile.png');
     		return Engine_Api::_()->sesevent()->getFileUrl($defaultPhoto);	
    }
  }
	/**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
  */
  public function getHref($params = array()) {
		$event_id = $this->event_id;
		$custom_url = Engine_Api::_()->getItem('sesevent_event',$event_id)->custom_url;
    $params = array_merge(array(
        'route' => 'sesevent_sponsorship_view',
        'reset' => true,
        'id' =>  $this->sponsorship_id,
				'event_id'=>$custom_url,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }
  public function setPhoto($photo) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
    } else {
      throw new Sesevent_Model_Exception('invalid argument passed to setPhoto');
    }
    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_id' => $this->getIdentity(),
        'parent_type' => 'sesevent_sponsorship'
    );
		
    // Save
    $storage = Engine_Api::_()->storage();
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($path . '/m_' . $name)
            ->destroy();
    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    // Remove temp files
    @unlink($path . '/m_' . $name);
    // Update row
    $this->modified_date = date('Y-m-d H:i:s');
    $this->photo_id = $iMain->file_id;
    $this->save();
    return $this;
  }
}
