<?php

class Sesevent_AdminIntegrateothermoduleController extends Core_Controller_Action_Admin {

  public function indexAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_integrateothermodule');
    
    $this->view->enabledModules = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    
    $select = Engine_Api::_()->getDbtable('integrateothermodules', 'sesevent')->select();
    
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(25);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  
  //Add New Plugin entry
  public function addmoduleAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesevent_admin_main', array(), 'sesevent_admin_main_integrateothermodule');
    
    $this->view->form = $form = new Sesevent_Form_Admin_Manage_Add();
    
    $this->view->type = $type = $this->_getParam('type');
    
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
    
      $values = $form->getValues();
      $integrateothermoduleTable = Engine_Api::_()->getDbtable('integrateothermodules', 'sesevent');
      
      $is_module_exists= $integrateothermoduleTable->fetchRow(array('content_type = ?' => $values['content_type'], 'module_name = ?' => $values['module_name']));
      
      if (!empty($is_module_exists)) {
        $error = Zend_Registry::get('Zend_Translate')->_("This Module already exist in our database.");
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }
			
      $contentTypeItem = Engine_Api::_()->getItemTable($values['content_type']);
      
			//get current content type item id
      $primaryId = current($contentTypeItem->info("primary"));
      
			//get primary key for content type
      if (!empty($primaryId))
        $values['content_id'] = $primaryId;

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      $dbInsert = Engine_Db_Table::getDefaultAdapter();
      try {
        $row = $integrateothermoduleTable->createRow();
        $values['type'] = $type;
        $row->setFromArray($values);
        $row->save();

        $modulename = $values['module_name'];
        $dbInsert->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES ("sesevent_main_browseevent_'.$row->getIdentity().'", "'.$modulename.'", "Browse Events", "", \'{"route":"sesevent_browseevent_'.$row->getIdentity().'","action":"browse-events", "resource_type":"'.$values['content_type'].'"}\', "'.$modulename.'_main", "", 1, 0, 999)');
        
        $this->createBrowseEventPage($modulename, $row->getIdentity());
        
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }
  
  function createBrowseEventPage($modulename, $id) {
  
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    
    //Event Browse Page
    $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'sesevent_index_'.$id)
            ->limit(1)
            ->query()
            ->fetchColumn();
    if (!$page_id) {
      $widgetOrder = 1;
      $db->insert('engine4_core_pages', array(
          'name' => 'sesevent_index_'.$id,
          'displayname' => 'SES - Advanced Events - '.ucfirst($modulename).' Events Browse Page',
          'title' => ucfirst($modulename) .' Event Browse',
          'description' => 'This page lists events.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert top
      $db->insert('engine4_core_content', array(
          'type' => 'container',
          'name' => 'top',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $top_id = $db->lastInsertId();

      // Insert main
      $db->insert('engine4_core_content', array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 2,
      ));
      $main_id = $db->lastInsertId();

      // Insert top-middle
      $db->insert('engine4_core_content', array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $top_id,
      ));
      $top_middle_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert('engine4_core_content', array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
          'order' => 2,
      ));
      $main_middle_id = $db->lastInsertId();

      // Insert main-right
      $db->insert('engine4_core_content', array(
          'type' => 'container',
          'name' => 'right',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
          'order' => 1,
      ));
      $main_right_id = $db->lastInsertId();

      // Insert menu
      $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => $modulename.'.browse-menu',
          'page_id' => $page_id,
          'parent_content_id' => $top_middle_id,
          'order' => $widgetOrder++,
      ));

      // Insert content
      $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'sesevent.browse-events',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => $widgetOrder++,
          'params' => '{"enableTabs":["list","grid","pinboard"],"openViewType":"grid","show_criteria":["watchLater","favouriteButton","playlistAdd","likeButton","socialSharing","like","favourite","comment","rating","view","title","category","by","duration","descriptionlist","descriptionpinboard","enableCommentPinboard"],"sort":"mostSPliked","title_truncation_list":"70","title_truncation_grid":"30","description_truncation_list":"230","description_truncation_grid":"45","description_truncation_pinboard":"60","height_list":"180","width_list":"260","height_grid":"270","width_grid":"305","width_pinboard":"305","limit_data_pinboard":"10","limit_data_grid":"15","limit_data_list":"20","pagging":"pagging","title":"","nomobile":"0","name":"sesevent.browse-events"}',
      ));

      // Insert search
      $db->insert('engine4_core_content', array(
          'type' => 'widget',
          'name' => 'sesevent.browse-search',
          'page_id' => $page_id,
          'parent_content_id' => $main_right_id,
          'order' => $widgetOrder++,
          'params' => '{"search_for":"event","view_type":"vertical","search_type":["recentlySPcreated","mostSPviewed","mostSPliked","mostSPcommented","mostSPrated","mostSPfavourite","featured","sponsored","verified","hot"],"default_search_type":"mostSPliked","friend_show":"yes","search_title":"yes","browse_by":"yes","categories":"yes","location":"yes","kilometer_miles":"yes","title":"Search Events","nomobile":"0","name":"sesevent.browse-search"}',
      ));
    }
  }

  //Delete entry
  public function deleteAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
      
        $inttable = Engine_Api::_()->getItem('sesevent_integrateothermodule', $this->_getParam('integrateothermodule_id'));
        $pageName = "sesevent_index_".$this->_getParam('integrateothermodule_id');
        if (!empty($pageName)) {
          $page_id = $db->select()
                  ->from('engine4_core_pages', 'page_id')
                  ->where('name = ?', $pageName)
                  ->limit(1)
                  ->query()
                  ->fetchColumn();
          if($page_id) {
            Engine_Api::_()->getDbTable('content', 'core')->delete(array('page_id =?' => $page_id));
            Engine_Api::_()->getDbTable('pages', 'core')->delete(array('page_id =?' => $page_id));
          }
        }
        Engine_Api::_()->getDbtable('menuItems', 'core')->delete(array('name =?' => 'sesevent_main_browseevent_' . $this->_getParam('integrateothermodule_id')));
      
        $integrateothermodule = Engine_Api::_()->getItem('sesevent_integrateothermodule', $this->_getParam('integrateothermodule_id'));
        $integrateothermodule->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('You have successfully delete entry.')
      ));
    }
    $this->renderScript('admin-integrateothermodule/delete.tpl');
  }

  //Enable / Disable Action
  public function enabledAction() {
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $content = Engine_Api::_()->getItemTable('sesevent_integrateothermodule')->fetchRow(array('integrateothermodule_id = ?' => $this->_getParam('integrateothermodule_id')));
    try {
      
      Engine_Api::_()->getDbtable('menuItems', 'core')->update(array('enabled' => !$content->enabled), array('name =?' => 'sesevent_main_browseevent_' . $this->_getParam('integrateothermodule_id')));
    
      $content->enabled = !$content->enabled;
      $content->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }
}
