<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Useremotions.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedcomment_Model_DbTable_Useremotions extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedcomment_Model_Useremotion';
  public function getEmotion($params = array()){
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if(!$viewer_id)
      return array();
    $select = $this->select()->from($this->info('name'),'*')->where('user_id =?',$viewer_id)->setIntegrityCheck(false);
    $galleryTableName = Engine_Api::_()->getDbTable('emotiongalleries','sesadvancedcomment')->info('name');
    $select->joinLeft($galleryTableName,$galleryTableName.'.gallery_id ='.$this->info('name').'.gallery_id', array('title','file_id'))->where($galleryTableName.'.gallery_id IS NOT NULL');
    if(!empty($params['gallery_id']))
      $select->where($this->info('name').'.gallery_id =?',$params['gallery_id']);
   // if($params['type'] && $params['type'] == 'user')
     // $select->where('enabled = ?', 1);
    return $this->fetchAll($select);  
  }
}