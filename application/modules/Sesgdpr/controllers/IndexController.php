<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
     $this->_helper->content->setEnabled();  
  }
  public function getGdprDataAction(){
    $this->view->type = $this->_getParam('type');
      $this->view->contactdpo = $form = new Sesgdpr_Form_Contactdpo();
      
    $viewer = $this->view->viewer();
    if($viewer->getIdentity()){
     $form->email->setValue($viewer->email);  
     $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias($viewer);
     $form->first_name->setValue($fields['first_name']);
     $form->last_name->setValue($fields['last_name']);
    }
  }
  
  function insetAuditAction($typeValue = "",$params = array()){
    $type = $this->_getParam('type');  
    if(!$type)
      $type = $typeValue;
      
    $db = Engine_Api::_()->getItemTable('sesgdpr_audit')->getAdapter();
    $db->beginTransaction();
    try { 
       $table = Engine_Api::_()->getItemTable('sesgdpr_audit');
       $service = $table->createRow();
       if($type == "opt"){
        if(!$this->view->viewer()->getIdentity())
          return;
        $value['email'] = $this->view->viewer()->email;
        $value['description'] = 'User made changes to '.$this->_getParam('name').' settings';
       }
       if(count($params))
        $value = $params;
       $service->setFromArray($value);
       $service->save();
       $db->commit();
    }catch(Exception $e){
      $this->_helper->json(array('status'=>0));
      throw $e;  
    }
    $this->_helper->json(array('status'=>1));
  }
  function insetAction(){
    $firstName = $this->_getParam('firstName');
    $lastName = $this->_getParam('lastName');
    $email = $this->_getParam('emailName');
    $type = $this->_getParam('type');
    $db = Engine_Api::_()->getItemTable('sesgdpr_content')->getAdapter();
    $db->beginTransaction();
    try { 
       $table = Engine_Api::_()->getItemTable('sesgdpr_content');
       $service = $table->createRow();
       $value['email'] = $email;
       $value['name'] = $firstName.' '.$lastName;
       if($type == "dpo"){
         $value['type'] = "gdpr";
         $value['message'] = $_POST['message'];
         $string = "User made DPO request.";
         $subject = "New Form Submission for DPO related request";
         $url = 'admin/sesgdpr/settings/dpo';
       }else if($type == "request"){
         $value['type'] = "service-access";
         $string = "User made Subject Access request.";
         $subject = "New Form Submission for Service Access related request";
         $url = 'admin/sesgdpr/settings/subject-access';
       }else if($type == "unsubscribe"){
         $value['type'] = "unsubscribe";
         $string = "User made Unsubscribe request.";
         $subject = "New Form Submission for Unsubscribe related request";
         $url = 'admin/sesgdpr/settings/unsubscribe';
       }else if($type == "forget"){
         $value['type'] = "forgot";
         $string = "User made forget requests.";
         $subject = "New Form Submission for Forgot Me related request";
         $url = 'admin/sesgdpr/settings/forgot-me';
       }
       $service->setFromArray($value);
       $service->save();
       $db->commit();
       $url = "";  
       $settings = Engine_Api::_()->getApi('settings', 'core');
       $adminEmail = $settings->getSetting('core.mail.contact');
       if (!$adminEmail) {
          $users_table = Engine_Api::_()->getDbtable('users', 'user');
          $users_select = $users_table->select()
                  ->where('level_id = ?', 1)
                  ->where('enabled >= ?', 1);
          $super_admin = $users_table->fetchRow($users_select);
          $adminEmail = $super_admin->email;
          if($type == "dpo"){
            $url = '<b>Message: </b>'.$_POST['message'].'<br><br>'.$host.$url;
          }
       }
       $host = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'].'/';
       $values['body'] = '<b>Name:</b> '.$value['name'].'<br><b>Email: </b>'.$value['email'].$url;
       //send to admin
       $core_mail = Engine_Api::_()->getApi('mail', 'core');
       $core_mail->sendSystem($adminEmail, 'sesgdpr_consent_user', array(
          'subject' => $subject,
          'body' => $values['body'],
       ));
       if($type == "dpo"){
          $emails = $settings->getSetting('sesgdpr.dpoemails',false);
          if($emails){
              $emails = explode(',',$emails);
              foreach($emails as $dpoemails){
                if($dpoemails){
                  $core_mail->sendSystem($dpoemails, 'sesgdpr_consent_user', array(
                      'subject' => $subject,
                      'body' => '<b>'.$this->view->translate('Name').':</b> '.$value['name'].'<br><b>'.$this->view->translate("Email").': </b>'.$value['email'].'<b>'.$this->view->translate("Name").' : </b>'.$_POST['message'],
                  ));
                }
              }
          }
       }
       $this->insetAuditAction($string,$params = array('description'=>$string,'email'=>$email));
    }catch(Exception $e){
      $this->_helper->json(array('status'=>0));
    }
    $this->_helper->json(array('status'=>1));
  }
  function consentAction(){
    $consent = $this->_getParam('type',false);
    if(!$consent && $consent != 0)
     {
      header('Location:'.$_SERVER['HTTP_REFERER']);
      exit();  
     }
      $viewer = $this->view->viewer();
      if($viewer->getIdentity()){
        if($consent){
            Engine_Api::_()->getDbTable('settings', 'user')->setSetting($this->view->viewer(),'user_consent',1);
            Engine_Api::_()->getDbTable('settings', 'user')->setSetting($this->view->viewer(),'user_consent_time', date('Y-m-d H:i:s'));  
            $db = Engine_Api::_()->getItemTable('sesgdpr_audit')->getAdapter();
            $db->beginTransaction();
            try { 
               $table = Engine_Api::_()->getItemTable('sesgdpr_audit');
               $service = $table->createRow();
               $value['email'] = $this->view->viewer()->email;
               $value['description'] = 'User given explicit consent to privacy policy.';
               $service->setFromArray($value);
               $service->save();
               $db->commit();
            }catch(Exception $e){}
        }else{
          $_SESSION['consent'] = 1;
          
            Engine_Api::_()->getDbTable('settings', 'user')->setSetting($this->view->viewer(),'user_consent',null);
            Engine_Api::_()->getDbTable('settings', 'user')->setSetting($this->view->viewer(),'user_consent_time',null);
          $db = Engine_Api::_()->getItemTable('sesgdpr_audit')->getAdapter();
          $db->beginTransaction();
          try { 
             $table = Engine_Api::_()->getItemTable('sesgdpr_audit');
             $service = $table->createRow();
             $value['email'] = $this->view->viewer()->email;
             $value['description'] = 'User withdrawn explicit consent to privacy policy.';
             $service->setFromArray($value);
             $service->save();
             $db->commit();
          }catch(Exception $e){}
        }
      }else{
        if($consent){
          setcookie('user_consent_date', date('Y-m-d H:i:s'), time() + (86400 * 30), "/");
          setcookie('user_consent', 1, time() + (86400 * 30), "/");
        }else{
          unset($_COOKIE['user_consent']);
          unset($_COOKIE['user_consent_date']);  
          setcookie('user_consent_date', "", time() - 39000, "/");
          setcookie('user_consent', "", time() - 39000, "/");
          $_SESSION['consent'] = 1;
        }
      }
      header('Location:'.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ""));
      exit(); 
  }
  function popupAction(){
    $viewer = $this->view->viewer();
    if($viewer->getIdentity()){
        Engine_Api::_()->getApi('settings', 'core')->setSetting($this->view->viewer(),'gdpr_popup_consent',1);
    }else
       setcookie('user_popup_consent', 1, time() + (86400 * 30), "/");
    exit();  
  }
}