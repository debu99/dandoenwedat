<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Locations.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Model_DbTable_Locations extends Engine_Db_Table {

  protected $_name = 'sesbasic_locations';
  protected $_rowClass = 'Sesbasic_Model_Location';

  function getLocationData($resource_type = 'sesalbum_album', $resource_id = '') {
    $lName = $this->info('name');
    $select = $this->select()
            ->from($lName)
            ->where('resource_id = ?', $resource_id)
            ->where('resource_type =?', $resource_type);
    $row = $this->fetchRow($select);
    return $row;
  }
}