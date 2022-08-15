<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: IndexController.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_IndexController extends Core_Controller_Action_Standard {


  public function currencyConverterAction() {

    //default currency
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $defaultCurrency = $settings->getSetting('sesbasic.defaultcurrency', 'USD');
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;
    if ($is_ajax) {
      $curr = $this->_getParam('curr', 'USD');
      $val = $this->_getParam('val', '1');
      $currencyVal = $settings->getSetting('sesbasic.' . $curr);
      echo round($currencyVal*$val,2);die;
    }

    //currecy Array
    $fullySupportedCurrenciesExists = array();
    $fullySupportedCurrencies = Engine_Api::_()->sesbasic()->getSupportedCurrency();
    foreach ($fullySupportedCurrencies as $key => $values) {
      if ($settings->getSetting('sesbasic.' . $key))
        $fullySupportedCurrenciesExists[$key] = $values;
    }
    $this->view->form = $form = new Sesbasic_Form_Conversion();
    $form->currency->setMultioptions($fullySupportedCurrenciesExists);
    $form->currency->setValue($defaultCurrency);
  }

  //get user account details
  public function accountDetailsAction() {

    //Set up navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_settings');

    $viewer = Engine_Api::_()->user()->getViewer();

    $userGateway = Engine_Api::_()->getDbtable('usergateways', 'sesbasic')->getUserGateway(array('user_id' => $viewer->getIdentity(),'enabled' => true));

		$settings = Engine_Api::_()->getApi('settings', 'core');
    $userGatewayEnable = 'paypal';
		$this->view->form = $form = new Sesbasic_Form_PayPal();
		$gatewayTitle = 'Paypal';
		$gatewayClass= 'Sesbasic_Plugin_Gateway_PayPal';

    if (count($userGateway)) {
      $form->populate($userGateway->toArray());
      if (is_array($userGateway['config'])) {
        $form->populate($userGateway['config']);
      }
    }

    if (!$this->getRequest()->isPost())
      return;
    // Not post/invalid
    if (!$this->getRequest()->isPost() || $is_ajax_content)
      return;
    if (!$form->isValid($this->getRequest()->getPost()) || $is_ajax_content)
      return;
    // Process
    $values = $form->getValues();
    $enabled = (bool) $values['enabled'];
    unset($values['enabled']);

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $userGatewayTable = Engine_Api::_()->getDbtable('usergateways', 'sesbasic');
    // insert data to table if not exists
    try {
      if (!count($userGateway)) {
        $gatewayObject = $userGatewayTable->createRow();
        $gatewayObject->user_id = $viewer->getIdentity();
        $gatewayObject->title = $gatewayTitle;
        $gatewayObject->plugin = $gatewayClass;
        $gatewayObject->save();
      } else {
        $gatewayObject = Engine_Api::_()->getItem("sesbasic_usergateway", $userGateway['usergateway_id']);
      }
      $db->commit();
    } catch (Exception $e) {
      echo $e->getMessage();
    }
    // Validate gateway config
    if ($enabled) {
      $gatewayObjectObj = $gatewayObject->getGateway();
      try {
        $gatewayObjectObj->setConfig($values);
        $response = $gatewayObjectObj->test();
      } catch (Exception $e) {
        $enabled = false;
        $form->populate(array('enabled' => false));
        $form->addError(sprintf('Gateway login failed. Please double check ' .
                        'your connection information. The gateway has been disabled. ' .
                        'The message was: [%2$d] %1$s', $e->getMessage(), $e->getCode()));
      }
    } else {
      $form->addError('Gateway is currently disabled.');
    }
    // Process
    $message = null;
    try {
      $values = $gatewayObject->getPlugin()->processAdminGatewayForm($values);
    } catch (Exception $e) {
      $message = $e->getMessage();
      $values = null;
    }
    if (null !== $values) {
      $gatewayObject->setFromArray(array(
          'enabled' => $enabled,
          'config' => $values,
      ));
			//echo "asdf<pre>";var_dump($gatewayObject);die;
      $gatewayObject->save();
      $form->addNotice('Changes saved.');
    } else {
      $form->addError($message);
    }
  }

	public function externalPhotoAction()
  {
		if( !Engine_Api::_()->core()->hasSubject() ) {
      // Can specifiy custom id
      $id = $this->_getParam('id', null);
      $subject = null;
      if( null === $id ) {
        $subject = Engine_Api::_()->user()->getViewer();
        Engine_Api::_()->core()->setSubject($subject);
      } else {
        $subject = Engine_Api::_()->getItem('user', $id);
        Engine_Api::_()->core()->setSubject($subject);
      }
    }

    if( !empty($id) ) {
      $params = array('id' => $id);
    } else {
      $params = array();
    }
    // Set up navigation
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('user_edit', array('params' => $params));

    // Set up require's
    $this->_helper->requireUser();
    $this->_helper->requireSubject('user');
    $this->_helper->requireAuth()->setAuthParams(
      null,
      null,
      'edit'
    );
    if( !$this->_helper->requireSubject()->isValid() ) return;
    $user = Engine_Api::_()->core()->getSubject();

    // Get photo
    $photo = Engine_Api::_()->getItemByGuid($this->_getParam('photo'));
    if( !$photo || !($photo instanceof Core_Model_Item_Abstract) || !$photo->getIdentity() ) {
      $this->_forward('requiresubject', 'error', 'core');
      return;
    }

    if( !$photo->authorization()->isAllowed(null, 'view') ) {
      $this->_forward('requireauth', 'error', 'core');
      return;
    }


    // Make form
    $this->view->form = $form = new User_Form_Edit_ExternalPhoto();
    $this->view->photo = $photo;

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    $db = $user->getTable()->getAdapter();
    $db->beginTransaction();

    try {
      // Get the owner of the photo
      $photoOwnerId = null;
      if( isset($photo->user_id) ) {
        $photoOwnerId = $photo->user_id;
      } else if( isset($photo->owner_id) && (!isset($photo->owner_type) || $photo->owner_type == 'user') ) {
        $photoOwnerId = $photo->owner_id;
      }

      // if it is from your own profile album do not make copies of the image
      if( $photo instanceof Album_Model_Photo &&
          ($photoParent = $photo->getParent()) instanceof Album_Model_Album &&
          $photoParent->owner_id == $photoOwnerId &&
          $photoParent->type == 'profile' ) {

        // ensure thumb.icon and thumb.profile exist
        $newStorageFile = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
        if( $photo->file_id == $filesTable->lookupFile($photo->file_id, 'thumb.profile') ) {
          try {
            $tmpFile = $newStorageFile->temporary();
            $image = Engine_Image::factory();
            $image->open($tmpFile)
              ->resize(200, 400)
              ->write($tmpFile)
              ->destroy();
            $iProfile = $filesTable->createFile($tmpFile, array(
              'parent_type' => $user->getType(),
              'parent_id' => $user->getIdentity(),
              'user_id' => $user->getIdentity(),
              'name' => basename($tmpFile),
            ));
            $newStorageFile->bridge($iProfile, 'thumb.profile');
            @unlink($tmpFile);
          } catch( Exception $e ) { echo $e; die(); }
        }
        if( $photo->file_id == $filesTable->lookupFile($photo->file_id, 'thumb.icon') ) {
          try {
            $tmpFile = $newStorageFile->temporary();
            $image = Engine_Image::factory();
            $image->open($tmpFile);
            $size = min($image->height, $image->width);
            $x = ($image->width - $size) / 2;
            $y = ($image->height - $size) / 2;
            $image->resample($x, $y, $size, $size, 48, 48)
              ->write($tmpFile)
              ->destroy();
            $iSquare = $filesTable->createFile($tmpFile, array(
              'parent_type' => $user->getType(),
              'parent_id' => $user->getIdentity(),
              'user_id' => $user->getIdentity(),
              'name' => basename($tmpFile),
            ));
            $newStorageFile->bridge($iSquare, 'thumb.icon');
            @unlink($tmpFile);
          } catch( Exception $e ) { echo $e; die(); }
        }

        // Set it
        $user->photo_id = $photo->file_id;
        $user->save();

        // Insert activity
        // @todo maybe it should read "changed their profile photo" ?
        $action = Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $user, 'profile_photo_update',
                '{item:$subject} changed their profile photo.');
        if( $action ) {
          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbtable('actions', 'activity')
              ->attachActivity($action, $photo);
        }
      }

      // Otherwise copy to the profile album
      else {
        $user->setPhoto($photo);

        // Insert activity
        $action = Engine_Api::_()->getDbtable('actions', 'activity')
            ->addActivity($user, $user, 'profile_photo_update',
                '{item:$subject} added a new profile photo.');

        // Hooks to enable albums to work
        $newStorageFile = Engine_Api::_()->getItem('storage_file', $user->photo_id);
        $event = Engine_Hooks_Dispatcher::_()
          ->callEvent('onUserProfilePhotoUpload', array(
              'user' => $user,
              'file' => $newStorageFile,
            ));

        $attachment = $event->getResponse();
        if( !$attachment ) {
          $attachment = $newStorageFile;
        }

        if( $action  ) {
          // We have to attach the user himself w/o album plugin
          Engine_Api::_()->getDbtable('actions', 'activity')
              ->attachActivity($action, $attachment);
        }
      }

      $db->commit();
    }

    // Otherwise it's probably a problem with the database or the storage system (just throw it)
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Set as profile photo')),
      'smoothboxClose' => true,
    ));
  }

	public function adultAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();
		if($viewer_id == 0) {
			if(!isset($_COOKIE['ses_adult_filter'])) {
				$cookieval = 1;
			} elseif($_COOKIE['ses_adult_filter'] == 1) {
        $cookieval = 0;
			} else {
				$cookieval = 1;
      }

      if(empty($_COOKIE['ses_adult_filter'])){
        setcookie('ses_adult_filter', $cookieval, time() + (86400 * 30), "/");;
      } else {

        setcookie('ses_adult_filter', $cookieval, '-8798', "/");;
        setcookie('ses_adult_filter', $cookieval, time() + (86400 * 30), "/");;
        //$_COOKIE['ses_adult_filter']	= $cookieval;
      }
		} else {
			$getvalue =  Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.allow.adult.content.'.$viewer_id, 1);
			if($getvalue)
				Engine_Api::_()->getApi('settings', 'core')->setSetting('ses.allow.adult.content.'.$viewer_id, 0);
			else
				Engine_Api::_()->getApi('settings', 'core')->setSetting('ses.allow.adult.content.'.$viewer_id, 1);
		}
		echo true;die;
	}

	//get all module photo from Other Part modules
	function allphotoSesCompatibilityCodeAction(){
		$url = $this->_getParam('url',false);
		if(strpos($url,'https') === false && strpos($url,'http') === false){
				$itemUrl = $this->_getParam('url',false);
				$url = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://" ;
				$url = $url.$_SERVER['HTTP_HOST'].$itemUrl;
		}
		$request = new Zend_Controller_Request_Http($url);
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		$routeName=$router->route($request);
		$getParams = $routeName->getParams();
		if(!is_array($getParams)){
			echo json_encode(array('status'=>false));die;
		}
		$getModuleData = Engine_Api::_()->getDbTable('integrateothermodules', 'sesbasic')->getResults(array('column_name'=>'*','module_name'=>$getParams['module']));
		if(!$getModuleData){
			echo json_encode(array('status'=>false));die;
		}
		if(!isset($getModuleData[0]['content_id_photo']) || !isset($getModuleData[0]['content_id_photo']) || !isset($getParams[$getModuleData[0]['content_id_photo']]) || !isset($getParams[$getModuleData[0]['content_id']])){
			echo json_encode(array('status'=>false));die;
		}
		$this->view->child_item_primary = $getModuleData[0]['content_id_photo'];
    $this->view->child_id = $child_id = $getParams[$getModuleData[0]['content_id_photo']];
		$this->view->parent_id = $parent_id = $getParams[$getModuleData[0]['content_id']];
		$this->view->child_item = $child_item = Engine_Api::_()->getItem($getModuleData[0]['content_type_photo'], $child_id);
		$this->view->parent_item = $parent_item = Engine_Api::_()->getItem($getModuleData[0]['content_type'], $parent_id);
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $viewer = Engine_Api::_()->user()->getViewer();
		// send extra params to view for extra URL parameters
		$page = isset($_POST['page']) ? $_POST['page'] : 1;
		$is_ajax = isset($_POST['is_ajax']) ? $_POST['is_ajax'] : 0;
		$params['paginator'] = true;
		$this->view->childItem = $getModuleData[0]['content_type_photo'];
		//FETCH photos
		$paginator = $this->view->allPhotos =  Engine_Api::_()->sesbasic()->SesNextPreviousPhoto($child_item, '>', $getModuleData[0]['content_type_photo'],$getModuleData[0]['content_id_photo'],$getModuleData[0]['content_id'],true);
		$paginator->setItemCountPerPage(30);
		$this->view->limit = ($page-1)*30;
		$this->view->page = $page ;
		$this->view->is_ajax = $is_ajax ;
		$paginator->setCurrentPageNumber($page);
		$this->view->sesplugins = true;
	 	$this->renderScript('index/all-photos.tpl');
	}
	//function to open third party module photo
  public function sesCompatibilityCodeAction(){
		$itemUrl = $this->_getParam('url',false);
		$url = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://" ;
		$url = $url.$_SERVER['HTTP_HOST'].$itemUrl;
		$request = new Zend_Controller_Request_Http($url);
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
		$routeName=$router->route($request);
		$getParams = $routeName->getParams();
		if(!is_array($getParams)){
			echo json_encode(array('status'=>false));die;
		}
		$getModuleData = Engine_Api::_()->getDbTable('integrateothermodules', 'sesbasic')->getResults(array('column_name'=>'*','module_name'=>$getParams['module']));
		if(!$getModuleData){
			echo json_encode(array('status'=>false));die;
		}
		if(!isset($getModuleData[0]['content_id_photo']) || !isset($getModuleData[0]['content_id_photo']) || !isset($getParams[$getModuleData[0]['content_id_photo']]) || !isset($getParams[$getModuleData[0]['content_id']])){
			echo json_encode(array('status'=>false));die;
		}
    $this->view->child_id = $child_id = $getParams[$getModuleData[0]['content_id_photo']];
		$this->view->parent_id = $parent_id = $getParams[$getModuleData[0]['content_id']];
		$this->view->childItemPri = $getModuleData[0]['content_id_photo'];
		$this->view->child_item = $child_item = Engine_Api::_()->getItem($getModuleData[0]['content_type_photo'], $child_id);
		$this->view->parent_item = $parent_item = Engine_Api::_()->getItem($getModuleData[0]['content_type'], $parent_id);
		$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
		/*if(!$parent_item->authorization()->isAllowed($viewer, 'view')){
			$imagePrivateURL = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.private.photo', 1);
		 if(!is_file($imagePrivateURL))
      $imagePrivateURL = 'application/modules/Sesalbum/externals/images/private-photo.jpg';
		 $this->view->imagePrivateURL = $imagePrivateURL;
    }*/
    // get next photo URL
    $this->view->nextPhoto = Engine_Api::_()->sesbasic()->SesNextPreviousPhoto($child_item, '>', $getModuleData[0]['content_type_photo'],$getModuleData[0]['content_id_photo'],$getModuleData[0]['content_id']);
    // get previous photo URL
    $this->view->previousPhoto = Engine_Api::_()->sesbasic()->SesNextPreviousPhoto($child_item, '<', $getModuleData[0]['content_type_photo'],$getModuleData[0]['content_id_photo'],$getModuleData[0]['content_id']);
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$child_item_tablename =  Engine_Api::_()->getItemTable($getModuleData[0]['content_type_photo'])->info('name');
	  $checkColumnViewCount = $db->query('SHOW COLUMNS FROM '.$child_item_tablename.' LIKE \'view_count\'')->fetch();

		if (!empty($checkColumnViewCount) && !$viewer || !$viewer->getIdentity() || !$child_item->isOwner($viewer)) {
      $child_item->view_count = new Zend_Db_Expr('view_count + 1');
      $child_item->save();
    }
		//check user_id || owner_id
		if((isset($parent_item->owner_id) && $parent_item->owner_id == $viewer->getIdentity()) || (isset($parent_item->user_id) && $parent_item->user_id == $viewer->getIdentity())){
			$this->view->canEdit = $canEdit = true;
			$this->view->canDelete = $canDelete = true;
		}
    $this->renderScript('index/ses-imageviewer-advance.tpl');
  }
	public function editDetailAction(){
		$item_id = $this->_getParam('item_id', '0');
		$item_type = $this->_getParam('item_type', '0');
		if($item_id && $item_type){
			$item = Engine_Api::_()->getItem($item_type, $item_id);
			if(isset($item->description))
				$item->description = $_POST['description'];
			if(isset($item->title))
				$item->title = $_POST['title'];
			$item->save();
			echo json_encode(array('status' => true, 'error' => false));die;
		}
		echo json_encode(array('status' => false, 'error' => true));die;
	}
	public function deleteSesAction(){
		$photo_id = $this->_getParam('photo_id', '0');
		$item_type = $this->_getParam('item_type', '0');
		if(!$photo_id || !$item_type)	return;
		$this->view->form = $form = new Sesbasic_Form_Delete();
		 $form->setTitle('Delete Photo?');
		 $form->setDescription('Are you sure that you want to delete this photo?');
		 $form->submit->setLabel('Cancel');
		 if ($this->getRequest()->isPost()) {
				$photo = Engine_Api::_()->getItem($item_type, $photo_id);
				$parent = $photo->getParent();
				$db = Zend_Db_Table_Abstract::getDefaultAdapter();
				$tablename =  Engine_Api::_()->getItemTable($item_type);
				$db->query("DELETE FROM ".$tablename->info('name')." WHERE ".current($tablename->info('primary'))." = $photo_id");
				 return $this->_forward('success', 'utility', 'core', array(
									'messages' => array(Zend_Registry::get('Zend_Translate')->_('Photo deleted successfully.')),
									'layout' => 'default-simple',
									'parentRedirect' => $parent->getHref(),
				));
			}
	}

    public function tellafriendAction() {

        //SET LAYOUT
        $this->_helper->layout->setLayout('default-simple');

        //GET VIEWER
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewr_id = $viewer->getIdentity();

        //GET FORM
        $this->view->form = $form = new Sesbasic_Form_Tellafriend();

        if (!empty($viewr_id)) {
            $value['sender_email'] = $viewer->email;
            $value['sender_name'] = $viewer->displayname;
            $form->populate($value);
        }

        //FORM VALIDATION
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

            //GET EVENT ID AND OBJECT
            $item_id = $this->_getParam('item_id', $this->_getParam('id', null));
						$item_type = $this->_getParam('type', $this->_getParam('item_type', null));
            $item = Engine_Api::_()->getItem($item_type, $item_id);

            //GET FORM VALUES
            $values = $form->getValues();

            //EXPLODE EMAIL IDS
            $reciver_ids = explode(',', $values['reciver_emails']);
            if (!empty($values['send_me'])) {
                $reciver_ids[] = $values['sender_email'];
            }
            $sender_email = $values['sender_email'];
            $heading = $item->getTitle();

            //CHECK VALID EMAIL ID FORMAT
            $validator = new Zend_Validate_EmailAddress();

            if (!$validator->isValid($sender_email)) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid sender email address value'));
                return;
            }

            foreach ($reciver_ids as $reciver_id) {
                $reciver_id = trim($reciver_id, ' ');
                if (!$validator->isValid($reciver_id)) {
                    $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter correct email address of the receiver(s).'));
                    return;
                }
            }
            $sender = $values['sender_name'];
						$message = '';
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SESBASIC_TELLAFRIEND_EMAIL', array(
                'host' => $_SERVER['HTTP_HOST'],
                'sender' => $sender,
                'heading' => $heading,
                'message' => $message ,
                'object_link' => $item->getHref(),
                'email' => $sender_email,
                'queue' => false
            ));
					$item_title = ucfirst(str_replace(array('sesevent_',''),'',$item->getType()));
            $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => true,
                'parentRefreshTime' => '15',
                'format' => 'smoothbox',
                'messages' => Zend_Registry::get('Zend_Translate')->_('Your '.$item_title.' has been shared successfully.')
            ));
        }
    }
	 public function shareAction() {
    if (!$this->_helper->requireUser()->isValid())
      return;
    $type = $this->_getParam('type');
    $id = $this->_getParam('id');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->attachment = $attachment = Engine_Api::_()->getItem($type, $id);
    if (empty($_POST['is_ajax']))
      $this->view->form = $form = new Activity_Form_Share();
    if (!$attachment) {
      // tell smoothbox to close
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it has been removed.');
      $this->view->smoothboxClose = true;
      return $this->render('deletedItem');
    }
    // hide facebook and twitter option if not logged in
    $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
    if (!$facebookTable->isConnected() && empty($_POST['is_ajax'])) {
      $form->removeElement('post_to_facebook');
    }
    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
    if (!$twitterTable->isConnected() && empty($_POST['is_ajax'])) {
      $form->removeElement('post_to_twitter');
    }
    if (empty($_POST['is_ajax']) && !$this->getRequest()->isPost()) {
      return;
    }
    if (empty($_POST['is_ajax']) && !$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
    $db->beginTransaction();
    try {
      // Get body
      if (empty($_POST['is_ajax']))
        $body = $form->getValue('body');
      else
        $body = '';
      // Set Params for Attachment
      $params = array(
          'type' => '<a href="' . $attachment->getHref() . '">' . $attachment->getMediaType() . '</a>',
      );
      // Add activity
      $api = Engine_Api::_()->getDbtable('actions', 'activity');
      //$action = $api->addActivity($viewer, $viewer, 'post_self', $body);
      $action = $api->addActivity($viewer, $attachment->getOwner(), 'share', $body, $params);
      if ($action) {
        $api->attachActivity($action, $attachment);
      }
      $db->commit();
      // Notifications
      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      // Add notification for owner of activity (if user and not viewer)
      if ($action->subject_type == 'user' && $attachment->getOwner()->getIdentity() != $viewer->getIdentity()) {
        $notifyApi->addNotification($attachment->getOwner(), $viewer, $action, 'shared', array(
            'label' => $attachment->getMediaType(),
        ));
      }
      // Preprocess attachment parameters
      if (empty($_POST['is_ajax']))
        $publishMessage = html_entity_decode($form->getValue('body'));
      else
        $publishMessage = '';
      $publishUrl = null;
      $publishName = null;
      $publishDesc = null;
      $publishPicUrl = null;
      // Add attachment
      if ($attachment) {
        $publishUrl = $attachment->getHref();
        $publishName = $attachment->getTitle();
        $publishDesc = $attachment->getDescription();
        if (empty($publishName)) {
          $publishName = ucwords($attachment->getShortType());
        }
        if (($tmpPicUrl = $attachment->getPhotoUrl())) {
          $publishPicUrl = $tmpPicUrl;
        }
        // prevents OAuthException: (#100) FBCDN image is not allowed in stream
        if ($publishPicUrl &&
                preg_match('/fbcdn.net$/i', parse_url($publishPicUrl, PHP_URL_HOST))) {
          $publishPicUrl = null;
        }
      } else {
        $publishUrl = $action->getHref();
      }
      // Check to ensure proto/host
      if ($publishUrl &&
              false === stripos($publishUrl, 'http://') &&
              false === stripos($publishUrl, 'https://')) {
        $publishUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishUrl;
      }
      if ($publishPicUrl &&
              false === stripos($publishPicUrl, 'http://') &&
              false === stripos($publishPicUrl, 'https://')) {
        $publishPicUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishPicUrl;
      }
      // Add site title
      if ($publishName) {
        $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title
                . ": " . $publishName;
      } else {
        $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title;
      }
      // Publish to facebook, if checked & enabled
      if ($this->_getParam('post_to_facebook', false) &&
              'publish' == Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable) {
        try {
          $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
          $facebookApi = $facebook = $facebookTable->getApi();
          $fb_uid = $facebookTable->find($viewer->getIdentity())->current();
          if ($fb_uid &&
                  $fb_uid->facebook_uid &&
                  $facebookApi &&
                  $facebookApi->getUser() &&
                  $facebookApi->getUser() == $fb_uid->facebook_uid) {
            $fb_data = array(
                'message' => $publishMessage,
            );
            if ($publishUrl) {
              $fb_data['link'] = $publishUrl;
            }
            if ($publishName) {
              $fb_data['name'] = $publishName;
            }
            if ($publishDesc) {
              $fb_data['description'] = $publishDesc;
            }
            if ($publishPicUrl) {
              $fb_data['picture'] = $publishPicUrl;
            }
            $res = $facebookApi->api('/me/feed', 'POST', $fb_data);
          }
        } catch (Exception $e) {
          // Silence
        }
      } // end Facebook
      // Publish to twitter, if checked & enabled
      if ($this->_getParam('post_to_twitter', false) &&
              'publish' == Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable) {
        try {
          $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
          if ($twitterTable->isConnected()) {
            // Get attachment info
            $title = $attachment->getTitle();
            $url = $attachment->getHref();
            $picUrl = $attachment->getPhotoUrl();
            // Check stuff
            if ($url && false === stripos($url, 'http://')) {
              $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
            }
            if ($picUrl && false === stripos($picUrl, 'http://')) {
              $picUrl = 'http://' . $_SERVER['HTTP_HOST'] . $picUrl;
            }
            // Try to keep full message
            // @todo url shortener?
            $message = html_entity_decode($form->getValue('body'));
            if (strlen($message) + strlen($title) + strlen($url) + strlen($picUrl) + 9 <= 140) {
              if ($title) {
                $message .= ' - ' . $title;
              }
              if ($url) {
                $message .= ' - ' . $url;
              }
              if ($picUrl) {
                $message .= ' - ' . $picUrl;
              }
            } else if (strlen($message) + strlen($title) + strlen($url) + 6 <= 140) {
              if ($title) {
                $message .= ' - ' . $title;
              }
              if ($url) {
                $message .= ' - ' . $url;
              }
            } else {
              if (strlen($title) > 24) {
                $title = Engine_String::substr($title, 0, 21) . '...';
              }
              // Sigh truncate I guess
              if (strlen($message) + strlen($title) + strlen($url) + 9 > 140) {
                $message = Engine_String::substr($message, 0, 140 - (strlen($title) + strlen($url) + 9)) - 3 . '...';
              }
              if ($title) {
                $message .= ' - ' . $title;
              }
              if ($url) {
                $message .= ' - ' . $url;
              }
            }
            $twitter = $twitterTable->getApi();
            $twitter->statuses->update($message);
          }
        } catch (Exception $e) {
          // Silence
        }
      }
      // Publish to janrain
      if (//$this->_getParam('post_to_janrain', false) &&
              'publish' == Engine_Api::_()->getApi('settings', 'core')->core_janrain_enable) {
        try {
          $session = new Zend_Session_Namespace('JanrainActivity');
          $session->unsetAll();
          $session->message = $publishMessage;
          $session->url = $publishUrl ? $publishUrl : 'http://' . $_SERVER['HTTP_HOST'] . _ENGINE_R_BASE;
          $session->name = $publishName;
          $session->desc = $publishDesc;
          $session->picture = $publishPicUrl;
        } catch (Exception $e) {
          // Silence
        }
      }
    } catch (Exception $e) {
      $db->rollBack();
      throw $e; // This should be caught by error handler
    }
    // If we're here, we're done
    $this->view->status = true;
    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Success!');
    $typeItem = ucwords(str_replace(array('sesvideo_'), '', $attachment->getType()));
    // Redirect if in normal context
    if (null === $this->_helper->contextSwitch->getCurrentContext()) {
      $return_url = $form->getValue('return_url', false);
      if (!$return_url) {
        $return_url = $this->view->url(array(), 'default', true);
      }
      return $this->_helper->redirector->gotoUrl($return_url, array('prependBase' => false));
    } else if ('smoothbox' === $this->_helper->contextSwitch->getCurrentContext()) {
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRefresh' => false,
          'messages' => array($typeItem . ' share successfully.')
      ));
    } else if (isset($_POST['is_ajax'])) {
      echo "true";
      die();
    }
  }
	public function uploadImageAction() {


    $ses_public_path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'sesWysiwygPhotos';

    if (!is_dir($ses_public_path) && mkdir($ses_public_path, 0777, true))
      chmod($ses_public_path, 0777);

    // Prepare
    if (empty($_FILES['userfile'])) {
      $this->view->error = 'File failed to upload. Check your server settings (such as php.ini max_upload_filesize).';
      return;
    }

    $info = $_FILES['userfile'];
    $targetFile = realpath($ses_public_path) . '/' . $info['name'];

    if( !move_uploaded_file($info['tmp_name'], $targetFile) ) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Unable to move file to upload directory.");
      return;
    }

    $this->view->status = 1;

    $this->view->photo_url = ( _ENGINE_SSL ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . Zend_Registry::get('Zend_View')->baseUrl().'/public/sesWysiwygPhotos/' . $info['name'];
  }
	public function getDirectionAction(){
		$resouce_type = $this->_getParam('resource_type');
		$resource_id = $this->_getParam('resource_id',false);
		if(!$resource_id || !$resouce_type)
			return $this->_forward('requireauth', 'error', 'core');
		$latLng = Engine_Api::_()->getDbtable('locations', 'sesbasic')->getLocationData($resouce_type,$resource_id);
		/*if(!$latLng)
			return $this->_forward('requireauth', 'error', 'core');*/
		$this->view->item = Engine_Api::_()->getItem($resouce_type, $resource_id);
    $this->view->location = $latLng;
		$this->view->lat = $latLng->lat ? $latLng->lat : 0;
		$this->view->lng = $latLng->lng ? $latLng->lng : 0;
	}
  public function suggestAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');

    if($this->_getParam('allUser')){
        $select = $table->select()->where('enabled = ?','1');
    }else {
        $select = Engine_Api::_()->user()->getViewer()->membership()->getMembersObjectSelect();
    }

      if( $this->_getParam('includeSelf', true) ) {
        $data[] = array(
          'type' => 'user',
          'id' => $viewer->getIdentity(),
          'guid' => $viewer->getGuid(),
          'label' => $viewer->getTitle() . ' (you)',
          'photo' => $this->view->itemPhoto($viewer, 'thumb.icon'),
          'url' => $viewer->getHref(),
        );
      }

      if( 0 < ($limit = (int) $this->_getParam('limit', 10)) ) {
        $select->limit($limit);
      }

      if( null !== ($text = $this->_getParam('search', $this->_getParam('value'))) ) {
        $select->where('`'.$table->info('name').'`.`displayname` LIKE ?', '%'. $text .'%');
      }
      $ids = array();
      foreach( $select->getTable()->fetchAll($select) as $friend ) {
        $data[] = array(
          'type'  => 'user',
          'id'    => $friend->getIdentity(),
          'guid'  => $friend->getGuid(),
          'label' => $friend->getTitle(),
          'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
          'url'   => $friend->getHref(),
        );
      }
    }

    if( $this->_getParam('sendNow', true) ) {
      return $this->_helper->json($data);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }
}
