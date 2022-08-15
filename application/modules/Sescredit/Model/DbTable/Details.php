<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Details.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Details extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Detail";

  public function getCurrentUserPoint($params = array()) {
    return $this->select()
                    ->from($this->info('name'), 'total_credit')
                    ->where('owner_id =?', $params['owner_id'])
                    ->query()
                    ->fetchColumn();
  }

  public function getTotalPoint() {
    return $this->select()
                    ->from($this->info('name'), "SUM('total_credit')")
                    ->query()
                    ->fetchColumn();
  }

}
