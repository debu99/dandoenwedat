<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Sponsorshiprequests.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Sponsorshiprequests extends Engine_Db_Table {
  protected $_name = 'sesevent_sponsorshiprequests';
  protected $_rowClass = "Sesevent_Model_Sponsorshiprequest";
  public function getRequests($params = array()) {
    $tabeleName = $this->info('name');
    $select = $this->select()->from($tabeleName);
    if (isset($params['event_id']))
      $select->where('event_id =?', $params['event_id']);
    if (isset($params['user_id']) && $params['user_id'])
      $select->where('user_id =?', $params['user_id']);
		$select->order('sponsorshiprequest_id DESC');
    return $this->fetchAll($select);
  }
}