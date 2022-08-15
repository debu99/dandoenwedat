<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Slides.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Model_DbTable_Slides extends Engine_Db_Table {

	protected $_rowClass = "Sespwa_Model_Slide";

  public function getSlides($id, $show_type = '',$status = false,$params = array()) {
    $tableName = $this->info('name');
    $select = $this->select()
            ->where('banner_id =?', $id);
    if(empty($show_type))
            $select->where('enabled =?', 1);
	   $select->from($tableName);
		if(isset($params['order']) && $params['order'] == 'random'){
			$select ->order('RAND()')	;
		}else
			$select ->order('order ASC');
	  if($status)
			$select = $select->where('status = 1');
    if(!empty($show_type))
        return Zend_Paginator::factory($select);
    else
        return $this->fetchAll($select);
  }

}
