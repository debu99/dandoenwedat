<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Albumcomposer.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Plugin_Albumcomposer extends Core_Plugin_Abstract {
  public function onAttachSeseventphoto($data) { 
    if (!is_array($data) || empty($data['photo_id']))
      return;
    
    $photo = Engine_Api::_()->getItem('sesevent_photo', $data['photo_id']);
    if (!($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity())
      return;
    
    if (!empty($data['actionBody']) && empty($photo->description)) {
      $photo->description = $data['actionBody'];
      $photo->save();
    }
    return $photo;
  }
}
