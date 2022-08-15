<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Transactions.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Transactions extends Engine_Db_Table {

  protected $_rowClass = 'Sescredit_Model_Transaction';

  public function getBenefitStatus(User_Model_User $user = null) {
    // Get benefit setting
    $benefitSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.benefit');
    if (!in_array($benefitSetting, array('all', 'some', 'none'))) {
      $benefitSetting = 'all';
    }

    switch ($benefitSetting) {
      default:
      case 'all':
        return true;
        break;

      case 'some':
        if (!$user) {
          return false;
        }
        return (bool) $this->select()
                        ->from($this, new Zend_Db_Expr('TRUE'))
                        ->where('owner_id = ?', $user->getIdentity())
                        ->where('status = ?', 'okay')
                        ->limit(1);
        break;

      case 'none':
        return false;
        break;
    }

    return false;
  }

}
