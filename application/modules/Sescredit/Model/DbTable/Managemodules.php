<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Managemodules.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Managemodules extends Core_Model_Item_DbTable_Abstract {
    function getModule($module){
        return $this->fetchRow($this->select()->where('module =?',$module)->where('enabled =?','1'));
    }
}
