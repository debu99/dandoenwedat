<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Slidephotos.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Slidephotos extends Engine_Db_Table {
  protected $_rowClass = 'Sesevent_Model_Slidephoto';
	
	public function getSlides($param = array()){
		$select = $this->select()->order('order ASC');
		if(!isset($param['active']))
			$select->where('active =?',1);
		return $this->fetchAll($select);
	}
	
}
