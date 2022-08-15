<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Hosts.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Hosts extends Engine_Db_Table {
  protected $_rowClass = 'Sesevent_Model_Host';
	 public function getHosts($params = array()){
		 $select = $this->select()->where('owner_id =?',$params['owner_id'])->where('is_delete =?',0);
		 if(isset($params['value']))
		 	$select->where("host_name  LIKE ?  ", '%' . $params['value'] . '%');
		if(isset($params['type']))
			$select->where('type =?',$params['type']);
			if(empty($params['nolimit']))
				$select->limit(10);
		 return $this->fetchAll($select);
	 }
	 
	 public function hostInsert($valuesForm,$form, $type,$values,$isImport = false) {
    $table = Engine_Api::_()->getDbtable('hosts', 'sesevent');
	  $host = $table->createRow();
    $host->host_name = $values['host_name'];
    $host->host_description = $values['host_description'];
    $host->host_email = $values['host_email'];
    $host->host_phone = empty($values['host_phone']) ? 0 : $values['host_phone'];
		$host->facebook_url = $values['facebook_url'];
		$host->twitter_url = $values['twitter_url'];
		$host->website_url = $values['website_url'];
		$host->linkdin_url = $values['linkdin_url'];
		$host->googleplus_url = $values['googleplus_url'];
		if(!empty($values['toValues']) && $values['host_type'] == 'site') {
		  $user = Engine_Api::_()->getItem('user', $values['toValues']);
		  $host->host_name = $user->displayname;
		  $host->host_email = $user->email;
		  $host->photo_id = $user->photo_id;
			$host->user_id = $values['toValues'];
			$host->type = 'site';
	  } else {
		  $host->type = 'offsite';
	  }
		$host->owner_id =  !$isImport ? Engine_Api::_()->user()->getViewer()->getIdentity() : $valuesForm['user_id'];
		$host->ip_address = $_SERVER['REMOTE_ADDR'];
		$host->creation_date = date('Y-m-d H:i:s');
    $host->modified_date = date('Y-m-d H:i:s');
    $host->save();    
		if(isset($_FILES['host_photo']['name']) && $_FILES['host_photo']['name'] != ''){
			$host->photo_id = $this->setPhoto($_FILES['host_photo'],$host->host_id);
		}
		$host->save();
    return $host->host_id;
  }
	public function setPhoto($photo,$host_id){
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
        'parent_id' => $host_id,
        'parent_type' => 'sesevent_host'
    );
    
    // Save
    $storage = Engine_Api::_()->storage();
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($path . '/m_' . $name)
            ->destroy();            
            
    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(140, 160)
            ->write($path . '/in_' . $name)
            ->destroy();
    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);
    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;
    $image->resample($x, $y, $size, $size, 48, 48)
            ->write($path . '/is_' . $name)
            ->destroy();
    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iIconNormal = $storage->create($path . '/in_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');
    // Remove temp files
    @unlink($path . '/m_' . $name);
    @unlink($path . '/in_' . $name);
    @unlink($path . '/is_' . $name);
		return $iMain->file_id;
  }
  public function getHostId($params = array()) {
    $select  = $this->select()
                    ->from($this->info('name'), array('host_id'));
		if($params['host_type'] == 'offsite' && !empty($params['toValues'])) {
			$select->where('type =?', $params['host_type'])
							->where('host_id =?', $params['toValues']);
		} else if($params['host_type'] == 'site' && !empty($params['toValues'])) {
			$select->where('type =?', $params['host_type'])
						->where('user_id =?', $params['toValues']);
		}
		$select =  $select->query()
											->fetchColumn();
    return $select;
  }
  public function getAllHosts($params = array()) {
    $select = $this->select()->from($this->info('name'));
		
		if(isset($params['host_name']))
			$select->where('host_name =?',$params['host_name']);
		if(isset($params['host_type']))
			$select->where('type =?',$params['host_type']);
		if(isset($params['offtheday']))
			$select->where('offtheday =?',$params['offtheday']);
		if(isset($params['sponsored']))
			$select->where('sponsored =?',$params['sponsored']);
		if(isset($params['featured']))
			$select->where('featured =?',$params['featured']);
		if(isset($params['verified']))
			$select->where('verified =?',$params['verified']);
		
		if(isset($params['owner_id']))
			$select->where('owner_id =?',$params['owner_id']);
    if (isset($params['widgettype']) && $params['widgettype'] == 'widget')
      return $this->fetchAll($select);
    else
      return $paginator = Zend_Paginator::factory($select);
  }
  public function getOfTheDayResults($params = array()) {

    $select = $this->select()
            ->from($this->info('name'), array('*'))
            ->where('offtheday =?', 1)
            ->where('startdate <= DATE(NOW())')
            ->where('enddate >= DATE(NOW())')
            ->order('RAND()');
    return $this->fetchRow($select);
  }
  
  
  
}
