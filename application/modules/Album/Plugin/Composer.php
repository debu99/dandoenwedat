<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Composer.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Plugin_Composer extends Core_Plugin_Abstract
{
  public function onAttachPhoto($data)
  {
    if( !is_array($data) || empty($data['photo_id']) ) {
      return;
    }

    $photos = array();
    $photo = null;
    foreach( explode(',', $data['photo_id']) as $photoId ) {
      $photo = Engine_Api::_()->getItem('album_photo', $photoId);
      if( !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() ) {
        continue;
      }
      $photos[] = $photo;
    }

    if( count($photos) !=1 ) {
      return $photos;
    }

    if( !empty($data['actionBody']) && empty($photo->description) ) {
      $photo->description = $data['actionBody'];
      $photo->save();
    }

    return $photo;
  }

  public function onActivityActionUpdateAfter($event)
  {
    $payload = $event->getPayload();
    $modifiedFields = $payload->getModifiedFieldsName();
    $attachment = $payload->getFirstAttachment();
    if( in_array('body', $modifiedFields) && !empty($attachment) ) {
      $attachment = $payload->getFirstAttachment()->item;
      $attachmentType = $attachment->getType();
      $oldData = $payload->getCleanData();
      if( $attachmentType == 'album_photo' && $oldData['body'] == $attachment->description ) {
        $attachment->description = $payload['body'];
        $attachment->save();
      }
    }
  }

}
