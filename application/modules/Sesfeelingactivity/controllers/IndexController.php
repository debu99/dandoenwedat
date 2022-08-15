<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeelingactivity_IndexController extends Core_Controller_Action_Standard {

  public function getfeelingiconsAction() {

    $feeling_id = $this->_getParam('feeling_id', null);
    $feeling_type = $this->_getParam('feeling_type', null);
    $text = $this->_getParam('text', null);
    $edit = $this->_getParam('edit', 0);
    
    $table = Engine_Api::_()->getDbtable('feelingicons', 'sesfeelingactivity');
    
    if ($feeling_type == 1) {
      
      $select = $table->select()->where('type =?', $feeling_type)->order('feeling_id DESC');
      if($text != 'default')
        $select->where('title LIKE ?', '%' . $text . '%');

      if (!empty($feeling_id))
        $select->where('feeling_id =?', $feeling_id);

      $results = $table->fetchAll($select);
      
    } else if($feeling_type == 2) {
    
      $select = $table->select()
                      ->where('feeling_id =?', $feeling_id)
                      ->where('type =?', $feeling_type);
      $results = $table->fetchAll($select);
      $resource_typeArray = array();
      foreach($results as $result) {
        $resource_typeArray[] = $result->resource_type;
      }
      
      $searchtable = Engine_Api::_()->getDbtable('search', 'core');
      $select = $searchtable->select()
                            ->where('type in(?)', $resource_typeArray)
                            ->order('id DESC');
      if($text != 'default')
        $select->where('title LIKE ? OR description LIKE ? OR keywords LIKE ? OR hidden LIKE ?', '%' . $text . '%');
      $results = $searchtable->fetchAll($select);
    }
          
    $feelingsIcon = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $feeling_id);

    $html = '';
    foreach ($results as $result) {
      if ($feeling_type == 1) {

        if($edit) {
          $liClassName = 'sesact_feelingactivitytypeliedit';
        } else {
          $liClassName = 'sesact_feelingactivitytypeli';
        }
        
        $html .= '<li data-title="'.$result->title.'" class="'.$liClassName.' sesbasic_clearfix" data-rel='.$result->feelingicon_id.'><a href="javascript:void(0);"><img class="sesfeeling_feeling_icon" title="'.$result->title.'" src="'.Engine_Api::_()->storage()->get($result->feeling_icon, "")->getPhotoUrl().'"><span>'.$result->title.'</span></a></li>';

      } else {

        $itemType = $result->type;
        if (Engine_Api::_()->hasItemType($itemType)) {
        
          $item = Engine_Api::_()->getItem($itemType, $result->id);
          if($item) {
            $photo_icon_photo = $this->view->itemPhoto($item, 'thumb.icon');
            if($edit) {
              $liClassName = 'sesact_feelingactivitytypeliedit';
            } else {
              $liClassName = 'sesact_feelingactivitytypeli';
            }
            
            $html .= '<li data-type="'.$itemType.'" data-icon="'.Engine_Api::_()->storage()->get($feelingsIcon->file_id, "")->getPhotoUrl().'" data-title="'.$item->getTitle().'" class="'.$liClassName.' sesbasic_clearfix" data-rel='.$result->id.'><a href="javascript:void(0);" class="sesact_feelingactivitytypea">'.$photo_icon_photo.'<span>'.$item->getTitle().'</span></a></li>';
          }
        }
      }
    }
    echo Zend_Json::encode(array('status' => 1, 'html' => $html));exit();
  }
}