<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_AdminMenuController extends Core_Controller_Action_Admin {
  protected $_enabledModuleNames;
   public function init()
  {
    $this->_enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
  }
  public function sinkMenuAction(){
    $table = Engine_Api::_()->getDbtable('menuItems', 'core');
    $menuitems = $table->fetchAll($table->select()->where('menu = ?', 'core_mini'));
    $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
    foreach($menuitems as $menus){
      $menuItemsSelect = $menuItemsTable->select();
      $menuItemsSelect->where('id =?', $menus->id);
      $menusSelect = $menuItemsTable->fetchRow($menuItemsSelect);
      if(!$menusSelect){
        $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
        $menuItem = $menuItemsTable->createRow();
        $menuItem->label = $menus->label;
        $menuItem->params = $menus->params;
        $menuItem->menu = 'sesbasic_mini';
        $menuItem->module = $menus->module; // Need to do this to prevent it from being hidden
        $menuItem->plugin = $menus->plugin;
        $menuItem->submenu = $menus->submenu;
        $menuItem->custom = $menus->custom;
        $menuItem->save();
        $menuItem->name = $menus->name;
        $menuItem->id = $menus->id;
        $menuItem->save();
      }
    }
    $this->view->form = null;
    $this->view->status = true;

  }
  public function indexAction(){
    $this->view->moduleName = $moduleName = $this->_getParam('moduleName');

    if($moduleName == 'sesdating') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesdating_admin_main', array(), 'sesdating_admin_main_menus');
    }
    if($moduleName == 'sessportz') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sessportz_admin_main', array(), 'sessportz_admin_main_menus');
    }
    if($moduleName == 'sesatoz') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesatoz_admin_main', array(), 'sesatoz_admin_main_menus');
    }

    if($moduleName == 'sesmaterial') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesmaterial_admin_main', array(), 'sesmaterial_admin_main_menus');
    }

    if($moduleName == 'sesytube') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesytube_admin_main', array(), 'sesytube_admin_main_menus');
    }

    if($moduleName == 'sespwa') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sespwa_admin_main', array(), 'sespwa_admin_main_minimenu');
    }

    if($moduleName == 'sesariana') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesariana_admin_main', array(), 'sesariana_admin_main_menus');
    }

    if($moduleName == 'sesadvancedheader') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedheader_admin_main', array(), 'sesadvancedheader_admin_main_menus');
    }
    if($moduleName == 'seslinkedin') {
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('seslinkedin_admin_main', array(), 'seslinkedin_admin_main_minimenu');
    }

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation($moduleName.'_admin_main', array(), $moduleName.'_admin_main_minimenu');

    $this->view->name = $name = $this->_getParam('name', 'sesbasic_mini');


    // Get menu items
    $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
    $menuItemsSelect = $menuItemsTable->select()
      ->order('order');
    if( !empty($this->_enabledModuleNames) ) {
      $menuItemsSelect->where('module IN(?)',  $this->_enabledModuleNames);
    }
    $this->view->menuItems = $menuItems = $menuItemsTable->fetchAll($menuItemsSelect);

  }


  public function createAction()
  {
    $this->view->name = $name = $this->_getParam('name','sesbasic_mini');

    // Get form
    $this->view->form = $form = new Core_Form_Admin_Menu_ItemCreate();

    // Check stuff
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Save
    $values = $form->getValues();
    $label = $values['label'];
    unset($values['label']);

    $menuTable = Engine_Api::_()->getDbtable('menuItems', 'core');
    $menu = $menuTable->createRow();
    $menu->label = $label;
    $menu->params = $values;
    $menu->menu = $name;
    $menu->module = 'core'; // Need to do this to prevent it from being hidden
    $menu->plugin = '';
    $menu->submenu = '';
    $menu->custom = 1;
    $menu->save();
    $menu->name = 'custom_' . sprintf('%d', $menu->id);
    $menu->save();

    $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');

    $menuItem = $menuItemsTable->createRow();
    $menuItem->label = $label;
    $menuItem->params = $values;
    $menuItem->menu = $name;
    $menuItem->module = 'core'; // Need to do this to prevent it from being hidden
    $menuItem->plugin = '';
    $menuItem->submenu = '';
    $menuItem->custom = 1;
    $menuItem->save();
    $menuItem->name = 'custom_' . sprintf('%d', $menu->id);
    $menuItem->id = $menu->id;
    $menuItem->save();

    $this->view->status = true;
    $this->view->form = null;
  }

  public function editAction()
  {
    $this->view->name = $name = $this->_getParam('name');

    // Get menu item
    $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
    $menuItemsSelect = $menuItemsTable->select()
      ->where('name = ?', $name);
    if( !empty($this->_enabledModuleNames) ) {
      $menuItemsSelect->where('module IN(?)',  $this->_enabledModuleNames);
    }
    $this->view->menuItem = $menuItem = $menuItemsTable->fetchRow($menuItemsSelect);


    //menu
    $menuItemsTable1 = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
    $menuItemsSelect1 = $menuItemsTable1->select()
      ->where('name = ?', $name);
    if( !empty($this->_enabledModuleNames) ) {
      $menuItemsSelect1->where('module IN(?)',  $this->_enabledModuleNames);
    }
    $this->view->menuItem1 = $menuItem1 = $menuItemsTable1->fetchRow($menuItemsSelect1);

    if( !$menuItem || !$menuItem1) {
      throw new Core_Model_Exception('missing menu item');
    }

    // Get form
    $this->view->form = $form = new Core_Form_Admin_Menu_ItemEdit();

    // Make safe
    $menuItemData = $menuItem->toArray();
    if( isset($menuItemData['params']) && is_array($menuItemData['params']) ) {
      $menuItemData = array_merge($menuItemData, $menuItemData['params']);
    }
    if( !$menuItem->custom ) {
      $form->removeElement('uri');
    }
    unset($menuItemData['params']);

    // Check stuff
    if( !$this->getRequest()->isPost() ) {
      $form->populate($menuItemData);
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Save
    $values = $form->getValues();

    $menuItem1->label = $values['label'];
    $menuItem->label = $values['label'];
    $menuItem->enabled = !empty($values['enabled']);
    $menuItem1->enabled = !empty($values['enabled']);
    unset($values['label']);
    unset($values['enabled']);

    if( $menuItem->custom ) {
      $menuItem->params = $values;
      $menuItem1->params = $values;
    }

    $menuItem->params = $this->updateParam($values, 'icon', $menuItem->params);
    $menuItem->params = $this->updateParam($values, 'target', $menuItem->params);

    $menuItem1->params = $this->updateParam($values, 'icon', $menuItem1->params);
    $menuItem1->params = $this->updateParam($values, 'target', $menuItem1->params);

    if( empty($menuItem->params) ) {
      $menuItem->params = '';
      $menuItem1->params = '';
    }
    $menuItem->save();
    $menuItem1->save();

    $this->view->status = true;
    $this->view->form = null;

  }

  public function deleteAction()
  {
    $this->view->name = $name = $this->_getParam('name');

    // Get menu item
    $menuItemsTable = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
    $menuItemsSelect = $menuItemsTable->select()
      ->where('name = ?', $name)
      ->order('order ASC');
    if( !empty($this->_enabledModuleNames) ) {
      $menuItemsSelect->where('module IN(?)',  $this->_enabledModuleNames);
    }
    $this->view->menuItem = $menuItem = $menuItemsTable->fetchRow($menuItemsSelect);

    $menuItemsTable1 = Engine_Api::_()->getDbtable('menuItems', 'core');
    $menuItemsSelect1 = $menuItemsTable1->select()
      ->where('name = ?', $name)
      ->order('order ASC');
    if( !empty($this->_enabledModuleNames) ) {
      $menuItemsSelect1->where('module IN(?)',  $this->_enabledModuleNames);
    }
    $this->view->menus = $menus = $menuItemsTable1->fetchRow($menuItemsSelect1);
    if( !$menuItem || !$menuItem->custom || !$menus || !$menus->custom) {
      throw new Core_Model_Exception('missing menu item');
    }

    // Get form
    $this->view->form = $form = new Core_Form_Admin_Menu_ItemDelete();

    // Check stuff
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    if($menus){
      $menus->delete();
    }
    $menuItem->delete();

    $this->view->form = null;
    $this->view->status = true;
  }

  public function orderAction()
  {
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    $table = Engine_Api::_()->getDbtable('menuItems', 'sesbasic');
    $menuitems = $table->fetchAll($table->select()->where('menu = ?', 'sesbasic_mini'));
    foreach( $menuitems as $menuitem ) {
      $order = $this->getRequest()->getParam('admin_menus_item_'.$menuitem->name);
      if( !$order ){
        $order = 999;
      }
      $menuitem->order = $order;
      $menuitem->save();
    }

    $table = Engine_Api::_()->getDbtable('menuItems', 'core');
    $menuitems = $table->fetchAll($table->select()->where('menu = ?', 'sesbasic_mini'));
    foreach( $menuitems as $menuitem ) {
      $order = $this->getRequest()->getParam('admin_menus_item_'.$menuitem->name);
      if( !$order ){
        $order = 999;
      }
      $menuitem->order = $order;
      $menuitem->save();
    }
    return;
  }

  public function updateParam($formValues, $paramName, $params)
  {
    if( !empty($formValues[$paramName]) ) {
      if( !empty($params) ) {
        return $params = array_merge($params, array($paramName => $formValues[$paramName]));
      }
      return array($paramName => $formValues[$paramName]);
    } elseif( isset($params[$paramName]) ) {
      // Remove the $paramName
      $tempParams = array();
      foreach( $params as $key => $item ) {
        if( $key != $paramName ) {
          $tempParams[$key] = $item;
        }
      }
      return $tempParams;
    }
    return $params;
  }
}
