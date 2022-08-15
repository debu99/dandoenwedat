<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Event_IndexController extends Sesapi_Controller_Action_Standard
{
  public function init() 
  {
    if( !$this->_helper->requireAuth()->setAuthParams('event', null, 'view')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $id = $this->_getParam('event_id', $this->_getParam('id', null));
    if( $id ) {
      $event = Engine_Api::_()->getItem('event', $id);
      if( $event ) {
        Engine_Api::_()->core()->setSubject($event);
      }
    }
  }
  
  public function createalbumAction(){

      $event_id = $this->_getParam('event_id', false);
      $event = Engine_Api::_()->getItem('event', $event_id);
      $album = $event->getSingletonAlbum();
      $album_id = $album->getIdentity();
      
      if(!$event_id)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
      // set up data needed to check quota
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['user_id'] = $viewer->getIdentity();

      $quota = $quota = 0;
      // Get form
      $form = new Event_Form_Photo_Upload();
      $form->file->setAttrib('data', array('event_id' => $event->getIdentity()));

      // Render
      //$form->populate(array('album' => $album_id));
      if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields, array('resources_type' => 'event'));
      }
      if (!$form->isValid($this->getRequest()->getPost())){
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
          if (count($validateFields))
              $this->validateFormFields($validateFields);
      }
      
      $params = array(
        'user_id' => $viewer->getIdentity(),
      );

      
      // Process
      $photoTable = Engine_Api::_()->getDbtable('photos', 'event');
      $db = $photoTable->getAdapter();
      $db->beginTransaction();
      try {

          // Add action and attachments
          $api = Engine_Api::_()->getDbtable('actions', 'activity');
          $action = $api->addActivity(Engine_Api::_()->user()->getViewer(), $event, 'event_photo_upload', null, array(
            'count' => count($_FILES['attachmentImage']['name'])
          ));          
          $count = 0;
          foreach($_FILES['attachmentImage']['name'] as $key => $files) {
            if(!empty($_FILES['attachmentImage']['name'][$key])) {
              $image = array('name' => $_FILES['attachmentImage']['name'][$key], 'type' => $_FILES['attachmentImage']['type'][$key], 'tmp_name' => $_FILES['attachmentImage']['tmp_name'][$key],'error' => $_FILES['attachmentImage']['error'][$key],'size' => $_FILES['attachmentImage']['size'][$key]);
              
              $photo = $photoTable->createRow();
              $photo->setFromArray($params);
              $photo->collection_id = $album->album_id;
              $photo->album_id = $album->album_id;
              $photo->event_id = $event->event_id;
              $photo->save();
              $photo->setPhoto($image);
              
              if( $action instanceof Activity_Model_Action && $count < 100 ) {
                $api->attachActivity($action, $photo, Activity_Model_Action::ATTACH_MULTI);
              }
              $count++;
            }
          }

          $db->commit();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), 'album_id' => $album->getIdentity()))));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
  }
  
  public function searchFormAction() {

    $filterOptions = (array)$this->_getParam('order', array('starttime ASC' => 'Start Time',
      'creation_date DESC' => 'Recently Created',
      'member_count DESC' => 'Most Popular',));
    $search_for = $this-> _getParam('search_for', 'event');

    $default_search_type = $this->_getParam('default_search_type', 'recentlySPcreated');

    $form = new Event_Form_Filter_Browse();

    if(count($filterOptions)) {
      $arrayOptions = $filterOptions;
      $filterOptions = array();
      foreach ($arrayOptions as $key=>$filterOption) {
        $value = str_replace(array('SP',''), array(' ',' '), $filterOption);
        $filterOptions[$key] = ucwords($value);
      }
      $filterOptions = array(''=>'')+$filterOptions;
      $form->order->setMultiOptions($filterOptions);
      $form->order->setValue($default_search_type);
    }
    // Populate with categories
    $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
    asort($categories, SORT_LOCALE_STRING);
    $categoryOptions = array('0' => '');
    foreach( $categories as $k => $v ) {
      $categoryOptions[$k] = $v;
    }
    if (sizeof($categoryOptions) <= 1) {
      $form->removeElement('category_id');
    } else {
      $form->category_id->setMultiOptions($categoryOptions);
    }
    $form->populate($_POST);
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
    $this->generateFormFields($formFields);
  }

  public function browseAction() {
  
    // Prepare
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('event', null, 'create');
    
    
    $filter = $this->_getParam('filter', 'future');
    if( $filter != 'past' && $filter != 'future' ) $filter = 'future';
      $this->view->filter = $filter;

    // Create form
    $this->view->formFilter = $formFilter = new Event_Form_Filter_Browse();
    $defaultValues = $formFilter->getValues();

    if( !$viewer || !$viewer->getIdentity() ) {
      $formFilter->removeElement('view');
    }

    // Populate options
    foreach( Engine_Api::_()->getDbtable('categories', 'event')->select()->order('title ASC')->query()->fetchAll() as $row ) {    
      $formFilter->category_id->addMultiOption($row['category_id'], $row['title']);
    }
    if (count($formFilter->category_id->getMultiOptions()) <= 1) {
      $formFilter->removeElement('category_id');
    }

    // Populate form data
    $formValues = array_merge($defaultValues, $this->_getAllParams());
    if( $formFilter->isValid($formValues) ) {
      $this->view->formValues = $values = $formFilter->getValues();
    } else {
      $formFilter->populate($defaultValues);
      $this->view->formValues = $values = array();
    }

    // Prepare data
    $this->view->formValues = $values = $formFilter->getValues();

    if( $viewer->getIdentity() && @$values['view'] == 1 ) {
      $values['users'] = array();
      foreach( $viewer->membership()->getMembersInfo(true) as $memberinfo ) {
        $values['users'][] = $memberinfo->user_id;
      }
    }

    $values['search'] = 1;
    
    $user_id = $this->_getParam('user_id');
    
    if(!$user_id) {
      if( $filter == "past" ) {
        $values['past'] = 1;
      } else {
        $values['future'] = 1;
      }
    }

     // check to see if request is for specific user's listings
    if( ($user_id = $this->_getParam('user_id')) ) {
      $values['user_id'] = $user_id;
    }

    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('event')
            ->getEventPaginator($values);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
  
    $result = $this->eventsResult($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist events.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));  
  }
  

  public function categoryAction() {

    $paginator = Engine_Api::_()->getDbTable('categories', 'event')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {
    
      if($key == '') continue;
      
      $category = Engine_Api::_()->getItem('event_category', $key);
      
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->title;
      
      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');
      
      //Events Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'events', 'module_name' => 'event'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s event', '%s events', $Itemcount), $this->view->locale()->toNumber($Itemcount));
      
      $counter++;
    }

    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }

  public function createAction()
  {
    if( !$this->_helper->requireUser->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireAuth()->setAuthParams('event', null, 'create')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $parent_type = $this->_getParam('parent_type');
    $parent_id = $this->_getParam('parent_id', $this->_getParam('subject_id'));

    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
      $this->view->group = $group = Engine_Api::_()->getItem('group', $parent_id);
      if( !$this->_helper->requireAuth()->setAuthParams($group, null, 'event')->isValid() ) {
        return;
      }
    } else {
      $parent_type = 'user';
      $parent_id = $viewer->getIdentity();
    }

    // Create form
    $this->view->parent_type = $parent_type;
    $this->view->form = $form = new Event_Form_Create(array(
      'parent_type' => $parent_type,
      'parent_id' => $parent_id
    ));

    // Populate with categories
    $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
    asort($categories, SORT_LOCALE_STRING);
    $categoryOptions = array('0' => '');
    foreach( $categories as $k => $v ) {
      $categoryOptions[$k] = $v;
    }
    if (sizeof($categoryOptions) <= 1) {
      $form->removeElement('category_id');
    } else {
      $form->category_id->setMultiOptions($categoryOptions);
    }

    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'event'));
    }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $formFields[2]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }


    // Process
    $values = $form->getValues();

    if( empty($values['auth_view']) ) {
      $values['auth_view'] = 'everyone';
    }

    if( empty($values['auth_comment']) ) {
      $values['auth_comment'] = 'everyone';
    }

    $values['user_id'] = $viewer->getIdentity();
    $values['parent_type'] = $parent_type;
    $values['parent_id'] =  $parent_id;
    $values['view_privacy'] =  $values['auth_view'];
    if( $parent_type == 'group' && Engine_Api::_()->hasItemType('group') && empty($values['host']) ) {
      $values['host'] = $group->getTitle();
    }
    
    // Convert times
    $oldTz = date_default_timezone_get();
    date_default_timezone_set($viewer->timezone);
    $start = strtotime($values['starttime']);
    $end = strtotime($values['endtime']);

    // check dates
    if( $start > $end ) {
      $form->starttime->setErrors(array('Start Date should be before End Date.'));
      return;
    }

    date_default_timezone_set($oldTz);
    $values['starttime'] = date('Y-m-d H:i:s', $start);
    $values['endtime'] = date('Y-m-d H:i:s', $end);

    $db = Engine_Api::_()->getDbtable('events', 'event')->getAdapter();
    $db->beginTransaction();

    try
    {
      // Create event
      $table = Engine_Api::_()->getDbtable('events', 'event');
      $event = $table->createRow();

      $event->setFromArray($values);
      $event->save();

      // Add owner as member
      $event->membership()->addMember($viewer)
        ->setUserApproved($viewer)
        ->setResourceApproved($viewer);

      // Add owner rsvp
      $event->membership()
        ->getMemberInfo($viewer)
        ->setFromArray(array('rsvp' => 2))
        ->save();

      if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
        $this->setPhoto($_FILES['image'],$event);
      }

      // Set auth
      $auth = Engine_Api::_()->authorization()->context;
      
      if( $values['parent_type'] == 'group' ) {
        $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $photoMax = array_search($values['auth_photo'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($event, $role, 'view',    ($i <= $viewMax));
        $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($event, $role, 'photo',   ($i <= $photoMax));
      }

      $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);

      // Add an entry for member_requested
      $auth->setAllowed($event, 'member_requested', 'view', 1);

      // Add action
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

      $action = $activityApi->addActivity($viewer, $event, 'event_create');
      
      if( $action ) {
        $activityApi->attachActivity($action, $event);
      }
      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('event_id' => $event->getIdentity(),'message' => $this->view->translate('Event created successfully.'))));
  }
  
  public function editAction()
  {
    $event_id = $this->getRequest()->getParam('event_id');
    $event = Engine_Api::_()->getItem('event', $event_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !($this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid() || $event->isOwner($viewer)) ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    
    // Create form
    $event = Engine_Api::_()->core()->getSubject();
    $this->view->form = $form = new Event_Form_Edit(array('parent_type'=>$event->parent_type, 'parent_id'=>$event->parent_id));

    // Populate with categories
    $categories = Engine_Api::_()->getDbtable('categories', 'event')->getCategoriesAssoc();
    asort($categories, SORT_LOCALE_STRING);
    $categoryOptions = array('0' => '');
    foreach( $categories as $k => $v ) {
      $categoryOptions[$k] = $v;
    }
    if (sizeof($categoryOptions) <= 1) {
      $form->removeElement('category_id');
    } else {
      $form->category_id->setMultiOptions($categoryOptions);
    }

   // if( !$this->getRequest()->isPost() ) {
      // Populate auth
      $auth = Engine_Api::_()->authorization()->context;

      if( $event->parent_type == 'group' ) {
        $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      foreach( $roles as $role ) {
        if( isset($form->auth_view->options[$role]) && $auth->isAllowed($event, $role, 'view') ) {
          $form->auth_view->setValue($role);
        }
        if( isset($form->auth_comment->options[$role]) && $auth->isAllowed($event, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
        if( isset($form->auth_photo->options[$role]) && $auth->isAllowed($event, $role, 'photo') ) {
          $form->auth_photo->setValue($role);
        }
      }
      $form->auth_invite->setValue($auth->isAllowed($event, 'member', 'invite'));
      $form->populate($event->toArray());

      // Convert and re-populate times
      $start = strtotime($event->starttime);
      $end = strtotime($event->endtime);
      $oldTz = date_default_timezone_get();
      date_default_timezone_set($viewer->timezone);
      $start = date('Y-m-d H:i:s', $start);
      $end = date('Y-m-d H:i:s', $end);
      date_default_timezone_set($oldTz);

      $form->populate(array(
        'starttime' => $start,
        'endtime' => $end,
      ));
      //return;
    //}
    
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $formFields[6]['name'] = "file";
      $this->generateFormFields($formFields,array('resources_type'=>'group'));
    }


    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    // Process
    $values = $form->getValues();

    if( empty($values['auth_view']) ) {
      $values['auth_view'] = 'everyone';
    }

    if( empty($values['auth_comment']) ) {
      $values['auth_comment'] = 'everyone';
    }

    $values['view_privacy'] =  $values['auth_view'];

    // Convert times
    $oldTz = date_default_timezone_get();
    date_default_timezone_set($viewer->timezone);
    $start = strtotime($values['starttime']);
    $end = strtotime($values['endtime']);

    // check dates
    if( $start > $end ) {
      $form->starttime->setErrors(array('Start Date should be before End Date.'));
      return;
    }

    date_default_timezone_set($oldTz);
    $values['starttime'] = date('Y-m-d H:i:s', $start);
    $values['endtime'] = date('Y-m-d H:i:s', $end);
    
    // Check parent
    if( !isset($values['host']) && $event->parent_type == 'group' && Engine_Api::_()->hasItemType('group') ) {
     $group = Engine_Api::_()->getItem('group', $event->parent_id);
     $values['host']  = $group->getTitle();
    }
    
    // Process
    $db = Engine_Api::_()->getItemTable('event')->getAdapter();
    $db->beginTransaction();

    try
    {
      // Set event info
      $event->setFromArray($values);
      $event->save();

      if( !empty($_FILES['file']['name']) &&  !empty($_FILES['file']['size']) ) {
        $this->setPhoto($_FILES['file'],$event);
      }

      // Process privacy
      $auth = Engine_Api::_()->authorization()->context;

      if( $event->parent_type == 'group' ) {
        $roles = array('owner', 'member', 'parent_member', 'registered', 'everyone');
      } else {
        $roles = array('owner', 'member', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      }

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $photoMax = array_search($values['auth_photo'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($event, $role, 'view',    ($i <= $viewMax));
        $auth->setAllowed($event, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($event, $role, 'photo',   ($i <= $photoMax));
      }

      $auth->setAllowed($event, 'member', 'invite', $values['auth_invite']);
      
      // Commit
      $db->commit();
    }

    catch( Engine_Image_Exception $e )
    {
      $db->rollBack();
      //$form->addError(Zend_Registry::get('Zend_Translate')->_('The image you selected was too large.'));
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }


    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($event) as $action ) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('event_id' => $event->getIdentity(),'message' => $this->view->translate('Event edited successfully.'))));
  }
  
  public function setPhoto($photo, $event)
  {

    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
    } elseif( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
    } elseif( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
    } else {
      throw new Event_Model_Exception('Invalid argument passed to setPhoto: ' . print_r($photo, 1));
    }

    $name = basename($photo['name']);

    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => 'event',
      'parent_id' => $event->getIdentity()
    );
    
    // Save
    $storage = Engine_Api::_()->storage();
    
    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(720, 720)
      ->write($path.'/m_'.$name)
      ->destroy();

    // Resize image (profile)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(330, 660)
      ->write($path.'/p_'.$name)
      ->destroy();

    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(140, 160)
      ->write($path.'/in_'.$name)
      ->destroy();

    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($path.'/is_'.$name)
      ->destroy();

    // Store
    $iMain = $storage->create($path.'/m_'.$name, $params);
    $iProfile = $storage->create($path.'/p_'.$name, $params);
    $iIconNormal = $storage->create($path.'/in_'.$name, $params);
    $iSquare = $storage->create($path.'/is_'.$name, $params);

    $iMain->bridge($iProfile, 'thumb.profile');
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');

    // Remove temp files
    @unlink($path.'/p_'.$name);
    @unlink($path.'/m_'.$name);
    @unlink($path.'/in_'.$name);
    @unlink($path.'/is_'.$name);

    // Update row
    $event->modified_date = date('Y-m-d H:i:s');
    $event->photo_id = $iMain->file_id;
    $event->save();

    // Add to album
    $viewer = Engine_Api::_()->user()->getViewer();
    $photoTable = Engine_Api::_()->getItemTable('event_photo');
    $eventAlbum = $event->getSingletonAlbum();
    $photoItem = $photoTable->createRow();
    $photoItem->setFromArray(array(
      'event_id' => $event->getIdentity(),
      'album_id' => $eventAlbum->getIdentity(),
      'user_id' => $viewer->getIdentity(),
      'file_id' => $iMain->getIdentity(),
      'collection_id' => $eventAlbum->getIdentity(),
      'user_id' =>$viewer->getIdentity(),
    ));
    $photoItem->save();

    return $event;
  }

  function eventsResult($paginator) {
  
    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    
    foreach($paginator as $item) {
    
      $resource = $item->toArray();
      $resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['owner_id'])->getTitle();
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();
      
      //Category name
      if(!empty($resource['category_id'])) {
        $category = Engine_Api::_()->getItem('event_category', $resource['category_id']);
        $resource['category_name'] = $category->title;
      }
      
      // Check content like or not and get like count
      if($viewer->getIdentity() != 0) {
        $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
        $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
      }

      if($item->isOwner($viewer)) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions= array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'event', 'edit');
        $counter = 0;
        if($canEdit) {
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit"); 
          $counter++;
        }
        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'event', 'delete');
        if($canDelete) {
          $menuoptions[$counter]['name'] = "delete";
          $menuoptions[$counter]['label'] = $this->view->translate("Delete");
        }
        $resource['menus'] = $menuoptions;  
      }
      
      if($item->isOwner($viewer)) {
        $viewer = Engine_Api::_()->user()->getViewer();
        $menuoptions= array();
        $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'event', 'edit');
        $counter = 0;
        if($canEdit) {
          $menuoptions[$counter]['name'] = "edit";
          $menuoptions[$counter]['label'] = $this->view->translate("Edit"); 
          $counter++;
        }
        $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'event', 'delete');
        if($canDelete) {
          $menuoptions[$counter]['name'] = "delete";
          $menuoptions[$counter]['label'] = $this->view->translate("Delete");
        }
        $resource['menus'] = $menuoptions;  
      }
      
      $result['events'][$counterLoop] = $resource;
      if($item->photo_id)
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($item,'','');
      else {
        $images = array('main' => $this->getBaseUrl(true, 'application/modules/Event/externals/images/nophoto_event_thumb_profile.png'),'icon' => $this->getBaseUrl(true, 'application/modules/Event/externals/images/nophoto_event_thumb_profile.png'),'normal' => $this->getBaseUrl(true, 'application/modules/Event/externals/images/nophoto_event_thumb_profile.png'),'profile' => $this->getBaseUrl(true, 'application/modules/Event/externals/images/nophoto_event_thumb_profile.png'));
      }
      if(!count($images))
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl());
      $result['events'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }
    return $result;
  }

}
