<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ReportController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_ReportController extends Sesapi_Controller_Action_Standard
{
  public function init()
  {  
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
  }
  
  public function createAction()
  {
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();
    $this->view->form = $form = new Sesapi_Form_Report();
    if($this->_getParam('getForm')){
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }
    if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }

    // Process
    $table = Engine_Api::_()->getItemTable('core_report');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $report = $table->createRow();
      $report->setFromArray(array_merge($form->getValues(), array(
        'description'=>$_POST['des'],
        'subject_type' => $subject->getType(),
        'subject_id' => $subject->getIdentity(),
        'user_id' => $viewer->getIdentity(),
      )));
      $report->save();
      // Increment report count
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.reports');
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'','result'=>"Your report has been submitted."));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(),'result'=>array()));
    }
    
    
  }
}