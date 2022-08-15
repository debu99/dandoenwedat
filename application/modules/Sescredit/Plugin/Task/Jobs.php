<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Jobs.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Plugin_Task_Jobs extends Core_Plugin_Task_Abstract {

  public function execute() {
    $creditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
    $month = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.month', 0);
    $year = Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.year', 0);
    $endDate = "date_format(date_add(date_add(first_activity_date,interval $month month),interval $year year),'%Y-%m-%d')";
    $select = $creditDetailTable->select()
            ->from($creditDetailTable->info('name'), array('*'))
            ->where(new Zend_Db_Expr($endDate) . " <= date('Y-m-d')")
            ->where('first_activity_date != ?','0000-00-00 00:00:00');
    $creditOwners = $creditDetailTable->fetchAll($select);
    foreach ($creditOwners as $creditOwner) {
      $creditDetailTable->update(array('total_credit' => 0, 'first_activity_date' => '0000-00-00'), array('owner_id =?' => $creditOwner->owner_id));
    }
    return true;
  }

}
