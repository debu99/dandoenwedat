<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: List.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_List extends Core_Model_Item_Abstract {
  protected $_searchTriggers = false;
  public function getParent($recurseType = NULL) {
    return $this->getOwner();
  }

  public function addEvent($file_id, $event_id = null) {
    $list_event = Engine_Api::_()->getDbtable('listevents', 'sesevent')->createRow();
    $list_event->list_id = $this->getIdentity();
    $list_event->file_id = $event_id;
    $list_event->order = 0;
    $list_event->save();
    return $list_event;
  }

  public function getEvents($params = array(), $paginator = true) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $listEvents = Engine_Api::_()->getDbtable('listevents', 'sesevent');
		$listEventsName = $listEvents->info('name');
		$eventTableName = Engine_Api::_()->getDbTable('events', 'sesevent')->info('name');
    $select = $listEvents->select()
							->from($listEvents->info('name'))
            ->where('list_id = ?', $this->getIdentity())
						 ->joinLeft($eventTableName, "$eventTableName.event_id = $listEventsName.file_id", null)
						 ->where($eventTableName.'.event_id IS NOT NULL');
	  $select = $select->setIntegrityCheck(false);
    if (!isset($params) && !$params['order'])
      $select->order('order ASC');
    if ($paginator)
      return Zend_Paginator::factory($select);
    if (!empty($params['limit'])) {
      $select->limit($params['limit'])
              ->order('RAND() DESC');
    }
    return $listEvents->fetchAll($select);
  }

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {

    $params = array_merge(array(
        'route' => 'sesevent_list_view',
        'reset' => true,
        'list_id' => $this->list_id,
        'slug' => $this->getSlug(),
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, $reset);
  }

  public function getPhotoUrl($type = NULL) {

    $photo_id = $this->photo_id;
    if ($photo_id) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->photo_id, '');
      if ($file)
        return $file->map();
    }
			return	"application/modules/Sesevent/externals/images/nophoto_list_thumb_profile.png";
  }

  public function countEvents() {
    $eventTable = Engine_Api::_()->getItemTable('sesevent_listevent');
    return $eventTable->select()
                    ->from($eventTable, new Zend_Db_Expr('COUNT(listevent_id)'))
                    ->where('list_id = ?', $this->list_id)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
  }

  public function setPhoto($photo, $param = null) {

    if ($photo instanceof Zend_Form_Element_File)
      $file = $photo->getFileName();
    else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
			$name = basename($photo['name']);
    }
    else if (is_string($photo) && file_exists($photo))
      $file = $photo;
    else
      throw new Sesevent_Model_Exception('Invalid argument passed to setPhoto: ' . print_r($photo, 1));
    if(empty($name))
      $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'sesevent_list',
        'parent_id' => $this->getIdentity()
    );

    //Save
    $storage = Engine_Api::_()->storage();

    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($path . '/m_' . $name)
            ->destroy();

    //Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
            ->write($path . '/is_' . $name)
            ->destroy();

    //Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);
    $iMain->bridge($iMain, 'thumb.profile');
    $iMain->bridge($iSquare, 'thumb.icon');


    //Remove temp files
    @unlink($path . '/m_' . $name);
    @unlink($path . '/is_' . $name);

    if ($param == 'mainPhoto')
      $this->photo_id = $iMain->getIdentity();
    else
      $this->song_cover = $iMain->getIdentity();

    $this->save();

    return $this;
  }

}
