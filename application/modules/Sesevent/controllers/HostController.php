<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: HostController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_HostController extends Core_Controller_Action_Standard {
	public function init() {
		if (!$this->_helper->requireAuth()->setAuthParams('sesevent_event', null, 'view')->isValid())
		return;
		if (!$this->_helper->requireUser->isValid())
		return;
			$viewer = Engine_Api::_()->user()->getViewer();
		$host_id = $this->_getParam('host_id', null);
		$host = Engine_Api::_()->getItem('sesevent_host', $host_id);
		if ($host) {
		if ($viewer->getIdentity() == $host->owner_id || $viewer->level_id == 1 )
			Engine_Api::_()->core()->setSubject($host);
		else
			return $this->_forward('requireauth', 'error', 'core');
		} else
		return $this->_forward('requireauth', 'error', 'core');
  }
  public function editAction() {
		$host =  Engine_Api::_()->core()->getSubject();
		$this->view->form = $form = new Sesevent_Form_Host_Edit();
		if (!$this->getRequest()->isPost()) {
			$form->populate($host->toArray());
      return;	 
		}
		if ($this->getRequest()->isPost()) {
			$db = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getAdapter();
			$db->beginTransaction();
			try {
				$host->setFromArray($_POST);				
				if(isset($_FILES['host_photo']['name']) && $_FILES['']['name'] != 'host_photo' && !$_POST['remove_host_img']){
					$photo_id = Engine_Api::_()->getDbtable('hosts', 'sesevent')->setPhoto($_FILES['host_photo'],$host->getIdentity());	
					$host->photo_id = $photo_id;
				}else if($_POST['remove_host_img']){
					$host->photo_id = 0;	
				}
				$host->save();
				$db->commit();			 
			} catch (Exception $e) {
				$db->rollBack();
				throw $e;
			}
		$this->_redirectCustom(array('route' => 'sesevent_viewhost', 'host_id' => $host->getIdentity()));
		}
	}
	public function deleteAction(){
		$admin = $this->_getParam('admin',false);
    $this->view->host = $host = Engine_Api::_()->core()->getSubject();		
    $viewer = Engine_Api::_()->user()->getViewer();
		if($host->type != 'offsite')
			return $this->_forward('notfound', 'error', 'core');
    // Make form
    $this->view->form = $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Host?');
    $form->setDescription('Are you sure that you want to delete this host ? It will not be recoverable after being deleted and event owner becomes the host of all events associalted with this host by default.');
    $form->submit->setLabel('Delete');
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
		$eventTable = Engine_Api::_()->getItemTable('sesevent_event');
    $db = $host->getTable()->getAdapter();
    $db->beginTransaction();
    try {
			$select= $eventTable->select()
													->setIntegrityCheck(false)
													->from($eventTable->info('name'))
													->where('host =?',$host->getIdentity())
													->where('host_type =?','offsite');
			$events = $eventTable->fetchAll($select);
			foreach($events as $event){
				$getEventHostId = Engine_Api::_()->getDbtable('hosts', 'sesevent')->getHostId(array('toValues' => $event->user_id, 'host_type' => 'site'));	
				if($getEventHostId){
					$host_id_in = $getEventHostId;
					$host_type = 'site';
				}else{
					$table = Engine_Api::_()->getDbtable('hosts', 'sesevent');
					$user =  Engine_Api::_()->getItem('user', $event->user_id);
	  			$hostIn = $table->createRow();
					$hostIn->host_name = $user->displayname;
					$hostIn->host_email = $user->email;
					$hostIn->photo_id = $user->photo_id;
					$hostIn->user_id = $user->getIdentity();
					$hostIn->type = 'site';
					$hostIn->owner_id =  $user->getIdentity();
					$hostIn->ip_address = $_SERVER['REMOTE_ADDR'];
					$hostIn->creation_date = date('Y-m-d H:i:s');
					$hostIn->modified_date = date('Y-m-d H:i:s');
					$hostIn->save();    
					$host_id_in = $hostIn->getIdentity();
					$host_type = 'site';
				}
				$event->host = $host_id_in;
				$event->host_type = $host_type;
				$event->save();
			}
      $host->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Host deleted successfully.');
		if($admin){
			return $this->_forward('success', 'utility', 'core', array(
                  'messages' => array($this->view->message),
                  'layout' => 'default-simple',
                  'parentRefresh' => true,
                  'smoothboxClose' => false,
      ));
		}else{
			return $this->_forward('success', 'utility', 'core', array('parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'manage'), 'sesevent_general', true), 'messages' => array($this->view->message)));
		}
	}
}
