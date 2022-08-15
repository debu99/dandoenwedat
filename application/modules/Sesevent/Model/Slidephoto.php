<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Slidephoto.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_Slidephoto extends Core_Model_Item_Abstract {
	
	protected $_searchTriggers = false;
	public function setPhoto($photo){
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
			$name = basename($file);
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
			$name = basename($photo['name']);
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
			$name = basename($file);
    } else {
      throw new Sesevent_Model_Exception('invalid argument passed to setPhoto');
    }
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_id' => $this->getIdentity(),
        'parent_type' => 'sesevent_slidephoto'
    );
    // Save
    $storage = Engine_Api::_()->storage();
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(1600, 1600)
            ->write($path . '/m_' . $name)
            ->destroy();
    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);   
    // Remove temp files
    @unlink($path . '/m_' . $name);
    // Update row
    $this->creation_date = date('Y-m-d H:i:s');
    $this->photo_id = $iMain->file_id;
    $this->save();
     return $this;
  	
	}
}