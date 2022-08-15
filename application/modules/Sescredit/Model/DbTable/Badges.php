<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Badges.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Badges extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Badge";

  public function badges() {
    $select = $this->select()
            ->from($this->info('name'), array('*'))
            ->where('enabled =?', 1);
    return $this->fetchAll($select);
  }

  public function nextBadge($params = array()) {
    $select = $this->select()
            ->from($this->info('name'), array('*'))
            ->where('credit_value > ?',$params['point'])
            ->where('enabled =?', 1)
            ->limit(1);
    return $this->fetchRow($select);
  }

}
