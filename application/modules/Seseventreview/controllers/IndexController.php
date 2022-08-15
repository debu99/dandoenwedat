<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_IndexController extends Core_Controller_Action_Standard {
  public function init() {
		$viewer = Engine_Api::_()->user()->getViewer();
		if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1) || !$this->_helper->requireAuth()->setAuthParams('eventreview', null, 'view')->isValid())
			return $this->_forward('notfound', 'error', 'core');    
    //Get subject
      $review_id = $this->_getParam('review_id');
      $review = Engine_Api::_()->getItem('eventreview', $review_id);
    if ($review_id && $review && $review instanceof Seseventreview_Model_Eventreview && !Engine_Api::_()->core()->hasSubject()) {
      Engine_Api::_()->core()->setSubject($review);
			$event = Engine_Api::_()->getItem($review->content_type, $review->content_id);
			$currentTime = time();
			//don't render widget if event ends
			if(strtotime($event->starttime) > ($currentTime))
				return $this->_forward('notfound', 'error', 'core');
    }else if($this->getParam('type',false) && strpos('sesevent_event',$this->getParam('type')) !== FALSE &&  $item = Engine_Api::_()->getItemByGuid($this->getParam('type'))){
			$currentTime = time();
			//don't render widget if event ends
			if(strtotime($item->starttime) > ($currentTime))
				return $this->_forward('notfound', 'error', 'core');
		}
  }
	public function browseAction() {
    // Render
    $this->_helper->content->setEnabled();
  }
  public function indexAction() {
    $this->view->someVar = 'someVal';
  }
  public function viewAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
		if( Engine_Api::_()->core()->hasSubject('eventreview'))
    	$subject = Engine_Api::_()->core()->getSubject();
		else
			return $this->_forward('notfound', 'error', 'core');
    $review_id = $this->_getParam('review_id', null);
		if (!$this->_helper->requireAuth()->setAuthParams('eventreview', null, 'view')->isValid())
    	return $this->_forward('notfound', 'error', 'core');
    //Increment view count
    if (!$viewer->isSelf($subject->getOwner())) {
      $subject->view_count++;
      $subject->save();
    }
    //Render
    $this->_helper->content->setEnabled();
  }
  public function createAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->item = $item = Engine_Api::_()->getItemByGuid($this->getParam('type'));
	if (!Engine_Api::_()->authorization()->getPermission($viewer, 'eventreview', 'create'))
    	return $this->_forward('notfound', 'error', 'core');
    if(!$item)
		return $this->_forward('notfound', 'error', 'core');
	//check review exists
	$isReview = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview')->isReview(array('content_id' => $item->getIdentity(), 'content_type' => $item->getType(), 'module_name' => 'sesevent', 'column_name' => 'review_id'));
	if(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.owner', 1)){
		$allowedCreate = true;	
	}else{
		if($item->user_id == $viewer->getIdentity())	
			$allowedCreate = false;
		else
			$allowedCreate = true;		
	}
	if($isReview || !$allowedCreate)
		return $this->_forward('notfound', 'error', 'core');
    if (isset($item->category_id) && $item->category_id != 0)
      $this->view->category_id = $item->category_id;
    else
      $this->view->category_id = 0;
    if (isset($item->subsubcat_id) && $item->subsubcat_id != 0)
      $this->view->subsubcat_id = $item->subsubcat_id;
    else
      $this->view->subsubcat_id = 0;
    if (isset($item->subcat_id) && $item->subcat_id != 0)
      $this->view->subcat_id = $item->subcat_id;
    else
      $this->view->subcat_id = 0;
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'seseventreview')->profileFieldId();
    $this->view->form = $form = new Seseventreview_Form_Review_Create(array('defaultProfileId' => $defaultProfileId));
    $title = Zend_Registry::get('Zend_Translate')->_('Write a Review for "<b>%s</b>".');
    $form->setTitle(sprintf($title, $item->getTitle()));
    $form->setDescription("Please fill below information.");
    if (!$this->getRequest()->isPost())
      return;
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    $values = $form->getValues();
    $values['rating'] = $_POST['rate_value'];
    $values['owner_id'] = $viewer->getIdentity();
    $values['module_name'] = strtolower($item->getModuleName());
    $values['content_type'] = $item->getType();
    $values['content_id'] = $item->getIdentity();
    $reviews_table = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview');
    $db = $reviews_table->getAdapter();
    $db->beginTransaction();
    try {
      $review = $reviews_table->createRow();
      $review->setFromArray($values);
      $review->save();
      
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search('everyone', $roles);
      $commentMax = array_search('everyone', $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($review, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($review, $role, 'comment', ($i <= $commentMax));
      }
      
		$dbObject = Engine_Db_Table::getDefaultAdapter();
		//tak review ids from post
		$table = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview');
		$tablename = $table->info('name');
		foreach($_POST as $key => $reviewC){
			if(count(explode('_',$key)) != 3 || !$reviewC)
				continue;
			$key = str_replace('review_parameter_','',$key);
			if(!is_numeric($key))
				continue;
			$parameter = Engine_Api::_()->getItem('seseventreview_parameter',$key);
			$query = 'INSERT INTO '.$tablename.' (`parameter_id`, `rating`, `user_id`, `resources_id`,`resources_type`,`content_id`) VALUES ("'.$key.'","'.$reviewC.'","'.$viewer->getIdentity().'","'.$item->getIdentity().'","sesevent_event","'.$review->getIdentity().'") ON DUPLICATE KEY UPDATE rating = "'.$reviewC.'"';
			$dbObject->query($query);
			$ratingP = $table->getRating($key, $review->content_type);
			$parameter->rating  = $ratingP;
			$parameter->save();
		}
		$db->commit();
		//save rating in parent table if exists
		if(isset($item->rating)){
			$item->rating = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview')->getRating($review->content_id, $review->content_type);
			$item->save();
		}
		 //Add fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($review);
      $customfieldform->saveValues();
			$review->save();
      $db->commit();
      return $this->_helper->redirector->gotoRoute(array('action' => 'view', 'review_id' => $review->review_id, 'slug' => $review->getSlug()), 'seseventreview_view', true);
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }
 public function editAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    $review_id = $this->_getParam('review_id', null);
		if (!Engine_Api::_()->authorization()->getPermission($viewer, 'eventreview', 'edit'))
    	return $this->_forward('notfound', 'error', 'core');
		$this->view->item = $item = Engine_Api::_()->getItem($subject->content_type, $subject->content_id);
		 if (isset($item->category_id) && $item->category_id != 0)
      $this->view->category_id = $item->category_id;
    else
      $this->view->category_id = 0;
    if (isset($item->subsubcat_id) && $item->subsubcat_id != 0)
      $this->view->subsubcat_id = $item->subsubcat_id;
    else
      $this->view->subsubcat_id = 0;
    if (isset($item->subcat_id) && $item->subcat_id != 0)
      $this->view->subcat_id = $item->subcat_id;
    else
      $this->view->subcat_id = 0;
    if(!$review_id || !$subject)
			 return $this->_forward('notfound', 'error', 'core');
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'seseventreview')->profileFieldId();
    $this->view->form = $form = new Seseventreview_Form_Review_Edit(array('defaultProfileId' => $defaultProfileId));
    $title = Zend_Registry::get('Zend_Translate')->_('Edit a Review for "<b>%s</b>".');
    $form->setTitle(sprintf($title, $subject->getTitle()));
    $form->setDescription("Please fill below information.");
    if (!$this->getRequest()->isPost()){
			$form->populate($subject->toArray());
			$form->rate_value->setValue($subject->rating);
      return;
		}
    if (!$form->isValid($this->getRequest()->getPost()))
      return;
    $values = $form->getValues();
    $values['rating'] = $_POST['rate_value'];
    $reviews_table = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview');
    $db = $reviews_table->getAdapter();
    $db->beginTransaction();
    try {
      $subject->setFromArray($values);
      $subject->save();
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search('everyone', $roles);
      $commentMax = array_search('everyone', $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($subject, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($subject, $role, 'comment', ($i <= $commentMax));
      }
			//save rating in parent table if exists
				//tak review ids from post
			$table = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview');
			$tablename = $table->info('name');
			$dbObject = Engine_Db_Table::getDefaultAdapter();
			foreach($_POST as $key => $reviewC){
				if(count(explode('_',$key)) != 3 || !$reviewC)
					continue;
				$key = str_replace('review_parameter_','',$key);
				if(!is_numeric($key))
					continue;
				$parameter = Engine_Api::_()->getItem('seseventreview_parameter',$key);
				$query = 'INSERT INTO '.$tablename.' (`parameter_id`, `rating`, `user_id`, `resources_id`,`resources_type`,`content_id`) VALUES ("'.$key.'","'.$reviewC.'","'.$subject->owner_id.'","'.$item->getIdentity().'","sesevent_event","'.$subject->getIdentity().'") ON DUPLICATE KEY UPDATE rating = "'.$reviewC.'"';
				$dbObject->query($query);
				$ratingP = $table->getRating($key, $subject->content_type);
				$parameter->rating  = $ratingP;
				$parameter->save();
			}
			if(isset($item->rating)){
				$item->rating = Engine_Api::_()->getDbtable('eventreviews', 'seseventreview')->getRating($subject->content_id, $subject->content_type);
				$item->save();
			}
			 //Add fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($subject);
      $customfieldform->saveValues();
			$subject->save();
      $db->commit();
      return $this->_helper->redirector->gotoRoute(array('action' => 'view', 'review_id' => $subject->review_id, 'slug' => $subject->getSlug()), 'seseventreview_view', true);
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }
  public function deleteAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $review = Engine_Api::_()->getItem('eventreview', $this->getRequest()->getParam('type'));
    $content_item = Engine_Api::_()->getItem($review->content_type, $review->content_id);
    if (!$this->_helper->requireAuth()->setAuthParams($review, $viewer, 'delete')->isValid())
      return;
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    $this->view->form = $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Review?');
    $form->setDescription('Are you sure that you want to delete this review? It will not be recoverable after being deleted.');
    $form->submit->setLabel('Delete');
    if ($this->getRequest()->isPost()) {
      $db = $review->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $review->delete();
        $db->commit();

        $this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected review has been deleted.');
        return $this->_forward('success', 'utility', 'core', array('parentRedirect' => $content_item->gethref(), 'messages' => array($this->view->message)));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }
}