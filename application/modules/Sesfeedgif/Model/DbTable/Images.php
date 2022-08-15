<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Images.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_Model_DbTable_Images extends Engine_Db_Table {

  protected $_rowClass = 'Sesfeedgif_Model_Image';
  public function getPaginator($params = array()) {
    return Zend_Paginator::factory($this->getImages($params));
  }
  
  public function getImages($params = array()) {
  
  
    $select = $this->select()->where('file_id <>?', 0)->order('order ASC');
    if(!empty($params['limit'])) {
      $select->limit($params['limit']);
    }
    if(!empty($params['fetchAll']))
      return $this->fetchAll($select);
    return $select;
  }
  
  public function getCode($code) {
  
    return $this->select()->where('gifimage_code =?', $code)->query()->fetchColumn();
  }
  
  public function searchGif($text) {
  
    $rName = $this->info('name');
    $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $tmName = $tmTable->info('name');
    $tagName = Engine_Api::_()->getDbtable('Tags', 'core')->info('name');

    $select = $this->select()
                  ->from($rName)
                  ->setIntegrityCheck(false)
                  ->joinLeft( $tmName, "$tmName.resource_id = $rName.image_id and " . $tmName . ".resource_type = 'sesfeedgif_image'", null )
                  ->joinLeft( $tagName, "$tagName.tag_id = $tmName.tag_id", array($tagName.".text") )
                  ->where( $tagName . ".text LIKE ? ",'%'.$text.'%' )
                  ->where( $rName . ".file_id <> ? ", 0 )
                  ->order( 'image_id DESC' )
                  ->group($rName . '.image_id');

    return Zend_Paginator::factory($select);
  }
}