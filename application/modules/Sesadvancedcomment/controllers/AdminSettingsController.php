<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesadvancedcomment_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    //Upgrade Work for Reactions
    $this->uploadReactions();
    //SE comment widgte replace with our plugin

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedactivity_admin_main', array(), 'sesadvancedcomment_admin_main_cmtsettings');

    $this->view->subnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesadvancedcomment_admin_main', array(), 'sesadvancedcomment_admin_main_settings');

    $this->view->form = $form = new Sesadvancedcomment_Form_Admin_Settings_General();
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();

      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.pluginactivated')) {

        //SE comment widgte replace with our plugin
        if (!empty($values['sesadvancedcomment_enablesecomment'])) {
            $db->query("UPDATE `engine4_core_content` SET `name` = 'sesadvancedcomment.comments' WHERE `name` = 'core.comments'");
        }

        if (isset($values['sesadvancedcomment_enableordering']))
          $values['sesadvancedcomment_enableordering'] = serialize($values['sesadvancedcomment_enableordering']);
        else
          $values['sesadvancedcomment_enableordering'] = serialize(array());

        if (isset($values['sesadvancedcomment_enableattachement']))
          $values['sesadvancedcomment_enableattachement'] = serialize($values['sesadvancedcomment_enableattachement']);
        else
          $values['sesadvancedcomment_enableattachement'] = serialize(array());
        foreach ($values as $key => $value) {
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        $this->_helper->redirector->gotoRoute(array());
      }
    }
  }

  public function uploadReactions() {

    $mangereaction = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.managereactions', 0);
    if(empty($mangereaction) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.pluginactivated')) {

      //Upload Reactions
      $reactionsTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment');
      $emotiongalleriesselect = $reactionsTable->select()->order('reaction_id ASC');
      $paginator = $reactionsTable->fetchAll($emotiongalleriesselect);
      $db = Engine_Db_Table::getDefaultAdapter();

      if(count($paginator) > 0) {
        foreach($paginator as $result) {

          $title = $result->title;
          if($title == 'Like') {
            $title = 'icon-like';
          } elseif($title == 'Love') {
            $title = 'icon-love';
          } elseif($title == 'Sad') {
            $title = 'icon-sad';
          } elseif($title == 'Wow') {
            $title = 'icon-wow';
          } elseif($title == 'Haha') {
            $title = 'icon-haha';
          } elseif($title == 'Angry') {
            $title = 'icon-angery';
          }

          $PathFile = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Sesadvancedcomment' . DIRECTORY_SEPARATOR . "externals" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;

          if (is_file($PathFile . $title . '.png'))  {
            $pngFile = $PathFile . $title . '.png';
            $photo_params = array(
                'parent_id' => $result->reaction_id,
                'parent_type' => "sesadvancedcomment_reaction",
            );
            $photoFile = Engine_Api::_()->storage()->create($pngFile, $photo_params);
            if (!empty($photoFile->file_id)) {
              $db->update('engine4_sesadvancedcomment_reactions', array('file_id' => $photoFile->file_id), array('reaction_id = ?' => $result->reaction_id));
            }
          }
        }
        Engine_Api::_()->getApi('settings', 'core')->setSetting('sesadvancedcomment.managereactions', 1);
      }
    }
  }
}
