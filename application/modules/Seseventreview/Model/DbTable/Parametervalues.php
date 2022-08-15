<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Parametervalues.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Model_DbTable_Parametervalues extends Engine_Db_Table {
  protected $_rowClass = 'Seseventreview_Model_Parametervalue';
	protected $_name  = 'seseventreview_review_parametervalues';
	public function getParameters($params = array()){
		$pTable = Engine_Api::_()->getDbtable('parameters', 'seseventreview');
    	$pTableName = $pTable->info('name');
		$tablename = $this->info('name');
		$select = $this->select()
							->from($tablename)
							->setIntegrityCheck(false)
            	->joinLeft($pTableName, "$pTableName.parameter_id = $tablename.parameter_id", array("title"))
							->where($tablename.'.content_id =?',$params['content_id'])
							->where($tablename.'.user_id =?',$params['user_id'])
							->where($pTableName.'.parameter_id != ?','');
			return $this->fetchAll($select);
		
	}
	public function ratingCount($parameter_id = NULL,$resource_type = 'sesevent_event'){
    $rName = $this->info('name');
    return $this->select()
		->from($rName,new Zend_Db_Expr('COUNT(parametervalue_id) as total_rating'))
		->where('resources_type =?',$resource_type)
		->where($rName.'.parameter_id = ?', $parameter_id)
		->limit(1)->query()->fetchColumn();
  }
   // rating functions
  public function getRating($parameter_id, $resource_type = 'sesevent_event') {
    $rating_sum = $this->select()
            ->from($this->info('name'), new Zend_Db_Expr('SUM(rating)'))
            ->group('parameter_id')
            ->where('parameter_id = ?', $parameter_id)
            ->where('resources_type =?', $resource_type)
            ->query()
            ->fetchColumn(0)
    ;
    $total = $this->ratingCount($parameter_id, $resource_type = 'sesevent_event');
    if ($total)
      $rating = $rating_sum / $total;
    else
      $rating = 0;
    return $rating;
  }
  public function getSumRating($parameter_id, $resource_type = 'sesevent_event') {
    $rName = $this->info('name');
    $rating_sum = $this->select()
    ->from($rName, new Zend_Db_Expr('SUM(rating)'))
    ->where('parameter_id = ?', $parameter_id)
    ->where('resources_type = ?', $resource_type)
    ->group('parameter_id')
    ->query()
    ->fetchColumn();
    return $rating_sum;
  }
}
