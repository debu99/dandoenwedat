<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Commentfiles.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedcomment_Model_DbTable_Commentfiles extends Engine_Db_Table
{
  public function getFiles($params = array()){
    $select = $this->select()->where('comment_id =?',$params['comment_id']);
    return $this->fetchAll($select);  
  }
}