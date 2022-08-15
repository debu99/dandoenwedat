<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Usersponsorshippayrequests.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Model_DbTable_Usersponsorshippayrequests extends Engine_Db_Table {

  protected $_name = 'sesevent_usersponsorshippayrequests';
  protected $_rowClass = "Sesevent_Model_Usersponsorshippayrequest";

  public function getPaymentRequests($params = array()) {
    $tabeleName = $this->info('name');
    $select = $this->select()->from($tabeleName);
    if (isset($params['event_id']))
      $select->where('event_id =?', $params['event_id']);

    if (isset($params['state']) && $params['state'] == 'complete')
      $select->where('state =?', $params['state']);

    $select->where('is_delete	= ?', '0');
    return $this->fetchAll($select);
  }

}
