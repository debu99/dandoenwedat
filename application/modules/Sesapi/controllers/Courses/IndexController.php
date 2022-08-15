<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesapi
 * @package    Sesapi
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php  2018-08-14 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Courses_IndexController extends Sesapi_Controller_Action_Standard {
  protected $_innerCalling = false;
 	public function init() { 
    if (!$this->_helper->requireAuth()->setAuthParams('courses', null, 'view')->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $course_id = $this->_getParam('course_id');
    $course = null;
    $course = Engine_Api::_()->getItem('courses', $course_id);
    if ($course) {
      if ($course) {
        Engine_Api::_()->core()->setSubject($course);
      } else {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      }
    }
  } 
  public function createAction() { 
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('courses', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $classroomId = $this->_getParam('classroom_id',false);
    $classroom = Engine_Api::_()->getItem('classroom', $classroomId);
    $sessmoothbox = false;
        $quckCreate = 0;
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if ($settings->getSetting('course.category.selection', 0) && $settings->getSetting('course.quick.create', 0)) {
      $quckCreate = 1;
    }
    if($this->_getParam('typesmoothbox',false)){
      // Render
      $sessmoothbox = true;
      $this->view->typesmoothbox = true;
      $layoutOri = $this->view->layout()->orientation;
      $language = explode('_', $this->view->locale()->getLocale()->__toString());
      $this->view->language = $language[0];
    } else {
      $this->_helper->content->setEnabled();
    }
    //get all allowed types product
    $viewer = Engine_Api::_()->user()->getViewer();
    $session = new Zend_Session_Namespace();
    if(empty($_POST))
      unset($session->album_id);
    $resource_id = $this->_getParam('resource_id', null);
    $resource_type = $this->_getParam('resource_type', null);
    $parentId = $this->_getParam('parent_id', 0);
    $values['user_id'] = $viewer->getIdentity();
    $allowCourseCount = Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'course_count');
    $totalCourse = Engine_Api::_()->getDbTable('courses', 'courses')->countCourses($viewer->getIdentity());
    // Prepare form
        if ($totalCourse >= $allowCourseCount && $allowCourseCount != 0) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'You have already uploaded the maximum number of entries allowed.', 'result' => array()));
    } else {
        $form = new Courses_Form_Course_Create(array(
            'defaultProfileId' => 1,
            'smoothboxType' => $sessmoothbox,
            'fromApi'=>true
        ));
    }
    // Check if post and populate
    if($form->getElement('formHeading1')){
      $form->removeElement('formHeading1');
    }
    if($form->getElement('formHeading11')){
      $form->removeElement('formHeading11');
    } 
    if($form->getElement('formHeading10')){
      $form->removeElement('formHeading10');
    } 
    if($form->getElement('formHeading12')){
      $form->removeElement('formHeading12');
    } 
    if($form->getElement('formHeading13')){
      $form->removeElement('formHeading13');
    }
    if($form->getElement('formHeading4')){
      $form->removeElement('formHeading4');
    }
    if($form->getElement('formHeading5')){
      $form->removeElement('formHeading5');
    }
    if($form->getElement('formHeading6')){
      $form->removeElement('formHeading6');
    }
    if($form->getElement('formHeading16')){
      $form->removeElement('formHeading16');
    } 
    if($form->getElement('formHeading17')){
      $form->removeElement('formHeading17');
    }
    if($form->getElement('submit_check')){
      $form->removeElement('submit_check');
    }
    if($form->getElement('courses_main_photo_preview')){
      $form->removeElement('courses_main_photo_preview');
    }
    if($form->getElement('photo-uploader')){
      $form->removeElement('photo-uploader');
    }
    if($form->getElement('removeimage')){
      $form->removeElement('removeimage');
    }
    if($form->getElement('course_custom_discount_datetimes')){
      $form->getElement('course_custom_discount_datetimes')->setLabel('Discount Start Date');
    }

    // 
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'courses'));
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }

    $settings = Engine_Api::_()->getApi('settings', 'core');
    if ($settings->getSetting('courses.mainPhoto.mandatory', 1)) {
      if (empty($_FILES['photo']['size']) && empty($_FILES['image']['size'])) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Main Photo is a required field.'), 'result' => array()));
      }
    }
    if (isset($_POST['custom_url_course']) && !empty($_POST['custom_url_course'])) {
      $custom_url = Engine_Api::_()->getDbtable('courses', 'courses')->checkCustomUrl($_POST['custom_url_course']);
      if ($custom_url) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Custom URL is not available. Please select another URL."), 'result' => array()));
      }
    }
        //price check
    if(empty($_POST['price'])){
      $form->addError($this->view->translate('Price is required.'));
      $priceError = true;
    }
      //discount check
    if(!empty($_POST['discount'])){
      if(empty($_POST['price'])){
        $form->addError($this->view->translate('Price is required.'));
        $priceError = true;
      }
      if(!empty($_POST['discount_end_type']) && empty($_POST['discount_end_date'])){
        $form->addError($this->view->translate('Discount End Date is required.'));
      }
      if(empty($priceError) && empty($_POST['discount_type'])){
        if(empty($_POST['percentage_discount_value'])){
          $form->addError($this->view->translate('Discount Value is required.'));
        }else if($_POST['percentage_discount_value'] > 100){
          $form->addError($this->view->translate('Discount Value must be less than or equal to 100.'));
        }
      }else if(empty($priceError)){
        if(empty($_POST['fixed_discount_value'])){
          $form->addError($this->view->translate('Discount Value is required.'));
        }else if($_POST['fixed_discount_value'] > $_POST['price']){
          $form->addError($this->view->translate('Discount Value must be less than or equal to Price.'));
        }
      }
        //check discount dates
      if(!empty($_POST['discount_start_date'])){
        $time = $_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00"); 
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($this->view->viewer()->timezone);
        $start = strtotime($time);
        if($start < time()){
          $timeDiscountError = true;
          $form->addError($this->view->translate('Discount Start Date field value must be greater than Current Time.'));
        }
        date_default_timezone_set($oldTz);
      }
      if(!empty($_POST['discount_end_date'])){
        $time = $_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00");
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($this->view->viewer()->timezone);
        $start = strtotime($time);

        if($start < time()){
          $timeDiscountError = true;
          $form->addError($this->view->translate('Discount End Date field value must be greater than Current Time.'));
        }
        date_default_timezone_set($oldTz);
      }
      if(empty($timeDiscountError)){
        if(!empty($_POST['discount_start_date'])){
          if(!empty($_POST['discount_end_date'])){
            $starttime = $_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00");
            $endtime = $_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00");
            $oldTz = date_default_timezone_get();
            date_default_timezone_set($this->view->viewer()->timezone);
            $start = strtotime($starttime);
            $end = strtotime($endtime);
            if($start > $end){
              $form->addError($this->view->translate('Discount Start Date value must be less than Discount End Date field value.'));
            }
            date_default_timezone_set($oldTz);
          }
        }
      }
    }
    $arrMessages = $form->getMessages();
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $error = '';
    foreach($arrMessages as $field => $arrErrors) {
      if(!$form->getElement($field))
        continue;
      if($field && intval($field) <= 0){
        $error .= sprintf(
          '<li>%s%s</li>',
          $form->getElement($field)->getLabel(),
          $view->formErrors($arrErrors)

        );
      }else{
        $error .= sprintf(
          '<li>%s</li>',
          $arrErrors
        );
      }
    }
    if($error){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array()));
    }
    //check custom url
    if (isset($_POST['custom_url_course']) && !empty($_POST['custom_url_course'])) {
        $custom_url = Engine_Api::_()->getDbtable('courses', 'courses')->checkCustomUrl($_POST['custom_url_course']);
        if ($custom_url) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate("Custom URL is not available. Please select another URL."), 'result' => array()));
        }
    }
    // Process
    $table = Engine_Api::_()->getDbtable('courses', 'courses');
    $values = $form->getValues();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $courses = $table->createRow();
      $values['owner_id'] = $viewer->getIdentity();
      if (!$quckCreate) {
        if (empty($values['category_id']))
          $values['category_id'] = 0;
        if (empty($values['subsubcat_id']))
          $values['subsubcat_id'] = 0;
        if (empty($values['subcat_id']))
          $values['subcat_id'] = 0;
      }
      if (!isset($values['discount_end_type']))
          $values['discount_end_type'] = 0;
      $values['style'] = !empty($_POST['style']) ? $_POST['style'] : 1;
      $courses->setFromArray($values);
      if(!isset($values['search']))
          $courses->search = 1;
      else
          $courses->search = $values['search'];
      if (isset($_POST['title'])) {
          $courses->title = $_POST['title'];
      }
      if (isset($_POST['subcat_id']))
          $courses->category_id = $_POST['category_id'];
      if (isset($_POST['subcat_id']))
          $courses->category_id = $_POST['category_id'];
      if (isset($_POST['subsubcat_id']))
          $courses->category_id = $_POST['category_id'];
      if (isset($_POST['draft']))
          $courses->draft = $_POST['draft'];
      $courses->parent_id = $parentId;
      $courses->save();
      
      // Other module work
      if(!empty($resource_type) && !empty($resource_id)) {
        $courses->resource_id = $resource_id;
        $courses->resource_type = $resource_type;
        $courses->save();
      }
      if (!empty($_POST['custom_url_course']) && $_POST['custom_url_course'] != '')
        $courses->custom_url = $_POST['custom_url_course'];
      else
        $courses->custom_url = $courses->course_id;
      //upsell
      if(!empty($_POST['upsell_id'])){
          $upsell = trim($_POST['upsell_id'],',');
          $upsells = explode(',',$upsell);
          foreach($upsells as $item){
              $params['course_id'] = $courses->getIdentity();
              $params['resource_id'] = $item;
              Engine_Api::_()->getDbTable('upsells','courses')->create($params);
          }
      }
      //crosssell
      if(!empty($_POST['crosssell_id'])){
          $crosssell = trim($_POST['crosssell_id'],',');
          $crosssells = explode(',',$crosssell);
          foreach($crosssells as $item){
              $params['course_id'] = $courses->getIdentity();
              $params['resource_id'] = $item;
              Engine_Api::_()->getDbTable('crosssells','courses')->create($params);
          }
      }
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
          $customfieldform->setItem($courses);
          $customfieldform->saveValues();
      }
      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if( empty($values['auth_view']) ) {
          $values['auth_view'] = 'everyone';
      }
      if( empty($values['auth_comment']) ) {
          $values['auth_comment'] = 'everyone';
      }
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $lectureCreate = array_search(isset($values['auth_ltr_create']) ? $values['auth_ltr_create']: '', $roles);
      $tstCreate = array_search(isset($values['auth_tst_create']) ? $values['auth_tst_create']: '', $roles);
      foreach( $roles as $i => $role ) {
          $auth->setAllowed($courses, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($courses, $role, 'comment', ($i <= $commentMax));
          $auth->setAllowed($courses, $role, 'ltr_create', ($i <= $lectureCreate));
          $auth->setAllowed($courses, $role, 'tst_create', ($i <= $tstCreate));
      }

      $tags = preg_split('/[,]+/', $values['tags']);
      $courses->tags()->addTagMaps($viewer, $tags);
      $courses->seo_keywords = implode(',', $tags);
      $courses->save();
      if(!empty($classroom)) {
        $courses->classroom_id = $classroom->classroom_id;
        $classroom->course_count++;
        $classroom->save();
      }
      if (isset($_FILES['photo']) && $_FILES['photo']['name'] != '') {
        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('coursesalbum')) {
          $courses->setPhoto($form->photo, '', 'profile');
        } else {
          $courses->photo_id = Engine_Api::_()->sesbasic()->setPhoto($form->photo, false,false,'courses','courses','',$courses,true);
        }
      }
      //discount
      if(!empty($_POST['discount_start_date'])){
          if(isset($_POST['discount_start_date']) && $_POST['discount_start_date'] != ''){
              $starttime = isset($_POST['discount_start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_start_date'].' '.$_POST['discount_start_date_time'])) : '';
              $courses->discount_start_date =$starttime;
          }
          if(isset($_POST['discount_start_date']) && $viewer->timezone && $_POST['discount_start_date'] != ''){
              //Convert Time Zone
              $oldTz = date_default_timezone_get();
              date_default_timezone_set($viewer->timezone);
              $start = strtotime($_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00"));

              $courses->discount_start_date = date('Y-m-d H:i:s', $start);
              date_default_timezone_set($oldTz);
          }
      }
      if(!empty($_POST['discount_end_date'])){
          if(isset($_POST['discount_end_date']) && $_POST['discount_end_date'] != ''){
              $starttime = isset($_POST['discount_end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_end_date'].' '.$_POST['discount_end_date_time'])) : '';
              $courses->discount_end_date =$starttime;
          }
          if(isset($_POST['discount_end_date']) && $viewer->timezone && $_POST['discount_end_date'] != ''){
              //Convert Time Zone
              $oldTz = date_default_timezone_get();
              date_default_timezone_set($viewer->timezone);
              $start = strtotime($_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00"));
              $courses->discount_end_date = date('Y-m-d H:i:s', $start);
              date_default_timezone_set($oldTz);
          }
      }
      if (!Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'auto_approve'))
          $courses->is_approved = 0;
      else
          $courses->is_approved = 1;
      if (Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'bs_featured'))
          $courses->featured = 1;
      if (Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'bs_sponsored'))
          $courses->sponsored = 1;
      if (Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'bs_verified'))
          $courses->verified = 1;
      if (Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'course_hot'))
          $courses->hot = 1;
      $courses->save();
      $coursename = '<a href="'.$courses->getHref().'">'.$courses->getTitle().'</a>';
        // Add activity only if courses is published
      if( $values['draft'] && $courses->is_approved == 1) {
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $courses, 'courses_course_create');
          // make sure action exists before attaching the courses to the activity
          if($action) {
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $courses);
          }
          //Tag Work
          if($action && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            foreach($tags as $tag) {
              $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
            }
          }
          $followers = Engine_Api::_()->getDbtable('followers', 'eclassroom')->getFollowers($courses->classroom_id);
          $favourites = Engine_Api::_()->getDbtable('favourites', 'courses')->getAllFavMembers($courses->course_id);
          $likes = Engine_Api::_()->getDbtable('likes', 'core')->getAllLikes($courses);
          $followerCourse = array();
          $favouriteCourse = array();
          $likesCourse = array();
          foreach($favourites as $favourite){
              $favouriteCourse[$favourite->owner_id] = $favourite->owner_id;
          }
          foreach($followers as $follower){
              $followerCourse[$follower->owner_id] = $follower->owner_id;

          }
          foreach($likes as $like){
              $likesCourse[$likes->owner_id] =  $likes->owner_id;
          }
          $users = array_unique(array_merge($likesCourse ,$followerCourse, $favouriteCourse), SORT_REGULAR);
          foreach($users as $user){ 
              $usersOject = Engine_Api::_()->getItem('user', $user);
              Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($usersOject, $viewer, $courses, 'courses_course_create');
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($usersOject->email, 'courses_course_create', array('host' => $_SERVER['HTTP_HOST'], 'course_name' => $coursename,'object_link'=>$courses->getHref()));
          }
      }
      $emails = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.emailalert', null);
      if(!empty($emails)) {
          $emailArray = explode(",",$emails);
          foreach($emailArray as $email) {
              $email = str_replace(' ', '', $email);
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'courses_course_create', array('host' => $_SERVER['HTTP_HOST'], 'course_name' => $coursename,'object_link'=>$courses->getHref()));
          }
      }
    //Start Send Approval Request to Admin
    try {
      if (!$courses->is_approved) {
        $getAdminnSuperAdmins = Engine_Api::_()->courses()->getAdminnSuperAdmins();
          foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {  
            $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $courses, 'courses_waitingadminapproval');
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'courses_waitingadminapproval', array('sender_title' => $courses->getOwner()->getTitle(), 'adminmanage_link' => 'admin/courses/manage','course_name' => $coursename, 'object_link' => $courses->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        }
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($courses->getOwner(), 'courses_course_wtapr', array('course_title' => $courses->getTitle(), 'course_name' => $coursename, 'object_link' => $courses->getHref(), 'host' => $_SERVER['HTTP_HOST']));
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($viewer, $viewer, $courses, 'courses_course_wtapr');
        $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.receivenewalertemails');
        if (!empty($receiverEmails)) {
          $receiverEmails = explode(',', $receiverEmails);
          foreach ($receiverEmails as $receiverEmail) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'courses_waitingadminapproval', array('sender_title' => $courses->getOwner()->getTitle(), 'object_link' => $courses->getHref(),'course_name' => $coursename, 'host' => $_SERVER['HTTP_HOST']));
          }
        }
      }
      //Send mail to all super admin and admins
      if ($courses->is_approved) {
        $getAdminnSuperAdmins = Engine_Api::_()->courses()->getAdminnSuperAdmins();
        foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
          $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'courses_waitingadminapproval', array('sender_title' => $courses->getOwner()->getTitle(), 'object_link' => $courses->getHref(),'course_name' => $coursename, 'host' => $_SERVER['HTTP_HOST']));
        }
        $receiverEmails = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.receivenewalertemails');
        if (!empty($receiverEmails)) {
          $receiverEmails = explode(',', $receiverEmails);
          foreach ($receiverEmails as $receiverEmail) { 
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($receiverEmail, 'courses_waitingadminapproval', array('sender_title' => $courses->getOwner()->getTitle(), 'object_link' => $courses->getHref(),'course_name' => $coursename, 'host' => $_SERVER['HTTP_HOST']));
          }
        }
      }
      // Commit
      $db->commit();
      }
      catch( Exception $e ) {
        $db->rollBack();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('course_id' => $courses->getIdentity(),'message' => $this->view->translate('You have successfully created a course.'))));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage()));
    }
  }
  public function menuAction()
  {
    $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('courses_main', array());
    $menu_counter = 0;
    foreach ($menus as $menu) {
        $class = end(explode(' ', $menu->class));
        $result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
        $result_menu[$menu_counter]['action'] = $class;
        $result_menu[$menu_counter]['isActive'] = $menu->active;
        $menu_counter++;
    }
    $result['menus'] = $result_menu;
    $result['can_create_courses'] = ( $this->_helper->requireAuth()->setAuthParams('courses', null, 'create')->isValid() && $this->_helper->requireAuth()->setAuthParams('courses', null, 'view')->isValid()) ? true : false;
    $result['can_create_classroom'] = ( $this->_helper->requireAuth()->setAuthParams('eclassroom', null, 'create')->isValid() && $this->_helper->requireAuth()->setAuthParams('classroom', null, 'view')->isValid()) ? true : false;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
  }
	public function reviewAction(){
		$courseId = $this->_getParam('course_id');
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course = Engine_Api::_()->core()->getSubject();
		}else{
			$course = Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.review', 1)){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		if (!Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'view'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.owner', 1)) {
			$allowedCreate = true;
		} else {
			if ($course->course_id == $viewer->getIdentity())
				$allowedCreate = false;
			else
				$allowedCreate = true;
		}
		$cancreate = Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'create');
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$editReviewPrivacy = Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'edit');
		$reviewTable = Engine_Api::_()->getDbtable('reviews', 'courses');
		$isReview = $hasReview = $reviewTable->isReview(array('course_id' => $course->getIdentity(), 'column_name' => 'review_id'));
		if($viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.review', 1) && $allowedCreate){
			if($cancreate && !$isReview){
				$result['button']['label'] = $this->view->translate('Write a Review');
				$result['button']['name'] = 'createreview';
			}
			if($editReviewPrivacy && $isReview){
				$result['button']['label'] = $this->view->translate('Update Review');
				$result['button']['name'] = 'updatereview';
			}
		}
		$table = Engine_Api::_()->getItemTable('courses_review');
		$course_id = $course->getIdentity();
		$params['course_id'] = $course_id;
		$select = $table->getCourseReviewSelect($params);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		$result['reviews'] = $this->getReviews($paginator,$course);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function reviewCreateAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
		$courseId = $this->_getParam('course_id');
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course = Engine_Api::_()->core()->getSubject();
		}else{
			$course = Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
    //check review exists
    $isReview = Engine_Api::_()->getDbtable('reviews', 'courses')->isReview(array('course_id' => $course->getIdentity(), 'column_name' => 'review_id'));
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.owner', 1)) {
        $allowedCreate = true;
    } else {
        if ($course->owner_id == $viewer->getIdentity())
            $allowedCreate = false;
        else
            $allowedCreate = true;
    }
    if ($isReview || !$allowedCreate)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if ($hasReview && Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'edit')) {
      $select = $reviewTable->select()
              ->where('course_id = ?', $course->getIdentity())
              ->where('owner_id =?', $viewer->getIdentity());
      $reviewObject = $reviewTable->fetchRow($select);
      $form = new Courses_Form_Review_Create(array( 'reviewId' => $reviewObject->review_id, 'courseItem' =>$course));
      $form->populate($reviewObject->toArray());
      $form->rate_value->setvalue($reviewObject->rating);
      $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'courses', 'controller' => 'review', 'action' => 'edit', 'review_id' => $reviewObject->review_id), 'default', true));
    } else {
        $form = new Courses_Form_Review_Create(array('courseItem' =>$course));
    }
		if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
    if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }
    if($_POST['rate_value'] == "0.0"){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '1', 'error_message' => "Rating required.", 'result' => array()));
    }
  $values = $_POST;
  $values['rating'] = $_POST['rate_value'] == "0.0" ? 1 : $_POST['rate_value'];
  $values['owner_id'] = $viewer->getIdentity();
  $values['course_id'] = $course->getIdentity();
  $reviews_table = Engine_Api::_()->getDbtable('reviews', 'courses');
  $db = $reviews_table->getAdapter();
  $db->beginTransaction();
  try {
      $review = $reviews_table->createRow();
      $review->setFromArray($values);
      $review->description = $_POST['description'];
      $review->save();
      $reviewObject = $review;
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      //tak review ids from post
      $table = Engine_Api::_()->getDbtable('parametervalues', 'courses');
      $tablename = $table->info('name');
      foreach ($_POST as $key => $reviewC) {
          if (count(explode('_', $key)) != 3 || !$reviewC)
              continue;
          $key = str_replace('review_parameter_', '', $key);
          if (!is_numeric($key))
              continue;
          $parameter = Engine_Api::_()->getItem('courses_parameter', $key);
          $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $viewer->getIdentity() . '","' . $course->getIdentity() . '","' . $review->getIdentity() . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
          $dbObject->query($query);
          $ratingP = $table->getRating($key);
          $parameter->rating = $ratingP;
          $parameter->save();
      }
      $db->commit();
      //save rating in parent table if exists
      if (isset($course->rating)) {
          $course->rating = Engine_Api::_()->getDbtable('reviews', 'courses')->getRating($review->course_id);
          $course->save();
      }
      $review->save();
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $course, 'courses_reviewpost');
      if ($action != null) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $review);
      }
      if ($course->owner_id != $viewer->getIdentity()) {
          $itemOwner = $course->getOwner('user');
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($itemOwner, $viewer, $review, 'courses_reviewpost');
      }
      $db->commit();
      $stats = Engine_Api::_()->courses()->getWidgetParams($viewer->getIdentity());
      $this->view->stats = count($stats) ? $stats : $this->_getParam('stats', array('featured', 'sponsored', 'likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended', 'parameter', 'rating'));
      if (Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'edit')) {
          $this->view->form = $form = new Courses_Form_Review_Create(array( 'reviewId' => $reviewObject->review_id, 'courseItem' => $course));
          $form->populate($reviewObject->toArray());
          $form->rate_value->setvalue($reviewObject->rating);
          $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'courses', 'controller' => 'review', 'action' => 'edit', 'review_id' => $reviewObject->review_id), 'default', true));
      }
      $this->view->rating_count = Engine_Api::_()->getDbTable('reviews', 'courses')->ratingCount($course->getIdentity());
      $this->view->rating_sum = $userInfoItem->rating;
			$msg = $isReview ? $this->view->translate('Review edited successfully.') : $this->view->translate('Review created successfully.');
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('review_id' => $review->getIdentity(), 'message' =>$msg)));
        } catch (Exception $e) {
            $db->rollBack();
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function getReviews($paginator,$course){
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		foreach($paginator as $item){
			$result[$counter] = $item->toArray();
			$owner = $item->getOwner();
			$result[$counter]['course']['images'] = $this->getBaseUrl(true, $course->getPhotoUrl());
			$result[$counter]['course']['title'] = $course->getTitle();
			$result[$counter]['course']['Guid'] = $course->getGuid();
			$result[$counter]['course']['id'] = $course->getIdentity();
			$result[$counter]['owner']['id'] = $owner->getIdentity();
			$result[$counter]['owner']['Guid'] = $owner->getGuid();
			$result[$counter]['owner']['title'] = $owner->getTitle();
			$result[$counter]['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
			$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'courses')->getParameters(array('content_id'=>$item->getIdentity(),'user_id'=>$item->owner_id));
			$perameterCounter = 0;
			foreach($reviewParameters as $reviewP){ 
				$result[$counter]['review_perameter'][$perameterCounter] = $reviewP->toArray();
				$perameterCounter++;
			}
			$result[$counter]['can_show_pros']  = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.show.pros', 1) ? true : false;
			$result[$counter]['can_show_cons']  = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.show.cons', 1) ? true : false;
			
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.votes', 1)){
				$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','courses')->isReviewVote(array('review_id'=>$item->getIdentity(),'course_id'=>$course->getIdentity(),'type'=>1));
				$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','courses')->isReviewVote(array('review_id'=>$item->getIdentity(),'course_id'=>$course->getIdentity(),'type'=>2));
				$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','courses')->isReviewVote(array('review_id'=>$item->getIdentity(),'course_id'=>$course->getIdentity(),'type'=>3));
				$voteCounter = 0;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.first', 'Useful'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 1;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypeone ? true : false;
				$voteCounter++;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.second', 'Funny'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 2;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypetwo ? true : false;
				$voteCounter++;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.third', 'Cool'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 3;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypethree ? true : false;
				$voteCounter++;
			}
				$ownerSelf = $viewer->getIdentity() == $item->owner_id ? true : false;
				$counterOption = 0;
				if($item->authorization()->isAllowed($viewer, 'edit')) {
					$result[$counter]['options'][$counterOption]['name'] = 'edit'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Edit Review'); 
					$counterOption++;
				}
				if($item->authorization()->isAllowed($viewer, 'delete')) {
					$result[$counter]['options'][$counterOption]['name'] = 'delete'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Delete Review'); 
					$counterOption++;
				}
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.show.report', 1) && $viewer->getIdentity()){
					$result[$counter]['options'][$counterOption]['name'] = 'report'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Report'); 
					$counterOption++;
				}
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.share', 1) && $viewer->getIdentity()){
					$result[$counter]['options'][$counterOption]['name'] = 'share'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Share Review'); 
					$counterOption++;
				}
			$counter++;
		}
		return $result;
	}
	public function editReviewAction() {
    $review_id = $this->_getParam('review_id', null);
    $subject = Engine_Api::_()->getItem('courses_review', $review_id);
    if (!Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'edit'))
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $item = Engine_Api::_()->getItem('courses', $subject->course_id);
    if (!$review_id || !$subject)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $form = new Courses_Form_Review_Edit(array('reviewId' => $subject->review_id,  'courseItem' => $item));
    $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'courses', 'controller' => 'review', 'action' => 'edit-review', 'review_id' => $review_id), 'default', true));
    $title = Zend_Registry::get('Zend_Translate')->_('Edit a Review for "<b>%s</b>".');
    $form->setTitle(sprintf($title, $subject->getTitle()));
    $form->setDescription("Please fill below information.");
    $form->populate($subject->toArray());
    $form->rate_value->setValue($subject->rating);
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields, array('resources_type' => 'courses_review','rate_value'=>$subject->rating));
    }
    if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
          $this->validateFormFields($validateFields);
    }
    $values = $_POST;
    $values['rating'] = $_POST['rate_value'] == "0.0" ? 1 : $_POST['rate_value'];
    $reviews_table = Engine_Api::_()->getDbtable('reviews', 'courses');
    $db = $reviews_table->getAdapter();
    $db->beginTransaction();
    try {
        $subject->setFromArray($values);
        $subject->save();
        $table = Engine_Api::_()->getDbtable('parametervalues', 'courses');
        $tablename = $table->info('name');
        $dbObject = Engine_Db_Table::getDefaultAdapter();
        foreach ($_POST as $key => $reviewC) {
            if (count(explode('_', $key)) != 3 || !$reviewC)
                continue;
            $key = str_replace('review_parameter_', '', $key);
            if (!is_numeric($key))
                continue;
            $parameter = Engine_Api::_()->getItem('courses_parameter', $key);
            $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $subject->owner_id . '","' . $item->owner_id . '","' . $subject->review_id . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
            $dbObject->query($query);
            $ratingP = $table->getRating($key);
            $parameter->rating = $ratingP;
            $parameter->save();
        }
        if (isset($item->rating)) {
            $item->rating = Engine_Api::_()->getDbtable('reviews', 'courses')->getRating($subject->course_id);
            $item->save();
        }
        $subject->save();
        $reviewObject = $subject;
        $db->commit();
        $message = Zend_Registry::get('Zend_Translate')->_('The selected review has been edited.');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('review_id' => $subject->getIdentity(), 'message' =>$message)));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
	public function likeReviewAction() {
        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $item_id = $this->_getParam('id',$this->_getParam('review_id'));
        if (intval($item_id) == 0) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $itemTable = Engine_Api::_()->getItemTable('courses_review');
        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $tableLike->info('name');
        $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', 'courses_review')
            ->where('poster_id = ?', $viewer_id)
            ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $item_id);
        $result = $tableLike->fetchRow($select);
        if (count($result) > 0) {
          //delete
          $db = $result->getTable()->getAdapter();
          $db->beginTransaction();
          try {
              $result->delete();
              //$itemTable->update(array('like_count' => new Zend_Db_Expr('like_count - 1')), array('review_id = ?' => $item_id));
              $db->commit();
              $temp['data']['message'] = $this->view->translate('Review Successfully Unliked.');
          } catch (Exception $e) {
              $db->rollBack();
              Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
          }
          $selectUser = $itemTable->select()->where('review_id =?', $item_id);
          $item = $user = $itemTable->fetchRow($selectUser);
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
            $db->beginTransaction();
            try {
                $like = $tableLike->createRow();
                $like->poster_id = $viewer_id;
                $like->resource_type = 'courses_review';
                $like->resource_id = $item_id;
                $like->poster_type = 'user';
                $like->save();
                $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array('review_id = ?' => $item_id));
                //Commit
                $db->commit();
                $temp['data']['message'] = $this->view->translate('Review Successfully liked.');
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            //Send notification and activity feed work.
            $selectUser = $itemTable->select()->where('review_id =?', $item_id);
            $item = $itemTable->fetchRow($selectUser);
            $subject = $item;
            $owner = $subject->getOwner();
            if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer_id) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'liked', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, 'liked');
                $result = $activityTable->fetchRow(array('type =?' => 'liked', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                if (!$result) {
                    $action = $activityTable->addActivity($viewer, $subject, 'liked');
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                }
            }
      }
      $temp['data']['like_count'] = $item->like_count;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
  }
	public function deleteReviewAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $review = Engine_Api::_()->getItem('courses_review', $this->getRequest()->getParam('review_id'));
        $content_item = Engine_Api::_()->getItem('courses', $review->course_id);
        if (!$this->_helper->requireAuth()->setAuthParams($review, $viewer, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        // In smoothbox
        if ($this->getRequest()->isPost()) {
            $db = $review->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $reviewParameterTable = Engine_Api::_()->getDbTable('parametervalues', 'courses');
                $select = $reviewParameterTable->select()->where('content_id =?', $review->review_id);
                $parameters = $reviewParameterTable->fetchAll($select);
                if (count($parameters) > 0) {
                    foreach ($parameters as $parameter) {
                        $reviewParameterTable->delete(array('parametervalue_id =?' => $parameter->parametervalue_id));
                    }
                }
                $review->delete();
                $db->commit();
                //$this->view->message = Zend_Registry::get('Zend_Translate')->_('The selected review has been deleted.');
                //return $this->_forward('success', 'utility', 'core', array('parentRedirect' => $content_item->gethref(), 'messages' => array($this->view->message)));
        				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message'=> $this->view->translate('The selected review has been deleted.'))));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
        }else{
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
  }
	public function reviewVotesAction() {
        if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
           Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $item_id = $this->_getParam('id',$this->_getParam('review_id'));
        $type = $this->_getParam('type');
        if (intval($item_id) == 0 || ($type != 1 && $type != 2 && $type != 3)) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $itemTable = Engine_Api::_()->getItemTable('courses_review');
        $tableVotes = Engine_Api::_()->getDbtable('reviewvotes', 'courses');
        $tableMainVotes = $tableVotes->info('name');
        $review = Engine_Api::_()->getItem('courses_review',$item_id);
        $course = Engine_Api::_()->getItem('courses',$review->course_id);
        $select = $tableVotes->select()
            ->from($tableMainVotes)
            ->where('review_id = ?', $item_id)
            ->where('user_id = ?', $viewer_id)
            ->where('type =?', $type);
        $result = $tableVotes->fetchRow($select);
        if ($type == 1)
            $votesTitle = 'useful_count';
        else if ($type == 2)
            $votesTitle = 'funny_count';
        else
            $votesTitle = 'cool_count';
        if (count($result) > 0) {
            //delete
            $db = $result->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $result->delete();
                $itemTable->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' - 1')), array('review_id = ?' => $item_id));
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            $selectReview = $itemTable->select()->where('review_id =?', $item_id);
            $review = $itemTable->fetchRow($selectReview);
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0','condition' => 'reduced', 'error_message' => '', 'result' => array('status'=>true,'count'=>$review->{$votesTitle})));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('reviewvotes', 'courses')->getAdapter();
            $db->beginTransaction();
            try {
                $votereview = $tableVotes->createRow();
                $votereview->user_id = $viewer_id;
                $votereview->review_id = $item_id;
                $votereview->type = $type;
                $votereview->save();
                $itemTable->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' + 1')), array('review_id = ?' => $item_id));
                //Commit
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
            //Send notification and activity feed work.
            $selectReview = $itemTable->select()->where('review_id =?', $item_id);
            $review = $itemTable->fetchRow($selectReview);


            //echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'increment', 'count' => $review->{$votesTitle}));
            //die;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('status'=>true,'condition' => 'increment','count'=>$review->{$votesTitle})));
        }
  }
	public function reviewViewAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $review_id = $this->_getParam('review_id', null);
		if(!$review_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.review', 1))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			
    if (!Engine_Api::_()->sesbasic()->getViewerPrivacy('courses_review', 'view'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		$review = Engine_Api::_()->getItem('courses_review', $review_id);
		$course = Engine_Api::_()->getItem('courses', $review->course_id);
		if(!$review)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    //Increment view count
    if (!$viewer->isSelf($review->getOwner())) {
        $review->view_count++;
        $review->save();
    }
		$params = array();
		$result = array();
		/*----------------make data-----------------------------*/
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$result = $review->toArray();
		$reviewer = Engine_Api::_()->getItem('user', $review->owner_id);
		$owner = $reviewer->getOwner();
		$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'courses')->getParameters(array('content_id'=>$review->getIdentity(),'user_id'=>$review->owner_id));
		$likeStatus = Engine_Api::_()->courses()->getLikeStatus($review->review_id,$review->getType());
		$ownerSelf = $viewerId == $review->owner_id ? true : false;
		$parameterCounter = 0;
		if(count($reviewParameters)>0){
			foreach($reviewParameters as $reviewP){ 
				$result['review_perameter'][$parameterCounter] = $reviewP->toArray();
				$parameterCounter++;
			}
		}
		$result['course']['images'] = $this->getBaseUrl(true, $course->getPhotoUrl());
		$result['course']['title'] = $course->getTitle();
		$result['course']['Guid'] = $course->getGuid();
		$result['course']['id'] = $course->getIdentity();
		$result['owner']['id'] = $owner->getIdentity();
		$result['owner']['Guid'] = $owner->getGuid();
		$result['owner']['title'] = $owner->getTitle();
		$result['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
		$result['show_pros'] = true;
		$result['show_cons'] = true;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.votes', 1)){
			$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','courses')->isReviewVote(array('review_id'=>$review->getIdentity(),'course_id'=>$course->getIdentity(),'type'=>1));
			$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','courses')->isReviewVote(array('review_id'=>$review->getIdentity(),'course_id'=>$course->getIdentity(),'type'=>2));
			$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','courses')->isReviewVote(array('review_id'=>$review->getIdentity(),'course_id'=>$course->getIdentity(),'type'=>3));
			$result['voting']['label'] = $this->view->translate("Was this Review...?");
			$bttonCounter	= 0 ;			
			$result['voting']['buttons'][$bttonCounter]['name'] = 'useful';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.first', 'Useful'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypeone ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $review->useful_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'funny';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.second', 'Funny'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypetwo ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $review->funny_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'cool';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.review.third', 'Cool'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypethree ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $review->cool_count;
			
		}
		if($review->authorization()->isAllowed($viewer, 'comment')){
			$result['is_content_like'] = $likeStatus ? true : false;
		}
		$optionCounter = 0;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.show.report', 1) && $viewerId && $viewerId != $owner->getIdentity()){
			$result['options'][$optionCounter]['name'] = 'report';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Report');
			$optionCounter++;
		}
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.share', 1) && $viewerId){
			$result['options'][$optionCounter]['name'] = 'share';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Share');
			$optionCounter++;
			
			/*------------- share object -----------------*/
				$result["share"]["imageUrl"] = $this->getBaseUrl(false, $review->getPhotoUrl());
				$result["share"]["url"] = $this->getBaseUrl(false,$review->getHref());
				$result["share"]["title"] = $review->getTitle();
				$result["share"]["description"] = strip_tags($review->getDescription());
				$result["share"]["setting"] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespagereview.allow.share', 1);
				$result["share"]['urlParams'] = array(
					"type" => $review->getType(),
					"id" => $review->getIdentity()
				);
				/*------------- share object -----------------*/
		}
		if($review->authorization()->isAllowed($viewer, 'edit')) { 
			$result['options'][$optionCounter]['name'] = 'edit';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Edit Review');
			$optionCounter++;
		}
		if($review->authorization()->isAllowed($viewer, 'delete')) {
			$result['options'][$optionCounter]['name'] = 'delete';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Delete Review');
			$optionCounter++;
		}
		/*----------------make data-----------------------------*/
		$data['review'] = $result;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data)));
	}
	public function editWishlistAction() {
		//Only members can upload video
		if (!$this->_helper->requireUser()->isValid())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

		//Get wishlist
		if(!$this->_getParam('wishlist_id'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		$wishlist = Engine_Api::_()->getItem('courses_wishlist', $this->_getParam('wishlist_id'));
		if(!$wishlist)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Wishlist not found'), 'result' => array()));
		//Make form
		$form = new Courses_Form_Wishlist_Edit();
		$form->populate($wishlist->toarray());
		if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'courses_wishlist'));
		}
		if (!$this->getRequest()->isPost())
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

		if (!$form->isValid($this->getRequest()->getPost())){
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		}
		$values = $form->getValues();
      unset($values['file']);
		$db = Engine_Api::_()->getDbTable('wishlists', 'courses')->getAdapter();
		$db->beginTransaction();
		try {
			$wishlist->title = $values['title'];
			$wishlist->description = $values['description'];
			$wishlist->is_private = $values['is_private'];
			$wishlist->save();
			//Photo upload for wishlist
			if (!empty($values['mainphoto'])) {
				$previousPhoto = $wishlist->photo_id;
				if ($previousPhoto) {
					$wishlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
					$wishlistPhoto->delete();
				}
				$wishlist->setPhoto($form->mainphoto, 'mainPhoto');
			}
			if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
				$storage = Engine_Api::_()->getItem('storage_file', $wishlist->photo_id);
				$wishlist->photo_id = 0;
				$wishlist->save();
				if ($storage)
				$storage->delete();
			}
		  $db->commit();
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Wishlist edited successfully .'), 'status'=>true)));
		} catch (Exception $e) {
		  $db->rollback();
		   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
		}
	}
	public function deleteWishlistAction() {
		$wishlist_id = $this->_getParam('wishlist_id');
		if(!$wishlist_id)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		$wishlist = Engine_Api::_()->getItem('courses_wishlist', $wishlist_id);
		if (!$wishlist) {
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		if (!$this->getRequest()->isPost()) {
		   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid request method'), 'result' => array()));
		}
		$db = $wishlist->getTable()->getAdapter();
		$db->beginTransaction();
		try {
		  //Delete all wishlist products which is related to this wishlist
		  Engine_Api::_()->getDbtable('playlistcourses', 'courses')->delete(array('wishlist_id =?' => $this->_getParam('wishlist_id')));
		  $wishlist->delete();
		  $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected wishlist has been deleted.'), 'status'=>true)));
		} catch (Exception $e) {
		  $db->rollBack();
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
		}
	}
	public function addWishlistAction() {
    //Check auth
    if (!$this->_helper->requireUser()->isValid())
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if (!$this->_helper->requireAuth()->setAuthParams('courses', null, 'addwishlist')->isValid())
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    //Set song
    $course_id = $this->_getParam('course_id'); 
    if(!$course_id)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $course = Engine_Api::_()->getItem('courses', $course_id);
    if (!$course) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    //Get form
    $form = new Courses_Form_Wishlist_Append();
    if ($form->wishlist_id) {
      //Populate form
      $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'courses');
      $select = $wishlistTable->select()
              ->from($wishlistTable, array('wishlist_id', 'title'));
      $select->where('owner_id = ?', $viewer->getIdentity());
      $wishlists = $wishlistTable->fetchAll($select);
      if ($wishlists)
        $wishlists = $wishlists->toArray();
      foreach ($wishlists as $wishlist)
        $form->wishlist_id->addMultiOption($wishlist['wishlist_id'], html_entity_decode($wishlist['title']));
      $form->wishlist_id->setValue(0);
    }
    if ($this->_getParam('getForm')) {
      $values =  $form->getValues();
      if($_POST['wishlist_id']){
        $form->populate(array('wishlist_id' => $_POST['wishlist_id']));
        $form->removeElement('title');
        $form->removeElement('description');
        $form->removeElement('mainphoto');
        $form->removeElement('is_private');
      }
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'coursesreview'));
    }
    //Check method/data
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
        $this->validateFormFields($validateFields);
    }
    //Get values
    $values = $form->getValues();
    if (empty($values['wishlist_id']) && empty($values['title'])){
      $form->title->setRequired(true);
      $form->addError('Please enter a title or select a wishlist.');
    }
	//Existing wishlist
    if (!empty($values['wishlist_id'])) {
        $wishlist = Engine_Api::_()->getItem('courses_wishlist', $values['wishlist_id']);
        //Already exists in wishlist
        $alreadyExists = Engine_Api::_()->getDbtable('playlistcourses', 'courses')->checkCoursesAlready(array('column_name' => 'playlistcourse_id','file_id' => $course_id, 'wishlist_id' => $wishlist->wishlist_id));
        if ($alreadyExists)
          $form->addError($this->view->translate("This wishlist already has this course."));
    }
    if (!$form->isValid($this->getRequest()->getPost())){
      $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
      if (count($validateFields))
        $this->validateFormFields($validateFields);
    }
    //Process
    $wishlistCoursesTable = Engine_Api::_()->getDbtable('wishlists', 'courses');
    $db = $wishlistCoursesTable->getAdapter();
    $db->beginTransaction();
    try {
    //New wishlist
      $wishlist = $wishlistCoursesTable->createRow();
      $wishlist->title = trim($values['title']);
      $wishlist->description = $values['description'];
      $wishlist->owner_id = $viewer->getIdentity();
      $wishlist->course_id = $course_id;
      $wishlist->is_private = $values['is_private'];
      $wishlist->save();
      $wishlist->courses_count++;
      $wishlist->save();
      //Add song
      $wishlist->addCourse($course->photo_id, $course_id);
      $wishlistID = $wishlist->getIdentity();
      //Photo upload for wishlist
      if (!empty($values['mainphoto'])) {
        $previousPhoto = $wishlist->photo_id;
        if ($previousPhoto) {
          $wishlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $wishlistPhoto->delete();
        }
        $wishlist->setPhoto($form->mainphoto, 'mainPhoto');
      }
      if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
        $storage = Engine_Api::_()->getItem('storage_file', $wishlist->photo_id);
        $wishlist->photo_id = 0;
        $wishlist->save();
        if ($storage)
          $storage->delete();
      }
      //Activity Feed work
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $course, "courses_wishlist_create", '', array('wishlist' => array($wishlist->getType(), $wishlist->getIdentity()),
      ));
      if ($action) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $course);
      }
      $db->commit();
      //Response
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Courses has been successfully added to your wishlist.'), 'status'=>true)));
      } catch (Sesproduct_Model_Exception $e) {
        $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
	public function viewWishlistAction(){
		$wishlist_id = $this->_getParam('wishlist_id');
		$wishlist = null;
		$wishlist = Engine_Api::_()->getItem('courses_wishlist', $wishlist_id);
		if ($wishlist) {
			if ($wishlist) {
			  Engine_Api::_()->core()->setSubject($wishlist);
			} else {
			  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			}
		}
		if (!$this->_helper->requireSubject()->isValid()){
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		//Get viewer/subject
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$wishlist_id = $this->_getParam('wishlist_id', null);
		if(!$wishlist_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		$wishlist = Engine_Api::_()->getItem('courses_wishlist', $wishlist_id);
		if(!$viewer->isSelf($wishlist->getOwner())){
			
			if($wishlist->is_private){
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			}
		}
		//Increment view count
		if (!$viewer->isSelf($wishlist->getOwner())) {
		  $wishlist->view_count++;
		  $wishlist->save();
		}
    /* Insert data for recently viewed widget */
		if ($viewer->getIdentity() != 0) {
		  $dbObject = Engine_Db_Table::getDefaultAdapter();
		  $dbObject->query('INSERT INTO engine4_courses_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $wishlist->wishlist_id . '", "courses_wishlist","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	 creation_date = NOW()');
		}
		$result['wishlist'] = $wishlist->toArray();
		$result['wishlist']['image'] = $this->getBaseUrl(true,$wishlist->getPhotoUrl());
		$optionCounter = 0;
		if($wishlist->owner_id == $viewer_id) {
			$result['wishlist']['options'][$optionCounter]['name'] = 'edit';
			$result['wishlist']['options'][$optionCounter]['label'] = $this->view->translate('Edit');
			$optionCounter++;
			$result['wishlist']['options'][$optionCounter]['name'] = 'delete';
			$result['wishlist']['options'][$optionCounter]['label'] = $this->view->translate('Delete');
		}
		$result['wishlist']['owner_title'] = $wishlist->getOwner()->getTitle();
		$params['wishlist_id'] = $wishlist_id;
		$paginator = Engine_Api::_()->getDbtable('courses', 'courses')->getCoursePaginator($params);
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		$result['wishlist']['courses'] = $this->getCourses($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function addtocartAction(){
		if( !$this->getRequest()->isPost() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		$course_id = $this->_getParam('course_id','');
		if(!$course_id)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		$course = Engine_Api::_()->getItem('courses',$course_id);
		if(!$course)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('course not found'), 'result' => array()));
			
    $cartId = Engine_Api::_()->courses()->getCartId();
    $courseTable = Engine_Api::_()->getDbTable('cartcourses','courses');
    //check course already added to cart
    $isAlreadyAdded = Engine_Api::_()->getDbTable('cartcourses','courses')->checkcourseadded(array('course_id' => $course_id,'cart_id'=>$cartId->getIdentity()));
    if(!$isAlreadyAdded) {
        $courseTable->insert(array('cart_id' => $cartId->getIdentity(),'course_id' => $course_id));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("You have successfully added course to cart."),'result' => array()));
    }else {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("You have already added this course in your court."),'result' => array()));
    }
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('This Courses has been successfully added to your cart.'), 'status'=>true)));
	}
	
	// Course edit
	public function editAction(){
		$courseId = $this->_getParam('course_id');
        if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course = Engine_Api::_()->core()->getSubject();
		}else{
			$course= Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$defaultProfileId = Engine_Api::_()->getDbTable('metas', 'courses')->profileFieldId();
		if( !$this->_helper->requireSubject()->isValid() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		if( !$this->_helper->requireAuth()->setAuthParams('courses', $viewer, 'edit')->isValid() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		$form = new Courses_Form_Course_Edit(array('defaultProfileId' => $defaultProfileId,'fromApi'=>true));
    if($form->getElement('formHeading1')){
      $form->removeElement('formHeading1');
    }
    if($form->getElement('formHeading11')){
      $form->removeElement('formHeading11');
    } 
    if($form->getElement('formHeading10')){
      $form->removeElement('formHeading10');
    } 
    if($form->getElement('formHeading12')){
      $form->removeElement('formHeading12');
    } 
    if($form->getElement('formHeading13')){
      $form->removeElement('formHeading13');
    }
    if($form->getElement('formHeading4')){
      $form->removeElement('formHeading4');
    }
    if($form->getElement('formHeading5')){
      $form->removeElement('formHeading5');
    }
    if($form->getElement('formHeading6')){
      $form->removeElement('formHeading6');
    }
    if($form->getElement('formHeading16')){
      $form->removeElement('formHeading16');
    } 
    if($form->getElement('formHeading17')){
      $form->removeElement('formHeading17');
    }
    if($form->getElement('submit_check')){
      $form->removeElement('submit_check');
    }
    if($form->getElement('courses_main_photo_preview')){
      $form->removeElement('courses_main_photo_preview');
    }
    if($form->getElement('photo-uploader')){
      $form->removeElement('photo-uploader');
    }
    if($form->getElement('removeimage')){
      $form->removeElement('removeimage');
    }
    
		// Populate form
		$form->populate($course->toArray());
		$form->populate(array(
			'networks' => explode(",",$course->networks),
			'levels' => explode(",",$course->levels)
		));
// 		if($form->getElement('coursestyle'))
// 		$form->getElement('productstyle')->setValue($course->style);

		$latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('courses',$course->course_id);
		if($latLng){
		  if($form->getElement('lat'))
		  $form->getElement('lat')->setValue($latLng->lat);
		  if($form->getElement('lng'))
		  $form->getElement('lng')->setValue($latLng->lng);
		}
		if($form->getElement('location'))
		$form->getElement('location')->setValue($course->location);
			if($form->getElement('category_id'))
		$form->getElement('category_id')->setValue($course->category_id);

		$tagStr = '';
		foreach($course->tags()->getTagMaps() as $tagMap ) {
		  $tag = $tagMap->getTag();
		  if( !isset($tag->text) ) continue;
		  if( '' !== $tagStr ) $tagStr .= ', ';
		  $tagStr .= $tag->text;
		}
		$form->populate(array(
		  'tags' => $tagStr,
		));
		$auth = Engine_Api::_()->authorization()->context;
		$roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
		foreach( $roles as $role ) {
		  if ($form->auth_view){
        if( $auth->isAllowed($course, $role, 'view') ) {
        $form->auth_view->setValue($role);
        }
      }
      if ($form->auth_comment){
        if( $auth->isAllowed($course, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
		}
		//hide status change if it has been already published
		if( $course->draft == 0 )
		  $form->removeElement('draft');
		$this->view->edit = true;
		$upsells = Engine_Api::_()->getDbTable('upsells','courses')->getSells(array('course_id'=>$course->getIdentity()));
		if(count($upsells)){
		  $content = "";
		  $upsellsArray = array();
		  foreach($upsells as $upsell){
        $resource = Engine_Api::_()->getItem('courses',$upsell->resource_id);
        if(!$resource)
          continue;
			  $upsellsArray[] = $resource->getIdentity();
        $content .='<span id="upsell_remove_'.$resource->getIdentity().'" class="courses_tag tag">'.$resource->getTitle().' <a href="javascript:void(0);" onclick="removeFromToValueUpsell('.$resource->getIdentity().');">x</a></span>';
		  }
		  $form->upsell_id->setValue(implode(',',$upsellsArray));
		  $this->view->upsells = $content;
		}
		$crosssells = Engine_Api::_()->getDbTable('crosssells','courses')->getSells(array('course_id'=>$course->getIdentity()));
		if(count($crosssells)){
		  $content = "";
		  $crosssellsArray = array();
		  foreach($crosssells as $crosssell){
			$resource = Engine_Api::_()->getItem('courses',$crosssell->resource_id);
			if(!$resource)
			  continue;
			$crosssellsArray[] = $resource->getIdentity();
			$content .='<span id="crosssell_remove_'.$resource->getIdentity().'" class="courses_tag tag">'.$resource->getTitle().' <a href="javascript:void(0);" onclick="removeFromToValueCrossSell('.$resource->getIdentity().');">x</a></span>';
		  }
		  $form->crosssell_id->setValue(implode(',',$crosssellsArray));
		  $this->view->crosssells = $content;
		}
		//get all allowed types course
		$viewer = Engine_Api::_()->user()->getViewer();
		$allowed_types = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('courses', $viewer, 'allowed_types');
		if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		//is post
		if (!$form->isValid($this->getRequest()->getPost())) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
		}

    $settings = Engine_Api::_()->getApi('settings', 'core');
    if ($settings->getSetting('courses.mainPhoto.mandatory', 1)) {
      if (empty($_FILES['photo']['size']) && empty($_FILES['image']['size'])) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Main Photo is a required field.'));
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Main Photo is a required field.'), 'result' => array()));
      }
    }
		
    if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
      $custom_url = Engine_Api::_()->getDbtable('courses', 'courses')->checkCustomUrl($_POST['custom_url'],$course->getIdentity());
      if ($custom_url) {
        $form->addError($this->view->translate("Custom URL is not available. Please select another URL."));
      }
    }
    //discount check
    if(!empty($_POST['discount'])){
      if(empty($_POST['price'])){
          $form->addError($this->view->translate('Price is required.'));
          $priceError = true;
      }
      if(!empty($_POST['discount_end_type']) && empty($_POST['discount_end_date'])){
        $form->addError($this->view->translate('Discount End Date is required.'));
      }
      if(empty($priceError) && empty($_POST['discount_type'])){
        if(empty($_POST['percentage_discount_value'])){
          $form->addError($this->view->translate('Discount Value is required.'));
        }else if($_POST['percentage_discount_value'] > 100){
            $form->addError($this->view->translate('Discount Value must be less than or equal to 100.'));
        }
      }else if(empty($priceError)){
        if(empty($_POST['fixed_discount_value'])){
          $form->addError($this->view->translate('Discount Value is required.'));
        }else if($_POST['fixed_discount_value'] > $_POST['price']){
            $form->addError($this->view->translate('Discount Value must be less than or equal to Price.'));
          }
      }
      //check discount dates
      if(!empty($_POST['discount_start_date'])){
          $time = $_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00");
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($this->view->viewer()->timezone);
          $start = strtotime($time);
          $preciousstart = strtotime($course->discount_start_date);
          date_default_timezone_set($oldTz);
          if($start < time() && $preciousstart != $start){
              $timeDiscountError = true;
              $form->addError($this->view->translate('Discount Start Date field value must be greater than Current Time.'));
          }
        }
        if(!empty($_POST['discount_end_date'])){
          $time = $_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00");
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($this->view->viewer()->timezone);
          $start = strtotime($time);
          $preciousend = strtotime($course->discount_end_date);
          date_default_timezone_set($oldTz);
          if($start < time() && $preciousend != $start){
              $timeDiscountError = true;
              $form->addError($this->view->translate('Discount End Date field value must be greater than Current Time.'));
          }
        }
        if(empty($timeDiscountError)){
          if(!empty($_POST['discount_start_date'])){
              if(!empty($_POST['discount_end_date'])){
                $starttime = $_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00");
                $endtime = $_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00");
                $oldTz = date_default_timezone_get();
                date_default_timezone_set($this->view->viewer()->timezone);
                $start = strtotime($starttime);
                $end = strtotime($endtime);
                date_default_timezone_set($oldTz);
                if($start > $end){
                    $form->addError($this->view->translate('Discount Start Date value must be less than Discount End Date field value.'));
                }
              }
          }
        }
    }
    
    $arrMessages = $form->getMessages();
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $error = '';
    foreach($arrMessages as $field => $arrErrors) {
      if($field && intval($field) <= 0){
        $error .= sprintf(
          '<li>%s%s</li>',
          $form->getElement($field)->getLabel(),
          $view->formErrors($arrErrors)

        );
      }else{
        $error .= sprintf(
          '<li>%s</li>',
          $arrErrors
        );
      }
    }
    if($error)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$error, 'result' => array()));		
		// Process
		$db = Engine_Db_Table::getDefaultAdapter();
		$db->beginTransaction();
		try
		{
      $values = $form->getValues();
      $course->setFromArray($values);
      $course->modified_date = date('Y-m-d H:i:s');
      if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
          $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
          $course->publish_date =$starttime;
      }
      if(isset($values['levels']))
          $values['levels'] = implode(',',$values['levels']);
      if(isset($values['networks']))
          $values['networks'] = implode(',',$values['networks']);
      if(isset($values['levels']))
          $course->levels = $values['levels'];

      if(isset($values['networks']))
          $course->networks = implode(',',$values['networks']);
      $values['ip_address'] = $_SERVER['REMOTE_ADDR'];
      $course->save();
      unset($_POST['title']);
      unset($_POST['tags']);
      unset($_POST['category_id']);
      unset($_POST['subcat_id']);
      unset($_POST['MAX_FILE_SIZE']);
      unset($_POST['body']);
      unset($_POST['search']);
      unset($_POST['execute']);
      unset($_POST['token']);
      unset($_POST['submit']);
      $values['fields'] = $_POST;
      $values['fields']['0_0_1'] = '2';
      //discount
      if(!empty($_POST['discount_start_date'])){
        if(isset($_POST['discount_start_date']) && $_POST['discount_start_date'] != ''){
          $starttime = isset($_POST['discount_start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_start_date'].' '.$_POST['discount_start_date_time'])) : '';
          $course->discount_start_date =$starttime;
        }
        if(isset($_POST['discount_start_date']) && $viewer->timezone && $_POST['discount_start_date'] != ''){
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($viewer->timezone);
          $start = strtotime($_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00"));
          $course->discount_start_date = date('Y-m-d H:i:s', $start);
          date_default_timezone_set($oldTz);
        }
      }
      if(!empty($_POST['discount_end_date'])){
        if(isset($_POST['discount_end_date']) && $_POST['discount_end_date'] != ''){
          $starttime = isset($_POST['discount_end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_end_date'].' '.$_POST['discount_end_date_time'])) : '';
          $course->discount_end_date =$starttime;
        }
        if(isset($_POST['discount_end_date']) && $viewer->timezone && $_POST['discount_end_date'] != ''){
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($viewer->timezone);
          $start = strtotime($_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00"));
          $course->discount_end_date = date('Y-m-d H:i:s', $start);
          date_default_timezone_set($oldTz);
        }
      }
      if(isset($values['draft']) && !$values['draft']) {
        $currentDate = date('Y-m-d H:i:s');
        if($course->publish_date < $currentDate) {
          $course->publish_date = $currentDate;
          $course->save();
        }
      }
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
        $customfieldform->setItem($course);
        $customfieldform->saveValues($values['fields']);
      }
      // Auth
      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);
      $lectureCreate = array_search(isset($values['auth_ltr_create']) ? $values['auth_ltr_create']: '', $roles);
      $tstCreate = array_search(isset($values['auth_tst_create']) ? $values['auth_tst_create']: '', $roles);
      foreach( $roles as $i => $role ) {
          $auth->setAllowed($course, $role, 'view', ($i <= $viewMax));
          $auth->setAllowed($course, $role, 'comment', ($i <= $commentMax));
          $auth->setAllowed($course, $role, 'ltr_create', ($i <= $lectureCreate));
          $auth->setAllowed($course, $role, 'tst_create', ($i <= $tstCreate));
      }
      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $course->tags()->setTagMaps($viewer, $tags);
      if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
          $course->custom_url = $_POST['custom_url'];
      else
          $course->custom_url = $course->course_id;
      $course->save();
      $db->commit();
      $upsellcrosssell = Engine_Db_Table::getDefaultAdapter();
      $upsellcrosssell->query('DELETE FROM `engine4_courses_upsells` WHERE course_id = '.$course->getIdentity());
      $upsellcrosssell->query('DELETE FROM `engine4_courses_crosssells` WHERE course_id = '.$course->getIdentity());
      //upsell
      if(!empty($_POST['upsell_id'])){
          $upsell = trim($_POST['upsell_id'],',');
          $upsells = explode(',',$upsell);
          foreach($upsells as $item){
              $params['course_id'] = $course->getIdentity();
              $params['resource_id'] = $item;
              $params['creation_date'] = date('Y-m-d H:i:s');
              Engine_Api::_()->getDbTable('upsells','courses')->create($params);
          }
      }
      //crosssell
      if(!empty($_POST['crosssell_id'])){
          $crosssell = trim($_POST['crosssell_id'],',');
          $crosssells = explode(',',$crosssell);
          foreach($crosssells as $item){
              $params['course_id'] = $course->getIdentity();
              $params['resource_id'] = $item;
              $params['creation_date'] = date('Y-m-d H:i:s');
              Engine_Api::_()->getDbTable('crosssells','courses')->create($params);
          }
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('course_id' => $course->getIdentity(), 'success_message' => $this->view->translate('Course edited successfully.'))));
		}
		catch( Exception $e )
		{
			$db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
		}	
	}
	public function browseWishlistAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
		$alphabet = isset($_GET['alphabet']) ? $_GET['alphabet'] : (isset($params['alphabet']) ? $params['alphabet'] : '');
		$popularity = isset($_GET['popularity']) ? $_GET['popularity'] : $popularity;
		$title = isset($_GET['title_name']) ? $_GET['title_name'] : (isset($params['title_name']) ? $params['title_name'] : '');
		$show = isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ? $params['show'] : 1);
		$brand = isset($_GET['brand']) ? $_GET['brand'] : (isset($params['brand']) ? $params['brand'] : '');
		$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
		$users = array();
		if (isset($_GET['show']) && $_GET['show'] == 2 && $viewer->getIdentity()) {
		  $users = $viewer->membership()->getMembershipsOfIds();
		}
		$action = isset($_GET['action']) ? $_GET['action'] : (isset($params['action']) ? $params['action'] : 'browse');
		$page = isset($_GET['page']) ? $_GET['page'] : $this->_getParam('page', 1);
		$values = array('alphabet' => $alphabet,'popularity' => $popularity,'category_id'=>$category_id,'brand'=>$brand, 'show' => $show, 'users' => $users, 'title' => $title, 'action' => $action, 'user' => $viewerId);
		$paginator = Engine_Api::_()->getDbTable('wishlists', 'courses')->getWishlistPaginator($values);
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		$result['wishlists'] = $this->getWishlists($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	public function getWishlists($paginator){
		$result = array();
    $counter = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $viewerId = $viewer->getIdentity();
    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    foreach ($paginator as $wishlist) {
			$result[$counter] = $wishlist->toArray();
			if($wishlist->photo_id) {
        $result[$counter]['images'] = $this->getBaseUrl(false,Engine_Api::_()->sesapi()->getPhotoUrls($wishlist->photo_id, '', ""));
			} else {
        $result[$counter]['images'] = array('main' => $this->getBaseUrl(false,Engine_Api::_()->getApi('settings', 'core')->getSetting('courses_wishlist_default_image', Zend_Registry::get('StaticBaseUrl')."application/modules/Courses/externals/images/nophoto_wishlist_thumb_profile.png")));
			}
			$LikeStatus = Engine_Api::_()->courses()->getLikeStatus($wishlist->getIdentity(),$wishlist->getType());
			$favStatus = Engine_Api::_()->getDbtable('favourites', 'courses')->isFavourite(array('resource_id' => $wishlist->getIdentity(), 'resource_type' => $wishlist->getType()));
			if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
				$classroomdata['is_content_like'] = $LikeStatus >0 ? true : false;
				$classroomdata['is_content_fav'] = $favStatus >0 ? true : false;
			}
      if(!empty($viewer_id) && $viewer_id == $wishlist->owner_id) {
          $menuoptions= array();
          $menucounter = 0;
          $menuoptions[$menucounter]['name'] = "edit";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Edit");
          $menucounter++;
          
          $menuoptions[$menucounter]['name'] = "delete";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
          $menucounter++;
          $result[$counter]['menus'] = $menuoptions;
      }
			$result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $wishlist->getPhotoUrl());
			$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$wishlist->getHref());
			$result[$counter]["share"]["title"] = $wishlist->getTitle();
			$result[$counter]["share"]["description"] = strip_tags($wishlist->getDescription());
			$result[$counter]["share"]["setting"] = 1;
			$result[$counter]["share"]['urlParams'] = array(
				"type" => $wishlist->getType(),
				"id" => $wishlist->getIdentity()
			);
			$counter++;
		}
		return $result;
	}
  public function browseAction() {
      $isSearch = $this->_getParam('search', 0);
      $isCategory = $this->_getParam('category_id', 0);
      $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
      $coreContentTableName = $coreContentTable->info('name');
      $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
      $corePagesTableName = $corePagesTable->info('name');
      $select = $corePagesTable->select()
          ->setIntegrityCheck(false)
          ->from($corePagesTable, null)
          ->where($coreContentTableName . '.name=?', 'courses.browse-search')
          ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
          ->where($corePagesTableName . '.name = ?', 'courses_index_browse');
      $id = $select->query()->fetchColumn();
      $form = new Courses_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
      $form->populate($_POST);
      $params = $form->getValues();
      $value = array();
      $value['status'] = 1;
      $value['search'] = 1;
      $value['draft'] = "0";
      if (isset($params['search']))
          $params['text'] = addslashes($params['search']);
      $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
      if(isset($isSearch))
        $value['alphabet'] = $isSearch;
      $params = array_merge($params, $value);
      if ($classroom == 0 && isset($params['search'])) {
        unset($params['price_max']);
      }
      if(!empty($isCategory))
        $params['category_id'] = $isCategory;
      $paginator = Engine_Api::_()->getDbTable('courses', 'courses')->getCoursePaginator($params);
      $paginator->setItemCountPerPage($this->_getParam('limit', 5));
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      $classroom = $this->_getParam('page', 1);
      $result['courses'] = $this->getCourses($paginator);
      $this->_innerCalling = true;
      if (($this->_getParam('page', 1) == 1) && $paginator->getTotalItemCount() > 0){
        if(empty($isSearch) && empty($isCategory)){
          $hotCourses = $this->hotAction();
          if(sizeof($hotCourses))
            $result['hot_courses'] = $hotCourses;
          $featuredCourses = $this->featuredAction();
          if(sizeof($featuredCourses))
            $result['featured_courses'] = $featuredCourses;
          $verifiedCourses = $this->verifiedAction();
          if(sizeof($verifiedCourses))
            $result['verified_courses'] = $verifiedCourses;
        }
      }
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function getPopularCourses($Paginator){
      $params['info'] = 'most_viewed';
      $paginator = Engine_Api::_()->getDbTable('courses', 'courses')->getCoursePaginator($params);
      $paginator->setItemCountPerPage(6);
      $paginator->setCurrentPageNumber(1);
      $result = $this->getCourses($paginator);
      return $result;
    }
    public function featuredAction()
    {
      $params['sort'] = 'featured';
      $paginator = Engine_Api::_()->getDbTable('courses', 'courses')
          ->getCoursePaginator($params);
      $paginator->setItemCountPerPage($this->_getParam('limit', 5));
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      if ($paginator->getCurrentPageNumber() == 1) {
          $categories = Engine_Api::_()->getDbtable('categories', 'courses')->getCategory(array('column_name' => '*', 'limit' => 25));
          $category_counter = 0;
          foreach ($categories as $category) {
              if ($category->thumbnail)
                  $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
              if ($category->cat_icon)
                  $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
              if ($category->colored_icon)
                  $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
              $result_category[$category_counter]['slug'] = $category->slug;
              $result_category[$category_counter]['category_name'] = $category->category_name;
              $result_category[$category_counter]['total_courses_categories'] = $category->total_courses_categories;
              $result_category[$category_counter]['category_id'] = $category->category_id;

              $category_counter++;
          }
          $result['category'] = $result_category;
      }
      if($this->_innerCalling){
        $paginator->setItemCountPerPage(5);
        return $this->getCourses($paginator);
      }
      $result['courses'] = $this->getCourses($paginator);
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function sponsoredAction()
    {
      $params['sort'] = 'sponsored';
      $paginator = Engine_Api::_()->getDbTable('courses', 'courses')
          ->getCoursePaginator($params);
      $paginator->setItemCountPerPage($this->_getParam('limit', 10));
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      if ($paginator->getCurrentPageNumber() == 1) {
        $categories = Engine_Api::_()->getDbtable('categories', 'courses')->getCategory(array('column_name' => '*', 'limit' => 25));
        $category_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_courses_categories'] = $category->total_courses_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
        }
        $result['category'] = $result_category;
      }
      if($this->_innerCalling){
        $paginator->setItemCountPerPage(5);
        return $this->getCourses($paginator);
      }
      $result['courses'] = $this->getCourses($paginator);
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function verifiedAction()
    {
      $params['sort'] = 'verified';
      $paginator = Engine_Api::_()->getDbTable('courses', 'courses')
          ->getCoursePaginator($params);
      $paginator->setItemCountPerPage($this->_getParam('limit', 5));
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      if ($paginator->getCurrentPageNumber() == 1) {
          $categories = Engine_Api::_()->getDbtable('categories', 'courses')->getCategory(array('column_name' => '*', 'limit' => 25));
          $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('courses_main', array());
          $category_counter = 0;
          $menu_counter = 0;
          foreach ($categories as $category) {
              if ($category->thumbnail)
                  $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
              if ($category->cat_icon)
                  $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
              if ($category->colored_icon)
                  $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
              $result_category[$category_counter]['slug'] = $category->slug;
              $result_category[$category_counter]['category_name'] = $category->category_name;
              $result_category[$category_counter]['total_courses_categories'] = $category->total_courses_categories;
              $result_category[$category_counter]['category_id'] = $category->category_id;
              $category_counter++;
          }
          $result['category'] = $result_category;
      }
      if($this->_innerCalling){
        $paginator->setItemCountPerPage(5);
        return $this->getCourses($paginator);
      }
      $result['courses'] = $this->getCourses($paginator);
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function hotAction()
    {
      $params['sort'] = 'hot';
      $paginator = Engine_Api::_()->getDbTable('courses', 'courses')
          ->getCoursePaginator($params);
      $paginator->setItemCountPerPage($this->_getParam('limit', 5));
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      if ($paginator->getCurrentPageNumber() == 1) {
          $categories = Engine_Api::_()->getDbtable('categories', 'courses')->getCategory(array('column_name' => '*', 'limit' => 25));
          $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('courses_main', array());
          $category_counter = 0;
          $menu_counter = 0;
          foreach ($categories as $category) {
              if ($category->thumbnail)
                  $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
              if ($category->cat_icon)
                  $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
              if ($category->colored_icon)
                  $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
              $result_category[$category_counter]['slug'] = $category->slug;
              $result_category[$category_counter]['category_name'] = $category->category_name;
              $result_category[$category_counter]['total_courses_categories'] = $category->total_courses_categories;
              $result_category[$category_counter]['category_id'] = $category->category_id;
              $category_counter++;
          }
          $result['category'] = $result_category;
      }
      if($this->_innerCalling){
        $paginator->setItemCountPerPage(5);
        return $this->getCourses($paginator);
      }
      $result['classrooms'] = $this->getCourses($paginator);
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function manageAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!$viewer_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $params['user_id'] = $viewer_id;
        $defaultOpenTab = $this->_getParam('search_type', 'recentlySPcreated');
        switch ($defaultOpenTab) {
            case 'recentlySPcreated':
                $params['sort'] = 'creation_date';
                break;
            case 'mostSPviewed':
                $params['sort'] = 'view_count';
                break;
            case 'mostSPliked':
                $params['sort'] = 'like_count';
                break;
            case 'mostSPcommented':
                $params['sort'] = 'comment_count';
                break;
            case 'mostSPfavourite':
                $params['sort'] = 'favourite_count';
                break;
            case 'mostSPfollowed':
                $params['sort'] = 'follow_count';
                break;
            case 'featured':
                $params['sort'] = 'featured';
                break;
            case 'sponsored':
                $params['sort'] = 'sponsored';
                break;
            case 'verified':
                $params['sort'] = 'verified';
                break;
            case 'hot':
                $params['sort'] = 'hot';
                break;
            case 'mostSPlecture':
                $params['lecture'] = 'lecture_count';
                break;
        }
        $params['widgetManage'] = true;
        $params['manage-widget'] = true;

        $paginator = Engine_Api::_()->getDbTable('courses', 'courses')->getCoursePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.share', 0);
        $viewerId = $viewer->getIdentity();
        $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'courses', 'courses_can_join') : 0;
        $filterOptionsMenu = array();
        $filterMenucounter = 0;

        $resultmenu[$filterMenucounter]['name'] = 'recentlySPcreated';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Recently Created');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPliked';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Liked');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPcommented';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Commented');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPviewed';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most viewed');
        $filterMenucounter++;
        if ($canFavourite) {
            $resultmenu[$filterMenucounter]['name'] = 'mostSPfavourite';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Favourited');
            $filterMenucounter++;
        }
        if ($canJoin) {
            $resultmenu[$filterMenucounter]['name'] = 'mostSPjoined';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Joined');
            $filterMenucounter++;
        }
        $resultmenu[$filterMenucounter]['name'] = 'featured';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Featured');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'sponsored';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Sponsored');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'verified';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Verified');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'hot';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Hot');
        $data['courses'] = $this->getCourses($paginator);
        if ($this->_getParam('page', 1))
          $data['filterMenuOptions'] = $resultmenu;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
    }
    
    public function browsesearchAction(){
      $defaultProfileId = 1;
      $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
      $coreContentTableName = $coreContentTable->info('name');
      $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
      $corePagesTableName = $corePagesTable->info('name');
      $select = $corePagesTable->select()
          ->setIntegrityCheck(false)
          ->from($corePagesTable, null)
          ->where($coreContentTableName . '.name=?', 'courses.browse-search')
          ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', array('content_id', 'params'))
          ->where($corePagesTableName . '.name = ?', 'courses_index_browse'); 
      $results = $corePagesTable->fetchRow($select);
      $param = json_decode($results->params, true);
      $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => $this->view->translate('Recently Created'),'mostSPviewed' => $this->view->translate('Most Viewed'),'mostSPliked' => $this->view->translate('Most Liked'), 'mostSPcommented' => $this->view->translate('Most Commented'),'mostSPfavourite' => $this->view->translate('Most Favourite'),'featured' => $this->view->translate('Featured'),'sponsored' => $this->view->translate('Sponsored'),'verified' => $this->view->translate('Verified'),'mostSPrated'=>$this->view->translate('Most Rated')));
      if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.enable.favourite', 1))
        unset($filterOptions['mostSPfavourite']);
      $searchForm = new Courses_Form_Search(array('searchTitle' => $this->_getParam('search_title', 'yes'),'browseBy' => $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $this->_getParam('categories', 'yes'),'searchFor'=>$search_for,'FriendsSearch'=>$this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type,'price' => $this->_getParam('price', 'yes'),'discount' => $this->_getParam('discount', 'yes'),'hasPhoto' => $this->_getParam('has_photo', 'yes'),'lecture'=>$this->_getParam('lecture','yes')));
      if($this->_getParam('search_type','course') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
        $arrayOptions = $filterOptions;
        $filterOptions = array();
        foreach ($arrayOptions as $key=>$filterOption) {
          if(is_numeric($key))
          $columnValue = $filterOption;
          else
          $columnValue = $key;
          $value = str_replace(array('SP',''), array(' ',' '), $columnValue);
          $filterOptions[$columnValue] = ucwords($value);
        }
        $filterOptions = array(''=>'')+$filterOptions;
        $searchForm->sort->setMultiOptions($filterOptions);
        $searchForm->sort->setValue($default_search_type);
      }
      $request = Zend_Controller_Front::getInstance()->getRequest();
      $searchForm->setMethod('get')->populate($request->getParams());
      $searchForm->removeElement('cancel');
      if ($this->_getParam('getForm')) {
          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
          $this->generateFormFields($formFields, array('resources_type' => 'courses'));
      } else {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      }
    }
    public function profileCoursesAction() {
      $classroomId = $this->_getParam('classroom_id',null);
      if(!$classroomId)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      if (Engine_Api::_()->core()->hasSubject()) {
        $classroom = Engine_Api::_()->core()->getSubject();
      } else {
        $classroom = Engine_Api::_()->getItem('classroom',$classroomId);
      }
      if(!$classroom) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      }
      $value['classroom_id'] =  $classroomId;
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();
      $value['popularCol'] = isset($popularCol) ? $popularCol : '';
      $value['fixedData'] = isset($fixedData) ? $fixedData : '';
      $value['draft'] = 0;
      $value['search'] = 1;
      $options = array('tabbed' => true, 'paggindData' => true);
      $this->view->optionsListGrid = $options;
      $this->view->widgetName = 'profile-courses';
      //$params = array_merge($params, $value);
      // Get Course
      $paginator = Engine_Api::_()->getDbtable('courses', 'courses')->getCoursePaginator($value);
      $result['courses'] = $this->getCourses($paginator);
      // Set item count per page and current page number
      $limit_data = $this->view->{'limit_data_'.$view_type};
      $paginator->setItemCountPerPage(10);
      $page = isset($_POST['page']) ? $_POST['page'] : 1;
      $this->view->page = $page;
      $paginator->setCurrentPageNumber($page);
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }
    
    public function getCourses($paginator) {
      $result = array();
      $counter = 0;
      $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.favourite', 0);
      $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.enable.sharing', 1);
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewerId = $viewer->getIdentity();
      $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      foreach ($paginator as $course) {
        $classroomItem = Engine_Api::_()->getItem('classroom', $course->classroom_id);
        $result[$counter] = $course->toArray();
        $result[$counter]['owner_title'] = $course->getOwner()->getTitle();
        $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
        $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
        $result[$counter]['currency'] = $curArr[$currency];
        if ($course->category_id) {
            $category = Engine_Api::_()->getItem('courses_category', $course->category_id);
            if ($category) {
                $result[$counter]['category_title'] = $category->category_name;
                if ($course->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('courses_category', $course->subcat_id);
                    if ($subcat) {
                        $result[$counter]['subcategory_title'] = $subcat->category_name;
                        if ($course->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('courses_category', $course->subsubcat_id);
                            if ($subsubcat) {
                                $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }
        $tags = array();
        foreach ($course->tags()->getTagMaps() as $tagmap) {
            $arrayTag = $tagmap->toArray();
            if(!$tagmap->getTag())
                continue;
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        if (count($tags)) {
            $result[$counter]['tag'] = $tags;
        }
        $result[$counter]['images']['main']= $this->getBaseUrl(true, $course->getPhotoUrl());
        //$result[$counter]['cover_image']['main'] = $this->getBaseUrl(true, $course->getCoverPhotoUrl());
        $result[$counter]['cover_images']['main'] = $result[$counter]['cover_image']['main'];
        $i = 0;
        if ($course->is_approved) {
            if ($shareType) {
                $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $course->getPhotoUrl());
                $result[$counter]["share"]["url"] = $this->getBaseUrl(false,$course->getHref());
                $result[$counter]["share"]["title"] = $course->getTitle();
                $result[$counter]["share"]["description"] = strip_tags($course->getDescription());
                $result[$counter]["share"]["setting"] = $shareType;
                $result[$counter]["share"]['urlParams'] = array(
                    "type" => $course->getType(),
                    "id" => $course->getIdentity()
                );
            }
        }
        //Rating Count
        $reviews = Engine_Api::_()->getDbTable('reviews','courses');
        $result[$counter]['ratings'] = round($reviews->getRating($course->getIdentity()),1);
        $totalReviewCount = (int)$reviews->getReviewCount(array('course_id'=>$course->getIdentity()))[0];
        $result[$counter]['review_count'] = '('.(int) $totalReviewCount.')';
        if(!empty($classroomItem))
          $result[$counter]['classroom_title'] = $classroomItem->title;
        if ($course->is_approved) {
          if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.wishlists', 1) && Engine_Api::_()->courses()->allowAddWishlist()) {
            $result[$counter]['is_content_wishlist'] = true;
          }
          $priceDiscount = Engine_Api::_()->courses()->courseDiscountPrice($course);
          $result[$counter]['course_price'] = $priceDiscount['discountPrice'] > 0 ? Engine_Api::_()->courses()->getCurrencyPrice($priceDiscount['discountPrice']) : $this->view->translate('FREE');
          if($priceDiscount['discountPrice'] > 0){
            if($priceDiscount['discount']){
              $result[$counter]["price_with_discount"] = Engine_Api::_()->courses()->getCurrencyPrice($course->price);
              $result[$counter]['discount_price'] = Engine_Api::_()->courses()->getCurrencyPrice($priceDiscount['discount']);
            }
          }
          
          if ($viewerId != 0) {
              $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($course);
              $result[$counter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($course);
              if ($canFavourite) {
                  $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($course, 'favourites', 'courses', 'courses', 'owner_id');
                  $result[$counter]['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($course, 'favourites', 'courses', 'courses', 'owner_id');
              }
          }
          $counter++;
        }
      }
      $results['courses'] = $result;
      return $result;
  }
	public function getCoursesCategory($categoryPaginator){
    $result = array();
    $counter = 0;
    foreach ($categoryPaginator as $categories) {
        $course = $categories->toArray();
        $params['category_id'] = $categories->category_id;
        $params['limit'] = 5;
        $paginator = Engine_Api::_()->getDbTable('courses', 'courses')->getCoursePaginator($params);
        $paginator->setItemCountPerPage(3);
        $paginator->setCurrentPageNumber(1);
        if($paginator->getTotalItemCount() > 0){
          $result[$counter] = $course;
          $result[$counter]['items'] = $this->getCourses($paginator);
          if ($paginator->getTotalItemCount() > 3) {
            $result[$counter]['see_all'] = true;
          } else {
            $result[$counter]['see_all'] = false;
          }
          $counter++;
        }
    }
    $results = $result;
    return $results;
  }
  public function categoriesAction(){
		$paginator = Engine_Api::_()->getDbTable('categories', 'courses')->getCoursePaginator();
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
      if (count($paginator) > 0) {
        $categories = Engine_Api::_()->getDbtable('categories', 'courses')->getCategory(array('column_name' => '*'));
        $category_counter = 0;
        $menu_counter = 0;
        foreach ($categories as $category) {
            if ($category->thumbnail)
                $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
            if ($category->cat_icon)
                $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
            if ($category->colored_icon)
                $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
            $result_category[$category_counter]['slug'] = $category->slug;
            $result_category[$category_counter]['category_name'] = $category->category_name;
            $result_category[$category_counter]['total_courses_categories'] = $category->total_courses_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
        }
        $result['category'] = $result_category;
    }
    //$result['categories'] = $this->getCoursesCategory($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function categoryAction(){
    $params['countBlogs'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'courses')->getCategory($params);
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $category){
      $catgeoryArray["category"][$counter]["category_id"] = $category->getIdentity();
      $catgeoryArray["category"][$counter]["label"] = $category->category_name;
      if($category->thumbnail != '' && !is_null($category->thumbnail) && intval($category->thumbnail)):
        $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->thumbnail)->getPhotoUrl(''));
      endif;
      if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)):
        $catgeoryArray["category"][$counter]["cat_icon"] = $this->getBaseUrl(false,Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon'));
      endif;
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s course', '%s courses', $category->total_courses_categories), $this->view->locale()->toNumber($category->total_courses_categories));
      $counter++;
    }
    if($catgeoryArray <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }
	function likeAction() {
		if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		$type = 'courses';
		$dbTable = 'courses';
		$resorces_id = 'course_id';
		$notificationType = 'liked';
		$actionType = 'courses_course_like';
		if($this->_getParam('type',false) && $this->_getParam('type') == 'courses_album'){
				$type = 'courses_album';
			$dbTable = 'albums';
			$resorces_id = 'album_id';
			$actionType = 'courses_album_like';
			} else if($this->_getParam('type',false) && $this->_getParam('type') == 'courses_photo') {
				$type = 'courses_photo';
			$dbTable = 'photos';
			$resorces_id = 'photo_id';
			$actionType = 'courses_photo_like';
			}else if($this->_getParam('type',false) && $this->_getParam('type') == 'courses_wishlist'){
		  $type = 'courses_wishlist';
			$dbTable = 'wishlists';
			$resorces_id = 'wishlist_id';
			$actionType = 'liked';
		}
		$item_id = $this->_getParam('id',$this->_getParam('course_id'));
		if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$itemTable = Engine_Api::_()->getDbtable($dbTable, 'courses');
		$tableLike = Engine_Api::_()->getDbtable('likes', 'core');
		$tableMainLike = $tableLike->info('name');
		$select = $tableLike->select()
				->from($tableMainLike)
				->where('resource_type = ?', $type)
				->where('poster_id = ?', $viewer_id)
				->where('poster_type = ?', 'user')
				->where('resource_id = ?', $item_id);
		$result = $tableLike->fetchRow($select);
		if (count($result) > 0) {
		  //delete
		  $db = $result->getTable()->getAdapter();
		  $db->beginTransaction();
		  try {
          $result->delete();
          $db->commit();
          $temp['data']['message'] = $this->view->translate('Courses Successfully Unliked.');
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
		} else {
		  //update
		  $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
		  $db->beginTransaction();
			try {
				$like = $tableLike->createRow();
				$like->poster_id = $viewer_id;
				$like->resource_type = $type;
				$like->resource_id = $item_id;
				$like->poster_type = 'user';
				$like->save();
				$itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array($resorces_id . '= ?' => $item_id));
				//Commit
				$db->commit();
				$temp['data']['message'] = $this->view->translate('Courses Successfully Liked.');
			} catch (Exception $e) {
				$db->rollBack();
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
			}
		  //Send notification and activity feed work.
		  $item = Engine_Api::_()->getItem($type, $item_id);
		  $subject = $item;
		  $owner = $subject->getOwner();
			 if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
			   $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
			   Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
			   Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
			   $result = $activityTable->fetchRow(array('type =?' => $actionType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        if (!$result) {
          if($subject && empty($subject->title) && $this->_getParam('type') == 'courses_photo') {
          $album_id = $subject->album_id;
          $subject = Engine_Api::_()->getItem('courses_album', $album_id);
          }
          $action = $activityTable->addActivity($viewer, $subject, $actionType);
          if ($action)
            $activityTable->attachActivity($action, $subject);
			   }
			 }
		}
		$temp['data']['like_count'] = $item->like_count;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
	}
	function favouriteAction(){
		if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		if ($this->_getParam('type') == 'courses') {
		  $type = 'courses';
		  $dbTable = 'courses';
		  $resorces_id = 'course_id';
		  $notificationType = 'courses_course_favourite';
		} else if ($this->_getParam('type') == 'courses_photo') {
		  $type = 'courses_photo';
		  $dbTable = 'photos';
		  $resorces_id = 'photo_id';
		  $notificationType = 'coursesalbum_photo';
		}elseif ($this->_getParam('type') == 'courses_wishlist') {
		  $type = 'courses_wishlist';
		  $dbTable = 'wishlists';
		  $resorces_id = 'wishlist_id';
		  $notificationType = 'courses_wishlist_favourite';
		}
		 else if ($this->_getParam('type') == 'courses_album') {
		  $type = 'courses_album';
		  $dbTable = 'albums';
		  $resorces_id = 'album_id';
		  $notificationType = 'coursesalbum_album';
		}
		$item_id = $this->_getParam('id',$this->_getParam('course_id'));
		if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$Fav = Engine_Api::_()->getDbTable('favourites', 'courses')->getItemfav($type, $item_id);

		$favItem = Engine_Api::_()->getDbtable($dbTable, 'courses');
		if (count($Fav) > 0) {
		  //delete
		  $db = $Fav->getTable()->getAdapter();
		  $db->beginTransaction();
		  try {
			$Fav->delete();
			$db->commit();
			 $temp['data']['message'] = 'Courses Successfully Unfavourited.';
		  } catch (Exception $e) {
			$db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
		  }
		  $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
		  $item = Engine_Api::_()->getItem($type, $item_id);
		  if(@$notificationType) {
			  Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
			  Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
			  Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
		  }
			$temp['data']['favourite_count'] = $item->favourite_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
		} else {
		  //update
		  $db = Engine_Api::_()->getDbTable('favourites', 'courses')->getAdapter();
		  $db->beginTransaction();
		  try {
			$fav = Engine_Api::_()->getDbTable('favourites', 'courses')->createRow();
			$fav->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
			$fav->resource_type = $type;
			$fav->resource_id = $item_id;
			$fav->save();
			$favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
					), array(
				$resorces_id . '= ?' => $item_id,
			));
			// Commit
			$db->commit();
			$temp['data']['message'] = 'Courses Successfully Favourited.';
		  } catch (Exception $e) {
			$db->rollBack();
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
		  }
		  //send notification and activity feed work.
		  $item = Engine_Api::_()->getItem(@$type, @$item_id);
		  if(@$notificationType) {
			  $subject = $item;
			  $owner = $subject->getOwner();
			  if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
				$activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
				Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
				Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
				$result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
				if (!$result) {
				  $action = $activityTable->addActivity($viewer, $subject, $notificationType);
				  if ($action)
					$activityTable->attachActivity($action, $subject);
				}
			  }
		  }
		}
		$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
	}
  public function viewAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		$id = $this->_getParam('course_id', null);
		$course_id = Engine_Api::_()->getDbtable('courses', 'courses')->getCourseId($id);
		if(!Engine_Api::_()->core()->hasSubject())
		  $course = Engine_Api::_()->getItem('courses', $course_id);
		else
		  $course = Engine_Api::_()->core()->getSubject();
		if( !$this->_helper->requireSubject()->isValid() )
		   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array())));

		if( !$this->_helper->requireAuth()->setAuthParams($course, $viewer, 'view')->isValid() )
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array())));
		  
		if(!$course->getIdentity() || (!$course->draft && !$course->isOwner($viewer)) )
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		//Privacy: networks and member level based
		if (Engine_Api::_()->authorization()->isAllowed('courses', $course->getOwner(), 'allow_levels') || Engine_Api::_()->authorization()->isAllowed('courses', $course->getOwner(), 'allow_networks')) {
			$returnValue = Engine_Api::_()->courses()->checkPrivacySetting($course->getIdentity());
			if ($returnValue == false) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			}
		}
		$result = array();
    $tabcounter = 0;
    $result['menus'][$tabcounter]['name'] = 'info';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Info');
    $tabcounter++;
    $result['menus'][$tabcounter]['name'] = 'comment';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Comments');
    $tabcounter++;
    $result['menus'][$tabcounter]['name'] = 'upsell';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Upsell Courses');
    $tabcounter++;
    $result['menus'][$tabcounter]['name'] = 'lecture';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Lecture');
    $tabcounter++;
    $result['menus'][$tabcounter]['name'] = 'test';
    $result['menus'][$tabcounter]['label'] = $this->view->translate('Test');
    $tabcounter++;
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses_enable_location', 1)){
        $result['menus'][$tabcounter]['name'] = 'map';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Locations');
        $tabcounter++;
    }
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.review', 1)){
      $result['menus'][$tabcounter]['name'] = 'review';
      $result['menus'][$tabcounter]['label'] = $this->view->translate('Reviews');
      $tabcounter++;
    }		
		$result['course'] = $this->getCourse($course);
    $counterOption = 0;
    if($course->isOwner($viewer) && ($course->authorization()->isAllowed($viewer, 'edit'))){
      $action = 'dashboard';
      $custumurl = $this->view->url(array('course_id' => $course->custom_url,'action'=>'edit'), 'courses_dashboard', true);
      $value = $this->getBaseUrl(true , $custumurl);
      $result['options'][$counterOption]['label'] = ucwords($action);
      $result['options'][$counterOption]['name'] = $action;
      $result['options'][$counterOption]['value'] = $value;
      $counterOption++;
    }
    if($course->authorization()->isAllowed($viewer, 'edit')) {
      $result['options'][$counterOption]['name'] = 'edit'; 
      $result['options'][$counterOption]['label'] = $this->view->translate('Edit Course'); 
      $counterOption++;
    }
    if($course->authorization()->isAllowed($viewer, 'delete')) {
      $result['options'][$counterOption]['name'] = 'delete'; 
      $result['options'][$counterOption]['label'] = $this->view->translate('Delete Course'); 
      $counterOption++;
    }
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.show.report', 1) && !$course->isOwner($viewer)){
      $result['options'][$counterOption]['name'] = 'report'; 
      $result['options'][$counterOption]['label'] = $this->view->translate('Report'); 
      $counterOption++;
    }
    //For Similar Courses 
    if($this->_getParam('related_courses', false)) {
      $result['related_courses'] = $this->relatedCourses($paginator);
		}
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
  }
  public function getCourse($course){
		$coursedata = array();
    $coursedata = $course->toArray();
    $owner = Engine_Api::_()->getItem('user', $course->owner_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$classroom = Engine_Api::_()->getItem('classroom',$course->classroom_id);
		if(!empty($classroom)) {
      $coursedata['classroom_logo'] = $this->getBaseUrl(true,$classroom->getPhotoUrl('thumb.icon'));
      $coursedata['classroom_title'] = $classroom->title;
		}
		$currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
    $coursedata['owner_image'] = $this->getBaseUrl(true, (!empty($owner->photo_id)) ? $owner->getPhotoUrl('thumb.icon') : '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
    $coursedata['currency'] = $curArr[$currency];
		$coursedata['creation_date'] = $course->publish_date ? $course->publish_date : $course->creation_date;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.enable.location', 1)){
      $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('courses', $course->getIdentity());
      if ($location) {
          $coursedata['location'] = $location->toArray();
          if (Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.enable.map.integration', 1)) {
              $coursedata['location']['showMap'] = true;
          } else {
              $coursedata['location']['showMap'] = false;
          }
      }
		}
		$rating = Engine_Api::_()->getDbTable('reviews','courses')->getRating($course->getIdentity());
		$coursedata['rating'] =$this->view->locale()->toNumber(round($rating,1));
		$totalReviewCount = (int)Engine_Api::_()->getDbTable('reviews','courses')->getReviewCount(array('course_id'=>$course->getIdentity()))[0];
		$coursedata['review_count'] = $this->view->locale()->toNumber(round($totalReviewCount,1));
		$optionCounter = 0;
		if($viewer_id){
			$coursedata['options'][$optionCounter]['name'] = 'createreview';
			$coursedata['options'][$optionCounter]['label'] = $this->view->translate('Write a Review');
			$optionCounter++;
		}
		if($course->discount) {
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.start.date', 1) && isset($course->discount_start_date)) { 
				$coursedata['discount_start_date'] =  date('M d, Y',strtotime($course->discount_start_date));
			}
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.end.date', 1)  && isset($course->discount_end_date)) { 
				$coursedata['discount_end_date'] =  date('M d, Y',strtotime($course->discount_end_date));
			}
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.enable.sharing', 1)){
			$coursedata["share"]["imageUrl"] = $this->getBaseUrl(false, $course->getPhotoUrl());
			$coursedata["share"]["url"] = $this->getBaseUrl(false,$course->getHref());
			$coursedata["share"]["title"] = $course->getTitle();
			$coursedata["share"]["description"] = strip_tags($course->getDescription());
			$coursedata["share"]["setting"] = $shareType;
			$coursedata["share"]['urlParams'] = array(
				"type" => $course->getType(),
				"id" => $course->getIdentity()
			);
		}
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.like', 1)){
      $coursedata['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($course);
      $coursedata['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($course);
    }
		$favStatus = Engine_Api::_()->getDbtable('favourites', 'courses')->isFavourite(array('resource_type'=>'courses','resource_id'=>$course->course_id));
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.favourite', 1)){
			$coursedata['is_content_favourite'] = $favStatus ? true : false;
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.allow.wishlists', 1) && Engine_Api::_()->courses()->allowAddWishlist()){
			$coursedata['options'][$optionCounter]['name'] = 'addtowishlist';
			$coursedata['options'][$optionCounter]['label'] = $this->view->translate('Add to Wishlist');
			$optionCounter++;
		}
    $priceDiscount = Engine_Api::_()->courses()->courseDiscountPrice($course);
    $coursedata['course_price'] = $priceDiscount['discountPrice'] > 0 ? Engine_Api::_()->courses()->getCurrencyPrice($priceDiscount['discountPrice']) : $this->view->translate('FREE');
		$paymentGateways = Engine_Api::_()->courses()->checkPaymentGatewayEnable();
		$paymentMethods = $paymentGateways['methods'];
		$paymentMethodsCounter = 0;
		if(in_array('paypal',$paymentMethods)){
			$coursedata["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Paypal');
			$coursedata["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/paypal.png');
			$paymentMethodsCounter++;
			
		}
		if(in_array(0,$paymentMethods)){
			$coursedata["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cash on Delivery');
			$coursedata["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cash.png');
			$paymentMethodsCounter++;
		}
		if(in_array(1,$paymentMethods)){
			$coursedata["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cheque');
			$coursedata["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cheque.png');
			$paymentMethodsCounter++;
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.purchasenote', 1) && $course->purchase_note) {
			$coursedata["purchase_note"] = $course->purchase_note;
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('courses.enablecomparision',1)) {
			$existsCompare = Engine_Api::_()->courses()->checkAddToCompare($course);
			$compareData = Engine_Api::_()->courses()->compareData($course);
			$coursedata["can_compre"] = 1;
		}else{
			$coursedata["can_compre"] = 0;
		}
    // Get category
    if($course->category_id != '' && intval($course->category_id) && !is_null($course->category_id)) {
          $category = Engine_Api::_()->getItem('courses_category', $course->category_id);
          if ($category) {
              $coursedata['category_title'] = $category->category_name;
              if ($course->subcat_id) {
                  $subcat = Engine_Api::_()->getItem('courses_category', $course->subcat_id);
                  if ($subcat) {
                      $coursedata['subcategory_title'] = $subcat->category_name;
                      if ($course->subsubcat_id) {
                          $subsubcat = Engine_Api::_()->getItem('courses_category', $course->subsubcat_id);
                          if ($subsubcat) {
                              $coursedata['subsubcategory_title'] = $subsubcat->category_name;
                          }
                      }
                  }
              }
          }
    }
    foreach ($course->tags()->getTagMaps() as $tagmap) {
        $arrayTag = $tagmap->toArray();
        if(!$tagmap->getTag())
            continue;
        $tags[] = array_merge($tagmap->toArray(), array(
            'id' => $tagmap->getIdentity(),
            'text' => $tagmap->getTitle(),
            'href' => $tagmap->getHref(),
            'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
        ));
    }
    if (count($tags)) {
        $coursedata['tag'] = $tags;
    }
		$edit_product = Engine_Api::_()->getDbtable('dashboards', 'courses')->getDashboardsItems(array('type' => 'edit_product'));
		$edit_photo = Engine_Api::_()->getDbtable('dashboards', 'courses')->getDashboardsItems(array('type' => 'edit_photo'));
		if(!empty($edit_product) && $edit_product->enabled){
			$coursedata['options'][$optionCounter]['name'] = 'edit';
			$coursedata['options'][$optionCounter]['label'] = $this->view->translate($edit_product->title);
		}
		if(!empty($edit_photos) && $edit_photos->enabled){
			$coursedata['options'][$optionCounter]['name'] = 'uploadphoto';
			$coursedata['options'][$optionCounter]['label'] = $this->view->translate($edit_photo->title);
			$optionCounter++;
		}
    return $coursedata;
  }
  function getType($type){
    switch ($type) {
        case "booknow":
            return 'Book Now';
        case "callnow":
            return "Call Now";
        case "contactus":
            return "Contact Us";
        case "sendmessage":
            return "Send Message";
        case "signup":
            return "Sign Up";
        case "sendemail":
            return "Send Email";
        case "watchvideo":
            return "Watch Video";
        case "learnmore":
            return "Learn More";
        case "shopnow":
            return "Shop Now";
        case "seeoffers":
            return "See Offers";
        case "useapp":
            return "Use App";
        case "playgames":
            return "Play Games";
    }
    return "";
  }
  public function relatedCourses($course){
    $value['category_id'] = $course->category_id;
    $value['widgetName'] = 'Similar Courses';
    $paginator = Engine_Api::_()->getDbTable('courses', 'courses')->getCoursePaginator($value);
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = $this->getCourses($paginator);
        return $result;
  }
  public function photo($courseid){
      $params['course_id'] = $courseid;
      $paginator = Engine_Api::_()->getDbTable('photos', 'courses')
          ->getPhotoPaginator($params);
      $paginator->setItemCountPerPage(5);
      $paginator->setCurrentPageNumber(1);
      $i = 0;
      foreach ($paginator as $photos) {
          $images = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");
          if (!count($images)) {
              $images['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Courses/externals/images/nophoto_group_thumb_profile.png';
              $images['normal'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Courses/externals/images/nophoto_group_thumb_profile.png';
          }
          $result[$i]['images'] = $images;
          $result[$i]['photo_id'] = $photos->getIdentity();
          $result[$i]['album_id'] = $photos->album_id;
          $i++;
      }
      return $result;
  }
  public function infoAction(){
      $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('course_id', null);
      if (!$id) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      }
      if (!Engine_Api::_()->core()->hasSubject()) {
          $course = Engine_Api::_()->getItem('courses', $id);
      } else {
          $course = Engine_Api::_()->core()->getSubject();
      }
      $result['information'] = $this->getInformation($course);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
  }
  public function getInformation($course){
    $result = $course->toArray();
    $color = "";
    $data = "";
    $hours = "";
    $classroom = Engine_Api::_()->getItem('classroom',$course->classroom_id);

    if($course->category_id != '' && intval($course->category_id) && !is_null($course->category_id)) {
      $category = Engine_Api::_()->getItem('courses_category', $course->category_id);
      if ($category) {
        $result['category_title'] = $category->category_name;
        if ($category->subcat_id) {
          $subcat = Engine_Api::_()->getItem('courses_category', $category->subcat_id);
          if ($subcat) {
            $result['subcategory_title'] = $subcat->category_name;
            if ($subcat->subsubcat_id) {
              $subsubcat = Engine_Api::_()->getItem('courses_category', $subcat->subsubcat_id);
              if ($subsubcat) {
                $result['subsubcategory_title'] = $subsubcat->category_name;
              }
            }
          }
        }
      }
    }
    $paymentGateways = Engine_Api::_()->courses()->checkPaymentGatewayEnable();
    $paymentMethods = $paymentGateways['methods'];
    $paymentMethodsCounter = 0;
    if(in_array('paypal',$paymentMethods)){
      $result["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Paypal');
      $result["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/paypal.png');
      $paymentMethodsCounter++;
      
    }
    if(in_array(0,$paymentMethods)){
      $result["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cash on Delivery');
      $result["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cash.png');
      $paymentMethodsCounter++;
    }
    if(in_array(1,$paymentMethods)){
      $result["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cheque');
      $result["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cheque.png');
      $paymentMethodsCounter++;
    }
    $basicInformationCounter = 0;
    $owner = $course->getOwner();
    $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('course_show_userdetail', 0);
    if(!$hideIdentity){
      $result['basicInformation'][$basicInformationCounter]['name'] = 'createdby';
      $result['basicInformation'][$basicInformationCounter]['value'] = $owner->displayname;
      $result['basicInformation'][$basicInformationCounter]['label'] = 'Created By';
      $basicInformationCounter++;
    }
    $result['basicInformation'][$basicInformationCounter]['name'] = 'creationdate';
    $result['basicInformation'][$basicInformationCounter]['value'] = $course->creation_date;
    $result['basicInformation'][$basicInformationCounter]['label'] = 'Created on';

    if(!empty($classroom)) {
     $result['basicInformation'][$basicInformationCounter]['name'] = 'classroom_title';
     $result['basicInformation'][$basicInformationCounter]['value'] = $classroom->title;
     $result['basicInformation'][$basicInformationCounter]['label'] = 'Classroom Title';
   }

   $basicInformationCounter++;
   $statsCounter = 0;

   $state[$statsCounter]['name'] = 'comment';
   $state[$statsCounter]['value'] = $course->comment_count;
   $state[$statsCounter]['label'] = 'Comments';
   $statsCounter++;

   $state[$statsCounter]['name'] = 'like';
   $state[$statsCounter]['value'] = $course->like_count;
   $state[$statsCounter]['label'] = 'Likes';
   $statsCounter++;

   $state[$statsCounter]['name'] = 'view';
   $state[$statsCounter]['value'] = $course->view_count;
   $state[$statsCounter]['label'] = 'Views';
   $statsCounter++;

   $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('course_allow_favourite', 0);
   $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('course_allow_follow', 0);

   if ($canFavourite) {
    $state[$statsCounter]['name'] = 'favourite';
    $state[$statsCounter]['value'] = $course->favourite_count;
    $state[$statsCounter]['label'] = 'Favourites';
    $statsCounter++;
  }

  if ($canFollow) {
    $state[$statsCounter]['name'] = 'follow';
    $state[$statsCounter]['value'] = $course->follow_count;
    $state[$statsCounter]['label'] = 'Follows';
  }
  $statsCounter++;

  $result['basicInformation'][$basicInformationCounter]['name'] = 'stats';
  $result['basicInformation'][$basicInformationCounter]['value'] = $state;
  $result['basicInformation'][$basicInformationCounter]['label'] = 'Stats';
  $basicInformationCounter++;

  if ($course->category_id) {
    $category = Engine_Api::_()->getItem('courses_category', $course->category_id);
    if ($category) {
      $result['basicInformation'][$basicInformationCounter]['name'] = 'category';
      $result['basicInformation'][$basicInformationCounter]['value'] = $category->category_name;
      $result['basicInformation'][$basicInformationCounter]['label'] = 'Category';

      $basicInformationCounter++;
      if ($course->subcat_id) {
        $subcat = Engine_Api::_()->getItem('courses_category', $course->subcat_id);
        if ($subcat) {
          $result['basicInformation'][$basicInformationCounter]['name'] = 'subcategory';
          $result['basicInformation'][$basicInformationCounter]['value'] = $subcat->category_name;
          $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Category';
          $basicInformationCounter++;
          if ($course->subsubcat_id) {
            $subsubcat = Engine_Api::_()->getItem('courses_category', $course->subsubcat_id);
            if ($subsubcat) {
              $result['basicInformation'][$basicInformationCounter]['name'] = 'subsubcategory';
              $result['basicInformation'][$basicInformationCounter]['value'] = $subsubcat->category_name;
              $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Sub Category';
              $basicInformationCounter++;
            }
          }
        }
      }
    }
  }

  $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
  $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($course);
    if (count($fieldStructure)) { // @todo figure out right logic
      $content = $this->view->fieldSesapiValueLoop($course, $fieldStructure);;
      $counter = 0;
      foreach ($content as $key => $value) {
        $result['profileDetail'][$counter]['label'] = $key;
        $result['profileDetail'][$counter]['value'] = $value;
        $counter++;
      }
    }

    $result['Detail'] = $course->description;
    $contactInformationCounter = 0;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'phone';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'View Phone Number';
    if ($course->course_contact_phone)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_phone;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'mail';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Send Email';
    if ($course->course_contact_email)
      $result['contactInformation'][$contactInformationCounter]['value'] = $$course->course_contact_email;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'website';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Visit Website';
    if ($course->course_contact_website)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_website;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'facebook';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Facebook.com';
    if ($course->course_contact_facebook)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_facebook;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'linkedin';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Linkedin';
    if ($course->course_contact_linkedin)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_linkedin;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'twitter';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Twitter';
    if ($course->course_contact_twitter)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_twitter;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'instagram';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Instagram.com';
    if ($course->course_contact_instagram)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_instagram;
    $contactInformationCounter++;
    $result['contactInformation'][$contactInformationCounter]['name'] = 'pinterest';
    $result['contactInformation'][$contactInformationCounter]['label'] = 'Pinterest.com';
    if ($course->course_contact_pinterest)
      $result['contactInformation'][$contactInformationCounter]['value'] = $course->course_contact_pinterest;

    $likeMembers = Engine_Api::_()->courses()->getMemberByLike($course->course_id);
    $favMembers = Engine_Api::_()->courses()->getMemberFavourite($course->course_id);
    $followMembers = Engine_Api::_()->courses()->getMemberFollow($course->course_id);

    if (count($likeMembers)) {
      $likeCounter = 0;
      $result['total_people_who_liked'] = count($likeMembers) > 4 ? count($likeMembers) - 4 : 0;
      foreach ($likeMembers as $member) {
        if($likeCounter > 3)
          break;  
        $item = Engine_Api::_()->getItem('user', $member['poster_id']);
        if(!$item){
          $result['total_people_who_liked'] = $result['total_people_who_liked'] > 0 ? $result['total_people_who_liked'] - 1 : 0;
          continue; 
        }
        $nameLike = $item->getTitle();
        $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
        if ($image) {
          $result['people_who_liked'][$likeCounter]['images'] = $image;
        }
        if ($nameLike) {
          $result['people_who_liked'][$likeCounter]['name'] = $nameLike;
        }

        $result['people_who_liked'][$likeCounter]['user_id'] = $item->user_id;
        $likeCounter++;
      }
    }
    if (count($followMembers) && $canFollow) {

      $followCounter = 0;
      $result['total_people_who_follow_this'] = count($followMembers) > 4 ? count($followMembers) - 4 : 0;
      foreach ($followMembers as $member) {
        if($followCounter > 3)
          break;
        $item = Engine_Api::_()->getItem('user', $member['owner_id']);


        if(count($item->toArray()) == 0){
          $result['total_people_who_follow_this'] = $result['total_people_who_follow_this'] > 0 ? $result['total_people_who_follow_this'] - 1 : 0;
          continue;
        }


        $name = $item->getTitle();

        $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");


        if ($image) {
          $result['people_who_follow_this'][$followCounter]['images'] = $image;
        }
        if ($name) {
          $result['people_who_follow_this'][$followCounter]['name'] = $name;
        }
        $result['people_who_follow_this'][$followCounter]['user_id'] = $item->user_id;
        $followCounter++;
      }

    }

    if (count($favMembers) && $canFavourite) {
      $favCounter = 0;
      $result['total_people_who_favourited'] = count($favMembers) > 4 ? count($favMembers) - 4 : 0;
      foreach ($favMembers as $member) {
        if($favCounter > 3)
          break;
        $item = Engine_Api::_()->getItem('user', $member['owner_id']);
        if(!$item){
          $result['total_people_who_favourited'] = $result['total_people_who_favourited']> 0 ? $result['total_people_who_favourited'] - 1 : 0;
          continue;
        }
        $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
        $nameFav = $item->getTitle();
        if ($image) {
          $result['people_who_favourited'][$favCounter]['images'] = $image;
        } else {

        }
        if ($nameFav) {
          $result['people_who_favourited'][$favCounter]['name'] = $nameFav;
        }
        $result['people_who_favourited'][$favCounter]['user_id'] = $item->user_id;
        $favCounter++;
      }
    }
    return $result;
  }
	public function getChildCount(){
    return $this->_childCount;
  }
	public function editoverviewAction(){
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
    }
    $subject = Engine_Api::_()->core()->getSubject();
    if ($this->_getParam('getForm')) {
      $formFields = array();
      $formFields[0]['name'] = "purchase_note";
      $formFields[0]['type'] = "Textarea";
      $formFields[0]['multiple'] = "";
      $formFields[0]['label'] = "Course Overview";
      $formFields[0]['description'] = "";
      $formFields[0]['isRequired'] = "1";
      $formFields[0]['value'] = $subject->purchase_note;
      $formFields[1]['name'] = "submit";
      $formFields[1]['type'] = "Button";
      $formFields[1]['multiple'] = "";
      $formFields[1]['label'] = "Save Changes";
      $formFields[1]['description'] = "";
      $formFields[1]['isRequired'] = "0";
      $formFields[1]['value'] = '';
      $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
    $subject->purchase_note = $_POST['purchase_note'];
    $subject->save();
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Course overview saved successfully.'))));
  }
  public function overviewAction(){
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!Engine_Api::_()->core()->hasSubject()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      }
      $subject = Engine_Api::_()->core()->getSubject();
      $editOverview = $subject->authorization()->isAllowed($viewer, 'edit');
      if (!$editOverview && (!$subject->purchase_note || is_null($subject->purchase_note))) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
      }
      if ($editOverview) {
        if ($subject->purchase_note) {
            $result['button'][0]['name'] = "editoverview";
            $result['button'][0]['lable'] = $this->view->translate("Change Overview");
        } else {
            $result['button'][0]['name'] = "editoverview";
            $result['button'][0]['lable'] = $this->view->translate("Add Overview");
        }
      }
      if ($subject->purchase_note) {
          $result['overview'] = $subject->purchase_note;
      } else {
          $result['overview'] = $this->view->translate("There is currently no overview.");
      }
     
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
  }
	public function deleteAction(){
		$courseId = $this->_getParam('course_id');
        if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course  = Engine_Api::_()->core()->getSubject();
		}else{
			$course = Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->getRequest()->isPost()) {
        $status['status'] = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try
    {
        Engine_Api::_()->courses()->deleteCourse($course);
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this course.'),'status' => true)));

    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
	protected function setPhoto($photo, $id) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'video',
        'parent_id' => $id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($mainPath)
            ->destroy();
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Core_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }
	function getDay($number){
    switch ($number) {
        case 1:
            return "Monday";
            break;
        case 2:
            return "Tuesday";
            break;
        case 3:
            return "Wednesday";
            break;
        case 4:
            return "Thursday";
            break;
        case 5:
            return "Friday";
            break;
        case 6:
            return "Saturday";
            break;
        case 7:
            return "Sunday";
            break;
    }
  }
	public function profileUpsellAction(){
		$courseId = $this->_getParam('course_id',null);
		if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course = Engine_Api::_()->core()->getSubject();
		}else{
			$course= Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$paginator = Engine_Api::_()->getDbtable('courses', 'courses')->getCoursePaginator(array('course_id'=>$course->course_id,'upsell'=>true,'manage-widget'=>true));
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$data['upsell_course'] = count($paginator) ? $this->getCourses($paginator) : array();
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
  public function crosssellCoursesAction(){
    $cartData = Engine_Api::_()->courses()->cartTotalPrice();
		if(empty($cartData['cartCourseIds'])){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$paginator = Engine_Api::_()->getDbtable('courses', 'courses')->getCoursePaginator(array('courseIds'=>implode(",", $cartData['cartCourseIds']),'crosssell'=>true,'manage-widget'=>true,'fetchAll'=>true));
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$data['crosssell_course'] = count($paginator) ? $this->getCourses($paginator) : array();
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
  public function myCartAction(){
    $cartData = Engine_Api::_()->courses()->cartTotalPrice();
    $counter = 0;
    $productsCounter = 0;
    $result = array();
    $_SESSION['courses_cart_checkout']['cart_total_price'] = array(0);
    foreach($cartData['cartCourses'] as $cart) { 
      $course = Engine_Api::_()->getItem('courses',$cart->course_id);
      if(!$course)
        Continue;
      $priceData = Engine_Api::_()->courses()->courseDiscountPrice($course);
      $result['cartData'][$counter]['productData'][$productsCounter]['course_id'] = $course->getIdentity();
      $result['cartData'][$counter]['productData'][$productsCounter]['title'] = $course->getTitle();
      $result['cartData'][$counter]['productData'][$productsCounter]['quantity'] = $quantity;
      if(!empty($priceData['discountPrice'])){
        $result['cartData'][$counter]['productData'][$productsCounter]['price'] = Engine_Api::_()->courses()->getCurrencyPrice(round($priceData['discountPrice'],2)) ;
      } else {
        $result['cartData'][$counter]['productData'][$productsCounter]['price'] = 'FREE';
      }
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($course,'',"");
      if(!count($images))
        $images['main'] = $this->getBaseUrl(true,$course->getPhotoUrl());
        $result['cartData'][$counter]['productData'][$productsCounter]['course_images'] = $images;
      $taxes = Engine_Api::_()->getDbTable('taxstates','courses')->getOrderTaxes(array_merge(array('course_id'=>$course->course_id,'total_price'=>round($priceData['discountPrice'],2)),$_SESSION['courses_cart_checkout']));
      $result['cartData'][$counter]['productData'][$productsCounter]['taxes'] = $taxes;
      $_SESSION['courses_cart_checkout']['cart_total_price'][$course->course_id] = round($priceData['discountPrice']+$taxes['total_tax'],2);
      //Menus
      $menuoptions= array();
      $menucounter = 0;
      $menuoptions[$menucounter]['name'] = "remove";
      $menuoptions[$menucounter]['id'] = $cart->cartcourse_id;
      $menuoptions[$menucounter]['label'] = $this->view->translate("Remove");
      $menucounter++;
      //Menus
      $result['cartData'][$counter]['productData'][$productsCounter]['buttons'] = $menuoptions;
      $productsCounter++;
    }
    $counter++;
    $extraParams = array();
    $extraParams['empty'] = $this->view->translate('Clear All');
    $extraParams['checkout'] = $this->view->translate('Checkout');
    $extraParams['cart_total'] = $cartData['cartCoursesCount'];
    $result['extraParams'] = $extraParams;
    $result['grand_total'] = Engine_Api::_()->courses()->getCurrencyPrice(round(array_sum($_SESSION['courses_cart_checkout']['cart_total_price']),2));
    $result['checkout'] = $this->view->translate("Proceed to Checkout");
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Course Exists.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
  }
  function deletecartAction() {
    $id = $this->_getParam('id');
    $this->view->form = $form = new Sesbasic_Form_Delete();
    if($id) {
        $form->setTitle($this->view->translate("Delete Course from Cart?"));
        $form->setDescription($this->view->translate('Are you sure that you want to delete this product from your cart? Course will not be recoverable after being deleted.'));
    }else{
        $form->setTitle($this->view->translate('Delete Courses from Cart?'));
        $form->setDescription($this->view->translate('Are you sure that you want to clear your shopping cart? Course’s will not be recoverable after being deleted.'));
    }
    $form->submit->setLabel('Delete');
    if (!$this->getRequest()->isPost()) {
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }
    $cartCourseTable = Engine_Api::_()->getDbTable('cartcourses','courses');
    $db = $cartCourseTable->getAdapter();
    $db->beginTransaction();
    try {
      $cartId = Engine_Api::_()->courses()->getCartId();
      if($id) {
        $select = $cartCourseTable->select()->where('cartcourse_id =?',$id);
        $course = $cartCourseTable->fetchRow($select);
        unset($_SESSION['courses_cart_checkout']['cart_total_price'][$course->course_id]);
        $cartCourseTable->delete(array('cartcourse_id =?' => $id, 'cart_id =?' => $cartId->getIdentity()));
        $status = 1;
      }else{
        $cartCourseTable = Engine_Api::_()->getDbTable('cartcourses','courses');
        $select = $cartCourseTable->select()->where('cart_id =?',$cartId->getIdentity());
        $courses = $cartCourseTable->fetchAll($select);
        foreach ($courses as $course) {
            $course->delete();
            unset($_SESSION['courses_cart_checkout']['cart_total_price'][$course->course_id]);
        }
        $status = 1;
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    if($id) {
      $message = Zend_Registry::get('Zend_Translate')->_('Course removed from your cart.');
    } else {
      $message = Zend_Registry::get('Zend_Translate')->_('All Course removed from your cart.');
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
  public function createLectureAction() { 
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('courses', null, 'lec_create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      $courseId = $this->_getParam('course_id',false);
      $course = Engine_Api::_()->getItem('courses', $courseId);
    $sessmoothbox = $this->view->typesmoothbox = false;
    if($this->_getParam('typesmoothbox',false)){
      // Render
      $sessmoothbox = true;
      $this->view->typesmoothbox = true;
      $layoutOri = $this->view->layout()->orientation;
      if($layoutOri == 'right-to-left'){
        $this->view->direction = 'rtl';
      }else{
        $this->view->direction = 'ltr';
      }
      $language = explode('_', $this->view->locale()->getLocale()->__toString());
      $this->view->language = $language[0];
    } else {
      $this->_helper->content->setEnabled();
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $course = Engine_Api::_()->getItem('courses', $courseId);
    if(empty($course)){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid course id supplied'), 'result' => array()));
    }
    $totalLecture = Engine_Api::_()->getDbTable('lectures', 'courses')->countLectures($viewer->getIdentity());
    $allowLectureCount = Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'lecture_count');
    $this->view->createLimit = 1;
    if ($totalLecture >= $allowLectureCount && $allowLectureCount != 0) {
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('You have already uploaded the maximum number of entries allowed.'), 'result' => array()));
    } else {
        $this->view->form = $form = new Courses_Form_Lecture_Create();
    }    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'courses_lecture'));
    }
    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    $values = $form->getValues();
    $lectureTable = Engine_Api::_()->getDbTable('lectures', 'courses');
    $db = $lectureTable->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      if (isset($_POST['title']) && !empty($_POST['title']))
          $values['title'] = $_POST['title'];
      if (isset($_POST['as_preview']) && !empty($_POST['as_preview']))
          $values['as_preview'] = $_POST['as_preview'];
      if (isset($_POST['description']) && !empty($_POST['description']))
          $values['description'] = $_POST['description'];
      if (isset($_POST['timer']) && !empty($_POST['timer']))
          $values['timer'] = $_POST['timer'];
      if (isset($_POST['type']) && !empty($_POST['type']))
          $values['type'] = $_POST['type'];
      if (isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name']))
          $_POST['id'] = $this->uploadVideoAction();
      if($values['type'] == 'external') {
          $information = $this->handleIframelyInformation($_POST['url']);
          if (empty($information)) {
             Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$form->getMessages(), 'result' => array()));
          }
          $values['code'] = $information['code'];
          $values['thumbnail'] = $information['thumbnail'];
          $values['duration'] = $information['duration'];
      }  else if ($values['type'] == 'html') {
          $values['code'] = Engine_Text_BBCode::prepare($_POST['htmltext']);
      } else if($values['type'] == 'internal') {
          $lecture = Engine_Api::_()->getItem('courses_lecture', $this->_getParam('id'));
          if(empty($lecture)){
              $lecture = $lectureTable->createRow();
          }
      } 
      if(empty($lecture)) {
          $lecture = $lectureTable->createRow();
      }
      if ($values['type'] == 'internal' && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
          $values['photo_id'] = $this->setPhoto($form->photo_id, $lecture->lecture_id, true);
      }
      $values['course_id'] = $course->course_id;
      $values['owner_id'] = $viewer->getIdentity();
      $lecture->setFromArray($values);
      $lecture->save();
      $thumbnail = $values['thumbnail'];
      $ext = ltrim(strrchr($thumbnail, '.'), '.');
      $thumbnail_parsed = @parse_url($thumbnail);
      $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
      $content = $this->url_get_contents($thumbnail);
      if ($content) {
          $valid_thumb = true;
          file_put_contents($tmp_file, $content);
      } else {
          $valid_thumb = false;
      }
      if( isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != ''){
          $lecture->photo_id = $this->setPhoto($form->photo_id,  $lecture->lecture_id, true);
          $lecture->save();
      } else if($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
          //resize video thumbnails
          $image = Engine_Image::factory();
          $image->open($tmp_file)
                  ->resize(500, 500)
                  ->write($thumb_file)
                  ->destroy();
          try {
          $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
              'parent_type' => 'courses_lecture',
              'parent_id' => $lecture->lecture_id,
          ));
          // Remove temp file
          @unlink($thumb_file);
          @unlink($tmp_file);
          $lecture->photo_id = $thumbFileRow->file_id;
          $lecture->save();
          } catch (Exception $e){
          throw $e;
          @unlink($thumb_file);
          @unlink($tmp_file);
          }
      }
      $course->lecture_count++;
      $course->save();
      $db->commit();
      $users = Engine_Api::_()->getDbtable('ordercourses', 'courses')->getCoursePurchasedMember($course->course_id);
      $lectureTitle = '<a href="'.$lecture->getHref().'">'.$lecture->getTitle().'</a>';
      $courseTitle = '<a href="'.$course->getHref().'">'.$course->getTitle().'</a>';
      foreach($users as $user){
        $user = Engine_Api::_()->getItem('user', $user->user_id);
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $course, 'courses_lecture_create',array('lecture'=>$lectureTitle));
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_courses_lecture_create', array('lecture_title' => $lectureTitle, 'course_name' => $courseTitle,'sender_title' => $viewer->getTitle(), 'object_link' => $user->getHref(), 'host' => $_SERVER['HTTP_HOST']));
          //Activity Feed work
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $course, "courses_lecture_create");
        if ($action) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $course);
        }
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('lecture_id' => $lecture->lecture_id,'message' => $this->view->translate('The selected lecture has been created.'))));
    }catch(Exception $e){
      $db->rollBack();
      throw $e;
    }
  }
  public function editLectureAction() {
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('courses', null, 'lec_edit')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $viewer = $this->view->viewer();
    $sessmoothbox = $this->view->typesmoothbox = false;
    if ($this->_getParam('typesmoothbox', false)) {
      // Render
      $sessmoothbox = true;
      $this->view->typesmoothbox = true;
    } 
    $lectureId = $this->_getParam('lecture_id',false);
    $format = $this->_getParam('format',false);
    $this->view->lecture = $lecture = Engine_Api::_()->getItem('courses_lecture', $lectureId);
    $this->view->form = $form = new Courses_Form_Lecture_Edit();
    $this->view->type = $lecture->type; 
    if($form->getElement('url')){
      $form->removeElement('url');
    }
    $form->populate($lecture->toArray());
    $form->populate(
        array('htmltext'=>$lecture->code)
    ); 
		$viewer = Engine_Api::_()->user()->getViewer();
		if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		//is post
		if (!$form->isValid($this->getRequest()->getPost())) {
       $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
       if(count($validateFields))
        $this->validateFormFields($validateFields);
		}
    $values = $form->getValues(); 
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();  
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $course = Engine_Api::_()->getItem('courses', $lecture->course_id);
      $values['type'] = $lecture->type;
      if (isset($_POST['title']) && !empty($_POST['title']))
          $values['title'] = $_POST['title'];
      if (isset($_POST['description']) && !empty($_POST['description']))
          $values['description'] = $_POST['description'];
      if (isset($_POST['description']) && !empty($_POST['description']))
          $values['timer'] = $_POST['timer'];
      $values['as_preview'] = $_POST['as_preview'];
      if($values['type'] == 'external') {
        $values['code'] = $lecture->code;
      }  else if ($values['type'] == 'html') {
          $values['code'] = Engine_Text_BBCode::prepare($_POST['htmltext']);
      } else if($values['type'] == 'internal') {
            
      } 
      if ($values['type'] == 'internal' && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') { die;
          $values['photo_id'] = $this->setPhoto($form->photo_id, $lecture->lecture_id, true);
      } 
      $values['owner_id'] = $viewer->getIdentity();
      if(empty($values['photo_id']))
        $values['photo_id'] = $lecture->photo_id;
      $lecture->setFromArray($values);
      $lecture->save();
      $thumbnail = $values['thumbnail'];
      $ext = ltrim(strrchr($thumbnail, '.'), '.');
      $thumbnail_parsed = @parse_url($thumbnail);
      if (@GetImageSize($thumbnail)) {
        $valid_thumb = true;
      } else {
        $valid_thumb = false;
      }
      if( isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != ''){
          $lecture->photo_id = $this->setPhoto($form->photo_id,  $lecture->lecture_id, true);
          $lecture->save();
      } else if($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) { 
        $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
        //resize video thumbnails
        $image = Engine_Image::factory();
        $image->open($tmp_file)
                ->resize(500, 500)
                ->write($thumb_file)
                ->destroy();
        try {
          $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
              'parent_type' => 'courses_lecture',
              'parent_id' => $lecture->lecture_id,
          ));
          // Remove temp file
          @unlink($thumb_file);
          @unlink($tmp_file);
          $lecture->file_id = $thumbFileRow->file_id;
          $lecture->save();
        } catch (Exception $e){
          throw $e;
          @unlink($thumb_file);
          @unlink($tmp_file);
        }
      }
      $db->commit();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('lecture_id' => $lecture->lecture_id,'message' => $this->view->translate('The selected lecture has been edited.'))));
    }catch(Exception $e){
        $db->rollBack();
        throw $e;
    }
  }
  public function profileLectureAction(){
		$courseId = $this->_getParam('course_id');
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course = Engine_Api::_()->core()->getSubject();
		}else{
			$course = Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$value['course_id'] = $course->getIdentity();
		$paginator = Engine_Api::_()->getDbTable('lectures', 'courses')->getLecturesPaginator($value,array('title','course_id','lecture_id','as_preview','duration','photo_id','owner_id','code'));
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		$result['lectures'] = $this->getLectures($paginator);
		$result['isPurchesed'] = Engine_Api::_()->courses()->getUserPurchesedCourse($course->course_id);
    $result['can_create'] = ($viewer->getIdentity()==$course->owner_id && Engine_Api::_()->authorization()->isAllowed('courses', $viewer, 'lec_create')) ? true : false;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
  public function getLectures($paginator){
		$counter = 0;
		$result = array();
		foreach($paginator as $item){
			$result[$counter] = $item->toArray();
			$result[$counter]['image'] = $this->getBaseUrl(true, $item->getPhotoUrl());
      if(!empty($item->duration))
        $result[$counter]['duration'] = gmdate("H:i:s", $item->duration);
      preg_match('/src="([^"]+)"/', $item->code, $match);
      if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
        $result[$counter]['iframeURL'] = str_replace('//','https://',$match[1]);
      }else{
        $result[$counter]['iframeURL'] = $match[1];
      }
			$counter++;
		}
		return $result;
	}
  public function lectureViewAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
    $lecture_id = $this->_getParam('lecture_id', null);
		if(!$lecture_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		$lecture = Engine_Api::_()->getItem('courses_lecture', $lecture_id);
    $courses = Engine_Api::_()->getItem('courses',$lecture->course_id);
    if(!$courses) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    }
    if(!Engine_Api::_()->courses()->getUserPurchesedCourse($lecture->course_id)  && !$lecture->as_preview){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Please buy this course to view lecture.'), 'result' => array()));
    }
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$result = $lecture->toArray();
    preg_match('/src="([^"]+)"/', $lecture->code, $match);
    if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
      if ($lecture->type == "internal" && $lecture->status == 1) { 
        if (!empty($lecture->file_id)) {
          $storage_file = Engine_Api::_()->getItem('storage_file', $lecture->file_id);
          if ($storage_file) { 
            $result['iframeURL'] = $this->getBaseUrl(true,$storage_file->map());
          }
        }
      }
      if($lecture->status == 0): 
        $result['status_message'] = $this->view->translate('Your video is in queue to be processed - you will be notified when it is ready to be viewed.');
      elseif($lecture->status == 2):
        $result['status_message'] = $this->view->translate('Your video is currently being processed - you will be notified when it is ready to be viewed.');
      elseif($lecture->status == 3):
        $result['status_message'] = $this->view->translate('Video conversion failed. Please try uploading again.'); 
      elseif($lecture->status == 4):
        $result['status_message'] = $this->view->translate('Video conversion failed. Video format is not supported by FFMPEG. Please try again.'); 
      elseif($lecture->status == 2):
        $result['status_message'] = $this->view->translate('Video conversion failed. Audio files are not supported. Please try again.'); 
      endif; 
    }else{
      $result['iframeURL'] = $match[1];
    }
		$lecturer = Engine_Api::_()->getItem('user', $lecture->owner_id);
		$owner = $lecturer->getOwner();
		$result['owner']['id'] = $owner->getIdentity();
		$result['owner']['Guid'] = $owner->getGuid();
		$result['owner']['title'] = $owner->getTitle();
		$result['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
		$isPurchesed =  Engine_Api::_()->courses()->getUserPurchesedCourse($lecture->course_id);
		$value['course_id'] = $lecture->course_id;
		if(empty($isPurchesed))
      $value['as_preview'] = 1;
    $paginator = Engine_Api::_()->getDbTable('lectures', 'courses')->getLecturesPaginator($value,array('title','course_id','lecture_id','as_preview','duration','photo_id','owner_id','code'));
    $result['related_lectures'] = $this->getLectures($paginator);
    // Set item count per page and current page number
    $paginator->setItemCountPerPage($limit_data);
    $optionCounter = 0;
    if(($viewer->getIdentity() == $lecture->owner_id) || $viewer->isAdmin()) { 
			$result['menus'][$optionCounter]['name'] = 'edit';
			$result['menus'][$optionCounter]['label'] = $this->view->translate('Edit Lecture');
			$optionCounter++;
      $result['menus'][$optionCounter]['name'] = 'delete';
			$result['menus'][$optionCounter]['label'] = $this->view->translate('Delete Lecture');
			$optionCounter++;
		}
    if($viewerId && $viewerId != $owner->getIdentity()){
      $result['menus'][$optionCounter]['name'] = 'report';
      $result['menus'][$optionCounter]['label'] = $this->view->translate('Report');
      $optionCounter++;
    }
		$data['lecture'] = $result;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data)));
	}
  function url_get_contents ($Url) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $Url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      $output = curl_exec($ch);
      curl_close($ch);
      return $output;
  }
  public function uploadVideoAction() { 
    if (!$this->_helper->requireUser()->checkRequire()) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
        return;
    }
    if (!$this->getRequest()->isPost()) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        return;
    }
    $values = $this->getRequest()->getPost();
    if (empty($_FILES['Filedata'])) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
        return;
    }
    $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt','zip');
    if (in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
        $this->view->status = false;
        $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
        return;
    } 
    $uploadwall = $this->_getParam('uploadwall',0);
    $db = Engine_Api::_()->getDbtable('lectures', 'courses')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();
      $params = array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
      );
      $lecture = $this->createVideo($params, $_FILES['Filedata'], $values);
      $lecture->save();
      $db->commit();
      return $lecture->lecture_id;
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.') . $e;
      // throw $e;
      return;
    }
  }
  public function createVideo($params, $file, $values,$lecture_date = false) { 
    if ($file instanceof Storage_Model_File) { 
      $params['file_id'] = $file->getIdentity();
    } else { 
      // create video item
    if(!$lecture_date){
      	$lecture = Engine_Api::_()->getDbtable('lectures', 'courses')->createRow();
      	$file_ext = pathinfo($file['name']);
				$file_ext = $file_ext['extension'];
			}else{
        $lecture = $lecture_date;
    }
    $lecture->owner_id = $params['owner_id'];
    $lecture->status = 2;
    $lecture->save();
      // Store video in temporary storage object for ffmpeg to handle
      $storage = Engine_Api::_()->getItemTable('storage_file');
			$params = array(
          'parent_id' => $lecture->lecture_id,
          'parent_type' => $lecture->getType(),
          'user_id' => $lecture->owner_id,
          'mime_major' => 'video',
          'mime_minor' => $file_ext,
      );
    if(!$lecture_date){ 
        $lecture->code = $file_ext;
      	$storageObject = $storage->createFile($file, $params); 
        $lecture->file_id = $file_id = $storageObject->file_id; 
    }
    // Remove temporary file
    @unlink($file['tmp_name']);
        $file = Engine_Api::_()->getItemTable('storage_file')->getFile($file_id, null); 
        $file = (_ENGINE_SSL ? 'https://' : 'http://')
            . $_SERVER['HTTP_HOST'].$file->map(); 
        $lecture->duration = $duration = $this->getVideoDuration($lecture,$file);
        if($duration){
            $thumb_splice = $duration / 2;
            $this->getVideoThumbnail($lecture,$thumb_splice,$file);
        }
    $lecture->status = 1;
    $lecture->save();
      // Add to jobs
      Engine_Api::_()->getDbtable('jobs', 'core')->addJob('lecture_encode', array('lecture_id' => $lecture->getIdentity(), ));
    }
    return $lecture;
  }
  public function getVideoThumbnail($lecture,$thumb_splice,$file = false){ 
		$tmpDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary' . DIRECTORY_SEPARATOR . 'video';
		$thumbImage = $tmpDir . DIRECTORY_SEPARATOR . $lecture -> getIdentity() . '_thumb_image.jpg';
		$ffmpeg_path = Engine_Api::_() -> getApi('settings', 'core') ->video_ffmpeg_path;
		if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path))
		{
			$output = null;
			$return = null;
			exec($ffmpeg_path . ' -version', $output, $return);
			if ($return > 0)
			{
				return 0;
			}
		}
		if(!$file)
			$fileExe = $lecture->code;
		else
			$fileExe = $file;
		$output = PHP_EOL;
		$output .= $fileExe . PHP_EOL;
		$output .= $thumbImage . PHP_EOL;
		$thumbCommand = $ffmpeg_path . ' ' . '-i ' . escapeshellarg($fileExe) . ' ' . '-f image2' . ' ' . '-ss ' . $thumb_splice . ' ' . '-vframes ' . '1' . ' ' . '-v 2' . ' ' . '-y ' . escapeshellarg($thumbImage) . ' ' . '2>&1';
		// Process thumbnail
		$thumbOutput = $output . $thumbCommand . PHP_EOL . shell_exec($thumbCommand);
		// Check output message for success
		$thumbSuccess = true;
		if (preg_match('/video:0kB/i', $thumbOutput))
		{
			$thumbSuccess = false;
		}
		// Resize thumbnail
		if ($thumbSuccess && is_file($thumbImage))
		{
			try
			{
				$image = Engine_Image::factory();
				$image->open($thumbImage)->resize(500, 500)->write($thumbImage)->destroy();
				$thumbImageFile = Engine_Api::_()->storage()->create($thumbImage, array(
					'parent_id' => $lecture -> getIdentity(),
					'parent_type' => $lecture -> getType(),
					'user_id' => $lecture -> owner_id
					)
				);
				$lecture->photo_id = $thumbImageFile->file_id;
				$lecture->save();
				@unlink($thumbImage);
				return true;
			}
			catch (Exception $e)
			{
				throw $e;
				@unlink($thumbImage);
			}
		}
		 @unlink(@$thumbImage);
		 return false;
	}
	public function getVideoDuration($lecture,$file = false)
	{ 
		$duration = 0;
		if ($lecture)
		{
      $ffmpeg_path = Engine_Api::_() -> getApi('settings', 'core') -> video_ffmpeg_path;
      if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path))
      {
          $output = null;
          $return = null;
          exec($ffmpeg_path . ' -version', $output, $return);
          if ($return > 0)
          {
              return 0;
          }
      }
      if(!$file)
          $fileExe = $lecture->code;
      else
          $fileExe = $file;
      // Prepare output header
      $fileCommand = $ffmpeg_path . ' ' . '-i ' . escapeshellarg($fileExe) . ' ' . '2>&1';
      // Process thumbnail
      $fileOutput = shell_exec($fileCommand);
      // Check output message for success
      $infoSuccess = true;
      if (preg_match('/video:0kB/i', $fileOutput))
      {
          $infoSuccess = false;
      }
      // Resize thumbnail
      if ($infoSuccess)
      {
        // Get duration of the video to caculate where to get the thumbnail
        if (preg_match('/Duration:\s+(.*?)[.]/i', $fileOutput, $matches))
        {
            list($hours, $minutes, $seconds) = preg_split('[:]', $matches[1]);
            $duration = ceil($seconds + ($minutes * 60) + ($hours * 3600));
        }
      }
		}
		return $duration;
	}
  public function deleteLectureAction(){
		$lectureId = $this->_getParam('lecture_id');
    if(!$lectureId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $lecture = Engine_Api::_()->getItem('courses_lecture', $lectureId);
		if(!$lecture){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->getRequest()->isPost()) {
        $status['status'] = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try
    {
        $course = Engine_Api::_()->getItem('courses', $lecture->course_id);
        $course->lecture_count--;
        $course->save();
        // delete the lecture entry into the database
        $lecture->delete();              
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Your Lecture has been  Deleted successfully'),'status' => true)));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function createTestAction() { 
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('courses', null, 'test_create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $courseId = $this->_getParam('course_id',false);
    $course = Engine_Api::_()->getItem('courses', $courseId);
    if(empty($course)){
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('Invalid course id supplied'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $totalTest = Engine_Api::_()->getDbTable('tests', 'courses')->countTests($viewer->getIdentity());
    $allowTestCount = Engine_Api::_()->authorization()->getPermission($viewer, 'courses', 'test_count');
    if ($totalTest >= $allowTestCount && $allowTestCount != 0) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('You have already uploaded the maximum number of entries allowed.'), 'result' => array()));
    } else {
      $this->view->form = $form = new Courses_Form_Test_Create();
    }
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'courses_test'));
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    $values = $form->getValues();
    $testTable = Engine_Api::_()->getDbTable('tests', 'courses');
    $db = $testTable->getAdapter();
    $db->beginTransaction();
    try {
        $test = $testTable->createRow();
        $viewer = Engine_Api::_()->user()->getViewer();
        if (isset($_FILES['photo']) && $_FILES['photo']['name'] != '') {
          $test->photo_id = Engine_Api::_()->sesbasic()->setPhoto($form->photo, false,false,'courses','courses','',$test,true);
        }
        $values['description'] = Engine_Text_BBCode::prepare($values['description']);
        $values['success_message'] = Engine_Text_BBCode::prepare($values['success_message']);
        $values['failure_message'] = Engine_Text_BBCode::prepare($values['failure_message']);
        $values['question'] = Engine_Text_BBCode::prepare($values['question']); 
        $values['owner_id'] = $viewer->getIdentity();
        $test->setFromArray($values);
        $test->save();
        $test->course_id = $course->course_id;
        $test->save();
        $course->test_count++;
        $course->save();
        $db->commit();
        $users = Engine_Api::_()->getDbtable('ordercourses', 'courses')->getCoursePurchasedMember($course->course_id);
        $courseTitle = '<a href="'.$course->getHref().'">'.$course->getTitle().'</a>';
        foreach($users as $user){
          $user = Engine_Api::_()->getItem('user', $user->user_id);
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $course, 'courses_test_create',array('testTitle'=>$test->getTitle()));
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'courses_test_create', array('course_name' => $courseTitle, 'test_title' => $test->getTitle(),'object_link' => $course->getHref(), 'host' => $_SERVER['HTTP_HOST']));
           //Activity Feed work
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $course, "courses_test_create", null, array('testTitle' => array($test->getTitle())));
          if ($action) {
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $course);
          }
        }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('test_id' => $test->test_id,'message' => $this->view->translate('The selected test has been created.'))));
    }catch(Exception $e){
      $db->rollBack();
      throw $e;
    }
  }
  public function editTestAction() {
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('courses', null, 'test_edit')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $testId = $this->_getParam('test_id',false);
    $test = Engine_Api::_()->getItem('courses_test', $testId);
    $form = new Courses_Form_Test_Edit();
    $form->populate($test->toArray());
		if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		//is post
		if (!$form->isValid($this->getRequest()->getPost())) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
		}
    $values = $form->getValues(); 
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
        if (isset($_FILES['photo']) && $_FILES['photo']['name'] != '') {
          $test->photo_id = Engine_Api::_()->sesbasic()->setPhoto($form->photo, false,false,'courses','courses','',$test,true);
        }
        $test->save();
        $values['description'] = Engine_Text_BBCode::prepare($values['description']);
        $values['success_message'] = Engine_Text_BBCode::prepare($values['success_message']);
        $values['failure_message'] = Engine_Text_BBCode::prepare($values['failure_message']);
        $values['question'] = Engine_Text_BBCode::prepare($values['question']);
        $test->setFromArray($values);
        $test->save();
        $db->commit();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('test_id' => $test->test_id,'message' => $this->view->translate('The selected test has been edited.'))));
    }catch(Exception $e){
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function deleteTestAction(){
		$testId = $this->_getParam('test_id');
    if(!$testId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $test = Engine_Api::_()->getItem('courses_test', $testId);
		if(!$test){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
    if (!$this->getRequest()->isPost()) {
        $status['status'] = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try
    {
      $course = Engine_Api::_()->getItem('courses', $test->course_id);
      $course->test_count--;
      $course->save();
      $test->is_delete = 1;
      $test->save();
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Your Test has been  Deleted successfully'),'status' => true)));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }  
  public function profileTestAction(){
		$courseId = $this->_getParam('course_id');
		$viewer = Engine_Api::_()->user()->getViewer();
    if(!$courseId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$course = Engine_Api::_()->core()->getSubject();
		}else{
			$course = Engine_Api::_()->getItem('courses',$courseId);
		}
		if(!$course){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$value['course_id'] = $course->getIdentity();
		$paginator = Engine_Api::_()->getDbTable('tests', 'courses')->getTestsPaginator($value);
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		$result['tests'] = $this->getTests($paginator);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
  public function getTests($paginator){
		$counter = 0;
		$result = array();
		foreach($paginator as $item){
			$result[$counter] = $item->toArray();
			$result[$counter]['image'] = $this->getBaseUrl(true, $item->getPhotoUrl());
      $result[$counter]['can_give_test'] = Engine_Api::_()->courses()->getUserPurchesedCourse($item->course_id);
      $result[$counter]['show_result'] = 0;
			$counter++;
		}
		return $result;
	}
  public function joinTestAction()
  {
    $testId = $this->_getParam('test_id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (!$testId)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $test = Engine_Api::_()->getItem('courses_test', $testId);
    if (!$test) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    }
    $value['test_id'] = $test->test_id;
    $value['fetchAll'] = true;
    $paginator = Engine_Api::_()->getDbTable('testquestions', 'courses')->getQuestionsPaginator($value);
    $formData = $_POST;
    $testDetails = array();
    $counter = 0;
    if ($this->_getParam('getForm')) {
      foreach ($paginator as $question) :
        $answers = Engine_Api::_()->getDbTable('testanswers', 'courses')->getAnswersSelect(array('testquestion_id' => $question->testquestion_id, 'fetchAll' => true));
        $testDetails['formFields'][$counter]['label'] = $question->question;
        if (($question->answer_type == 2) || ($question->answer_type == 3)) :
          $answer_type = 'radio';
          if ($question->answer_type == 2) {
            $answer_type = 'radio';
            $name = 'radio_' . $question->testquestion_id;
          } else if ($question->answer_type == 3) {
            $answer_type = 'checkbox';
            $name = 'checkbox_' . $question->testquestion_id;
          }
          $testDetails['formFields'][$counter]['type'] = $answer_type;
          $testDetails['formFields'][$counter]['name'] = $name;
          $testDetails['formFields'][$counter]['isRequired'] = 0;
          foreach ($answers as $answer) :
            $testDetails['formFields'][$counter]['multiOptions'][$answer->testanswer_id] = $answer->answer;
          endforeach;
        elseif ($question->answer_type == 1) :
          $testDetails['formFields'][$counter]['name'] = 'isTrue_' . $question->testquestion_id;
          foreach ($answers as $answer) :
            if ($answer->is_true) :
              $testDetails['formFields'][$counter]['multiOptions'][$answer->testanswer_id] = $this->view->translate('TRUE');
              $testDetails['formFields'][$counter]['multiOptions'][0] = $this->view->translate('FALSE');
            else :
              $testDetails['formFields'][$counter]['multiOptions'][0] = $this->view->translate('TRUE');
              $testDetails['formFields'][$counter]['multiOptions'][$answer->testanswer_id] = $this->view->translate('FALSE');
            endif;
            continue;
          endforeach;
        endif;
        $counter++;
      endforeach;
    } elseif ($this->_getParam('submit') && $formData) {
      $total_marks = 0;
      $currect_answers = 0;
      $startTime = $this->_getParam('start_time', date('Y-m-d H:i:s'));
      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $passPercentage = is_numeric($settings->getSetting('courses.ptest.pass', 1)) ? $settings->getSetting('courses.ptest.pass', 1) : 1;
      $isPass = 0;
      Zend_Db_Table_Abstract::getDefaultAdapter()->insert('engine4_courses_usertests', array('test_id' => $test->test_id, 'course_id' => $test->course_id, 'test_start' => $startTime, 'test_end' => date('Y-m-d H:i:s'), 'is_passed' => $isPass, 'test_pass_percentage' => $passPercentage, 'user_id' => $viewer_id, 'total_marks' => $total_marks));
      $usertest_id = $db->lastInsertId();
      foreach ($paginator as $question) :
        $is_attempt = 0;
        $is_true = 0;
        if ($question->answer_type == 2) {
          $name = 'radio_' . $question->testquestion_id;
        } else if ($question->answer_type == 3) {
          $name = 'checkbox_' . $question->testquestion_id;
        } else if ($question->answer_type == 1) {
          $name = 'isTrue_' . $question->testquestion_id;
        }
        $formData[$name] = json_decode($formData[$name]);
        if ((!empty($formData[$name]) && isset($formData[$name]))) {
          $is_true = 0;
          $is_attempt = 1;
          $answers = array();
          if (is_array($formData[$name])) {
            if ($question->answer_type == 3) {
              foreach ($formData[$name] as $ids) :
                $testanswer = Engine_Api::_()->getItem('courses_testanswer', $ids);
                if ($testanswer->is_true) {
                  $answers[] = $ids;
                }
              endforeach;
              if ($answers == $formData[$name])
                $is_true = 1;
            } elseif ($question->answer_type == 2 || $question->answer_type == 1) {
              $testanswer = Engine_Api::_()->getItem('courses_testanswer', $formData[$name][0]);
              if (!empty($testanswer)) {
                if ($testanswer->is_true) {
                  $is_true = 1;
                }
              }
            }
          }
          if ($is_true) {
            $total_marks = $total_marks + $question->marks;
            $currect_answers = $currect_answers + 1;
          }
          Zend_Db_Table_Abstract::getDefaultAdapter()->insert('engine4_courses_userquestions', array('test_id' => $test->test_id, 'course_id' => $test->course_id, 'testanswers' => json_encode($formData[$name]), 'user_id' => $viewer_id, 'is_true' => $is_true, 'is_attempt' => $is_attempt, 'testquestion_id' => $question->testquestion_id, 'usertest_id' => $usertest_id));
        }
      endforeach;
      $isPass = (($currect_answers / $test->total_questions) * 100) > $passPercentage ? 1 : 0;
      $usertest = Engine_Api::_()->getItem('courses_usertest', $usertest_id);
      if ($usertest) {
        $usertest->test_start = $isPass;
        $usertest->total_marks = $total_marks;
        $usertest->save();
      }
      $course = Engine_Api::_()->getItem('courses', $test->course_id);
      $courseTitle = '<a href="' . $course->getHref() . '">' . $course->getTitle() . '</a>';
      $result = Engine_Api::_()->courses()->getIsUserTestDetails(array('usertest_id' => $usertest_id, 'test_id' => $test->test_id, 'is_passed' => true));
      if ($result) {
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($course->getOwner(), $viewer, $course, 'courses_test_pass', array('course' => $courseTitle));
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($course->getOwner(), 'courses_test_pass', array('course_title' => $course->getTitle(), 'test_title' => $test->getTitle(), 'object_link' => $course->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      } else {
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($course->getOwner(), $viewer, $course, 'courses_test_fail', array('course' => $courseTitle));
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($course->getOwner(), 'courses_test_fail', array('course_title' => $course->getTitle(), 'test_title' => $test->getTitle(), 'object_link' => $course->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      }
      $value['test_id'] = $test->test_id;
      $value['usertest_id'] = $usertest_id;
      $paginator = Engine_Api::_()->getDbTable('usertests', 'courses')->getUserTestPaginator($value);
      $testDetails['test']['usertest'] = $this->getTestQuestion($paginator);
    }
    $extraParams = array();
    $testDetails['test'] = array_merge($test->toArray(), array('usertest_id' => $usertest_id));
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $testDetails), $extraParams));
  }
	public function myTestAction() {
    $viewer = $this->view->viewer();
    $viewerId = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if(isset($_POST['searchParams']) && $_POST['searchParams']){
      parse_str($_POST['searchParams'], $searchArray);
    }
    $value['title'] = isset($searchArray['title']) ? $searchArray['title'] : '';
    $value['is_passed'] = isset($searchArray['is_passed']) ? $searchArray['is_passed'] : '';
    $value['date_from'] = isset($searchArray['date']['date_from']) ? $searchArray['date']['date_from'] : '';
    $value['date_to'] = isset($searchArray['date']['date_to']) ? $searchArray['date']['date_to'] : '';
    $value['user_id'] = $viewerId;
    $tests = Engine_Api::_()->getDbtable('usertests', 'courses')->manageTest($value);
    $paginator = Zend_Paginator::factory($tests);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $paginator->setCurrentPageNumber($this->_getParam('page',1));
    $result = array();
    $counter = 0;
    foreach ($paginator as $usertest) {
      $result['tests'][$counter] = $usertest->toArray(); 
      $test = Engine_Api::_()->getItem('courses_test', $usertest->test_id);
      if($test){
        $result['tests'][$counter]['title'] = $test->getTitle();
        $result['tests'][$counter]['owner_name'] = $test->getOwner()->getTitle();
        $result['tests'][$counter]['test_time'] = $test->test_time;
        $result['tests'][$counter]['total_questions'] = $test->total_questions;
      }
      $result['tests'][$counter]['can_give_test'] = 0;
      $result['tests'][$counter]['show_result'] = 1;
      $menuoptions= array();
      $menucounter = 0;
      $menuoptions[$menucounter]['name'] = "view";
      $menuoptions[$menucounter]['label'] = $this->view->translate("View");
      $menucounter++;
      $menuoptions[$menucounter]['name'] = "print";
      $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
      $menucounter++;
      $result['tests'][$counter]['menus'] = $menuoptions;
      $counter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function viewResultAction() {
    $value['test_id'] = $this->_getParam('test_id',false);
    $value['usertest_id'] = $this->_getParam('usertest_id',false);    
    $value['order'] = true;
    $viewer = $this->view->viewer();
    $viewerId = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    if(isset($_POST['searchParams']) && $_POST['searchParams']){
      parse_str($_POST['searchParams'], $searchArray);
    }
    if(!$value['usertest_id'])
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $usertest = Engine_Api::_()->getItem('courses_usertest',$value['usertest_id']);
    if(!$usertest)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $paginator = Engine_Api::_()->getDbTable('usertests', 'courses')->getUserTestPaginator($value,array('usertest_id','test_id'));
    $test = Engine_Api::_()->getItem('courses_test', $value['test_id']);
    if(!$test)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $result = $this->getTestQuestion($paginator);
    $result['testresult'] = $usertest->toArray();
    $result['testresult']['title'] = $test->getTitle();
    $result['testresult']['test_question_count'] = Engine_Api::_()->getDbTable('testquestions', 'courses')->countQuestions($value['test_id']);
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function getTestQuestion($paginator) {
    $settings = Engine_Api::_()->getApi('settings', 'core'); 
		$setting = array_flip($settings->getSetting('courses.result.tests', 1));
		$counter = 0;
    foreach ($paginator as $question) {
      $testquestion = Engine_Api::_()->getItem('courses_testquestion',$question->testquestion_id);
      $result['usertest'][$counter] = $question->toArray(); 
      $result['usertest'][$counter]['testquestion'] = $testquestion->toArray(); 
      $test = Engine_Api::_()->getItem('courses_test', $question->test_id);
      if($test){
        $result['usertest'][$counter]['title'] = $test->getTitle();
        $result['usertest'][$counter]['owner_name'] = $test->getOwner()->getTitle();
        $result['usertest'][$counter]['test_time'] = $test->test_time;
      }
      $isTrue = $question->is_true ? 'application/modules/Courses/externals/images/tick.png':'application/modules/Courses/externals/images/cross.png';
      $providedAnswerId = 0;
      if(is_array(json_decode($question->testanswers,true))): 
        $providedAnswers = Engine_Api::_()->getItemMulti('courses_testanswer',json_decode($question->testanswers,true));
        if(!empty($providedAnswers)):
          foreach($providedAnswers as $providedAnswer):
            $result['usertest'][$counter]['testquestion']['providedAnswers'][$providedAnswerId]['answer'] = $providedAnswer->answer;
            $result['usertest'][$counter]['testquestion']['providedAnswers'][$providedAnswerId]['image'] = $isTrue;
            $result['usertest'][$counter]['testquestion']['providedAnswers'][$providedAnswerId]['is_true'] = $question->is_true;
            $providedAnswerId++;
          endforeach; 
        endif; 
      elseif(is_numeric(json_decode($question->testanswers,true))):
        $providedAnswer = Engine_Api::_()->getItem('courses_testanswer',json_decode($question->testanswers,true)); 
        $result['usertest'][$counter]['testquestion']['providedAnswers'][$providedAnswerId]['answer'] = $providedAnswer->answer;
        $result['usertest'][$counter]['testquestion']['providedAnswers'][$providedAnswerId]['image'] = $isTrue;
        $result['usertest'][$counter]['testquestion']['providedAnswers'][$providedAnswerId]['is_true'] = $question->is_true;
        $providedAnswerId++;
      endif; 
      if(isset($setting['result'])) { 
        $currectAnswerId = 0;
        $currectAnswers = Engine_Api::_()->getDbTable('testanswers', 'courses')->getAnswersPaginator(array('testquestion_id'=>$question->testquestion_id,'is_true'=>true));
        foreach($currectAnswers as $currectAnswer): 
          $result['usertest'][$counter]['testquestion']['currectAnswer'][$currectAnswerId]['answer'] = $currectAnswer->answer;
          $currectAnswerId++;
        endforeach; 
      } 
      $counter++;
    }
    if(isset($setting['print'])):
      $result['tests']['menus'][0]['name'] = "print";
      $result['tests']['menus'][0]['label'] = $this->view->translate("Print");
    endif; 
    return $result;
  }
  public function addQuestionAction() { 
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $testId = $this->_getParam('test_id',false);
    if(!$testId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $test = Engine_Api::_()->getItem('courses_test', $testId);
		if(!$test){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		
    $course = Engine_Api::_()->getItem('courses', $test->course_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->form = $form = new Courses_Form_Test_Addquestion();
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'courses_testquestion'));
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    $questionTable = Engine_Api::_()->getDbTable('testquestions', 'courses');
    $db = $questionTable->getAdapter();
    $db->beginTransaction();
    try {
      $question = $questionTable->createRow();
      $question->setFromArray(array_merge(array(
          'test_id' => $testId,
          'course_id'=>$test->course_id), $form->getValues()));
      $test->total_questions++;
      $test->save();
      $question->save();
      $db->commit();
      $result['question'] = $question->toArray();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result,'message' => $this->view->translate('The Question has been added in selected test.')));
    }catch(Exception $e){
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function editQuestionAction() {
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $questionId = $this->_getParam('question_id',false);
    if(!$questionId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $question = Engine_Api::_()->getItem('courses_testquestion', $questionId);
    if(!$question){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
    $form = new Courses_Form_Test_Editquestion(array('isEdit'=>1));
    $form->populate($question->toArray());
		if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		//is post
		if (!$form->isValid($this->getRequest()->getPost())) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
		}
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
        $question->setFromArray($form->getValues());
        $question->save();
        $db->commit();
        $result['question'] = $question->toArray();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result,'message' => $this->view->translate('The Question has been edited in selected test.')));
    }catch(Exception $e){
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function deleteQuestionAction(){
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $questionId = $this->_getParam('question_id',false);
    if(!$questionId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $question = Engine_Api::_()->getItem('courses_testquestion', $questionId);
    if(!$question){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try
    {
      $test = Engine_Api::_()->getItem('courses_test', $question->test_id);
      $test->total_questions--;
      $test->save();
      $question->delete();
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Your Question has been  Deleted successfully'),'status' => true)));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function addAnswerAction() { 
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $questionId = $this->_getParam('question_id',false);
    if(!$questionId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $question = Engine_Api::_()->getItem('courses_testquestion', $questionId);
		if(!$question){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
    $course = Engine_Api::_()->getItem('courses', $question->course_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    $form = new Courses_Form_Test_AddAnswer(array('question'=>$question));
    $form->removeElement('cancel');
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'courses_testquestion'));
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }
    $answerTable = Engine_Api::_()->getDbTable('testanswers', 'courses');
    $db = $answerTable->getAdapter();
    $db->beginTransaction();
    try {
      $answerTable = $answerTable->createRow();
      $answerTable->setFromArray(array_merge(array(
          'test_id' => $question->test_id,'course_id'=>$question->course_id,'testquestion_id'=>$question->testquestion_id), $form->getValues()));
      $question->total_options++;
      $question->save();
      $answerTable->save();
      $db->commit();
      $result['answer'] = $answerTable->toArray();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result,'message' => $this->view->translate('The Option has been added to selected Question.')));
    }catch(Exception $e){
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function editAnswerAction() {
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $answerId = $this->_getParam('answer_id',false);
    if(!$answerId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $answer = Engine_Api::_()->getItem('courses_testanswer', $answerId);
    if(!$answer){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$question = Engine_Api::_()->getItem('courses_testquestion', $answer->testquestion_id);
    $form = new Courses_Form_Test_EditAnswer(array('question'=>$question,'isTrue'=> $answer->is_true));
    $form->populate($answer->toArray());
		if ($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields, array('resources_type' => 'courses'));
    }
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		//is post
		if (!$form->isValid($this->getRequest()->getPost())) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
		}
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
        $answer->setFromArray($form->getValues());
        $answer->save();
        $db->commit();
        $result['answer'] = $answer->toArray();
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $result,'message' => $this->view->translate('The Answer has been edited to selected Question.')));
    }catch(Exception $e){
        $db->rollBack();
        throw $e;
    }
  }
  public function deleteAnswerAction(){
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $answerId = $this->_getParam('answer_id',false);
    if(!$answerId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $answer = Engine_Api::_()->getItem('courses_testanswer', $answerId);
    if(!$answer){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try
    {
      $question = Engine_Api::_()->getItem('courses_testquestion', $answer->testquestion_id);
      $question->total_options--;
      $question->save();
      $answer->delete();
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Your Option has been  Deleted successfully'),'status' => true)));
    } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  public function getStateAction(){ 
      $country_id = $this->_getParam('country_id');
      if(!$country_id)
      {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Choose Country.'), 'result' => array()));
      }
      $states = Engine_Api::_()->getDbTable('states','courses')->getStates(array('country_id'=>$country_id));
      $results = array('' => 'Select State');
      foreach($states as $state){
          $results[$state['state_id']] = $state['name'];
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $results));
  }
  public function billingAction() {
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $addressTable = Engine_Api::_()->getDbTable('addresses','courses');
    $billingAddressArray = $addressTable->getAddress(array('user_id'=>$viewer_id,'type'=>0));
    $this->view->form = $form = new Courses_Form_Billing();
    if(count($billingAddressArray)){
        $this->view->country_id = $billingAddressArray->country;
        $this->view->state_id = $billingAddressArray->state;
        $form->populate($billingAddressArray->toArray());
    }
    $form->setTitle('Billing form');
    $form->setAttrib('id', 'courses_billing_form');
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'courses'));
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    if($this->getRequest()->isPost())
    {
      if(!count($billingAddressArray)){
          $billing = $addressTable->createRow();
          $billing->setFromArray($_POST);
          $billing->type = 0;
          $billing->user_id = $viewer_id;
          $billing->save();
      }
      else{
          $billing = $billingAddressArray;
          $billing->setFromArray($_POST);
          $billing->type = 0;
          $billing->user_id = $viewer_id;
          $billing->save();
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message' => $this->view->translate('Billing Address added successfully.'))));
    }
  }
  public function myWishlistsAction() {
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $is_search_ajax = $this->_getParam('is_search_ajax', null) ? $this->_getParam('is_search_ajax') : false;
    $this->_helper->content->setEnabled();
    $this->view->formFilter = $formFilter = new Courses_Form_Admin_Wishlist();
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
    $values = $formFilter->getValues();
    $values = array_merge(array(
    'order' => isset($_GET['order']) ? $_GET['order'] :'',
    'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
    ), $values);
    if (isset($_POST['searchParams']) && $_POST['searchParams'])
        parse_str($_POST['searchParams'], $searchArray);
    $this->view->assign($values);
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $wishlistTable = Engine_Api::_()->getDbTable('wishlists', 'courses');
    $wishlistTableName = $wishlistTable->info('name');
    $select = $wishlistTable->select()
                        ->setIntegrityCheck(false)
                        ->from($wishlistTableName)
                        ->where($wishlistTableName.'.owner_id = ?',$viewer_id)
                        ->joinLeft($tableUserName, "$wishlistTableName.owner_id = $tableUserName.user_id", 'username')
                        ->order((!empty($_GET['order']) ? $_GET['order'] : 'wishlist_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));
    if (!empty($searchArray['name']))
        $select->where($wishlistTableName . '.title LIKE ?', '%' . $searchArray['name'] . '%');
    if (!empty($searchArray['owner_name']))
        $select->where($tableUserName . '.displayname LIKE ?', '%' . $searchArray['owner_name'] . '%');
    if (isset($searchArray['is_featured']) && $searchArray['is_featured'] != '')
        $select->where($wishlistTableName . '.is_featured = ?', $searchArray['is_featured']);
    if (isset($searchArray['is_sponsored']) && $searchArray['is_sponsored'] != '')
        $select->where($wishlistTableName . '.is_sponsored = ?', $searchArray['is_sponsored']);
    if (isset($searchArray['offtheday']) && $searchArray['offtheday'] != '')
        $select->where($wishlistTableName . '.offtheday = ?', $searchArray['offtheday']);
    if (isset($searchArray['rating']) && $searchArray['rating'] != '') {
        if ($searchArray['rating'] == 1):
            $select->where($wishlistTableName . '.rating <> ?', 0);
        elseif ($searchArray['rating'] == 0 && $searchArray['rating'] != ''):
            $select->where($wishlistTableName . '.rating = ?', $searchArray['rating']);
        endif;
    }
    if (!empty($searchArray['order_max']))
    $select->having("$wishlistTableName . '.creation_date <=?", $searchArray['order_max']);
    if (!empty($searchArray['order_min']))
    $select->having("$wishlistTableName . '.creation_date >=?", $searchArray['order_min']);
    if (isset($searchArray['subcat_id'])) {
        $formFilter->subcat_id->setValue($searchArray['subcat_id']);
        $this->view->category_id = $searchArray['category_id'];
    }
    if (isset($searchArray['subsubcat_id'])) {
            $formFilter->subsubcat_id->setValue($searchArray['subsubcat_id']);
            $this->view->subcat_id = $searchArray['subcat_id'];
    }
    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey=>$urlParamsVal){
    if($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
        $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $paginator = Zend_Paginator::factory($select);
    $result = array();
    $counter = 0;
    foreach ($paginator as $results) {
      $item = $results->toArray();
      $result['wishlists'][$counter] = $item;
      $result['wishlists'][$counter]['images']['main']= $this->getBaseUrl(true, $results->getPhotoUrl());
      $result['wishlists'][$counter]['owner_name'] = $results->getOwner()->getTitle();
      if(!empty($viewer_id) && $viewer_id == $results->owner_id) {
          $menuoptions= array();
          $menucounter = 0;
          $menuoptions[$menucounter]['name'] = "edit";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Edit");
          $menucounter++;
          $menuoptions[$menucounter]['name'] = "delete";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
          $menucounter++;
          $result['wishlists'][$counter]['menus'] = $menuoptions;
      }
      $counter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function myOrderAction() {
    $viewer = $this->view->viewer();
    $viewerId = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $viewer = $this->view->viewer();
    if(isset($_POST['searchParams']) && $_POST['searchParams']){
      parse_str($_POST['searchParams'], $searchArray);
    }
   // $searchForm = new Courses_Form_Searchorder();
    $value['order_id'] = isset($searchArray['order_id']) ? $searchArray['order_id'] : '';
    $value['buyer_name'] = isset($searchArray['buyer_name']) ? $searchArray['buyer_name'] : '';
    $value['date_from'] = isset($searchArray['date']['date_from']) ? $searchArray['date']['date_from'] : '';
    $value['date_to'] = isset($searchArray['date']['date_to']) ? $searchArray['date']['date_to'] : '';
    $value['order_min'] = isset($searchArray['order']['order_min']) ? $searchArray['order']['order_min'] : '';
    $value['order_max'] = isset($searchArray['order']['order_max']) ? $searchArray['order']['order_max'] : '';
    $value['commision_min'] = isset($searchArray['commision']['commision_min']) ? $searchArray['commision']['commision_min'] : '';
    $value['commision_max'] = isset($searchArray['commision']['commision_max']) ? $searchArray['commision']['commision_max'] : '';
    $value['gateway'] = isset($searchArray['gateway']) ? $searchArray['gateway'] : '';
    $value['user_id'] = $viewerId;
    $orders = Engine_Api::_()->getDbtable('orders', 'courses')->manageOrders($value);
    $paginator = Zend_Paginator::factory($orders);
    $result = array();
    $counter = 0;
    foreach ($paginator as $order) {
      $user = Engine_Api::_()->getItem('user', $order->user_id);
      $result['orders'][$counter] = $order->toArray();
      $result['orders'][$counter]["total"] = Engine_Api::_()->courses()->getCurrencyPrice(round($order->total,2));
      $result['orders'][$counter]['status'] = $order->state;
      $menuoptions= array();
      $menucounter = 0;
      $menuoptions[$menucounter]['name'] = "view";
      $menuoptions[$menucounter]['label'] = $this->view->translate("View Order");
      $menucounter++;
      $menuoptions[$menucounter]['name'] = "print";
      $menuoptions[$menucounter]['label'] = $this->view->translate("Print");
      $menucounter++;
      $result['orders'][$counter]['menus'] = $menuoptions;
      $counter++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  public function viewOrderAction() {
    $order_id = $this->_getParam('order_id', null);
    if(!$order_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    $orderCourseTable = Engine_Api::_()->getDbTable('ordercourses','courses');
    $order = Engine_Api::_()->getItem('courses_order', $order_id);
    $orderedCourses = $orderCourseTable->orderCourses(array('order_id'=>$order->order_id));
    $viewer = Engine_Api::_()->user()->getViewer();
    foreach($orderedCourses as $orderedCourse):
      $result['order']['courses'] = $orderedCourse->toArray();
    endforeach;
    $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
    $result['order']['courses']['currency'] = $curArr[$currency];
    $orderaddressTable = Engine_Api::_()->getDbTable('orderaddresses','courses');
    $billingAddress = $orderaddressTable->getAddress(array('user_id'=>$order->user_id,'order_id'=>$order->order_id,'view'=>true));
    $result['order']['billing_address']['name'] = $billingAddress->first_name.' '.$billingAddress->last_name;
    $result['order']['billing_address']['address'] = $billingAddress->address;
    $result['order']['billing_address']['email'] = $billingAddress->email; 
    $result['order']['billing_address']['email'] = $billingAddress->phone_number;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $result));
	}
  public function YoutubeVideoInfo($video_id) {
    $url = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key=AIzaSyDYwPzLevXauI-kTSVXTLroLyHEONuF9Rw&part=snippet,contentDetails';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response);
        return  $response_a->items[0]->contentDetails->duration; //get video duaration
      }
  public function handleIframelyInformation($uri) {
    $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('courses_iframely_disallow');
    if (parse_url($uri, PHP_URL_SCHEME) === null) {
      $uri = "http://" . $uri;
    }
    $uriHost = Zend_Uri::factory($uri)->getHost();
    if ($iframelyDisallowHost && in_array($uriHost, $iframelyDisallowHost)) {
      return;
    }
    $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
    $iframely = Engine_Iframely::factory($config)->get($uri);
    if (!in_array('player', array_keys($iframely['links']))) {
      return;
    }
    $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
    if (!empty($iframely['links']['thumbnail'])) {
      $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
      if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
        $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
        $information['thumbnail'] = "http://" . $information['thumbnail'];
      }
        }//canonical
        if (!empty($iframely['meta']['title'])) {
          $information['title'] = $iframely['meta']['title'];
        }
        if (!empty($iframely['meta']['description'])) {
          $information['description'] = $iframely['meta']['description'];
        }
        if (!empty($iframely['meta']['duration'])) {
          $information['duration'] = $iframely['meta']['duration'];
        } else {
          $video_id = explode("?v=", $iframely['meta']['canonical']);
          $video_id = $video_id[1];
          $information['duration'] = $this->YoutubeVideoInfo($video_id);
        }
        if(!empty($information['duration']))
          $information['duration'] = Engine_Date::convertISO8601IntoSeconds($information['duration']);
        $information['status'] = 1;
        $information['code'] = $iframely['html'];
        return $information;
      }
  public function subcategoryAction() {
    $category_id = $this->_getParam('category_id', null);
    if ($category_id) {
      $subcategory = Engine_Api::_()->getDbTable('categories', 'courses')->getModuleSubcategory(array('category_id' => $category_id, 'column_name' => '*'));
      $count_subcat = count($subcategory->toarray());
      if ($subcategory && $count_subcat) {
        $data = array('' => '');
        foreach ($subcategory as $category) {
          $data[$category['category_id']] = $category['category_name'];
        }
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
    } else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
  }
}
