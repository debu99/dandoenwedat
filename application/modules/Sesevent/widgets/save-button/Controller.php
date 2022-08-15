<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Widget_SaveButtonController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if (empty($this->view->viewer_id))
      return $this->setNoRender();

    $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
    $moduleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($moduleName);
    if (empty($moduleEnabled) || empty($moduleName))
      return $this->setNoRender();
    
    if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.event.save', 1))
	    return $this->setNoRender();
      
    $this->view->item = $item = Engine_Api::_()->core()->getSubject(); 
    $this->view->type = $item->getType();
    $this->view->id = $item->getIdentity();

    $this->view->isSave = Engine_Api::_()->getDbTable('saves', 'sesevent')->isSave(array('resource_type' => $this->view->type, 'resource_id' => $this->view->id));

    $select = Engine_Api::_()->getDbtable('saves', 'sesevent')->getSaveSelect($item);
    $results = $select->query()->fetchAll();
    $this->view->saveCount = count($results);
  }

}