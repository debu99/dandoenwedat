<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Albums.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Albums extends Engine_Db_Table {

  protected $_rowClass = "Sesevent_Model_Album";
  
  public function getUserAlbumCount($params = array()){
    return $this->select()->from($this->info('name'), new Zend_Db_Expr('COUNT(album_id) as total_albums'))->where('owner_id = ?', $params['user_id'])->limit(1)->query()->fetchColumn();
  }
  public function editPhotos(){
		$albumTable = Engine_Api::_()->getItemTable('sesevent_album');
    $myAlbums = $albumTable->select()
            ->from($albumTable, array('album_id', 'title'))
            ->where('owner_id = ?', Engine_Api::_()->user()->getViewer()->getIdentity())
            ->query()
            ->fetchAll();	
	 return $myAlbums;
	}
  public function getAlbumSelect($value = array()){
    // Prepare data
    $albumTableName = $this->info('name');
    $select = $this->select()
		    ->from($albumTableName)
		    ->where('search =?',1)
		    ->where($albumTableName.'.event_id =?',$value['event_id'])
		    ->group($albumTableName.'.album_id');
    return Zend_Paginator::factory($select);
  }
		public function getSpecialAlbum(User_Model_User $user, $type,$event_id)
  {
    if( !in_array($type, array('wall')) ) {
      //throw new Sesevent_Model_Exception('Unknown special album type');
    }
    $select = $this->select()
        ->where('owner_id = ?', $user->getIdentity())
        //->where('type = ?', $type)
				->where('event_id =?',$event_id)
        ->order('album_id ASC')
        ->limit(1);
    $album = $this->fetchRow($select);
    // Create wall photos album if it doesn't exist yet
    if( null === $album ) {
      $translate = Zend_Registry::get('Zend_Translate');
      $album = $this->createRow();
      $album->owner_id = $user->getIdentity();
      $album->title = $translate->_(ucfirst($type) . ' Photos');
     // $album->type = $type;
			$album->event_id = $event_id;
      $album->search = 1;
      $album->save();
    }
    return $album;
  }
}