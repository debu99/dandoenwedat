<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorshipdetail.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Sponsorshipdetail extends Core_Model_Item_Collection {
	protected $_searchTriggers = false;
  protected $_modifiedTriggers = false;
	public function getLogoUrl($type = NULL) {
    $logo_id = $this->logo_id;
    if ($logo_id) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->logo_id, $type);
      return $file->map();
    } else {
			 $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      return $view->layout()->staticBaseUrl.'application/modules/Sesevent/externals/images/nophoto_event_thumb_profile.png';
    }
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
        'parent_type' => 'sesevent_sponsorshipdetail'
    );
		
    // Save
    $storage = Engine_Api::_()->storage();
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(300, 300)
            ->write($path . '/m_' . $name)
            ->destroy();
    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    // Remove temp files
    @unlink($path . '/m_' . $name);
    // Update row
    $this->logo_id = $iMain->file_id;
    $this->save();
    return $this;
  }
}
