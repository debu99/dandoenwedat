<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Photos.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Album_Model_DbTable_Photos extends Core_Model_Item_DbTable_Abstract
{
  protected $_rowClass = 'Album_Model_Photo';
  
  public function getPhotoSelect(array $params)
  {
    $select = $this->select()
      ->from($this->info('name'));

    if( !empty($params['album']) && $params['album'] instanceof Album_Model_Album ) {
      $select->where('album_id = ?', $params['album']->getIdentity());
    } else if( !empty($params['album_id']) && is_numeric($params['album_id']) ) {
      $select->where('album_id = ?', $params['album_id']);
    } else if (!empty($params['album_ids']) && is_array($params['album_ids'])) {
      $select->where('album_id IN (?)', $params['album_ids']);
    }

    if( !isset($params['order']) ) {
      $select->order('order DESC');
    } else if( is_string($params['order']) ) {
      $select->order($params['order'] . ' DESC');
    }

    if (!empty($params['search'])) {
      $select->where('title LIKE ? OR description LIKE ?', '%' . $params['search'] . '%');
    }

    if(!empty($params['tag'])) {
      $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
      $tmName = $tmTable->info('name');
      $rName = $this->info('name');
      $select
        ->joinLeft($tmName, "$tmName.resource_id = $rName.photo_id", NULL)
        ->where($tmName.'.resource_type = ?', 'album_photo')
        ->where($tmName.'.tag_id = ?', $params['tag']);
    }

    return $select;
  }
  
  public function getPhotoPaginator(array $params)
  {
    return Zend_Paginator::factory($this->getPhotoSelect($params));
  }
}
