<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Texts.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_Model_DbTable_Texts extends Engine_Db_Table {

  protected $_rowClass = 'Sesfeedgif_Model_Text';
  
  public function getValue($value) {
  
    return $this->select()->from($this->info('name'), 'limit')->where('text =?', $value)->query()->fetchColumn();
  }
}