<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9802 2012-10-20 16:56:13Z pamela $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('group_admin_main', array(), 'group_admin_main_settings');

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->form = $form = new Group_Form_Admin_Global();

    $form->bbcode->setValue($settings->getSetting('group_bbcode', 1));
    $form->html->setValue($settings->getSetting('group_html', 0));

    if( $this->getRequest()->isPost()&& $form->isValid($this->getRequest()->getPost()))
    {
      $values = $form->getValues();
      $settings->setSetting('group_bbcode', $values['bbcode']);
      $settings->setSetting('group_html', $values['html']);
        $settings->setSetting('group_allow_unauthorized', $values['group_allow_unauthorized']);

      $form->addNotice('Your changes have been saved.');
    }
  }

  public function categoriesAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('group_admin_main', array(), 'group_admin_main_categories');

    $this->view->categories = Engine_Api::_()->getDbtable('categories', 'group')->fetchAll();
  }

  public function levelAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('group_admin_main', array(), 'group_admin_main_level');

    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $level_id = $id = $level->level_id;

    // Make form
    $this->view->form = $form = new Group_Form_Admin_Settings_Level(array(
      'public' => ( in_array($level->type, array('public')) ),
      'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($level_id);

    if (isset($form->coverphoto_dummy)) {
      $groupId = Engine_Api::_()->getItemTable('group')->select()->query()->fetchColumn();
      if (empty($groupId)) {
        $description = '<div class="tip" style="margin-top:-9px"><span>Please create atleast one group first and then set the default cover photo.</div>';
      } else {
        $href = Engine_Api::_()->getItem('group', $groupId)->getHref() . '?uploadDefaultCover=1&level_id='.$level_id;
        $description = sprintf(
          "%1sClick here%2s to upload and set default cover photo for groups",
          "<a href='$href' target='_blank'>", "</a>"
        );
      }
      $form->coverphoto_dummy->setDescription($description);
    }

    if(!empty( $groupCover = Engine_Api::_()->getApi("settings", "core")->getSetting("groupcoverphoto.preview.level.id.$id"))) {
      $image = Engine_Api::_()->storage()->get($groupCover, 'thumb.cover')->map();
      $description = sprintf("%1sPreview Default Cover Photo%2s",
        "<a onclick='showPreview();'>",
        "</a><div class='is_hidden' id='show_default_preview' >"
          . "<img src='$image' style='max-height:600px;max-width:600px;'></div>"
      );
      $form->addElement('dummy', 'coverphoto_preview', array(
        'description' => $description,
      ));

      $form->coverphoto_preview->addDecorator(
        'Description',
        ['placement' => 'PREPEND', 'class' => 'description', 'escape' => false]
      );
    }

    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('group', $level_id, array_keys($form->getValues())));

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

   // Check validitiy
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process

    $values = $form->getValues();

    // Form elements with NonBoolean values
    $nonBooleanSettings = $form->nonBooleanFields();

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Set permissions
      if( isset($values['auth_comment']) ) {
        $values['auth_view'] = (array) @$values['auth_view'];
        $values['auth_comment'] = (array) @$values['auth_comment'];
        $values['auth_photo'] = (array) @$values['auth_photo'];
      }

      // coverphoto work
      unset($values['coverphoto_dummy']);
      unset($values['coverphoto_preview']);
      $permissionsTable->setAllowed('group', $level_id, $values, '', $nonBooleanSettings);

      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }

  public function addCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    // Generate and assign form
    $form = $this->view->form = new Group_Form_Admin_Category();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    // Check post
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      // we will add the category
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {
        // add category to the database
        // Transaction
        $table = Engine_Api::_()->getDbtable('categories', 'group');

        // insert the category into the database
        $row = $table->createRow();
        $row->title = $values["label"];
        $row->save();

        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-settings/form.tpl');
  }

  public function deleteCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->group_id=$id;

    $groupTable = Engine_Api::_()->getDbtable('groups', 'group');
    $categoryTable = Engine_Api::_()->getDbtable('categories', 'group');
    $category = $categoryTable->find($id)->current();

    // Check post
    if( $this->getRequest()->isPost() ) {
      $db = $categoryTable->getAdapter();
      $db->beginTransaction();

      try {
        // go through logs and see which groups used this category id and set it to ZERO
        $groupTable->update(array(
          'category_id' => 0,
        ), array(
          'category_id = ?' => $category->getIdentity(),
        ));

        $category->delete();

        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-settings/delete.tpl');
  }

  public function editCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $form = $this->view->form = new Group_Form_Admin_Category();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    // Must have an id
    if( !($id = $this->_getParam('id')) ) {
      die('No identifier specified');
    }

    $categoryTable = Engine_Api::_()->getDbtable('categories', 'group');
    $category = $categoryTable->find($id)->current();
    $form->setField($category);

    // Check post
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      // Ok, we're good to add field
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {
        $category->title = $values["label"];
        $category->save();

        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }

      return $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-settings/form.tpl');
  }
}
