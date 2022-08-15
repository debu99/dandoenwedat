<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: AdminSettingsController.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesmembershipswitch_AdminSettingsController extends Core_Controller_Action_Admin {

    public function supportAction() {

        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesmembershipswitch_admin_main', array(), 'sesmembershipswitch_admin_main_support');

    }

  public function indexAction() {

    $db = Engine_Db_Table::getDefaultAdapter();

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesmembershipswitch_admin_main', array(), 'sesmembershipswitch_admin_main_settings');

    $table_exist = $db->query('SHOW TABLES LIKE \'engine4_sesmembershipcard_settings\'')->fetch();
    if($table_exist) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sesmembershipswitch.pluginactivated', 1);
    }

    $this->view->form = $form = new Sesmembershipswitch_Form_Admin_Settings_General();

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
      unset($values['defaulttext']);
      include_once APPLICATION_PATH . "/application/modules/Sesmembershipswitch/controllers/License.php";
      if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmembershipswitch.pluginactivated')) {
        foreach ($values as $key => $value) {
          if($value != '')
          Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
        }
        $form->addNotice('Your changes have been saved.');
        if($error)
          $this->_helper->redirector->gotoRoute(array());
      }
    }
  }

  public function freePlanAction(){
    $db = Engine_Db_Table::getDefaultAdapter();
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesmembershipswitch_admin_main', array(), 'sesmembershipswitch_admin_main_freeplan');
    $this->view->form = $form = new Sesmembershipswitch_Form_Admin_Settings_Freeplan();
    $plan_id = $this->_getParam('plan_id');
    if(!$plan_id){
      $table = Engine_Api::_()->getDbTable('packages','payment');
      $select = $table->select()->where('enabled =?',1)->where('price =?',0);
      $result = $table->fetchAll($select);
      if(count($result))
        $plan_id = $result[0]->getIdentity();
    }
    if($plan_id){
      $plan = Engine_Api::_()->getItemTable('sesmembershipswitch_plan')->getPlans(array('current_plan_id'=>$plan_id,'plan_type'=>0));
      if($plan)
        $form->populate($plan->toArray());
    }
    if($plan_id)
      $form->current_plan_id->setValue($plan_id);
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $values = $form->getValues();
    if(empty($plan)){
      $table = Engine_Api::_()->getItemTable('sesmembershipswitch_plan');
      $plan = $table->createRow();
    }

    $plan->setFromArray($values);
    $plan->save();

    $form->addNotice("Changes Saved Successfully.");

  }
  public function paidPlanAction(){
    $db = Engine_Db_Table::getDefaultAdapter();
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sesmembershipswitch_admin_main', array(), 'sesmembershipswitch_admin_main_paidplan');
    $this->view->form = $form = new Sesmembershipswitch_Form_Admin_Settings_Paidplan();
    $plan_id = $this->_getParam('plan_id');
    if(!$plan_id){
      $table = Engine_Api::_()->getDbTable('packages','payment');
      $select = $table->select()->where('enabled =?',1)->where('price !=?',0);
      $result = $table->fetchAll($select);
      if(count($result))
        $plan_id = $result[0]->getIdentity();
    }
    if($plan_id){
      $plan = Engine_Api::_()->getItemTable('sesmembershipswitch_plan')->getPlans(array('current_plan_id'=>$plan_id,'plan_type'=>1));
      if($plan)
        $form->populate($plan->toArray());
    }
    if($plan_id)
      $form->current_plan_id->setValue($plan_id);
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $values = $form->getValues();
    $values['plan_type'] = 1;
    if(empty($plan)){
      $table = Engine_Api::_()->getItemTable('sesmembershipswitch_plan');
      $plan = $table->createRow();
    }

    $plan->setFromArray($values);
    $plan->save();

    $form->addNotice("Changes Saved Successfully.");
  }


}
