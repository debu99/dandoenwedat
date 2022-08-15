<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Reviews.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Model_DbTable_Eventreviews extends Engine_Db_Table {
  protected $_rowClass = 'Seseventreview_Model_Eventreview';
	protected $_name = "seseventreview_reviews";
	public function getEventReviewSelect($params = array()) {

    $table = $this;
    $eventReviewTableName = $table->info('name');
    $select = $table->select()->from($eventReviewTableName);

    $currentTime = date('Y-m-d H:i:s');
    if (isset($params['view'])) {
      if ($params['view'] == 'week') {
        $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
        $select->where("DATE(creation_date) between ('$endTime') and ('$currentTime')");
      } elseif ($params['view'] == 'month') {
        $endTime = date('Y-m-d H:i:s', strtotime("-1 month"));
        $select->where("DATE(creation_date) between ('$endTime') and ('$currentTime')");
      }
    }

    //Full Text	
    if (isset($params['order']) && !empty($params['order'])) {
      /*if ($params['order'] == 'featured')
        $select->where('featured = ?', '1');
      elseif ($params['order'] == 'verified')
        $select->where('verified = ?', '1');
      if ($params['order'] == 'week') {
        $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
        $select->where("DATE(creation_date) between ('$endTime') and ('$currentTime')");
      } elseif ($params['order'] == 'month') {
        $endTime = date('Y-m-d H:i:s', strtotime("-1 month"));
        $select->where("DATE(creation_date) between ('$endTime') and ('$currentTime')");
      }*/
    }
    if (!empty($params['search_text']))
      $select->where('`' . $eventReviewTableName . '`.`title` LIKE ?', '%' . $params['search_text'] . '%');
    if (!empty($params['owner_id']))
      $select->where('`' . $eventReviewTableName . '`.`owner_id` = ?', $params['owner_id']);
    //if (!empty($params['review_stars']))
    //  $select->where('`' . $eventReviewTableName . '`.`rating` = ?', $params['review_stars']);
    if (!empty($params['review_recommended']))
      $select->where('`' . $eventReviewTableName . '`.`recommended` = ?', $params['review_recommended']);
    if (isset($params['order']) && $params['order'] != '') {
      $select->order($eventReviewTableName . '.' . $params['order']);
    }
    /*if (isset($params['criteria'])) {
      if ($params['criteria'] == 1)
        $select->where($eventReviewTableName . '.featured =?', '1');
      else if ($params['criteria'] == 2)
        $select->where($eventReviewTableName . '.verified =?', '1');
      else if ($params['criteria'] == 3)
        $select->where($eventReviewTableName . '.featured = 1 OR ' . $eventReviewTableName . '.verified = 1');
      else if ($params['criteria'] == 4)
        $select->where($eventReviewTableName . '.featured = 0 AND ' . $eventReviewTableName . '.verified = 0');
    }*/
		// $select->order($eventReviewTableName . '.creation_date DESC');
    if (isset($params['info'])) {
      switch ($params['info']) {
        case 'most_viewed':
          $select->order('view_count DESC');
          break;
        case 'most_liked':
          $select->order('like_count DESC');
          break;
        case 'like_count':
          $select->order('like_count DESC');
          break;
        case 'most_commented':
          $select->order('comment_count DESC');
          break;
        case 'comment_count':
          $select->order('comment_count DESC');
          break;
        case "view_count":
          $select->order($eventReviewTableName . '.view_count DESC');
          break;
        case "most_rated":
          $select->order($eventReviewTableName . '.rating DESC');
          break;
        case "least_rated":
          $select->order($eventReviewTableName . '.rating ASC');
          break;
        case 'random':
          $select->order('Rand()');
          break;
        case "verified1" :
          $select->where($eventReviewTableName . '.verified' . ' = 1')
                  ->order($eventReviewTableName . '.owner_id DESC');
          break;
        case "featured1" :
          $select->where($eventReviewTableName . '.featured' . ' = 1')
                  ->order($eventReviewTableName . '.owner_id DESC');
          break;
        case "creation_date":
          $select->order($eventReviewTableName . '.creation_date DESC');
          break;
        case "modified_date":
          $select->order($eventReviewTableName . '.modified_date DESC');
          break;
      }
    }

    if (isset($params['widgetName']) && $params['widgetName'] == 'oftheday') {
      $select->where($eventReviewTableName . '.oftheday =?', 1)
              ->where($eventReviewTableName . '.starttime <= DATE(NOW())')
              ->where($eventReviewTableName . '.endtime >= DATE(NOW())')
              ->limit(1)
              ->order('RAND()');
    }
    
    if(isset($params['event_id']) && !empty($params['event_id']))
    $select->where($eventReviewTableName . '.event_id =?', $params['event_id']);
    if(isset($params['content_id']) && !empty($params['content_id']))
    $select->where($eventReviewTableName . '.content_id =?', $params['content_id']);
    
    if (isset($params['limit']) && !empty($params['limit']))
      $select->limit($params['limit']);
    if (!empty($params['limit_data']))
      $select->limit($params['limit_data']);
    $select->order($eventReviewTableName . '.creation_date DESC');
    //echo $select;die;
    if (isset($params['paginator']))
      return Zend_Paginator::factory($select);
    
    if (isset($params['fetchAll']))
      return $table->fetchAll($select);
    else
      return $select;
  }

	
  public function isReview($params = array()) {
    $select = $this->select()
            ->from($this->info('name'), $params['column_name']);
    if (isset($params['content_id']))
      $select->where('content_id = ?', $params['content_id']);

    if (isset($params['content_type']))
      $select->where('content_type = ?', $params['content_type']);
		$select->where('owner_id =?',Engine_Api::_()->user()->getViewer()->getIdentity());
    if (isset($params['module_name']))
      $select->where('module_name = ?', $params['module_name']);

    return $select = $select->query()->fetchColumn();
  }
  
  public function ratingCount($resource_id = NULL,$resource_type = 'sesevent_event'){

    $rName = $this->info('name');
    return $this->select()
		->from($rName,new Zend_Db_Expr('COUNT(review_id) as total_rating'))
		->where('content_type =?',$resource_type)
		->where($rName.'.content_id = ?', $resource_id)
		->limit(1)->query()->fetchColumn();
  }
   // rating functions
  public function getRating($resource_id, $resource_type = 'sesevent_event') {
    $rating_sum = $this->select()
            ->from($this->info('name'), new Zend_Db_Expr('SUM(rating)'))
            ->group('content_id')
            ->where('content_id = ?', $resource_id)
            ->where('content_type =?', $resource_type)
            ->query()
            ->fetchColumn(0)
    ;
    $total = $this->ratingCount($resource_id, $resource_type = 'sesevent_event');
    if ($total)
      $rating = $rating_sum / $total;
    else
      $rating = 0;
    return $rating;
  }
  public function getSumRating($resource_id, $resource_type = 'sesevent_event') {
    $rName = $this->info('name');
    $rating_sum = $this->select()
    ->from($rName, new Zend_Db_Expr('SUM(rating)'))
    ->where('content_id = ?', $resource_id)
    ->where('content_type = ?', $resource_type)
    ->group('content_id')
    ->group('content_type')
    ->query()
    ->fetchColumn();
    return $rating_sum;
  }

}
