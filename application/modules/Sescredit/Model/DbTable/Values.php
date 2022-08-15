<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Values.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Values extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Value";

  public function getValues($params = array()) {
    $select = $this->select()
            ->from($this->info('name'))
            ->where('type =?', $params['type'])
            ->where('member_level =?', $params['member_level']);
    return $this->fetchRow($select);
  }

}
