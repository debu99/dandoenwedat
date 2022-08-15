<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: ProductController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Estore_ProductController extends Sesapi_Controller_Action_Standard {

	public function init() {

      if (!$this->_helper->requireAuth()->setAuthParams('sesproduct', null, 'view')->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

      $product_id = $this->_getParam('product_id');
      $product = null;
      $product = Engine_Api::_()->getItem('sesproduct', $product_id);
      if ($product) {
        if ($product) {
          Engine_Api::_()->core()->setSubject($product);
        } else {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
      }
    }
  public function createAction() {
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    if( !$this->_helper->requireAuth()->setAuthParams('sesproduct', null, 'create')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        $this->view->storeId = $storeId = $this->_getParam('store_id',false);
        $store = Engine_Api::_()->getItem('stores', $storeId);
		$sessmoothbox = $this->view->typesmoothbox = false;
		if($this->_getParam('typesmoothbox',false)){
      // Render
			$sessmoothbox = true;
			$this->view->typesmoothbox = true;
			//$this->_helper->layout->setLayout('default-simple');
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

    //get all allowed types product
    $viewer = Engine_Api::_()->user()->getViewer();
    $allowed_types = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesproduct', $viewer, 'allowed_types');
    $this->view->allowedTypes = $allowed_types;
		if(!$this->_getParam('type')){
      $this->view->showType = true;
    }

    $session = new Zend_Session_Namespace();
		if(empty($_POST))
		  unset($session->album_id);
    $this->view->defaultProfileId = $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesproduct')->profileFieldId();
    if (isset($sesproduct->category_id) && $sesproduct->category_id != 0) {
      $this->view->category_id = $sesproduct->category_id;
    } else if (isset($_POST['category_id']) && $_POST['category_id'] != 0)
      $this->view->category_id = $_POST['category_id'];
    else
      $this->view->category_id = 0;
    if (isset($sesproduct->subsubcat_id) && $sesproduct->subsubcat_id != 0) {
      $this->view->subsubcat_id = $sesproduct->subsubcat_id;
    } else if (isset($_POST['subsubcat_id']) && $_POST['subsubcat_id'] != 0)
      $this->view->subsubcat_id = $_POST['subsubcat_id'];
    else
      $this->view->subsubcat_id = 0;
    if (isset($sesproduct->subcat_id) && $sesproduct->subcat_id != 0) {
      $this->view->subcat_id = $sesproduct->subcat_id;
    } else if (isset($_POST['subcat_id']) && $_POST['subcat_id'] != 0)
      $this->view->subcat_id = $_POST['subcat_id'];
    else
      $this->view->subcat_id = 0;


    $resource_id = $this->_getParam('resource_id', null);
    $resource_type = $this->_getParam('resource_type', null);

    $parentId = $this->_getParam('parent_id', 0);
		if($parentId && !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.subproduct', 1)){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
		}

    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->getSesproductsPaginator($values);

    $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesproduct', 'max');
    $this->view->current_count = $paginator->getTotalItemCount();

    $this->view->categories = Engine_Api::_()->getDbtable('categories', 'sesproduct')->getCategoriesAssoc();

    // Prepare form
    $this->view->form = $form = new Sesproduct_Form_Create(array('defaultProfileId' => $defaultProfileId,'smoothboxType'=>$sessmoothbox,));
    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'sesproduct'));
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

    if( !$form->isValid($_POST) || $this->_getParam('is_ajax')){

      if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
        $custom_url = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->checkCustomUrl($_POST['custom_url']);
        if ($custom_url) {
          $form->addError($this->view->translate("Custom URL is not available. Please select another URL."));
        }
      }
        if (isset($_POST['sku']) && !empty($_POST['sku'])) {
            $sku = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->checkSKU($_POST['sku']);
            if ($sku) {
                $form->addError($this->view->translate("SKU is not available. Please select another SKU."));
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
      //inventory check
      if(!empty($_POST['manage_stock']) && empty($_POST['stock_quatity'])){
         $form->addError($this->view->translate('Stock Quantity is required.'));
      }
      if(!empty($_POST['manage_stock']) && !empty($_POST['stock_quatity'])){
        if($_POST['stock_quatity'] < $_POST['min_quantity'] || $_POST['stock_quatity'] < $_POST['max_quatity']){
            $form->addError($this->view->translate('Minimum Order Quantity / Maximum Order Quantity must be less than Stock Quantity.'));
        }else if(!empty($_POST['max_quatity']) && $_POST['min_quantity'] > $_POST['max_quatity']){
            $form->addError($this->view->translate('Minimum Order Quantity must be less than Maximum Order Quantity.'));
        }
      }else if(!empty($_POST['max_quatity']) && $_POST['min_quantity'] > $_POST['max_quatity']){
            $form->addError($this->view->translate('Minimum Order Quantity must be less than Maximum Order Quantity.'));
      }
      //avalability check
      if(empty($_POST['show_start_time'])){
        if(empty($_POST['start_date'])){
          //  $form->addError($this->view->translate('Start Time is required.'));
        }else{
          $time = $_POST['start_date'].' '.(!empty($_POST['start_date_time']) ? $_POST['start_date_time'] : "00:00:00");
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($this->view->viewer()->timezone);
          $start = strtotime($time);

          if($start < time()){
             $timeError = true;
             $form->addError($this->view->translate('Start Time must be greater than Current Time.'));
          }
          date_default_timezone_set($oldTz);
        }
      }
      if(!empty($_POST['show_end_time'])){
        if(empty($_POST['end_date'])){
            $form->addError($this->view->translate('End Time is required.'));
        }else{
          $time = $_POST['end_date'].' '.(!empty($_POST['end_date_time']) ? $_POST['end_date_time'] : "00:00:00");
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($this->view->viewer()->timezone);
          $end = strtotime($time);

          if($end < time()){
             $timeError = true;
             $form->addError($this->view->translate('End Time must be greater than Current Time.'));
          }
			date_default_timezone_set($oldTz);
        }
      }
      if(empty($timeError)){
        if(!empty($_POST['show_end_time'])){
           if(empty($_POST['show_start_time'])){
              $starttime = $_POST['start_date'].' '.(!empty($_POST['start_date_time']) ? $_POST['start_date_time'] : "00:00:00");
              $endtime = $_POST['end_date'].' '.(!empty($_POST['end_date_time']) ? $_POST['end_date_time'] : "00:00:00");
              //Convert Time Zone
              $oldTz = date_default_timezone_get();
              date_default_timezone_set($this->view->viewer()->timezone);
              $start = strtotime($starttime);
              $end = strtotime($endtime);

              if($end < $start){
                  $form->addError($this->view->translate('End Time must be greater than Start Time.'));
              }
			date_default_timezone_set($oldTz);
           }
        }
      }
      if(!$this->_getParam('is_ajax')){
        return;
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
        echo json_encode(array('status'=>0,'message'=>'<ul class="form-errors">'.$error."<ul>"));
      else
        echo json_encode(array('status'=>1));
      die;
     }
    //check custom url
    if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
      $custom_url = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->checkCustomUrl($_POST['custom_url']);
      if ($custom_url) {
				$form->addError($this->view->translate("Custom URL is not available. Please select another URL."));
				return;
      }
    }

    // Process
    $table = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
        // $defaultMeter;die;
      // Create sesproduct
      $viewer = Engine_Api::_()->user()->getViewer();
      $values = array_merge($form->getValues(), array(
        'owner_type' => $viewer->getType(),
        'owner_id' => $viewer->getIdentity(),
      ));

      if(isset($values['levels']))
         $values['levels'] = implode(',',$values['levels']);
      if(isset($values['networks']))
         $values['networks'] = implode(',',$values['networks']);
      if(isset($_POST['Height']))
        $values['height'] = $_POST['Height'];
      if(isset($_POST['Width']))
       $values['width'] = $_POST['Width'];
      if(isset($_POST['Length']))
        $values['length'] = $_POST['Length'];
      $values['ip_address'] = $_SERVER['REMOTE_ADDR'];
      $sesproduct = $table->createRow();

      if (is_null($values['subsubcat_id']))
      $values['subsubcat_id'] = 0;
      if (is_null($values['subcat_id']))
      $values['subcat_id'] = 0;
      $values['style'] = !empty($_POST['productstyle']) ? $_POST['productstyle'] : 1;
      //SEO By Default Work
      //$values['seo_title'] = $values['title'];
			//if($values['tags'])
		 //$values['seo_keywords'] = $values['tags'];
      //$values['type'] = "simpleProduct";

      $sesproduct->setFromArray($values);
			//Upload Main Image
			if(isset($_FILES['photo_file']) && $_FILES['photo_file']['name'] != ''){
			  $sesproduct->photo_id = Engine_Api::_()->sesapi()->setPhoto($form->photo_file, false,false,'sesproduct','sesproduct','',$sesproduct,true);
			}

      if(empty($_POST['show_start_time'])){
        if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
          $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_date_time'])) : '';
          $sesproduct->starttime =$starttime;
        }
        if(isset($_POST['start_date']) && $viewer->timezone && $_POST['start_date'] != ''){
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($viewer->timezone);
          $start = strtotime($_POST['start_date'].' '.(!empty($_POST['start_date_time']) ? $_POST['start_date_time'] : "00:00:00"));

          $sesproduct->starttime = date('Y-m-d H:i:s', $start);
			date_default_timezone_set($oldTz);
        }
      }

      if(!empty($_POST['show_end_time'])){
        if(isset($_POST['end_date']) && $_POST['end_date'] != ''){
          $starttime = isset($_POST['end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['end_date'].' '.$_POST['end_date_time'])) : '';
          $sesproduct->endtime =$starttime;
        }
        if(isset($_POST['end_date']) && $viewer->timezone && $_POST['end_date'] != ''){
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($viewer->timezone);
          $start = strtotime($_POST['end_date'].' '.(!empty($_POST['end_date_time']) ? $_POST['end_date_time'] : "00:00:00"));

          $sesproduct->endtime = date('Y-m-d H:i:s', $start);
			date_default_timezone_set($oldTz);
        }
      }

      //discount
      //if(!empty($_POST['show_end_time'])){
        if(isset($_POST['discount_start_date']) && $_POST['discount_start_date'] != ''){
          $starttime = isset($_POST['discount_start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_start_date'].' '.$_POST['discount_start_date_time'])) : '';
          $sesproduct->discount_start_date =$starttime;
        }
        if(isset($_POST['discount_start_date']) && $viewer->timezone && $_POST['discount_start_date'] != ''){
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($viewer->timezone);
          $start = strtotime($_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00"));

          $sesproduct->discount_start_date = date('Y-m-d H:i:s', $start);
			date_default_timezone_set($oldTz);
        }
      //}

      if(!empty($_POST['discount_end_date'])){
        if(isset($_POST['discount_end_date']) && $_POST['discount_end_date'] != ''){
          $starttime = isset($_POST['discount_end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_end_date'].' '.$_POST['discount_end_date_time'])) : '';
          $sesproduct->discount_end_date =$starttime;
        }
        if(isset($_POST['discount_end_date']) && $viewer->timezone && $_POST['discount_end_date'] != ''){
          //Convert Time Zone
          $oldTz = date_default_timezone_get();
          date_default_timezone_set($viewer->timezone);
          $start = strtotime($_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00"));

          $sesproduct->discount_end_date = date('Y-m-d H:i:s', $start);
date_default_timezone_set($oldTz);
        }
      }

      $sesproduct->parent_id = $parentId;
      $sesproduct->save();

      $product_id = $sesproduct->product_id;
      $store->product_count++;
      $store->save();

      if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
        $sesproduct->custom_url = $_POST['custom_url'];
      else
        $sesproduct->custom_url = $sesproduct->product_id;

      $sesproduct->store_id = $storeId;
      $sesproduct->save();

    if(Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesproduct', $viewer, 'product_approve')) {
        $sesproduct->is_approved  = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesproduct', $viewer, 'product_approve');
         $sesproduct->save();
        } else {
        $product = Engine_Api::_()->getItem('sesproduct',$sesproduct->product_id);
        $sesproduct->is_approved = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesproduct', $viewer, 'product_approve');
        $getAdminnSuperAdmins = Engine_Api::_()->sesproduct()->getAdminnSuperAdmins();
        foreach ($getAdminnSuperAdmins as $getAdminnSuperAdmin) {
            $user = Engine_Api::_()->getItem('user', $getAdminnSuperAdmin['user_id']);
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $product, 'sesproduct_product_waitApprove');
        }
        $sesproduct->save();
    }

      $product_id = $sesproduct->product_id;

      $roleTable = Engine_Api::_()->getDbtable('roles', 'sesproduct');
			$row = $roleTable->createRow();
			$row->product_id = $product_id;
			$row->user_id = $viewer->getIdentity();
			$row->save();

			// Other module work
        if(!empty($resource_type) && !empty($resource_id)) {
            $sesproduct->resource_id = $resource_id;
            $sesproduct->resource_type = $resource_type;
            $sesproduct->save();
        }

      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
					Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $product_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesproduct")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }


      if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
				$sesproduct->photo_id = $_POST['cover'];
				$sesproduct->save();
      }

      //upsell
      if(!empty($_POST['upsell_id'])){
        $upsell = trim($_POST['upsell_id'],',');
        $upsells = explode(',',$upsell);
        foreach($upsells as $item){
          $params['product_id'] = $sesproduct->getIdentity();
          $params['resource_id'] = $item;
          Engine_Api::_()->getDbTable('upsells','sesproduct')->create($params);
        }
      }
      //crosssell
      if(!empty($_POST['crosssell_id'])){
        $crosssell = trim($_POST['crosssell_id'],',');
        $crosssells = explode(',',$crosssell);
        foreach($crosssells as $item){
          $params['product_id'] = $sesproduct->getIdentity();
          $params['resource_id'] = $item;
          Engine_Api::_()->getDbTable('crosssells','sesproduct')->create($params);
        }
      }
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
				$customfieldform->setItem($sesproduct);
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
      $videoMax = array_search(isset($values['auth_video']) ? $values['auth_video']: '', $roles);
      $musicMax = array_search(isset($values['auth_music']) ? $values['auth_music']: '', $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($sesproduct, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($sesproduct, $role, 'comment', ($i <= $commentMax));
        $auth->setAllowed($sesproduct, $role, 'video', ($i <= $videoMax));
        $auth->setAllowed($sesproduct, $role, 'music', ($i <= $musicMax));
      }

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
     // $sesproduct->seo_keywords = implode(',',$tags);
      //$sesproduct->seo_title = $sesproduct->title;
      $sesproduct->save();
      $sesproduct->tags()->addTagMaps($viewer, $tags);

      $session = new Zend_Session_Namespace();
      if(!empty($session->album_id)){
				$album_id = $session->album_id;
				if(isset($product_id) && isset($sesproduct->title)){
					Engine_Api::_()->getDbTable('albums', 'sesproduct')->update(array('product_id' => $product_id,'owner_id' => $viewer->getIdentity(),'title' => $sesproduct->title), array('album_id = ?' => $album_id));
					if(isset ($_POST['cover']) && !empty($_POST['cover'])) {
						Engine_Api::_()->getDbTable('albums', 'sesproduct')->update(array('photo_id' => $_POST['cover']), array('album_id = ?' => $album_id));
					}
					Engine_Api::_()->getDbTable('photos', 'sesproduct')->update(array('product_id' => $product_id), array('album_id = ?' => $album_id));
					unset($session->album_id);
				}
      }

      // Add activity only if sesproduct is published
    if( $values['draft'] == 0 && $values['is_approved'] == 1 && $values['enable_product'] == 1 && (!$sesproduct->starttime || strtotime($sesproduct->starttime) <= time())) {

        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesproduct, 'sesproduct_create_product');
        // make sure action exists before attaching the sesproduct to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesproduct);
        }

        if($action && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
            if($sesproduct->store_id){
                $store = Engine_Api::_()->getItem('stores',$sesproduct->store_id);
                $activity = $store;
            }
            $isRowExists = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->action_id);
            if($isRowExists) {
                $details = Engine_Api::_()->getItem('sesadvancedactivity_detail', $isRowExists);
                $details->sesresource_id = $store->getIdentity();
                $details->sesresource_type = $store->getType();
                $details->save();

            }
        }
        //Tag Work
        if($action && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedactivity') && $tags) {
          $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
          foreach($tags as $tag) {
            $dbGetInsert->query('INSERT INTO `engine4_sesadvancedactivity_hashtags` (`action_id`, `title`) VALUES ("'.$action->getIdentity().'", "'.$tag.'")');
          }
        }
        $followers = Engine_Api::_()->getDbtable('followers', 'estore')->getFollowers($sesproduct->store_id);
        $favourites = Engine_Api::_()->getDbtable('favourites', 'estore')->getAllFavMembers($sesproduct->store_id);
        $likes = Engine_Api::_()->getDbtable('likes', 'core')->getAllLikes($sesproduct);
        $followerStore = array();
        $favouriteStore = array();
        $likesStore = array();

        foreach($favourites as $favourite){
            $favouriteStore[$favourite->owner_id] = $favourite->owner_id;
        }
        foreach($followers as $follower){
            $followerStore[$follower->owner_id] = $follower->owner_id;

        }
         foreach($likes as $like){
             $likesStore[$likes->owner_id] =  $likes->owner_id;

        }
        $users = array_unique(array_merge($likesStore ,$followerStore, $favouriteStore), SORT_REGULAR);

        foreach($users as $user){
            $usersOject = Engine_Api::_()->getItem('user', $user);
            $productname = '<a href="'.$sesproduct->getHref().'">'.$sesproduct->getTitle().'</a>';
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($usersOject, $viewer, $store, 'sesproduct_product_creation', array('productname' => $productname));

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($usersOject->email, 'sesproduct_product_creation', array('host' => $_SERVER['HTTP_HOST'], 'product_name' => $productname,'object_link'=>$sesproduct->getHref()));

        }


        //Send notifications for subscribers
      	Engine_Api::_()->getDbtable('subscriptions', 'sesproduct')->sendNotifications($sesproduct);
      	$sesproduct->is_publish = 1;
      	$sesproduct->save();
     }
        $emails = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.emailalert', null);
        if(!empty($emails)) {
            $emailArray = explode(",",$emails);
            foreach($emailArray as $email) {
                $email = str_replace(' ', '', $email);
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'sesproduct_product_creation', array('host' => $_SERVER['HTTP_HOST'], 'product_name' => $productname,'object_link'=>$sesproduct->getHref()));
            }
        }
      // Commit
      $db->commit();
        //insert into attribute table
        Engine_Api::_()->getDbTable('cartoptions','sesproduct')->checkProduct($sesproduct);
    }

    catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $autoOpenSharePopup = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.autoopenpopup', 1);
    if ($autoOpenSharePopup) {
      $_SESSION['newProduct'] = true;
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('product_id' => $sesproduct->getIdentity(),'message' => $this->view->translate('Product created successfully.'))));
    
    
//     $redirect = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.redirect.creation', 1);
//     if(!empty($resource_id) && !empty($resource_type)) {
//       // Other module work
//       $resource = Engine_Api::_()->getItem($resource_type, $resource_id);
//         header('location:' . $resource->getHref());
//       die;
//     } else if($redirect) {
//    	 	return $this->_helper->redirector->gotoRoute(array('action' => 'dashboard','action'=>'edit','product_id'=>$sesproduct->custom_url),'sesproduct_dashboard',true);
//     } else {
// 		 	return $this->_helper->redirector->gotoRoute(array('action' => 'view','product_id'=>$sesproduct->custom_url),'sesproduct_entry_view',true);
//     }
  }
  
	public function reviewAction(){
		$productId = $this->_getParam('product_id');
		$viewer = Engine_Api::_()->user()->getViewer();
        if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$product = $sesproduct = Engine_Api::_()->core()->getSubject();
		}else{
			$product = $sesproduct= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$sesproduct){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.review', 1)){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		if (!Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'view'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.owner', 1)) {
			$allowedCreate = true;
		} else {
			if ($product->product_id == $viewer->getIdentity())
				$allowedCreate = false;
			else
				$allowedCreate = true;
		}
		$cancreate = Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'create');
		

		
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		
		$editReviewPrivacy = Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'edit');
		$reviewTable = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct');
		$isReview = $hasReview = $reviewTable->isReview(array('product_id' => $product->getIdentity(), 'column_name' => 'review_id'));
		
		if($viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.review', 1) && $allowedCreate){
			if($cancreate && !$isReview){
				$result['button']['label'] = $this->view->translate('Write a Review');
				$result['button']['name'] = 'createreview';
			}
			if($editReviewPrivacy && $isReview){
				$result['button']['label'] = $this->view->translate('Update Review');
				$result['button']['name'] = 'updatereview';
			}
		}
		
		$table = Engine_Api::_()->getItemTable('sesproductreview');
		$product_id = $product->getIdentity();
		$params['product_id'] = $product_id;
		$select = $table->getProductReviewSelect($params);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		
		$result['reviews'] = $this->getReviews($paginator,$product);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	
	}
	public function reviewCreateAction() {
        $viewer = Engine_Api::_()->user()->getViewer();
		$productId = $this->_getParam('product_id');
		$viewer = Engine_Api::_()->user()->getViewer();
        if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$product = $sesproduct = Engine_Api::_()->core()->getSubject();
		}else{
			$product = $sesproduct= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$sesproduct){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
        //check review exists
        $isReview = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct')->isReview(array('product_id' => $product->getIdentity(), 'column_name' => 'review_id'));
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.owner', 1)) {
            $allowedCreate = true;
        } else {
            if ($product->owner_id == $viewer->getIdentity())
                $allowedCreate = false;
            else
                $allowedCreate = true;
        }
        if ($isReview || !$allowedCreate)
           Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));



      if ($hasReview && Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'edit')) {
	        $select = $reviewTable->select()
	                ->where('product_id = ?', $product->getIdentity())
	                ->where('owner_id =?', $viewer->getIdentity());
	        $reviewObject = $reviewTable->fetchRow($select);
	        $form = new Sesproduct_Form_Review_Create(array( 'reviewId' => $reviewObject->review_id, 'productItem' =>$product));
	       
	        $form->populate($reviewObject->toArray());
	        $form->rate_value->setvalue($reviewObject->rating);
	        $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesproduct', 'controller' => 'review', 'action' => 'edit', 'review_id' => $reviewObject->review_id), 'default', true));
    	} else {
        	$form = new Sesproduct_Form_Review_Create(array('productItem' =>$product));

      	}
		if ($this->_getParam('getForm')) {
        	$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        	$this->generateFormFields($formFields, array('resources_type' => 'sesproduct'));
    	}
        $values = $_POST;
        $values['rating'] = $_POST['rate_value'];
        $values['owner_id'] = $viewer->getIdentity();
        $values['product_id'] = $product->getIdentity();
        $reviews_table = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct');
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
            $table = Engine_Api::_()->getDbtable('parametervalues', 'sesproduct');
            $tablename = $table->info('name');
            foreach ($_POST as $key => $reviewC) {
                if (count(explode('_', $key)) != 3 || !$reviewC)
                    continue;
                $key = str_replace('review_parameter_', '', $key);
                if (!is_numeric($key))
                    continue;
                $parameter = Engine_Api::_()->getItem('sesproduct_parameter', $key);
                $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $viewer->getIdentity() . '","' . $product->getIdentity() . '","' . $review->getIdentity() . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
                $dbObject->query($query);
                $ratingP = $table->getRating($key);
                $parameter->rating = $ratingP;
                $parameter->save();
            }
            $db->commit();
            //save rating in parent table if exists
            if (isset($product->rating)) {
                $product->rating = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct')->getRating($review->product_id);
                $product->save();
            }
            $review->save();
             
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $product, 'sesproduct_reviewpost');
            if ($action != null) {
                Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $review);
            }

            if ($product->owner_id != $viewer->getIdentity()) {
                $itemOwner = $product->getOwner('user');
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($itemOwner, $viewer, $review, 'sesproduct_reviewpost');
            }

            $db->commit();
            $stats = Engine_Api::_()->sesproduct()->getWidgetParams($viewer->getIdentity());
            $this->view->stats = count($stats) ? $stats : $this->_getParam('stats', array('featured', 'sponsored', 'likeCount', 'commentCount', 'viewCount', 'title', 'postedBy', 'pros', 'cons', 'description', 'creationDate', 'recommended', 'parameter', 'rating'));
            
            if (Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'edit')) {
                $this->view->form = $form = new Sesproduct_Form_Review_Create(array( 'reviewId' => $reviewObject->review_id, 'productItem' => $product));
                $form->populate($reviewObject->toArray());
                $form->rate_value->setvalue($reviewObject->rating);
                $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesproduct', 'controller' => 'review', 'action' => 'edit', 'review_id' => $reviewObject->review_id), 'default', true));
            }
            $this->view->rating_count = Engine_Api::_()->getDbTable('sesproductreviews', 'sesproduct')->ratingCount($product->getIdentity());
            $this->view->rating_sum = $userInfoItem->rating;
			
			
			$msg = $isReview ? $this->view->translate('Review edited successfully.') : $this->view->translate('Review created successfully.');
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('review_id' => $review->getIdentity(), 'message' =>$msg)));
        } catch (Exception $e) {
            $db->rollBack();
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function getReviews($paginator,$product){
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		foreach($paginator as $item){
			$result[$counter] = $item->toArray();
			$owner = $item->getOwner();
			//$result[$counter]['owner_title'] = $owner->getTitle();
			//$result[$counter]['owner_image'] = $this->getBaseUrl(true,$owner->getPhotoUrl());
			
			
			$result[$counter]['product']['images'] = $this->getBaseUrl(true, $product->getPhotoUrl());
			$result[$counter]['product']['title'] = $product->getTitle();
			$result[$counter]['product']['Guid'] = $product->getGuid();
			$result[$counter]['product']['id'] = $product->getIdentity();
			
			$result[$counter]['owner']['id'] = $owner->getIdentity();
			$result[$counter]['owner']['Guid'] = $owner->getGuid();
			$result[$counter]['owner']['title'] = $owner->getTitle();
			$result[$counter]['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
			
			$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'sesproduct')->getParameters(array('content_id'=>$item->getIdentity(),'user_id'=>$item->owner_id));
			$perameterCounter = 0;
			foreach($reviewParameters as $reviewP){ 
				$result[$counter]['review_perameter'][$perameterCounter] = $reviewP->toArray();
				$perameterCounter++;
			}
			
			$result[$counter]['can_show_pros']  = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.show.pros', 1)?true:false;
			
			$result[$counter]['can_show_cons']  = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.show.cons', 1)?true:false;
			
			
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.votes', 1)){
				$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','sesproduct')->isReviewVote(array('review_id'=>$item->getIdentity(),'product_id'=>$product->getIdentity(),'type'=>1));
				
				$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','sesproduct')->isReviewVote(array('review_id'=>$item->getIdentity(),'product_id'=>$product->getIdentity(),'type'=>2));
				$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','sesproduct')->isReviewVote(array('review_id'=>$item->getIdentity(),'product_id'=>$product->getIdentity(),'type'=>3));
				$voteCounter = 0;
				
				
				
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.first', 'Useful'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 1;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypeone ? true:false;
				$voteCounter++;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.second', 'Funny'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 2;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypetwo ? true:false;
				$voteCounter++;
				$result[$counter]['vote_option'][$voteCounter]['type'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.third', 'Cool'));
				$result[$counter]['vote_option'][$voteCounter]['value'] = 3;
				$result[$counter]['vote_option'][$voteCounter]['is_vote'] = $isGivenVoteTypethree ? true:false;
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
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.show.report', 1) && $viewer->getIdentity()){
					$result[$counter]['options'][$counterOption]['name'] = 'report'; 
					$result[$counter]['options'][$counterOption]['label'] = $this->view->translate('Report'); 
					$counterOption++;
				}
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.share', 1) && $viewer->getIdentity()){
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
        $subject = Engine_Api::_()->getItem('sesproductreview', $review_id);
        if (!Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'edit'))
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        $item = Engine_Api::_()->getItem('sesproduct', $subject->product_id);
        if (!$review_id || !$subject)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      
        $form = new Sesproduct_Form_Review_Edit(array('reviewId' => $subject->review_id,  'productItem' => $item));
        $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesproduct', 'controller' => 'review', 'action' => 'edit-review', 'review_id' => $review_id), 'default', true));
        $title = Zend_Registry::get('Zend_Translate')->_('Edit a Review for "<b>%s</b>".');
        $form->setTitle(sprintf($title, $subject->getTitle()));
        $form->setDescription("Please fill below information.");
		$form->populate($subject->toArray());
        $form->rate_value->setValue($subject->rating);
		
		
		if ($this->_getParam('getForm')) {
        	$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        	$this->generateFormFields($formFields, array('resources_type' => 'sesproductreview','rate_value'=>$subject->rating));
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
        $values['rating'] = $_POST['rate_value'];
        $reviews_table = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct');
        $db = $reviews_table->getAdapter();
        $db->beginTransaction();
        try {
            $subject->setFromArray($values);
            $subject->save();
            $table = Engine_Api::_()->getDbtable('parametervalues', 'sesproduct');
            $tablename = $table->info('name');
            $dbObject = Engine_Db_Table::getDefaultAdapter();
            foreach ($_POST as $key => $reviewC) {
                if (count(explode('_', $key)) != 3 || !$reviewC)
                    continue;
                $key = str_replace('review_parameter_', '', $key);
                if (!is_numeric($key))
                    continue;
                $parameter = Engine_Api::_()->getItem('sesproduct_parameter', $key);
               $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $subject->owner_id . '","' . $item->owner_id . '","' . $subject->review_id . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
                $dbObject->query($query);
                $ratingP = $table->getRating($key);
                $parameter->rating = $ratingP;
                $parameter->save();
            }
            if (isset($item->rating)) {
                $item->rating = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct')->getRating($subject->product_id);
                $item->save();
            }
            $subject->save();
            $reviewObject = $subject;
            $db->commit();
            $message = Zend_Registry::get('Zend_Translate')->_('The selected review has been edited.');
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' =>$message)));
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
        $itemTable = Engine_Api::_()->getItemTable('sesproductreview');
        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $tableLike->info('name');
        $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', 'sesproductreview')
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
                $like->resource_type = 'sesproductreview';
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
        $review = Engine_Api::_()->getItem('sesproductreview', $this->getRequest()->getParam('review_id'));
        $content_item = Engine_Api::_()->getItem('sesproduct', $review->product_id);
        if (!$this->_helper->requireAuth()->setAuthParams($review, $viewer, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        // In smoothbox
        //$this->_helper->layout->setLayout('default-simple');
       // $this->view->form = $form = new Sesbasic_Form_Delete();
        //$form->setTitle('Delete Review?');
       // $form->setDescription('Are you sure that you want to delete this review? It will not be recoverable after being deleted.');
        //$form->submit->setLabel('Delete');
        if ($this->getRequest()->isPost()) {
            $db = $review->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $reviewParameterTable = Engine_Api::_()->getDbTable('parametervalues', 'sesproduct');
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
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message'=>'The selected review has been deleted.')));
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
        $itemTable = Engine_Api::_()->getItemTable('sesproductreview');
        $tableVotes = Engine_Api::_()->getDbtable('reviewvotes', 'sesproduct');
        $tableMainVotes = $tableVotes->info('name');

        $review = Engine_Api::_()->getItem('sesproductreview',$item_id);
        $product = Engine_Api::_()->getItem('sesproduct',$review->product_id);


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


            //echo json_encode(array('status' => 'true', 'error' => '', 'condition' => 'reduced', 'count' => $review->{$votesTitle}));
            //die;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0','condition' => 'reduced', 'error_message' => '', 'result' => array('status'=>true,'count'=>$review->{$votesTitle})));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('reviewvotes', 'sesproduct')->getAdapter();
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
        //if (Engine_Api::_()->core()->hasSubject())
        //    $subject = Engine_Api::_()->core()->getSubject();
        //else
          //  return $this->_forward('notfound', 'error', 'core');

        $review_id = $this->_getParam('review_id', null);
		if(!$review_id){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.review', 1))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			
        if (!Engine_Api::_()->sesapi()->getViewerPrivacy('sesproductreview', 'view'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            
		
		$review = Engine_Api::_()->getItem('sesproductreview', $review_id);
		$product = Engine_Api::_()->getItem('sesproduct', $review->product_id);
		
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
		$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'sesproduct')->getParameters(array('content_id'=>$review->getIdentity(),'user_id'=>$review->owner_id));
		
		$likeStatus = Engine_Api::_()->sesproduct()->getLikeStatus($review->review_id,$review->getType());
		$ownerSelf = $viewerId == $review->owner_id ? true : false;
		$parameterCounter = 0;
		if(count($reviewParameters)>0){
			foreach($reviewParameters as $reviewP){ 
				$result['review_perameter'][$parameterCounter] = $reviewP->toArray();
				$parameterCounter++;
			}
		}
		$result['product']['images'] = $this->getBaseUrl(true, $product->getPhotoUrl());
		$result['product']['title'] = $product->getTitle();
		$result['product']['Guid'] = $product->getGuid();
		$result['product']['id'] = $product->getIdentity();
		
		$result['owner']['id'] = $owner->getIdentity();
		$result['owner']['Guid'] = $owner->getGuid();
		$result['owner']['title'] = $owner->getTitle();
		$result['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
		$result['show_pros'] = true;
		$result['show_cons'] = true;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.votes', 1)){
			$item = $review; 
			$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','sesproduct')->isReviewVote(array('review_id'=>$item->getIdentity(),'product_id'=>$product->getIdentity(),'type'=>1));
			$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','sesproduct')->isReviewVote(array('review_id'=>$item->getIdentity(),'product_id'=>$product->getIdentity(),'type'=>2));
			$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','sesproduct')->isReviewVote(array('review_id'=>$item->getIdentity(),'product_id'=>$product->getIdentity(),'type'=>3));
			$result['voting']['label'] = $this->view->translate("Was this Review...?");
			$bttonCounter	= 0 ;			
			$result['voting']['buttons'][$bttonCounter]['name'] = 'useful';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.first', 'Useful'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypeone ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $item->useful_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'funny';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.second', 'Funny'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypetwo ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $item->funny_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'cool';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.review.third', 'Cool'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypethree ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $item->cool_count;
			
		}
		if($item->authorization()->isAllowed($viewer, 'comment')){
			$result['is_content_like'] = $likeStatus?true:false;
		}
		$optionCounter = 0;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.show.report', 1) && $viewerId && $viewerId != $owner){
			$result['options'][$optionCounter]['name'] = 'report';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Report');
			$optionCounter++;
		}
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.share', 1) && $viewerId){
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
		
		
		if($item->authorization()->isAllowed($viewer, 'edit')) { 
			$result['options'][$optionCounter]['name'] = 'edit';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Edit Review');
			$optionCounter++;
		}
		if($item->authorization()->isAllowed($viewer, 'delete')) {
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
		
		$wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $this->_getParam('wishlist_id'));
		if(!$wishlist)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Wishlist not found'), 'result' => array()));
		//Make form
		$form = new Sesproduct_Form_Wishlist_Edit();

		$form->populate($wishlist->toarray());
		
		if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'sesproductreview'));
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

		$db = Engine_Api::_()->getDbTable('wishlists', 'sesproduct')->getAdapter();
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
		
		$wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $wishlist_id);
		
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
		  Engine_Api::_()->getDbtable('playlistproducts', 'sesproduct')->delete(array('wishlist_id =?' => $this->_getParam('wishlist_id')));
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

    if (!$this->_helper->requireAuth()->setAuthParams('sesproduct', null, 'addwishlist')->isValid())
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    //Set song
    
    $product = Engine_Api::_()->getItem('sesproduct', $this->_getParam('product_id'));
    $product_id = $this->_getParam('product_id'); //$product->product_id;
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //Get form
    $form = new Sesproduct_Form_Wishlist_Append();

    if ($form->wishlist_id) {
      $alreadyExistsResults = Engine_Api::_()->getDbtable('playlistproducts', 'sesproduct')->getPlaylistProducts(array('column_name' => 'wishlist_id', 'file_id' => $product_id));

      $allPlaylistIds = array();
      foreach ($alreadyExistsResults as $alreadyExistsResult) {
        $allPlaylistIds[] = $alreadyExistsResult['wishlist_id'];
      }

      //Populate form
      $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sesproduct');
      $select = $wishlistTable->select()
              ->from($wishlistTable, array('wishlist_id', 'title'));

//       if ($allPlaylistIds) {
//         $select->where($wishlistTable->info('name') . '.wishlist_id NOT IN(?)', $allPlaylistIds);
//       }

      /* if ($product_id) {
        $select->where($wishlistTable->info('name') . '.product_id =?',$product_id);
      } */
	  
	  

      $select->where('owner_id = ?', $viewer->getIdentity());
      $wishlists = $wishlistTable->fetchAll($select);
      if ($wishlists)
        $wishlists = $wishlists->toArray();
      foreach ($wishlists as $wishlist)
        $form->wishlist_id->addMultiOption($wishlist['wishlist_id'], html_entity_decode($wishlist['title']));
    }
	
	if ($this->_getParam('getForm')) {
		$values =  $form->getValues();
		if($_POST[wishlist_id]){
			$form->populate(array('wishlist_id' => $_POST[wishlist_id]));
			$form->removeElement('title');
			$form->removeElement('description');
			$form->removeElement('mainphoto');
			$form->removeElement('is_private');
		}
		$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
		$this->generateFormFields($formFields, array('resources_type' => 'sesproductreview'));
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
    if (empty($values['wishlist_id']) && empty($values['title']))
      $form->addError('Please enter a title or select a wishlist.');
  
	//Existing wishlist
    if (!empty($values['wishlist_id'])) {

        $wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $values['wishlist_id']);

        //Already exists in wishlist
        $alreadyExists = Engine_Api::_()->getDbtable('playlistproducts', 'sesproduct')->checkProductsAlready(array('column_name' => 'playlistproduct_id', 'file_id' => $product_id, 'wishlist_id' => $wishlist->wishlist_id));

        if ($alreadyExists)
			$form->addError($this->view->translate("This wishlist already has this product."));
    }
	
	if (!$form->isValid($this->getRequest()->getPost())){
		$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
		if (count($validateFields))
			$this->validateFormFields($validateFields);
	}

    //Process
    $wishlistProductTable = Engine_Api::_()->getDbtable('wishlists', 'sesproduct');
    $db = $wishlistProductTable->getAdapter();
    $db->beginTransaction();
    try {
      
      //New wishlist
        $wishlist = $wishlistProductTable->createRow();
        $wishlist->title = trim($values['title']);
        $wishlist->description = $values['description'];
        $wishlist->owner_id = $viewer->getIdentity();
        $wishlist->product_id = $product_id;
        $wishlist->is_private = $values['is_private'];
        $wishlist->save();
      
      $wishlist->product_count++;
      $wishlist->save();
      //Add song
      $wishlist->addProduct($product->photo_id, $product_id);
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
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $product, "sesproduct_wishlist_create", '', array('wishlist' => array($wishlist->getType(), $wishlist->getIdentity()),
      ));
      if ($action) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $product);
      }

      $db->commit();
      //Response
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Product has been successfully added to your wishlist.'), 'status'=>true)));
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
		$wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $wishlist_id);
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
		
		$wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $wishlist_id);

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
		  $dbObject->query('INSERT INTO engine4_sesproduct_recentlyviewitems (resource_id, resource_type,owner_id,creation_date ) VALUES ("' . $wishlist->wishlist_id . '", "sesproduct_wishlist","' . $viewer->getIdentity() . '",NOW())	ON DUPLICATE KEY UPDATE	 creation_date = NOW()');
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
		
		//$paginator = $wishlist->getProducts(array('wishlist_id' => $wishlist_id, 'order' => true), true);
		$params['wishlist_id'] = $wishlist_id;
		$paginator = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->getSesproductsPaginator($params);
		
		$paginator->setItemCountPerPage($this->_getParam('limit',10));
		$paginator->setCurrentPageNumber($this->_getParam('page',1));
		
		
		$result['wishlist']['products'] = $this->getProducts($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
	}
	
	public function addtocartAction(){
		if( !$this->getRequest()->isPost() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		
		$product_id = $this->_getParam('product_id','');
		if(!$product_id)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		
		$product = Engine_Api::_()->getItem('sesproduct',$product_id);
		
		if(!$product)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('product not found'), 'result' => array()));
		
		//check member level allowed to buy product
		if(!Engine_Api::_()->sesproduct()->memberAllowedToBuy($product) || !Engine_Api::_()->sesproduct()->memberAllowedToSell($product)) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Product is not allowed to purchase right now, please try again later.'), 'result' => array()));
			/* $this->view->message = $this->view->translate("Product is not allowed to purchase right now, please try again later.");
			return; */
		}
		if((empty($product['closed']) || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.openclose',1)) &&  $product['draft'] == 1 && $product['is_approved'] == 1 && $product['enable_product'] == 1 && (!$product->starttime || strtotime($product->starttime) <= time())) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Product is not allowed to purchase right now, please try again later.'), 'result' => array()));
		}
		//insert item in cart
		$cartId = Engine_Api::_()->sesproduct()->getCartId();
		$productTable = Engine_Api::_()->getDbTable('cartproducts','sesproduct');
		//check product already added to cart
		$isAlreadyAdded = Engine_Api::_()->getDbTable('cartproducts','sesproduct')->checkproductadded(array('product_id'=>$product_id,'cart_id'=>$cartId->getIdentity()));
		if(!$isAlreadyAdded) {
			if(!empty($product->manage_stock) && $product->stock_quatity < $product->min_quantity){
				if ($product->stock_quatity == 1)
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Only 1 quantity of this product is available in stock.'), 'result' => array()));
					/* $this->view->message = $this->view->translate("Only 1 quantity of this product is available in stock."); */
				else
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Only %s quantities of this product are available in stock. Please enter the quantity less than or equal to %s.", $product->stock_quatity, $product->stock_quatity), 'result' => array()));
					/* $this->view->message = $this->view->translate("Only %s quantities of this product are available in stock. Please enter the quantity less than or equal to %s.", $product->stock_quatity, $product->stock_quatity); */
					/* return; */
			}
			$productTable->insert(array('cart_id' => $cartId->getIdentity(), 'product_id' => $product_id, 'quantity' => $product->min_quantity));
		}else{
			$quantity = $isAlreadyAdded['quantity'] + 1;
			if(!empty($product->manage_stock) && empty($product->stock_quatity)) {
				/* $this->view->message = $this->view->translate("Product not available right now.");
				return; */
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Product not available right now."), 'result' => array()));
			}else if(!empty($product->manage_stock) && $product->stock_quatity < $quantity){
				if ($product->stock_quatity == 1)
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Only 1 quantity of this product is available in stock."), 'result' => array()));
					/* $this->view->message = $this->view->translate("Only 1 quantity of this product is available in stock."); */
				else
					Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate("Only %s quantities of this product are available in stock. Please enter the quantity less than or equal to %s.", $product->stock_quatity, $product->stock_quatity), 'result' => array()));
					/* $this->view->message = $this->view->translate("Only %s quantities of this product are available in stock. Please enter the quantity less than or equal to %s.", $product->stock_quatity, $product->stock_quatity);
				return; */
			}
			$isAlreadyAdded->quantity = $quantity;
			$isAlreadyAdded->save();
		}
      /* $status = true;
      $message = $this->view->translate("This Product has been successfully added to your cart."); */
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('This Product has been successfully added to your cart.'), 'status'=>true)));
	}
	// product edit
	public function editAction(){
		$productId = $this->_getParam('product_id');
        if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$sesproduct = Engine_Api::_()->core()->getSubject();
		}else{
			$sesproduct= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$sesproduct){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sesproduct')->profileFieldId();
		if( !$this->_helper->requireSubject()->isValid() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		if( !$this->_helper->requireAuth()->setAuthParams('sesproduct', $viewer, 'edit')->isValid() ) 
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		$form = new Sesproduct_Form_Edit(array('defaultProfileId' => $defaultProfileId));
		// Populate form
		$form->populate($sesproduct->toArray());
		$form->populate(array(
			'networks' => explode(",",$sesproduct->networks),
			'levels' => explode(",",$sesproduct->levels)
		));
		if($form->getElement('productstyle'))
		$form->getElement('productstyle')->setValue($sesproduct->style);
		$latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sesproduct',$sesproduct->product_id);
		if($latLng){
		  if($form->getElement('lat'))
		  $form->getElement('lat')->setValue($latLng->lat);
		  if($form->getElement('lng'))
		  $form->getElement('lng')->setValue($latLng->lng);
		}
		if($form->getElement('location'))
		$form->getElement('location')->setValue($sesproduct->location);
			if($form->getElement('category_id'))
		$form->getElement('category_id')->setValue($sesproduct->category_id);

		$tagStr = '';
		foreach( $sesproduct->tags()->getTagMaps() as $tagMap ) {
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
			if( $auth->isAllowed($sesproduct, $role, 'view') ) {
			 $form->auth_view->setValue($role);
			}
		  }

		  if ($form->auth_comment){
			if( $auth->isAllowed($sesproduct, $role, 'comment') ) {
			  $form->auth_comment->setValue($role);
			}
		  }

		  if ($form->auth_video){
			if( $auth->isAllowed($sesproduct, $role, 'video') ) {
			  $form->auth_video->setValue($role);
			}
		  }

		  if ($form->auth_music){
			if( $auth->isAllowed($sesproduct, $role, 'music') ) {
			  $form->auth_music->setValue($role);
			}
		  }
		}

		//hide status change if it has been already published
		if( $sesproduct->draft == 0 )
		  $form->removeElement('draft');
		$this->view->edit = true;


		$upsells = Engine_Api::_()->getDbTable('upsells','sesproduct')->getSells(array('product_id'=>$sesproduct->getIdentity()));
		if(count($upsells)){
		  $content = "";
		  $upsellsArray = array();
		  foreach($upsells as $upsell){
			$resource = Engine_Api::_()->getItem('sesproduct',$upsell->resource_id);
			if(!$resource)
			  continue;
			  $upsellsArray[] = $resource->getIdentity();
			$content .='<span id="upsell_remove_'.$resource->getIdentity().'" class="sesproduct_tag tag">'.$resource->getTitle().' <a href="javascript:void(0);" onclick="removeFromToValueUpsell('.$resource->getIdentity().');">x</a></span>';
		  }
		  $form->upsell_id->setValue(implode(',',$upsellsArray));
		  $this->view->upsells = $content;
		}
		$crosssells = Engine_Api::_()->getDbTable('crosssells','sesproduct')->getSells(array('product_id'=>$sesproduct->getIdentity()));
		if(count($crosssells)){
		  $content = "";
		  $crosssellsArray = array();
		  foreach($crosssells as $crosssell){
			$resource = Engine_Api::_()->getItem('sesproduct',$crosssell->resource_id);
			if(!$resource)
			  continue;
			$crosssellsArray[] = $resource->getIdentity();
			$content .='<span id="crosssell_remove_'.$resource->getIdentity().'" class="sesproduct_tag tag">'.$resource->getTitle().' <a href="javascript:void(0);" onclick="removeFromToValueCrossSell('.$resource->getIdentity().');">x</a></span>';
		  }
		  $form->crosssell_id->setValue(implode(',',$crosssellsArray));
		  $this->view->crosssells = $content;
		}

		//get all allowed types product
		$viewer = Engine_Api::_()->user()->getViewer();
		$allowed_types = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesproduct', $viewer, 'allowed_types');
		if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesproduct'));
        }
		
	/* 	if(!isset($_POST['type']) || !$_POST['type'])
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Please Select the Product Type First'), 'result' => array())); */
		
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		//is post
		if (!$form->isValid($this->getRequest()->getPost())) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
		}
		
		if (isset($_POST['custom_url']) && !empty($_POST['custom_url'])) {
			$custom_url = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->checkCustomUrl($_POST['custom_url'],$sesproduct->getIdentity());
			if ($custom_url) {
			  $form->addError($this->view->translate("Custom URL is not available. Please select another URL."));
			}
		}
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enablesku',1)) {
            if (isset($_POST['sku']) && !empty($_POST['sku'])) {
                $sku = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->checkSKU($_POST['sku'], $sesproduct->getIdentity());
                if ($sku) {
                    $form->addError($this->view->translate("SKU is not available. Please select another SKU."));
                }
            }
        }else{
            $_POST['sku'] = "";
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
				$preciousstart = strtotime($sesproduct->discount_start_date);
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
				$preciousend = strtotime($sesproduct->discount_end_date);
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
      //inventory check
		if(!empty($_POST['manage_stock']) && empty($_POST['stock_quatity'])){
			$form->addError($this->view->translate('Stock Quantity is required.'));
		}
		if(!empty($_POST['manage_stock']) && !empty($_POST['stock_quatity'])){
			if($_POST['stock_quatity'] < $_POST['min_quantity'] || $_POST['stock_quatity'] < $_POST['max_quatity']){
				$form->addError($this->view->translate('Minimum Order Quantity / Maximum Order Quantity must be less than Stock Quantity.'));
			}else if(!empty($_POST['max_quatity']) && $_POST['min_quantity'] > $_POST['max_quatity']){
				$form->addError($this->view->translate('Minimum Order Quantity must be less than Maximum Order Quantity.'));
			}
		}else if(!empty($_POST['max_quatity']) && $_POST['min_quantity'] > $_POST['max_quatity']){
			$form->addError($this->view->translate('Minimum Order Quantity must be less than Maximum Order Quantity.'));
		}
      //avalability check
		if(empty($_POST['show_start_time'])){
			if(empty($_POST['start_date'])){
				$form->addError($this->view->translate('Start Time is required.'));
			}else{
			  $time = $_POST['start_date'].' '.(!empty($_POST['start_date_time']) ? $_POST['start_date_time'] : "00:00:00");
			  //Convert Time Zone
			  $oldTz = date_default_timezone_get();
			  date_default_timezone_set($this->view->viewer()->timezone);
			  $start = strtotime($time);
			  date_default_timezone_set($oldTz);
			  if($start < time()){
				 $timeError = true;
				 $form->addError($this->view->translate('Start Time must be greater than Current Time.'));
			  }
			}
		}
		if(!empty($_POST['show_end_time'])){
			if(empty($_POST['end_date'])){
				$form->addError($this->view->translate('End Time is required.'));
			}else{
			  $time = $_POST['end_date'].' '.(!empty($_POST['end_date_time']) ? $_POST['end_date_time'] : "00:00:00");
			  //Convert Time Zone
			  $oldTz = date_default_timezone_get();
			  date_default_timezone_set($this->view->viewer()->timezone);
			  $end = strtotime($time);
			  date_default_timezone_set($oldTz);
			  if($end < time()){
				 $timeError = true;
				 $form->addError($this->view->translate('End Time must be greater than Current Time.'));
			  }
			}
		}
		if(empty($timeError)){
			if(!empty($_POST['show_end_time'])){
			   if(empty($_POST['show_start_time'])){
				  $starttime = $_POST['start_date'].' '.(!empty($_POST['start_date_time']) ? $_POST['start_date_time'] : "00:00:00");
				  $endtime = $_POST['end_date'].' '.(!empty($_POST['end_date_time']) ? $_POST['end_date_time'] : "00:00:00");
				  //Convert Time Zone
				  $oldTz = date_default_timezone_get();
				  date_default_timezone_set($this->view->viewer()->timezone);
				  $start = strtotime($starttime);
				  $end = strtotime($endtime);
				  date_default_timezone_set($oldTz);
				  if($end < $start){
					  $form->addError($this->view->translate('End Time must be greater than Start Time.'));
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
	  /* 
		if (!$form->isValid($this->getRequest()->getPost())) {
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
				$this->validateFormFields($validateFields);
		} */
		
		// Process
		$db = Engine_Db_Table::getDefaultAdapter();
		$db->beginTransaction();

		try
		{
			$values = $form->getValues();
			if($_POST['productstyle'])
			$values['style'] = $_POST['productstyle'];

			$sesproduct->setFromArray($values);
			$sesproduct->modified_date = date('Y-m-d H:i:s');
			if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
				$starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_time'])) : '';
				$sesproduct->publish_date =$starttime;
			}
			//else{
			//	$sesproduct->publish_date = '';
			//}
			if(isset($values['levels']))
				$values['levels'] = implode(',',$values['levels']);
			if(isset($values['networks']))
				$values['networks'] = implode(',',$values['networks']);
			if(isset($values['height']))
				$values['height'] = implode(',',$values['height']);
			if(isset($values['width']))
				$values['width'] = implode(',',$values['width']);
			if(isset($values['length']))
				$values['length'] = implode(',',$values['length']);
			if(isset($values['levels']))
				$sesproduct->levels = implode(',',$values['levels']);

			if(isset($values['networks']))
				$sesproduct->networks = implode(',',$values['networks']);

			$sesproduct->save();
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

			if(!empty($_POST['show_end_time'])){
				if(isset($_POST['start_date']) && $_POST['start_date'] != ''){
				  $starttime = isset($_POST['start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['start_date'].' '.$_POST['start_date_time'])) : '';
				  $sesproduct->starttime =$starttime;
				}
				if(isset($_POST['start_date']) && $viewer->timezone && $_POST['start_date'] != ''){
				  //Convert Time Zone
				  $oldTz = date_default_timezone_get();
				  date_default_timezone_set($viewer->timezone);
				  $start = strtotime($_POST['start_date'].' '.(!empty($_POST['start_date_time']) ? $_POST['start_date_time'] : "00:00:00"));          
				  $sesproduct->starttime = date('Y-m-d H:i:s', $start);
					date_default_timezone_set($oldTz);
				}
			}

			if(!empty($_POST['show_end_time'])){
				if(isset($_POST['end_date']) && $_POST['end_date'] != ''){
				  $starttime = isset($_POST['end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['end_date'].' '.$_POST['end_date_time'])) : '';
				  $sesproduct->endtime =$starttime;
				}
				if(isset($_POST['end_date']) && $viewer->timezone && $_POST['end_date'] != ''){
				  //Convert Time Zone
				  $oldTz = date_default_timezone_get();
				  date_default_timezone_set($viewer->timezone);
				  $start = strtotime($_POST['end_date'].' '.(!empty($_POST['end_date_time']) ? $_POST['end_date_time'] : "00:00:00"));
				  $sesproduct->endtime = date('Y-m-d H:i:s', $start);
					date_default_timezone_set($oldTz);
				}
			}
			//check attribute
			// Engine_Api::_()->getDbTable('cartoptions','sesproduct')->checkProduct($sesproduct);
			//discount
			if(!empty($_POST['show_end_time'])){
				if(isset($_POST['discount_start_date']) && $_POST['discount_start_date'] != ''){
				  $starttime = isset($_POST['discount_start_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_start_date'].' '.$_POST['discount_start_date_time'])) : '';
				  $sesproduct->discount_start_date =$starttime;
				}
				if(isset($_POST['discount_start_date']) && $viewer->timezone && $_POST['discount_start_date'] != ''){
				  //Convert Time Zone
				  $oldTz = date_default_timezone_get();
				  date_default_timezone_set($viewer->timezone);
				  $start = strtotime($_POST['discount_start_date'].' '.(!empty($_POST['discount_start_date_time']) ? $_POST['discount_start_date_time'] : "00:00:00"));
				  
				  $sesproduct->discount_start_date = date('Y-m-d H:i:s', $start);
					date_default_timezone_set($oldTz);
				}
			}

			if(!empty($_POST['discount_end_date'])){
				if(isset($_POST['discount_end_date']) && $_POST['discount_end_date'] != ''){
				  $starttime = isset($_POST['discount_end_date']) ? date('Y-m-d H:i:s',strtotime($_POST['discount_end_date'].' '.$_POST['discount_end_date_time'])) : '';
				  $sesproduct->discount_end_date =$starttime;
				}
				if(isset($_POST['discount_end_date']) && $viewer->timezone && $_POST['discount_end_date'] != ''){
				  //Convert Time Zone
				  $oldTz = date_default_timezone_get();
				  date_default_timezone_set($viewer->timezone);
				  $start = strtotime($_POST['discount_end_date'].' '.(!empty($_POST['discount_end_date_time']) ? $_POST['discount_end_date_time'] : "00:00:00"));
				  $sesproduct->discount_end_date = date('Y-m-d H:i:s', $start);
					date_default_timezone_set($oldTz);
				}
			}


			if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '' && $_POST['location']) {
				Engine_Db_Table::getDefaultAdapter()->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $sesproduct->getIdentity() . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesproduct") ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
			} else {
				$sesproduct->location = '';
				$sesproduct->save();
				$dbInsert = Engine_Db_Table::getDefaultAdapter();
				$dbInsert->query('DELETE FROM `engine4_sesbasic_locations` WHERE `engine4_sesbasic_locations`.`resource_type` = "sesproduct" AND `engine4_sesbasic_locations`.`resource_id` = "'.$sesproduct->getIdentity().'";');
			}

			if(isset($values['draft']) && !$values['draft']) {
				$currentDate = date('Y-m-d H:i:s');
				if($sesproduct->publish_date < $currentDate) {
					$sesproduct->publish_date = $currentDate;
					$sesproduct->save();
				}
			}

			// Add fields
			$customfieldform = $form->getSubForm('fields');

			if (!is_null($customfieldform)) {
				$customfieldform->setItem($sesproduct);
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
			$videoMax = array_search($values['auth_video'], $roles);
			$musicMax = array_search($values['auth_music'], $roles);
			foreach( $roles as $i => $role ) {
				$auth->setAllowed($sesproduct, $role, 'view', ($i <= $viewMax));
				$auth->setAllowed($sesproduct, $role, 'comment', ($i <= $commentMax));
				$auth->setAllowed($sesproduct, $role, 'video', ($i <= $videoMax));
				$auth->setAllowed($sesproduct, $role, 'music', ($i <= $musicMax));
			}

			// handle tags
			$tags = preg_split('/[,]+/', $values['tags']);
			$sesproduct->tags()->setTagMaps($viewer, $tags);

			//upload main image
			if(isset($_FILES['photo_file']) && $_FILES['photo_file']['name'] != ''){
				$photo_id = 	$sesproduct->setPhoto($form->photo_file,'direct');
			}

			if (!empty($_POST['custom_url']) && $_POST['custom_url'] != '')
				$sesproduct->custom_url = $_POST['custom_url'];
			else
				$sesproduct->custom_url = $sesproduct->product_id;
			$sesproduct->save();

			$db->commit();
			$upsellcrosssell = Engine_Db_Table::getDefaultAdapter();
			$upsellcrosssell->query('DELETE FROM `engine4_sesproduct_upsells` WHERE product_id = '.$sesproduct->getIdentity());
			$upsellcrosssell->query('DELETE FROM `engine4_sesproduct_crosssells` WHERE product_id = '.$sesproduct->getIdentity());
			//upsell
			if(!empty($_POST['upsell_id'])){
				$upsell = trim($_POST['upsell_id'],',');
				$upsells = explode(',',$upsell);
				foreach($upsells as $item){
				  $params['product_id'] = $sesproduct->getIdentity();
				  $params['resource_id'] = $item;
				  $params['creation_date'] = date('Y-m-d H:i:s');
				  Engine_Api::_()->getDbTable('upsells','sesproduct')->create($params);
				}
			}
			//crosssell
			if(!empty($_POST['crosssell_id'])){
				$crosssell = trim($_POST['crosssell_id'],',');
				$crosssells = explode(',',$crosssell);
				foreach($crosssells as $item){
				  $params['product_id'] = $sesproduct->getIdentity();
				  $params['resource_id'] = $item;
				  $params['creation_date'] = date('Y-m-d H:i:s');
				  Engine_Api::_()->getDbTable('crosssells','sesproduct')->create($params);
				}
			}
			// insert new activity if sesproduct is just getting published
			$action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionsByObject($sesproduct);
			if( count($action->toArray()) <= 0 && $values['draft'] == '0' && (!$sesproduct->publish_date || strtotime($sesproduct->publish_date) <= time())) {
				$action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $sesproduct, 'sesproduct_new');
				  // make sure action exists before attaching the sesproduct to the activity
				if( $action != null ) {
				  Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sesproduct);
				}
				$sesproduct->is_publish = 1;
				$sesproduct->save();
			}
			// Rebuild privacy
			$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
			foreach( $actionTable->getActionsByObject($sesproduct) as $action ) {
				$actionTable->resetActivityBindings($action);
			}
			// Send notifications for subscribers
			Engine_Api::_()->getDbtable('subscriptions', 'sesproduct')->sendNotifications($sesproduct);
			
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('store_id' => $sesproduct->getIdentity(), 'success_message' => $this->view->translate('Product edited successfully.'))));
		}
		catch( Exception $e )
		{
			$db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
		}	
	}
	public function browseWishlistAction(){
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

		$values = array('alphabet' => $alphabet,'popularity' => $popularity,'category_id'=>$category_id,'brand'=>$brand, 'show' => $show, 'users' => $users, 'title' => $title, 'action' => $action);
		
		$paginator = Engine_Api::_()->getDbTable('wishlists', 'sesproduct')->getWishlistPaginator($values);
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
        $viewerId = $viewer->getIdentity();
		    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;

        foreach ($paginator as $wishlist) {
			$result[$counter] = $wishlist->toArray();
			if($wishlist->photo_id) {
        $result[$counter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($wishlist->photo_id, '', "");
			} else {
        $result[$counter]['images'] = array('main' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct_wishlist_default_image', Zend_Registry::get('StaticBaseUrl')."application/modules/Sesproduct/externals/images/nophoto_wishlist_thumb_profile.png"));
			}
			$LikeStatus = Engine_Api::_()->sesproduct()->getLikeStatusProduct($wishlist->getIdentity(),$wishlist->getType());
			$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesproduct')->isFavourite(array('resource_id' => $wishlist->getIdentity(), 'resource_type' => $wishlist->getType()));
			if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
				$storedata['is_content_like'] = $LikeStatus >0?true:false;
				$storedata['is_content_fav'] = $favStatus >0?true:false;
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

        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
            ->setIntegrityCheck(false)
            ->from($corePagesTable, null)
            ->where($coreContentTableName . '.name=?', 'sesproduct.browse-search')
            ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
            ->where($corePagesTableName . '.name = ?', 'sesproduct_index_browse');
        $id = $select->query()->fetchColumn();
        if (!empty($_POST['location'])) {
            $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
            if ($latlng) {
                $_POST['lat'] = $latlng['lat'];
                $_POST['lng'] = $latlng['lng'];
            }
        }

        $form = new Sesproduct_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
        $form->populate($_POST);
        $params = $form->getValues();
        $params = array_merge($params,$_GET);
        $value = array();
        $value['status'] = 1;
        $value['search'] = 1;
        $value['draft'] = "0";
        if (isset($params['search']))
            $params['text'] = addslashes($params['search']);
        $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
        $params = array_merge($params, $value);
        if ($store == 0 && isset($params['search'])) {
          unset($params['price_max']);
        }

        $paginator = Engine_Api::_()->getDbTable('sesproducts', 'sesproduct')->getSesproductsPaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $store = $this->_getParam('page', 1);

        if ($store == 1 && !isset($params['search'])) {

          $categories = Engine_Api::_()->getDbtable('categories', 'sesproduct')->getCategory(array('column_name' => '*'));
          
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
            $result_category[$category_counter]['total_page_categories'] = $category->total_page_categories;
            $result_category[$category_counter]['category_id'] = $category->category_id;
            $category_counter++;
          }
        }

        $result['products'] = $this->getProducts($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
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
            ->where($coreContentTableName . '.name=?', 'sesproduct.browse-search')
            ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', array('content_id', 'params'))
            ->where($corePagesTableName . '.name = ?', 'sesproduct_index_browse'); 
        $results = $corePagesTable->fetchRow($select);
        
        $param = json_decode($results->params, true);
        
        
        
        $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));

        if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.favourite', 1))
          unset($filterOptions['mostSPfavourite']);
        $this->view->view_type = $param['view_type'] ? $param['view_type'] : $this-> _getParam('view_type', 'horizontal');
        $this->view->search_for = $search_for = $param['search_for'] ? $param['search_for'] :  $this-> _getParam('search_for', 'product');
        $default_search_type = $param['default_search_type'] ? $param['default_search_type'] :   $this-> _getParam('default_search_type', 'mostSPliked');

        if($this->_getParam('location','yes') == 'yes' && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.location', 1))
        $location = 'yes';
        else
        $location = 'no';

        $form = $this->view->form = new Sesproduct_Form_Search(array('searchTitle' => $param['search_title'] ? $param['search_title'] : $this->_getParam('search_title', 'yes'),'browseBy' => $param['browse_by'] ? $param['browse_by'] : $this->_getParam('browse_by', 'yes'),'categoriesSearch' => $param['categories'] ? $param['categories'] : $this->_getParam('categories', 'yes'),'searchFor'=> $search_for,'FriendsSearch'=>$param['friend_show'] ? $param['friend_show'] : $this->_getParam('friend_show', 'yes'),'defaultSearchtype'=>$default_search_type,'locationSearch' => $location,'kilometerMiles' => $param['kilometer_miles'] ? $param['kilometer_miles'] : $this->_getParam('kilometer_miles', 'yes'),'price' => $param['price'] ? $param['price'] : $this->_getParam('price', 'yes'),'discount' => $param['discount'] ? $param['discount'] : $this->_getParam('discount', 'yes'),'hasPhoto' => $param['has_photo'] ? $param['has_photo'] : $this->_getParam('has_photo', 'yes')));

        if($this->_getParam('search_type','product') !== null && $this->_getParam('browse_by', 'yes') == 'yes'){
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
          $form->sort->setMultiOptions($filterOptions);
          $form->sort->setValue($default_search_type);
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $form->setMethod('get')->populate($request->getParams());
        if($form->getElement('lat')){
          $form->removeElement('lat');
          $form->removeElement('lng');
        }
        $form->removeElement('cancel');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'sesproduct'));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
    }
    public function getProducts($paginator) {
        $result = array();
        $counter = 0;
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.favourite', 0);

        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.sharing', 1);

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
		    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;

        foreach ($paginator as $stores) {
          $store_item = Engine_Api::_()->getItem('stores', $stores->store_id);
          $store = $stores->toArray();
		  /* $result[$counter]['description'] = $stores->body; */
          $result[$counter] = $store;
          $result[$counter]['owner_title'] = $stores->getOwner()->getTitle();
          $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
          $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
          $result[$counter]['currency'] = $curArr[$currency];
          if ($stores->category_id) {
              $category = Engine_Api::_()->getItem('sesproduct_category', $stores->category_id);
              if ($category) {
                  $result[$counter]['category_title'] = $category->category_name;
                  if ($stores->subcat_id) {
                      $subcat = Engine_Api::_()->getItem('sesproduct_category', $stores->subcat_id);
                      if ($subcat) {
                          $result[$counter]['subcategory_title'] = $subcat->category_name;
                          if ($stores->subsubcat_id) {
                              $subsubcat = Engine_Api::_()->getItem('sesproduct_category', $stores->subsubcat_id);
                              if ($subsubcat) {
                                  $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                              }
                          }
                      }
                  }
              }
          }
          $tags = array();
          foreach ($stores->tags()->getTagMaps() as $tagmap) {
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

          $result[$counter]['images']['main']= $this->getBaseUrl(true, $stores->getPhotoUrl());
          //$result[$counter]['cover_image']['main'] = $this->getBaseUrl(true, $stores->getCoverPhotoUrl());
          $result[$counter]['cover_images']['main'] = $result[$counter]['cover_image']['main'];
          $i = 0;
          if ($stores->is_approved) {
              if ($shareType) {
                  $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $stores->getPhotoUrl());
                  $result[$counter]["share"]["url"] = $this->getBaseUrl(false,$stores->getHref());
                  $result[$counter]["share"]["title"] = $stores->getTitle();
                  $result[$counter]["share"]["description"] = strip_tags($stores->getDescription());
                  $result[$counter]["share"]["setting"] = $shareType;
                  $result[$counter]["share"]['urlParams'] = array(
                      "type" => $stores->getType(),
                      "id" => $stores->getIdentity()
                  );
              }
          }

          if((empty($stores->manage_stock) || $stores->stock_quatity) && empty($stores->outofstock) ){
            $result[$counter]['stock'] = $this->view->translate('In Stock');
          } else {
            $result[$counter]['stock'] = $this->view->translate('Out of Stock');
          }

          //Rating Count
          $rating = Engine_Api::_()->getDbTable('sesproductreviews','sesproduct')->getRating($stores->getIdentity());
          $result[$counter]['ratings'] = round($rating,1);
          $totalReviewCount = (int)Engine_Api::_()->getDbTable('sesproductreviews','sesproduct')->getReviewCount(array('product_id'=>$stores->getIdentity()))[0];
          $result[$counter]['review_count'] = '('.(int) $totalReviewCount.')';

          $result[$counter]['store_title'] = $store_item->title;

          if ($stores->is_approved) {

            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.wishlist', 1)) {
              $result[$counter]['is_content_wishlist'] = true;
            }

            if($stores->discount && $priceDiscount = Engine_Api::_()->sesproduct()->productDiscountPrice($stores)){
            
              $result[$counter]["price_with_discount"] = $priceDiscount;
              $result[$counter]['product_price'] = $price = Engine_Api::_()->sesproduct()->getCurrencyPrice($priceDiscount);
              $afterDiscount = Engine_Api::_()->sesproduct()->getCurrencySymbol(Engine_Api::_()->sesproduct()->getCurrentCurrency()) . '<strike>' . $stores->price . '</strike>';
              $result[$counter]['discount_price'] = $afterDiscount;
              if($stores->discount_type == 0) {
                $result[$counter]['product_price'] = $this->view->translate("%s%s OFF",str_replace('.00','', $stores->percentage_discount_value),"%");
              } else {
                $result[$counter]['product_price'] = $this->view->translate("%s OFF",Engine_Api::_()->sesproduct()->getCurrencyPrice($stores->fixed_discount_value));
              }
            } else {
              $result[$counter]['product_price'] = $price = $stores->price > 0 ? Engine_Api::_()->sesproduct()->getCurrencyPrice($stores->price) : $this->view->translate('FREE');
            }

//                 if ($shareType) {
//                     $result[$counter]['buttons'][$i]['name'] = 'share';
//                     $result[$counter]['buttons'][$i]['label'] = 'Share';
//                     $i++;
//                 }

            if ($viewerId != 0) {
                $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($stores);
                $result[$counter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($stores);
                if ($canFavourite) {
                    $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($stores, 'favourites', 'sesproduct', 'sesproduct', 'user_id');
                    $result[$counter]['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($stores, 'favourites', 'sesproduct', 'sesproduct', 'user_id');
                }
            }

            if ($stores->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.location', 1)) {
                unset($stores['location']);
                $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sesproduct', $stores->getIdentity());
                if ($location) {
                    $result[$counter]['location'] = $location->toArray();
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.map.integration', 1)) {
                        $result[$counter]['location']['showMap'] = true;
                    } else {
                        $result[$counter]['location']['showMap'] = false;
                    }
                }
            }
            
            $counter++;
          }
        }
        
        $results['products'] = $result;
        
        
        return $result;

    }
	public function getProductCategory($categoryPaginator){
        $result = array();
        $counter = 0;
        foreach ($categoryPaginator as $categories) {
            $product = $categories->toArray();
            $params['category_id'] = $categories->category_id;
            $params['limit'] = 5;

            $paginator = Engine_Api::_()->getDbTable('sesproducts', 'sesproduct')->getSesproductsPaginator($params);
            $paginator->setItemCountPerPage(3);
            $paginator->setCurrentPageNumber(1);
            if($paginator->getTotalItemCount() > 0){
              $result[$counter] = $product;
              $result[$counter]['items'] = $this->getProducts($paginator);
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
    public function productcategoriesAction(){
		$paginator = Engine_Api::_()->getDbTable('categories', 'sesproduct')->getProductPaginator();
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
		$paginator->setCurrentPageNumber($this->_getParam('page', 1));
          if (count($paginator) > 0) {
            $categories = Engine_Api::_()->getDbtable('categories', 'sesproduct')->getCategory(array('column_name' => '*'));
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
                //$result_category[$category_counter]['total_product_categories'] = $category->total_product_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;

                $category_counter++;
            }
            $result['category'] = $result_category;
        }
        $result['categories'] = $this->getProductCategory($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
    public function manageAction(){
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
            case 'close':
                $params['sort'] = 'close';
                break;
            case 'open':
                $params['sort'] = 'open';
                break;
        }
        $params['widgetManage'] = true;

        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 0);
        $viewerId = $viewer->getIdentity();
        $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'estore_can_join') : 0;
        $filterOptionsMenu = array();
        $filterMenucounter = 0;
        $resultmenu[$filterMenucounter]['name'] = 'open';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Open');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'close';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Close');
        $filterMenucounter++;
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
        if ($paginator) {
            $storeCounter = 0;
            foreach ($paginator as $stores) {
                $storeArray = $stores->toArray();
                if (!$canFavourite)
                    unset($storeArray['favourite_count']);
                if (!$canFollow)
                    unset($storeArray['follow_count']);
                unset($storeArray['location']);
                $result[$storeCounter] = $storeArray;
                $statsCounter = 0;
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($stores, '', "");
                if (image) {
                    $result[$storeCounter]['images'] = $image;
                } else {
                    $result[$storeCounter]['images'] = $image;
                }
                $isStoreEdit = Engine_Api::_()->getDbTable('storeroles', 'estore')->toCheckUserStoreRole($viewer->getIdentity(), $stores->getIdentity(), 'manage_dashboard', 'edit');
                $isStoreDelete = Engine_Api::_()->getDbTable('storeroles', 'estore')->toCheckUserStoreRole($viewer->getIdentity(), $stores->getIdentity(), 'manage_dashboard', 'delete');
                $buttonCounter = 0;
                if ($isStoreEdit) {
                    $result[$storeCounter]['buttons'][$buttonCounter]['name'] = 'edit';
                    $result[$storeCounter]['buttons'][$buttonCounter]['label'] = 'Edit';
                    $buttonCounter++;
                }
                if ($isStoreDelete) {
                    $result[$storeCounter]['buttons'][$buttonCounter]['name'] = 'delete';
                    $result[$storeCounter]['buttons'][$buttonCounter]['label'] = 'Delete';
                    $buttonCounter++;
                }
                $storeCounter++;
            }
        }
        $data['stores'] = $result;
        if ($this->_getParam('page', 1))
          $data['filterMenuOptions'] = $resultmenu;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
    }
    public function checkVersion($android,$ios){
        if(is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
            return  true;
        if(is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
            return true;
        return false;
    }
    public function menuAction(){
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());
        $menu_counter = 0;
        foreach ($menus as $menu) {
            $class = end(explode(' ', $menu->class));

            if($class == 'sesproduct_main_browsehome')
              continue;
            if($class == 'estore_main_browselocations')
              continue;
            if($class == 'sesproduct_main_location')
              continue;
            if($class == 'estore_main_pinboard')
             continue;
            if($class == 'estore_main_storealbumhome')
              continue;
            $result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
            $result_menu[$menu_counter]['action'] = $class;
            $result_menu[$menu_counter]['isActive'] = $menu->active;
            $menu_counter++;
        }
        $result['menus'] = $result_menu;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function contentFollow($subject = null,$tableName = "",$modulename = "",$resource_type = "",$column_name = "user_id"){
    $viewer = Engine_Api::_()->user()->getViewer();
    //return if non logged in user or content empty
    if (empty($subject) || empty($viewer))
        return;
    if ($viewer->getIdentity())
    {
          $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
          $select->where('resource_id =?',$subject->getIdentity())->where($column_name.' =?',$viewer->getIdentity());
          if($resource_type)
            $select->where('resource_type =?',$resource_type);
          $follow = (int) Zend_Paginator::factory($select)->getTotalItemCount();
    }
    return !empty($follow) ? true : false;
  }
	public function getContentFollowCount($subject,$tableName = "",$modulename = "",$resources_type = "",$column_name = "resource_id"){
      $viewer = Engine_Api::_()->user()->getViewer();
      if(!$tableName || !$modulename)
        return 0;
      $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
      $select->where('resource_id =?',$subject->getIdentity());
      if($resources_type)
            $select->where('resource_type =?',$resources_type);
      return (int) Zend_Paginator::factory($select)->getTotalItemCount();
  }
    public function contactAction(){
        $ownerId[] = $this->_getParam('owner_id', 0);
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        // Get form
        if (!$this->_getParam('owner_id', 0)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $form = new Estore_Form_ContactOwner();
        $form->store_owner_id->setValue($this->_getParam('owner_id', 0));

        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }


        if (!$form->isValid($this->getRequest()->getPost())) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Process
        $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $values = $form->getValues();
            $recipientsUsers = Engine_Api::_()->getItemMulti('user', $ownerId);
            $attachment = null;

            if ($values['store_owner_id'] != $viewer->getIdentity()) {

                // Create conversation
                $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $ownerId, $values['title'], $values['body'], $attachment);
            }

            // Send notifications
            foreach ($recipientsUsers as $user) {
                if ($user->getIdentity() == $viewer->getIdentity()) {
                    continue;
                }

                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');
            }

            // Increment messages counter
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

            // Commit
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Message sent successfully.')));
        } catch (Exception $e) {

            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));

        }
    }
    public function joinAction(){
        $store_id = $this->getParam('store_id', 0);
        if (!$store_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $item = Engine_Api::_()->getItem('stores', $store_id);
        if ($item->membership()->isResourceApprovalRequired()) {
            $row = $item->membership()->getReceiver()
                ->select()
                ->where('resource_id = ?', $item->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->query()
                ->fetch(Zend_Db::FETCH_ASSOC, 0);;


            if (empty($row)) {

                // has not yet requested an invite
                $message = $this->request();
                if ($message == 'Successfully requested.') {
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($item))));
                } else {
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('database_error'), 'result' => array()));

                }
            } elseif ($row['user_approved'] && !$row['resource_approved']) {

                // has requested an invite; show cancel invite store
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Has requested an invite'),'menus'=>$this->getButtonMenus($item))));
                //              return $this->_helper->redirector->gotoRoute(array('action' => 'cancel', 'format' => 'smoothbox'));
            }


        }

        $form = new Estore_Form_Member_Join();
        //      $form->store_owner_id->setValue($this->_getParam('owner_id',0));
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }

        if (!$form->isValid($this->getRequest()->getPost())) {

            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }


        $db = $item->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();

        try {

            $membership_status = $item->membership()->getRow($viewer)->active;
            if (!$membership_status) {
                $item->membership()->addMember($viewer)->setUserApproved($viewer);
                $row = $item->membership()->getRow($viewer);
                $row->save();
            }


            $owner = $item->getOwner();
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'estore_store_join');

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_estore_store_storejoined', array('store_title' => $item->getTitle(), 'sender_title' => $viewer->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));

            //Send to all joined members
            $joinedMembers = Engine_Api::_()->estore()->getallJoinedMembers($item);

            foreach ($joinedMembers as $joinedMember) {
                if ($joinedMember->user_id == $item->owner_id) continue;
                $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($joinedMember, $viewer, $item, 'estore_store_storesijoinedjoin');

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_estore_store_joinstorejoined', array('store_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            $followerMembers = Engine_Api::_()->getDbTable('followers', 'estore')->getFollowers($item->getIdentity());

            foreach ($followerMembers as $followerMember) {
                if ($followerMember->owner_id == $item->owner_id) continue;
                $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($followerMember, $viewer, $item, 'estore_store_storesifollowedjoin');

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_estore_store_joinedstorefollowed', array('store_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            // Add activity if membership status was not valid from before
            if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $item, 'estore_store_join');
                if ($action) {
                    $activityApi->attachActivity($action, $item);
                }
            }
            $db->commit();

            $viewerId = $viewer->getIdentity();
            $result['message'] = $this->view->translate('Store Successfully Joined.');
            $result['menus']  = $this->getButtonMenus($item);

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

        } catch (Exception $e) {
            $db->rollBack();
            $result['message'] = 'Database Error.';
            //              throw $e;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => $result));
        }
    }
	public function request(){
        // Check resource approval
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));


        // Make form
        $form = new Sesstore_Form_Member_Request();

        // Process form
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->addMember($viewer)->setUserApproved($viewer);

                // Add notification
                $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
                $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'estore_approve');

                $db->commit();
                $messgae = 'Successfully requested.';
            } catch (Exception $e) {
                $db->rollBack();
                $messgae = 'database_error';
            }

            return $messgae;
        }
    }
	function likeAction() {

		if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}

		$type = 'sesproduct';
		$dbTable = 'sesproducts';
		$resorces_id = 'product_id';
		$notificationType = 'liked';
		$actionType = 'sesproduct_product_like';

		if($this->_getParam('type',false) && $this->_getParam('type') == 'sesproduct_album'){
				$type = 'sesproduct_album';
			$dbTable = 'albums';
			$resorces_id = 'album_id';
			$actionType = 'sesproduct_album_like';
			} else if($this->_getParam('type',false) && $this->_getParam('type') == 'sesproduct_photo') {
				$type = 'sesproduct_photo';
			$dbTable = 'photos';
			$resorces_id = 'photo_id';
			$actionType = 'sesproduct_photo_like';
			}else if($this->_getParam('type',false) && $this->_getParam('type') == 'sesproduct_wishlist'){
		  $type = 'sesproduct_wishlist';
			$dbTable = 'wishlists';
			$resorces_id = 'wishlist_id';
			$actionType = 'liked';
		}

		$item_id = $this->_getParam('id',$this->_getParam('product_id'));
		if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
		}

		$viewer = Engine_Api::_()->user()->getViewer();
		$viewer_id = $viewer->getIdentity();

		$itemTable = Engine_Api::_()->getDbtable($dbTable, 'sesproduct');
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
                $temp['data']['message'] = $this->view->translate('Product Successfully Unliked.');
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
				$temp['data']['message'] = $this->view->translate('Product Successfully Liked.');
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
			  if($subject && empty($subject->title) && $this->_getParam('type') == 'sesproduct_photo') {
				$album_id = $subject->album_id;
				$subject = Engine_Api::_()->getItem('sesproduct_album', $album_id);
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
		if ($this->_getParam('type') == 'sesproduct') {
		  $type = 'sesproduct';
		  $dbTable = 'sesproducts';
		  $resorces_id = 'product_id';
		  $notificationType = 'sesproduct_product_favourite';
		} else if ($this->_getParam('type') == 'sesproduct_photo') {
		  $type = 'sesproduct_photo';
		  $dbTable = 'photos';
		  $resorces_id = 'photo_id';
		 // $notificationType = 'sesevent_favourite_playlist';
		}elseif ($this->_getParam('type') == 'sesproduct_wishlist') {
		  $type = 'sesproduct_wishlist';
		  $dbTable = 'wishlists';
		  $resorces_id = 'wishlist_id';
		  $notificationType = 'sesproduct_wishlist_favourite';
		}
		 else if ($this->_getParam('type') == 'sesproduct_album') {
		  $type = 'sesproduct_album';
		  $dbTable = 'albums';
		  $resorces_id = 'album_id';
		 // $notificationType = 'sesevent_favourite_playlist';
		}
		
		$item_id = $this->_getParam('id',$this->_getParam('product_id'));
		if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
		}
		$viewer = Engine_Api::_()->user()->getViewer();
		$Fav = Engine_Api::_()->getDbTable('favourites', 'sesproduct')->getItemfav($type, $item_id);

		$favItem = Engine_Api::_()->getDbtable($dbTable, 'sesproduct');
		if (count($Fav) > 0) {
		  //delete
		  $db = $Fav->getTable()->getAdapter();
		  $db->beginTransaction();
		  try {
			$Fav->delete();
			$db->commit();
			 $temp['data']['message'] = 'Product Successfully Unfavourited.';
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
			$temp['data']['like_count'] = $item->like_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
		} else {
		  //update
		  $db = Engine_Api::_()->getDbTable('favourites', 'sesproduct')->getAdapter();
		  $db->beginTransaction();
		  try {
			$fav = Engine_Api::_()->getDbTable('favourites', 'sesproduct')->createRow();
			$fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
			$fav->resource_type = $type;
			$fav->resource_id = $item_id;
			$fav->save();
			$favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
					), array(
				$resorces_id . '= ?' => $item_id,
			));
			// Commit
			$db->commit();
			$temp['data']['message'] = 'Product Successfully Favourited.';
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
		$id = $this->_getParam('product_id', null);
		$product_id = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->getProductId($id);
		if(!Engine_Api::_()->core()->hasSubject())
		  $sesproduct = Engine_Api::_()->getItem('sesproduct', $product_id);
		else
		  $sesproduct = Engine_Api::_()->core()->getSubject();

		if( !$this->_helper->requireSubject()->isValid() )
		   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array())));

		if( !$this->_helper->requireAuth()->setAuthParams($sesproduct, $viewer, 'view')->isValid() )
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array())));

		if( !$sesproduct || !$sesproduct->getIdentity() || ($sesproduct->draft && !$sesproduct->isOwner($viewer)) )
		  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

		//Privacy: networks and member level based
		if (Engine_Api::_()->authorization()->isAllowed('sesproduct', $sesproduct->getOwner(), 'allow_levels') || Engine_Api::_()->authorization()->isAllowed('sesproduct', $sesproduct->getOwner(), 'allow_networks')) {
			$returnValue = Engine_Api::_()->sesproduct()->checkPrivacySetting($sesproduct->getIdentity());
			if ($returnValue == false) {
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			}
		}
		$result = array();
		 $tabcounter = 0;
		/* $result['menus'][$tabcounter]['name'] = 'posts';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Posts');
        $tabcounter++; */
        $result['menus'][$tabcounter]['name'] = 'info';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Info');
        $tabcounter++;
        $result['menus'][$tabcounter]['name'] = 'album';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Albums');
        $tabcounter++;
		$result['menus'][$tabcounter]['name'] = 'comment';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Comments');
        $tabcounter++;
		$result['menus'][$tabcounter]['name'] = 'upsell';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Upsell Products');
        $tabcounter++;
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct_enable_location', 1)){
            $result['menus'][$tabcounter]['name'] = 'map';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Locations');
            $tabcounter++;
        }
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesvideo')){
			$result['menus'][$tabcounter]['name'] = 'video';
			$result['menus'][$tabcounter]['label'] = $this->view->translate('Videos');
			$tabcounter++;
        }
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.allow.review', 1)){
            $result['menus'][$tabcounter]['name'] = 'review';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Reviews');
			$tabcounter++;
        }
		
		
		$photoTable = Engine_Api::_()->getDbTable('slides','sesproduct');
		$photoPaginator = $photoTable->fetchAll($photoTable->select()->where('product_id =?',$sesproduct->getIdentity())->where('enabled =?',1));
		$photoCounter = 0;
		if(count($photoPaginator)){
			foreach($photoPaginator as $photo){
				$file = Engine_Api::_()->getItem('storage_file',$photo->file_id);	
				if($photo->type == 1){
					$result['slider_images'][$photoCounter]['type'] = 'video';
					$result['slider_images'][$photoCounter]['value'] = $photo->code;
				}else{
					$result['slider_images'][$photoCounter]['type'] = 'image';
					$result['slider_images'][$photoCounter]['value'] = $this->getBaseUrl(true,$file->map());
				}
				$photoCounter++;
			}
		}else{
			$result['slider_images'][$photoCounter]['type'] = 'image';
			$result['slider_images'][$photoCounter]['value'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/nophoto_product_thumb_profile.png');
		}
		
		$result['product'] = $this->getproduct($sesproduct);
		
	
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
    public function getproduct($product){
		$productdata = array();
        $productdata = $product->toArray();
        /* $productdata['description'] = $product->body; */
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$store = Engine_Api::_()->getItem('stores',$product->store_id);
		$productdata['store_logo'] = $this->getBaseUrl(true,$store->getPhotoUrl('thumb.icon'));
		$productdata['store_title'] = $store->title;
		
		$currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
    $productdata['currency'] = $curArr[$currency];
		
		$productdata['creation_date'] = $product->publish_date ? $product->publish_date : $product->creation_date;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.purchasenote', 1)) {
			if((empty($product->manage_stock) || $product->stock_quatity) && empty($product->outofstock) ){
				$productdata['stock_label'] = $this->view->translate("In Stock");
			}else{
				$productdata['stock_label'] = $this->view->translate("Out of Stock");
			}
		}
		
		
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.location', 1)){
			$location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sesproduct', $product->getIdentity());
            if ($location) {
                $productdata['location'] = $location->toArray();
                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.map.integration', 1)) {
                    $productdata['location']['showMap'] = true;
                } else {
                    $productdata['location']['showMap'] = false;
                }
            }
		}
		$rating = Engine_Api::_()->getDbTable('Sesproductreviews','sesproduct')->getRating($product->getIdentity());
		$productdata['rating'] =$this->view->locale()->toNumber(round($rating,1));
		$totalReviewCount = (int)Engine_Api::_()->getDbTable('sesproductreviews','sesproduct')->getReviewCount(array('product_id'=>$product->getIdentity()))[0];
		$productdata['review_count'] = $this->view->locale()->toNumber(round($totalReviewCount,1));
		
		$optionCounter = 0;
		if($viewer_id){
			$productdata['options'][$optionCounter]['name'] = 'createreview';
			$productdata['options'][$optionCounter]['label'] = $this->view->translate('Write a Review');
			$optionCounter++;
		
		}
		
    $productdata['options'][$optionCounter]['name'] = 'feed_link';
    $productdata['options'][$optionCounter]['label'] = $this->view->translate('Copy link');
    $productdata['options'][$optionCounter]['url'] =  $this->getBaseUrl(false,$product->getHref());
    $optionCounter++;
  
		if($product->discount) {
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.start.date', 1) && isset($product->discount_start_date)) { 
				$productdata['discount_start_date'] =  date('M d, Y',strtotime($product->discount_start_date));
			}
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.end.date', 1)  && isset($product->discount_end_date)) { 
				$productdata['discount_end_date'] =  date('M d, Y',strtotime($product->discount_end_date));
			}
			
		}
		
		
		if(Engine_Api::_()->sesproduct()->saleRunning($product,$viewer->getIdentity())  && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.sale', 1)){
			$productdata['is_enable_sale'] = $this->view->translate("Sale");
		}
		
		
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.sharing', 1)){
			$productdata["share"]["imageUrl"] = $this->getBaseUrl(false, $product->getPhotoUrl());
			$productdata["share"]["url"] = $this->getBaseUrl(false,$product->getHref());
			$productdata["share"]["title"] = $product->getTitle();
			$productdata["share"]["description"] = strip_tags($product->getDescription());
			$productdata["share"]["setting"] = $shareType;
			$productdata["share"]['urlParams'] = array(
				"type" => $product->getType(),
				"id" => $product->getIdentity()
			);
	 
		}
		$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesproduct')->isFavourite(array('resource_type'=>'sesproduct','resource_id'=>$product->product_id));
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.favourite', 1)){
			$productdata['is_content_favourite'] = $favStatus ? true:false;
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.wishlist', 1)){
			$productdata['options'][$optionCounter]['name'] = 'addtowishlist';
			$productdata['options'][$optionCounter]['label'] = $this->view->translate('Add to Wishlist');
			$optionCounter++;
		}
		
		if($product->discount && $priceDiscount = Engine_Api::_()->sesproduct()->productDiscountPrice($product)){
			$productdata["price_with_discount"] = $priceDiscount;
			if($product->discount_type == 0){ 
				$productdata["product_price"] = $this->view->translate("%s%s OFF",str_replace('.00','',$product->percentage_discount_value),"%");
			}else{
				$productdata["product_price"] = $this->view->translate("%s OFF",Engine_Api::_()->sesproduct()->getCurrencyPrice($product->fixed_discount_value));
			}
		}else{
			$productdata["product_price"] = $product->price > 0 ? Engine_Api::_()->sesproduct()->getCurrencyPrice($product->price) : $this->view->translate('FREE');
		}
		$paymentGateways = Engine_Api::_()->sesproduct()->checkPaymentGatewayEnable();
		$paymentMethods = $paymentGateways['methods'];
		$paymentMethodsCounter = 0;
		if(in_array('paypal',$paymentMethods)){
			$productdata["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Paypal');
			$productdata["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/paypal.png');
			$paymentMethodsCounter++;
			
		}
		if(in_array(0,$paymentMethods)){
			$productdata["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cash on Delivery');
			$productdata["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cash.png');
			$paymentMethodsCounter++;
		}
		if(in_array(1,$paymentMethods)){
			$productdata["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cheque');
			$productdata["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cheque.png');
			$paymentMethodsCounter++;
		}
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.purchasenote', 1) && $product->purchase_note) {
			$productdata["purchase_note"] = $product->purchase_note;
		}
		$memberAllowed = Engine_Api::_()->sesproduct()->memberAllowedToBuy($product);
		$sellerAllowed = Engine_Api::_()->sesproduct()->memberAllowedToSell($product);
		if($memberAllowed && $sellerAllowed){
			if(!empty($productLink['status'])){
				$productdata["can_add_to_cart"] = 1;
			}else{
				$productdata["can_add_to_cart"] = 0;
			}
		}
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enablecomparision',1)) {
			$existsCompare = Engine_Api::_()->sesproduct()->checkAddToCompare($product);
			$compareData = Engine_Api::_()->sesproduct()->compareData($product);
			$productdata["can_compre"] = 1;
		}else{
			$productdata["can_compre"] = 0;
		}
		
		
        // Get category
           
        if($product->category_id != '' && intval($product->category_id) && !is_null($product->category_id)) {
			$category = Engine_Api::_()->getItem('sesproduct_category', $product->category_id);
            if ($category) {
                $productdata['category_title'] = $category->category_name;
                if ($product->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('sesproduct_category', $product->subcat_id);
                    if ($subcat) {
                        $productdata['subcategory_title'] = $subcat->category_name;
                        if ($product->subsubcat_id) {
							$subsubcat = Engine_Api::_()->getItem('sesproduct_category', $product->subsubcat_id);
                            if ($subsubcat) {
                                $productdata['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }

        
        foreach ($product->tags()->getTagMaps() as $tagmap) {
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
            $productdata['tag'] = $tags;

        }
       
		
		$edit_product = Engine_Api::_()->getDbtable('dashboards', 'sesproduct')->getDashboardsItems(array('type' => 'edit_product'));
		$edit_photo = Engine_Api::_()->getDbtable('dashboards', 'sesproduct')->getDashboardsItems(array('type' => 'edit_photo'));
		if(!empty($edit_product) && $edit_product->enabled){
			$productdata['options'][$optionCounter]['name'] = 'edit';
			$productdata['options'][$optionCounter]['label'] = $this->view->translate($edit_product->title);
		}
		if(!empty($edit_photos) && $edit_photos->enabled){
			$productdata['options'][$optionCounter]['name'] = 'uploadphoto';
			$productdata['options'][$optionCounter]['label'] = $this->view->translate($edit_photo->title);
			$optionCounter++;
		}
		
		if($product->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){ 
			$productdata['options'][$optionCounter]['name'] = 'addtowishlist';
			$productdata['options'][$optionCounter]['label'] = $this->view->translate('Delete Product');
			$optionCounter++;
        }
        return $productdata;
    }
	public function claimAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		if( !$viewer || !$viewer->getIdentity() ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if( !$this->_helper->requireUser()->isValid() ){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if(!Engine_Api::_()->authorization()->getPermission($viewer, 'estore_store', 'auth_claim')){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		$store_id = $this->_getParam('store_id',0);
		$store = null;
		if($store_id){
			$store = Engine_Api::_()->getItem('estore_store', $store_id);
		}
		$store_title = $store->getTitle();
		if($store_title)
			$_POST['title'] = $store_title;
		$form = new Sesstore_Form_Claim();
		if(isset($_POST))
		$form->populate($_POST);
	 // check for claim already exist or not
		$storeClaimTable = Engine_Api::_()->getDbtable('claims', 'estore');
		$storeClaimTableName = $storeClaimTable->info('name');
		$selectClaimTable = $storeClaimTable->select()
		  ->from($storeClaimTableName, 'store_id')
		  ->where('user_id =?', $viewer->getIdentity());
		  $selectClaimTable->where('store_id =?', $store_id);
		$claimedStores = $storeClaimTable->fetchAll($selectClaimTable);
		if(count($claimedStores->toArray()) >0){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
		}
		if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'estore_store'));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
        }
		$values = $form->getValues();
		// Process
		$table = Engine_Api::_()->getDbtable('claims', 'estore');
		$db = $table->getAdapter();
		$db->beginTransaction();
		try {
			// Create Claim
			$viewer = Engine_Api::_()->user()->getViewer();
			$estoreClaim = $table->createRow();
			$estoreClaim->user_id = $viewer->getIdentity();
			$estoreClaim->store_id = $values['store_id'];
			$estoreClaim->title = $values['title'];
			$estoreClaim->user_email = $values['user_email'];
			$estoreClaim->user_name = $values['user_name'];
			$estoreClaim->contact_number = $values['contact_number'];
			$estoreClaim->description = $values['description'];
			$estoreClaim->save();
			// Commit
			$db->commit();
		}
		catch( Exception $e ) {
			$db->rollBack();
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
		}

		$mail_settings = array('sender_title' => $values['user_name']);
		$body = '';
		$body .= $this->view->translate("Email: %s", $values['user_email']) . '<br />';
		if(isset($values['contact_number']) && !empty($values['contact_number']))
		$body .= $this->view->translate("Claim Owner Contact Number: %s", $values['contact_number']) . '<br />';
		$body .= $this->view->translate("Claim Reason: %s", $values['description']) . '<br /><br />';
		$mail_settings['message'] = $body;
		$storeItem = Engine_Api::_()->getItem('estore_store', $values['store_id']);
		$storeOwnerId = $storeItem->owner_id;
		$owner = $storeItem->getOwner();
		$storeOwnerEmail = Engine_Api::_()->getItem('user', $storeOwnerId)->email;
		$fromAddress = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'admin@' . $_SERVER['HTTP_HOST']);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($storeOwnerEmail, 'estore_store_owner_claim', $mail_settings);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($fromAddress, 'estore_site_owner_for_claim', $mail_settings);
		//Send notification to store owner
		Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $storeItem, 'sesuser_claim_store');
		//Send notification to all superadmins
		$getAllSuperadmins = Engine_Api::_()->user()->getSuperAdmins();
		foreach($getAllSuperadmins as $getAllSuperadmin) {
		  Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($getAllSuperadmin, $viewer, $storeItem, 'sesuser_claimadmin_store');
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
	}
	function getNavigation($store,$viewer){
    $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_profile');
    $navigationCounter = 0;
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
	$canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'estore_store', 'store_can_join');
    foreach ($navigation as $link) {
  $class = end(explode(' ', $link->class));
        $label = $this->view->translate($link->getLabel());
   if ($class != "estore_profile_addtoshortcut") {
            $action = '';
            if ($class == 'estore_profile_dashboard') {
                $label = $label;
                $action = 'dashboard';
                $baseurl = $this->getBaseUrl();
                $custumurl = $store->custom_url;
                $pluralurl = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_stores_manifest', null);
                if ($pluralurl) {
                    $url = $baseurl . $pluralurl . '/dashboard/edit/' . $custumurl;
                } else {
                    $url = $baseurl . 'dashboard/edit/' . $custumurl;
                }
                $value = $url;
            } elseif ($class == 'estore_profile_member') {
      $row = $store->membership()->getRow($viewer);
      if (null === $row) {
			if ($store->membership()->isResourceApprovalRequired()) {
					$action = 'request';
				} else {
					$action = 'join';
				}
			} else if ($row->active) {
				if (!$store->isOwner($viewer)) {
					$action = 'leave';
				}
			}
            } elseif ($class == 'estore_profile_invite') {
                $action = 'invite';
            } elseif ($class == 'estore_profile_report') {
       if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.report', 1))
         continue;
                $action = 'report';
            } elseif ($class  == 'estore_profile_share') {
                $action = 'share';
            } elseif ($class  == 'estore_profile_member') {
                $action = 'join';
            } elseif ($class == 'estore_profile_delete') {
                $action = 'delete';
            } elseif ($class == 'estore_profile_substore') {
                $action = 'createAssociateStore';
            } elseif ($class == 'estore_profile_like') {
                $action = 'likeasyourstore';
            } elseif ($class == 'estore_profile_unlike') {
                $action = 'unlikeasyourstore';
            }
            if ($class == 'estore_profile_dashboard') {
                $result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $result[$navigationCounter]['value'] = $value;
                $navigationCounter++;
				if($this->_helper->requireAuth()->setAuthParams('estore_store', null, 'edit')->isValid()){
					 $result[$navigationCounter]['label'] = $this->view->translate('Edit Store');
                $result[$navigationCounter]['name'] = 'edit';
                $navigationCounter++;
				}
            }elseif($class == 'estore_profile_delete'){
				if(!$this->_helper->requireAuth()->setAuthParams('estore_store', null, 'delete')->isValid())
					continue;
				$result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $navigationCounter++;
			} else {
                $result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $navigationCounter++;
            }

        }

  }

  /*if ($canJoin) {
		$joincounter = 0;
		if ($viewerId) {
		$row = $store->membership()->getRow($viewer);
		if (null === $row) {
			if ($store->membership()->isResourceApprovalRequired()) {
				$result[$navigationCounter]['name'] = 'request';
				$result[$navigationCounter]['label'] = 'Request Membership';
				$joincounter++;

			} else {
				$result[$navigationCounter]['name'] = 'join';
				$result[$navigationCounter]['label'] = 'Join Store';
				$joincounter++;
			}
		} else if ($row->active) {
			if (!$store->isOwner($viewer)) {
				$result[$navigationCounter]['label'] = 'Leave Store';
				$result[$navigationCounter]['name'] = 'leave';
				$joincounter++;
			}
		} else if (!$row->resource_approved && $row->user_approved) {
			$result[$navigationCounter]['label'] = 'Cancel Membership Request';
			$result[$navigationCounter]['name'] = 'cancel';
			$joincounter++;

		} else if (!$row->user_approved && $row->resource_approved) {
			$result[$navigationCounter]['label'] = 'Accept Membership Request';
			$result[$navigationCounter]['name'] = 'accept';
			$joincounter++;
			$result[$navigationCounter]['label'] = 'Ignore Membership Request';
			$result[$navigationCounter]['name'] = 'reject';
		}
	  }
	}
	*/

    return $result;
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
    public function relatedProducts($product){
		$value['category_id'] = $product->category_id;
		$value['widgetName'] = 'Similar Products';
		$paginator = Engine_Api::_()->getDbTable('sesproducts', 'sesproduct')->getSesproductsPaginator($value);
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = $this->getProducts($paginator);
        return $result;
    }
    public function photo($storeid){
        $params['store_id'] = $storeid;
        $paginator = Engine_Api::_()->getDbTable('photos', 'estore')
            ->getPhotoPaginator($params);
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);
        $i = 0;
        foreach ($paginator as $photos) {
            $images = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");
            if (!count($images)) {
                $images['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';
                $images['normal'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';

            }
            $result[$i]['images'] = $images;
            $result[$i]['photo_id'] = $photos->getIdentity();
            $result[$i]['album_id'] = $photos->album_id;
            $i++;

        }
        return $result;

    }
    public function infoAction(){
        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('product_id', null);
        if (!$id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (!Engine_Api::_()->core()->hasSubject()) {
            $product = Engine_Api::_()->getItem('sestore', $id);
        } else {
            $product = Engine_Api::_()->core()->getSubject();
        }
        $result['information'] = $this->getInformation($product);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }
	public function moreMembersAction(){
		$id = $this->_getParam('store_id',null);
		if(!$id){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('estore_store', $id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }
		$storecheck = false;
		if($this->_getParam('type',null) == 'like'){
			$coreLikeTable = Engine_Api::_()->getDbTable('likes', 'core');
			$select = $coreLikeTable->select()->from($coreLikeTable->info('name'), 'poster_id')
            ->where('resource_id =?', $store->store_id )
            ->where('resource_type =?', 'estore_store');
		}else if($this->_getParam('type',null) == 'follow'){
			$followTable = Engine_Api::_()->getDbTable('followers', 'estore');
			$select = $followTable->select()->from($followTable->info('name'), 'owner_id')
            ->where('resource_id =?', $store->store_id )
            ->where('resource_type =?', 'estore_store');
		}else if($this->_getParam('type',null) == 'favourite'){
			$favouriteTable = Engine_Api::_()->getDbTable('favourites', 'estore');
			$select = $favouriteTable->select()->from($favouriteTable->info('name'), 'owner_id')
            ->where('resource_id =?', $store->store_id )
            ->where('resource_type =?', 'estore_store');
		}else if($this->_getParam('type',null) == 'store'){
			$tableLikestores = Engine_Api::_()->getDbTable('likestores', 'estore');
			$select = $tableLikestores->select()->where('store_id =?', $store->store_id);
			$storecheck = true;
		}

		if($select){

			$Members = Zend_Paginator::factory($select);
		}
		$Members->setItemCountPerPage($this->_getParam('limit', 10));
		$Members->setCurrentPageNumber($this->_getParam('page', 1));

	   if(count($Members) && $storecheck) {
			$likeStoresCounter = 0;
			foreach ($Members as $likestore) {
				$item = Engine_Api::_()->getItem('estore_store', $likestore->like_store_id);
				if ($item) {
					$nameLike = $item->getTitle();;
					$image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
					if ($image) {
						$result['store_liked_by_this_store'][$likeStoresCounter]['images'] = $image;
					}
					if ($nameLike) {
						$result['store_liked_by_this_store'][$likeStoresCounter]['name'] = $nameLike;
					}
					$result['store_liked_by_this_store'][$likeStoresCounter]['store_id'] = $item->store_id;
				}
				$likeStoresCounter++;
			}
		}
	 if (count($Members) && !$storecheck && $this->_getParam('type',null) != 'like') {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        foreach ($Members as $user)
          $userIds[] = $user['owner_id'];
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
        $result = $this->memberResult($recipientsUsers);
      }else{
			  $Counter = 0;
        foreach ($Members as $member) {
          $item = Engine_Api::_()->getItem('user', $member['owner_id']);
          $nameLike = $item->getTitle();
          $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
          if ($image) {
            $result['members'][$Counter]['images'] = $image;
          }
          if ($nameLike) {
            $result['members'][$Counter]['name'] = $nameLike;
          }
          $result['members'][$Counter]['user_id'] = $item->user_id;
          $Counter++;
        }
      }
		}
		if (count($Members) && !$storecheck && $this->_getParam('type',null) == 'like') {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        foreach ($Members as $user)
          $userIds[] = $user['poster_id'];
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
        $result = $this->memberResult($recipientsUsers);

      }else{
			  $Counter = 0;
        foreach ($Members as $member) {
          $item = Engine_Api::_()->getItem('user', $member['poster_id']);
          $itemArray = $item->toArray();
          if(!empty($itemArray)){
            $nameLike = $item->getTitle();
          $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
          if ($image) {
            $result['members'][$Counter]['images'] = $image;
          }
          if ($nameLike) {
            $result['members'][$Counter]['name'] = $nameLike;
          }
          $result['members'][$Counter]['user_id'] = $item->user_id;
          $Counter++;
          }
        }
      }
		}
		// Set item count per store and current store number

        $extraParams['pagging']['total_page'] = $Members->getStores()->storeCount;
        $extraParams['pagging']['total'] = $Members->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $Members->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
	 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));
	}
	public function memberResult($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
        $memberEnable = true;
      }
      $followActive = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active',1);
      if($followActive){
        $unfollowText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow'));
        $followText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow'));
      }
      foreach($paginator as $member){
        $result['notification'][$counterLoop]['user_id'] = $member->getIdentity();
        $result['notification'][$counterLoop]['title'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());
        if(!empty($member->location))
           $result['notification'][$counterLoop]['location'] =   $member->location;
       //follow
        if($followActive && $viewer->getIdentity() && $viewer->getIdentity() != $member->getIdentity()){
            $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($member->user_id);
            if(!$FollowUser){
                $result['notification'][$counterLoop]['follow']['action'] = 'follow';
                $result['notification'][$counterLoop]['follow']['text'] = $followText;
            }else{
                $result['notification'][$counterLoop]['follow']['action'] = 'unfollow';
                $result['notification'][$counterLoop]['follow']['text'] = $unfollowText;
            }
        }
       if(!empty($memberEnable)){
        //mutual friends
        $mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
        if(!$member->isSelf($viewer)){
           $result['notification'][$counterLoop]['mutualFriends'] = $mfriend == 1 ? $mfriend.$this->view->translate(" mutual friend") : $mfriend.$this->view->translate(" mutual friends");
        }
       }
        $result['notification'][$counterLoop]['user_image'] = $this->userImage($member->getIdentity(),"thumb.profile");
        $result['notification'][$counterLoop]['membership'] = $this->friendRequest($member);
        $counterLoop++;
      }
      return $result;
  }
	public function friendRequest($subject){

    $viewer = Engine_Api::_()->user()->getViewer();

    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return "";
    }

    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return "";
    }

    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if( !$eligible ) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

      $data = $select->query()->fetch();

      if( empty($data) ) {
        return '';
      }
    }

    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    if( !$direction ) {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        return array(
          'label' => $this->view->translate('Follow'),
          'action' => 'add',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
        return array(
          'label' => $this->view->translate('Cancel Request'),
          'action'=>'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      } else {
        // Unfollow
        return array(
          'label' => $this->view->translate('Unfollow'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      }
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',

        );
      } else {
        // Remove as follower?
        return array(
          'label' => $this->view->translate('Unfollow'),
           'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      }
      if( count($params) == 1 ) {
        return $params[0];
      } else if( count($params) == 0 ) {
        return "";
      } else {
        return $params;
      }
    }

    // Two-way mode
    else {

      $table =  Engine_Api::_()->getDbTable('membership','user');
      $select = $table->select()
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('user_id = ?', $subject->getIdentity());
      $select = $select->limit(1);
      $row = $table->fetchRow($select);

      if( null === $row ) {
        // Add
        return array(
          'label' => $this->view->translate('Add Friend'),
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          'action' => 'add',
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => $this->view->translate('Cancel Friend'),
          'action' => 'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',

        );
      } else {
        // Remove friend
        return array(
          'label' => $this->view->translate('Remove Friend'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      }
    }
  }
	public function userAge($member){
    $getFieldsObjectsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($member);
    if (!empty($getFieldsObjectsByAlias['birthdate'])) {
      $optionId = $getFieldsObjectsByAlias['birthdate']->getValue($member);
      if ($optionId && @$optionId->value) {
        $age = floor((time() - strtotime($optionId->value)) / 31556926);
        return $this->view->translate(array('%s year old', '%s years old', $age), $this->view->locale()->toNumber($age));
      }
    }
    return "";
  }
	public function getInformation($product){
		$counter =0;
		$result = array();
		$result['product_info'][$counter] = $product->toArray();
        $store = Engine_Api::_()->getItem('stores',$product->store_id);
		$result['product_info'][$counter]['store_logo'] = $this->getBaseUrl(true,$store->getPhotoUrl('thumb.icon'));
		$result['product_info'][$counter]['store_title'] = $store->title;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.location', 1)){
			$location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sesproduct', $product->getIdentity());
            if ($location) {
                $result['product_info'][$counter]['location'] = $location->toArray();
                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.map.integration', 1)) {
                    $result['product_info'][$counter]['location']['showMap'] = true;
                } else {
                    $result['product_info'][$counter]['location']['showMap'] = false;
                }
            }
		}
		
		if($product->category_id != '' && intval($product->category_id) && !is_null($product->category_id)) {
			$category = Engine_Api::_()->getItem('sesproduct_category', $product->category_id);
            if ($category) {
                $result['product_info'][$counter]['category_title'] = $category->category_name;
                if ($category->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('sesproduct_category', $category->subcat_id);
                    if ($subcat) {
                        $result['product_info'][$counter]['subcategory_title'] = $subcat->category_name;
                        if ($subcat->subsubcat_id) {
							$subsubcat = Engine_Api::_()->getItem('sesproduct_category', $subcat->subsubcat_id);
                            if ($subsubcat) {
                                $result['product_info'][$counter]['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }
		
		$paymentGateways = Engine_Api::_()->sesproduct()->checkPaymentGatewayEnable();
		$paymentMethods = $paymentGateways['methods'];
		$paymentMethodsCounter = 0;
		if(in_array('paypal',$paymentMethods)){
			$result['product_info'][$counter]["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Paypal');
			$result['product_info'][$counter]["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/paypal.png');
			$paymentMethodsCounter++;
			
		}
		if(in_array(0,$paymentMethods)){
			$result['product_info'][$counter]["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cash on Delivery');
			$result['product_info'][$counter]["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cash.png');
			$paymentMethodsCounter++;
		}
		if(in_array(1,$paymentMethods)){
			$result['product_info'][$counter]["payment_methods"][$paymentMethodsCounter]['label'] = $this->view->translate('Pay With Cheque');
			$result['product_info'][$counter]["payment_methods"][$paymentMethodsCounter]['image'] = $this->getBaseUrl(true,'/application/modules/Sesproduct/externals/images/cheque.png');
			$paymentMethodsCounter++;
		}
        $result['related_products'] = $this->relatedProducts($product);
		
        return $result;

    }
	public function memberAction(){

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('estore_store');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }

        // Get params
        $store = $this->_getParam('page', 1);
        $search = $this->_getParam('search');
        $waiting = $this->_getParam('waiting', false);

        // Prepare data
        $store = Engine_Api::_()->core()->getSubject();

        // get viewer
        $viewer = Engine_Api::_()->user()->getViewer();

        $result = array();
        if ($viewer->getIdentity() && ($store->isOwner($viewer))) {
            $waitingMembers = Zend_Paginator::factory($store->membership()->getMembersSelect(false));
            if ($waitingMembers->getTotalItemCount() > 0 && !$waiting) {
                $result['options']["label"] = $this->view->translate('See Waiting');
                $result['options']["name"] = 'waiting';
                $result['options']["value"] = '1';
            }
        }

        // if not showing waiting members, get full members
        $select = $store->membership()->getMembersObjectSelect();

        if ($search)
            $select->where('displayname LIKE ?', '%' . $search . '%');
        $fullMembers = Zend_Paginator::factory($select);

        if ($fullMembers->getTotalItemCount() > 0 && ($viewer->getIdentity() && ($store->isOwner($viewer))) && $waiting) {
            $result['options']["label"] = $this->view->translate('View all approved members');
            $result['options']["name"] = 'waiting';
            $result['options']["value"] = '0';
        }

        // if showing waiting members, or no full members
        if (($viewer->getIdentity() && ($store->isOwner($viewer))) && ($waiting || ($fullMembers->getTotalItemCount() <= 0 && $search == ''))) {
            $paginator = $waitingMembers;
            $waiting = true;
        } else {
            $paginator = $fullMembers;
            $waiting = false;
        }

        // Set item count per store and current store number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerStore', 10));
        $paginator->setCurrentPageNumber($this->_getParam('store', $store));

        $result['members'] = array();
        $counterLoop = 0;
        foreach ($paginator as $member) {
            if (!empty($member->resource_id)) {
                $memberInfo = $member;
                $member = Engine_Api::_()->getItem('user', $memberInfo->user_id);
            } else {
                $memberInfo = $store->membership()->getMemberInfo($member);
            }

            if (!$member->getIdentity())
                continue;
            $resource = $member->toArray();
            unset($resource['lastlogin_ip']);
            unset($resource['creation_ip']);
            $result['members'][$counterLoop] = $resource;
            $result['members'][$counterLoop]['owner_photo'] = Engine_Api::_()->sesapi()->getPhotoUrls($member, '', "");
//         $member->userImage($member->getIdentity(),'thumb.profile');
            if ($store->isOwner($viewer) && !$store->isOwner($member)) {
                $optionCounter = 0;
                if (!$store->isOwner($member) && $memberInfo->active == true) {
                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'removemember';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Remove Member');
                    $optionCounter++;
                }
                if ($memberInfo->active == false && $memberInfo->resource_approved == false) {
                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'approverequest';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Approve Request');
                    $optionCounter++;
                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'rejectrequest';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Reject Request');
                    $optionCounter++;
                }
                if ($memberInfo->active == false && $memberInfo->resource_approved == true) {

                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'cancelinvite';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Cancel Invite');
                    $optionCounter++;
                }
            }
            $counterLoop++;
        }


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }
	public function getChildCount(){
        return $this->_childCount;
    }
	public function announcementAction(){

        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('store_id', null);
        if (!$id) {

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing.'), 'result' => array()));
        }
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('estore_store', $id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }
        $paginator = Engine_Api::_()->getDbTable('announcements', 'estore')->getStoreAnnouncementPaginator(array('store_id' => $store->store_id));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = array();
        $announcementCounter = 0;
        foreach ($paginator as $announcement) {

            $result['announcements'][$announcementCounter]['announcement_id'] = $announcement->getIdentity();
            $result['announcements'][$announcementCounter]['title'] = $announcement->title;
            $result['announcements'][$announcementCounter]['creation_date'] = $announcement->creation_date;
            $result['announcements'][$announcementCounter]['detail'] = $announcement->body;
            $announcementCounter++;

        }


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));


    }
    public function servicesAction(){
        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('estore_store');

        if (!$subject) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }

        $paginator = Engine_Api::_()->getDbTable('services', 'estore')->getServicePaginator(array('store_id' => $subject->getIdentity(), 'widgettype' => 'widget'));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        //Manage Apps Check
        $isCheck = Engine_Api::_()->getDbTable('managestoreapps', 'estore')->isCheck(array('store_id' => $subject->getIdentity(), 'columnname' => 'service'));


        if (empty($isCheck)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $servicesCounter = 0;
        $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
        $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
        foreach ($paginator as $item) {
            $result['services'][$servicesCounter]['images']['main'] = $this->getBaseUrl(true,$item->getPhotoUrl());
            $result['services'][$servicesCounter]['title'] = $item->title;
            if ($item->duration && $item->duration_type) {
                $result['services'][$servicesCounter]['duration'] = $item->duration;
                $result['services'][$servicesCounter]['durationtype'] = $item->duration_type;
                $result["services"][$servicesCounter]['service_type'] = $item->duration.' '.$item->duration_type;
            }
            if ($item->price) {
                $result['services'][$servicesCounter]['price'] = $item->price;
                $result['services'][$servicesCounter]['service_price'] = $curArr[$currency].$item->price;
            }
            if ($item->description) {
                $result['services'][$servicesCounter]['description'] = $item->description;
            }

            $servicesCounter++;
        }


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));

    }
	public function mapAction(){

    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject() || !Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.location', 1)) {
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    }

    $product = Engine_Api::_()->core()->getSubject();
    
    
    $locations = Engine_Api::_()->getDbtable('locations', 'sesbasic')->getLocationData($product->getType(),$product->getIdentity());
	
	$locationCounter = 0 ;
	$result['locations'][$locationCounter] = $locations->toArray();
	$locationCounter++;
    /*$locationCounter = 0 ;
	foreach ($locations as $location) {
		
		$result['locations'][$locationCounter]['title'] = $location->title;
		$result['locations'][$locationCounter]['location'] = $location->location;
		$result['locations'][$locationCounter]['venue'] = $location->venue;
		$result['locations'][$locationCounter]['address'] = $location->address;
		$result['locations'][$locationCounter]['address2'] = $location->address2;
		$result['locations'][$locationCounter]['city'] = $location->city;
		$result['locations'][$locationCounter]['zip'] = $location->zip;
		$result['locations'][$locationCounter]['state'] = $location->state;
		$result['locations'][$locationCounter]['country'] = $location->country;

		$locationPhotos = Engine_Api::_()->getDbTable('locationphotos', 'estore')->getLocationPhotos(array('store_id' => $store->store_id, 'location_id' => $location->location_id));
		$photosCounter = 0;
		foreach ($locationPhotos as $photo) {
			$result['locations'][$locationCounter]['photos'][$photosCounter]['photoId'] = $photo->locationphoto_id;
			$result['locations'][$locationCounter]['photos'][$photosCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo, '', "");
			$photosCounter++;
		}
		$locationCounter++;
	}
	

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;*/


       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));


    }
	public function albumAction(){
		$productId = $this->_getParam('product_id');
        if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$product = Engine_Api::_()->core()->getSubject();
		}else{
			$product= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$product){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		
		$search = $this->_getParam('search',null);
		
		$order = $this->_getParam('sort','album_id');
		switch($order){
			case 'most_commented':
				$value['order'] = 'comment_count';
				break;
			case 'most_viewed':
				$value['order'] = 'view_count';
				break;
			case "most_liked":
				$value['order'] = 'like_count';
				break;
			case "creation_date":
				$value['order'] = 'creation_date';
				break;
		}

		if(!$orderval)
			$value['order'] = 'album_id';
		$value['product_id'] = $productId;
        $paginator = Engine_Api::_()->getDbTable('albums', 'sesproduct')->getAlbumSelect($value);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		
		if($allow_create && Engine_Api::_()->sesproduct()->checkProductAdmin($product)){
			$result['button']['label'] = $this->view->translate('Post New Video');
			$result['button']['name'] = 'create';
		}
		
		
        $albumCounter = 0;
        foreach ($paginator as $album) {
            $owner = $album->getOwner();
            $ownertitle = $owner->displayname;
            $result['albums'][$albumCounter] = $album->toArray();
            $photo = $image = Engine_Api::_()->sesproduct()->getAlbumPhoto($album->getIdentity(),$album->photo_id);
			$result['albums'][$albumCounter]['images'] = $this->getBaseUrl(true,$album->getPhotoUrl('thumb.normalmain')); 
				 
              /*   $result['albums'][$albumCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "") : $item->getPhotoUrl();
            else
                $result['albums'][$albumCounter]['images'] =  $this->getBaseUrl(true, $item->getPhotoUrl()); */
            $result['albums'][$albumCounter]['user_title'] = $ownertitle;
            $result['albums'][$albumCounter]['photo_count'] = $album->count();
            $albumCounter++;
        }

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));

    }
    public function associatedAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        $params['parent_id'] = $store->store_id;


        $paginator = $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result['stores'] = $this->getStores($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));


    }
    public function albumviewAction(){
        $albumid = $this->_getParam('album_id', 0);
        $storeId = $this->_getParam('store_id', 0);
        if (!$albumid) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->core()->getSubject();
            $album = Engine_Api::_()->getItem('estore_album', $albumid);
        } else {
            $album = Engine_Api::_()->getItem('estore_album', $albumid);
            $store = Engine_Api::_()->getItem('estore_store', $album->store_id);
        }

        $photoTable = Engine_Api::_()->getItemTable('estore_photo');
        $mine = true;
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        }
        $viewer_id = $viewer->getIdentity();

        $result['album'] = $album->toArray();
        $result['album']['user_title'] = $viewer->getOwner()->getTitle();
        $category = Engine_Api::_()->getItem('estore_category', $store->category_id);
        if ($category)
            $result['album']['category_title'] = $category->category_name;
		 $attachmentItem = $album;
            if ($attachmentItem->getPhotoUrl())
                $result['album']["share"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
						$result['album']["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
            $result['album']["share"]["title"] = $attachmentItem->getTitle();
            $result['album']["share"]["description"] = strip_tags($attachmentItem->getDescription());
            $result['album']["share"]['urlParams'] = array(
                "type" => $album->getType(),
                "id" => $album->getIdentity()
            );

        if ($viewer->getIdentity() > 0) {
            $canEdit = $editStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'edit');
            $editStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'edit');
            $canEditMemberLevelPermission = $editStoreRolePermission ? $editStoreRolePermission : $store->authorization()->isAllowed($viewer, 'edit');
            $deleteStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'delete');
            $canDeleteMemberLevelPermission = $deleteStoreRolePermission ? $deleteStoreRolePermission : $store->authorization()->isAllowed($viewer, 'delete');
        }

        $menusCounter = 0;
        if ($canEditMemberLevelPermission == 1) {
            if ($viewer->getIdentity() == $album->owner_id || $canEditMemberLevelPermission) {
                $result['album']['is_edit'] = true;
                $result['menus'][$menusCounter]['name'] = 'edit';
                $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
                $menusCounter++;
            } else {
                $result['album']['is_edit'] = false;
            }
        } else if ($canEditMemberLevelPermission == 2) {
            $result['album']['is_edit'] = true;
            $result['menus'][$menusCounter]['name'] = 'edit';
            $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
            $menusCounter++;
        } else {
            $result['album']['is_edit'] = false;
        }
        $result['album']['is_delete'] = true;
        if ($canDeleteMemberLevelPermission == 1) {
            if ($viewer->getIdentity() == $album->owner_id || $canDeleteMemberLevelPermission) {
                $result['album']['is_delete'] = true;
                $result['menus'][$menusCounter]['name'] = 'delete';
                $result['menus'][$menusCounter]['label'] = $this->view->translate('Delete');
                $menusCounter++;
            } else {
                $result['album']['is_delete'] = false;
            }
        } else if ($canDeleteMemberLevelPermission == 2) {
            $result['album']['is_delete'] = true;
            $result['menus'][$menusCounter]['name'] = 'delete';
            $result[$menusCounter]['label'] = $this->view->translate('Delete');
            $menusCounter++;
        } else {
            $result['album']['is_delete'] = false;
        }

        if ($viewer_id != $album->owner_id) {
            $result['menus'][$menusCounter]['name'] = 'report';
            $result['menus'][$menusCounter]['label'] = $this->view->translate("Report");
            $result['menus'][$menusCounter]["params"]['id'] = $album->getIdentity();
            $result['menus'][$menusCounter]["params"]['type'] = $album->getType();
            $menusCounter++;
        }
        $result['menus'][$menusCounter]['name'] = 'uploadphoto';
        $result['menus'][$menusCounter]['label'] = $this->view->translate("Upload more photos");
        $menusCounter++;

        $userimage = Engine_Api::_()->sesapi()->getPhotoUrls($album->getOwner(), '', "");

        $canComment = $store->authorization()->isAllowed($viewer, 'comment');


        if ($canComment)
            $result['album']['is_comment'] = true;
        else
            $result['album']['is_comment'] = false;

        $result['album']['user_image'] = $userimage;

        $paginator = $photoTable->getPhotoPaginator(array('album' => $album));
        $paginator->setItemCountPerPage('limit', 10);
        $paginator->setCurrentPageNumber('store_number', 1);

        $photoCounter = 0;
        foreach ($paginator as $photo) {
            $result['photos'][$photoCounter] = $photo->toArray();
            $result['photos'][$photoCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "");
            $albumLikeStatus = Engine_Api::_()->estore()->getLikeStatusStore($photo->photo_id, 'estore_photo');
            $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'estore')->isFavourite(array('resource_type' => 'estore_photo', 'resource_id' => $photo->photo_id));
            if ($albumLikeStatus)
                $result['photos'][$photoCounter]['like_status'] = true;
            else
                $result['photos'][$photoCounter]['like_status'] = false;
            if ($albumFavStatus)
                $result['photos'][$photoCounter]['fav-satus'] = true;
            else
                $result['photos'][$photoCounter]['fav-satus'] = false;

            if ($albumLikeStatus) {
                $result['photos'][$photoCounter]['is_content_like'] = true;
            } else {
                $result['photos'][$photoCounter]['is_content_like'] = false;
            }
            if ($albumFavStatus) {
                $result['photos'][$photoCounter]['is_content_favourite'] = true;
            } else {
                $result['photos'][$photoCounter]['is_content_favourite'] = false;
            }

            $photoCounter++;
        }


        $storeItem = $store;


        if (isset($album->art_cover) && $album->art_cover != 0 && $album->art_cover != '') {
            $albumArtCover = Engine_Api::_()->storage()->get($album->art_cover, '')->getPhotoUrl();
            $result['album']['albumArtCover'] = $this->getBaseurl(false, $albumArtCover);
            $result['album']['cover_pic'] = $this->getBaseurl(false, $albumArtCover);
        } else {
            $albumArtCover = '';
        }
        if(!$albumArtCover){
          $albumImage = Engine_Api::_()->estore()->getAlbumPhoto($album->getIdentity(), 0, 3);
          $countTotal = count($albumImage);
          $result['album']['image_count'] = $countTotal;
          $imageCounter = 0;
          foreach ($albumImage as $photo) {
              $imageURL[$imageCounter] =  $this->getBaseurl(false, $photo->getPhotoUrl('thumb.normalmain'));;
              $imageCounter++;
          }
          $result['album']['cover_pic'] = $imageURL;
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        if (count($result) > 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate(' There are no results that match your search. Please try again.'), 'result' => array()));


    }
    public function editalbumAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $album_id = $this->_getParam('album_id', false);

        if (!$album_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        else
            $album = Engine_Api::_()->getItem('estore_album', $album_id);

        $store = Engine_Api::_()->getItem('estore_store', $album->store_id);

        if ($store) {
            Engine_Api::_()->core()->setSubject($store);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        // Make form
        $form = new Sesstore_Form_Album_Edit();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'estore_store'));
        }
        $form->populate($album->toArray());
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        // Process
        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            $album->setFromArray($values);
            $album->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate("You have successfully edtited this album."))));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));

        }

    }
    public function deletealbumAction(){

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $album_id = $this->_getParam('album_id', false);
        if ($album_id)
            $album = Engine_Api::_()->getItem('estore_album', $album_id);
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));

        $store = Engine_Api::_()->getItem('estore_store', $album->store_id);
        if ($store) {
            Engine_Api::_()->core()->setSubject($store);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        // In smoothbox

        $form = new Sesstore_Form_Album_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'estore_store'));
        }
        if (!$album) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Album does not exists or not authorized to delete'), 'result' => array()));
        }

        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

        }

        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $album->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('Message' => $this->view->translate('album deleted successfully.'))));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));

        }
    }
    public function likeasstoreAction(){
        $id = $this->_getParam('id');
        if (!$id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => $result));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $store = Engine_Api::_()->getItem('estore_store', $id);
        $store_id = $this->_getParam('store_id');
        $table = Engine_Api::_()->getDbTable('storeroles', 'estore');

        $store_ids = $this->_getParam('store_ids');
        if($store_ids){
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            foreach($store_ids as $store_id){
              $row = $table->createRow();
              $row->store_id = $store_id;
              $row->like_store_id = $store->store_id;
              $row->user_id = $viewer->getIdentity();
              $row->save();
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Stores liked succuessfully.')));
        }

        if ($store_id) {
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            $row = $table->createRow();
            $row->store_id = $store_id;
            $row->like_store_id = $store->store_id;
            $row->user_id = $viewer->getIdentity();
            $row->save();
            $message = $this->view->translate('%s has been liked as Your store.', $store->getTitle());
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message)));
        }
        $selelct = $table->select()->where('user_id =?', $viewer->getIdentity())->where('memberrole_id =?', 1)->where('store_id !=?', $store->getIdentity())
            ->where('store_id NOT IN (SELECT store_id FROM engine4_estore_likestores WHERE like_store_id = ' . $store->store_id . ")");
        $myStores = ($table->fetchAll($selelct));
        if (count($myStores)) {
            $result = array();
            $result['title'] = 'Like ' . $store->getTitle() . ' as Your Store';
            $result['description'] = "Likes will show up on your Store's timeline. Which Store do you want to like " . $store->getTitle() . " as?";
            $result['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($store, '', "");
            $counter = 0;
            foreach ($myStores as $mystore) {
                $store = Engine_Api::_()->getItem('estore_store', $mystore->store_id);
                if (!$store)
                    continue;
                $storedata[$counter]['store_id'] = $store->store_id;
                $storedata[$counter]['store_title'] = $store->getTitle();
                $counter++;
            }
            $result['store'] = $storedata;
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

    }
    public function unlikeasstoreAction(){
        $id = $this->_getParam('id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $store = Engine_Api::_()->getItem('estore_store', $id);
        $store_id = $this->_getParam('store_id');
        $table = Engine_Api::_()->getDbTable('storeroles', 'estore');

        $store_ids = $this->_getParam('store_ids');
        if($store_ids){
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            foreach($store_ids as $store_id){
              $select = $table->select()->where('store_id =?', $store_id)->where('like_store_id =?', $store->getIdentity());
              $row = $table->fetchRow($select);
              if ($row)
                  $row->delete();
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Stores succuessfully removed.')));
        }
        if ($store_id) {
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            $select = $table->select()->where('store_id =?', $store_id)->where('like_store_id =?', $store->getIdentity());
            $row = $table->fetchRow($select);
            if ($row)
                $row->delete();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 'succuessfully removed.')));
        }
        $selelct = $table->select()->where('user_id =?', $viewer->getIdentity())->where('memberrole_id =?', 1)->where('store_id !=?', $store->getIdentity())
            ->where('store_id IN (SELECT store_id FROM engine4_estore_likestores WHERE like_store_id = ' . $store->store_id . ")");

        $myStores = ($table->fetchAll($selelct));
        if (count($myStores)) {
            $result = array();
            $result['title'] = "Remove " . $store->getTitle() . " from my Store's favorites";
            $result['description'] = "For which store would you like to remove  " . $store->getTitle() . " from favorites?";
            $result['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($store, '', "");
            $counter = 0;
            foreach ($myStores as $mystore) {
                $store = Engine_Api::_()->getItem('estore_store', $mystore->store_id);
                if (!$store)
                    continue;
                $storedata[$counter]['store_id'] = $store->store_id;
                $storedata[$counter]['store_title'] = $store->getTitle();

                $counter++;
            }
            $result['store'] = $storedata;

        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

    }
    public function leaveAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        if (!$this->_helper->requireSubject()->isValid()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $subject = Engine_Api::_()->core()->getSubject();
        $viewerId = $viewer->getIdentity();


        if ($subject->isOwner($viewer)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'estore_store', 'store_can_join') : 0;


        if (1) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->removeMember($viewer);
                $db->commit();
                $result['message'] = $this->view->translate('You have successfully left this store.');
               $result['menus'] = $this->getButtonMenus($subject);
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
        }
    }
    public function inviteAction(){
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject('estore_store')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

        // @todo auth
        // Prepare data
        $viewer = Engine_Api::_()->user()->getViewer();
        $store = Engine_Api::_()->core()->getSubject();

        // Prepare friends
        $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
        $friendsIds = $friendsTable->select()
            ->from($friendsTable, 'user_id')
            ->where('resource_id = ?', $viewer->getIdentity())
            ->where('active = ?', true)
            ->limit(100)
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);
        if (!empty($friendsIds)) {
            $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
        } else {
            $friends = array();
        }
        // Prepare form
        $form = new Sesstore_Form_Invite();

        $count = 0;
        foreach ($friends as $friend) {
            if ($store->membership()->isMember($friend, null)) {
                continue;
            }
            $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
            $count++;
        }
        if ($count == 1)
            $form->removeElement('all');
        else if (!$count)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have no friends you can invite.'))));
        if ($this->_getParam('getForm')) {
            if ($form->getElement('all'))
                $form->getElement('all')->setName('estore_choose_all');

            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }

        // Not posting
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
//        if (!$form->isValid($this->getRequest()->getPost())) {
//            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('validation_fail'), 'result' => array()));
//        }
        // Process
        $table = $store->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $usersIds = $form->getValue('users');

            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            foreach ($friends as $friend) {
                if (!in_array($friend->getIdentity(), $usersIds)) {
                    continue;
                }

                $store->membership()->addMember($friend)->setResourceApproved($friend);
                $notifyApi->addNotification($friend, $viewer, $store, 'estore_invite');
            }
            if ($count > 1) {
                $message = $this->view->translate('All members invited.');
            } else {
                $message = $this->view->translate('member invited.');
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function editoverviewAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        }
        $subject = Engine_Api::_()->core()->getSubject();
        if ($this->_getParam('getForm')) {
            $formFields = array();
            $formFields[0]['name'] = "overview";
            $formFields[0]['type'] = "Textarea";
            $formFields[0]['multiple'] = "";
            $formFields[0]['label'] = "Store Overview";
            $formFields[0]['description'] = "";
            $formFields[0]['isRequired'] = "1";
            $formFields[0]['value'] = $subject->overview;
            $formFields[1]['name'] = "submit";
            $formFields[1]['type'] = "Button";
            $formFields[1]['multiple'] = "";
            $formFields[1]['label'] = "Save Changes";
            $formFields[1]['description'] = "";
            $formFields[1]['isRequired'] = "0";
            $formFields[1]['value'] = '';
            $this->generateFormFields($formFields, array('resources_type' => 'estore_store'));
        }
        $subject->overview = $_POST['overview'];
        $subject->save();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Store overview saved successfully.'))));
    }
    public function overviewAction(){
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        $subject = Engine_Api::_()->core()->getSubject();
        $editOverview = $subject->authorization()->isAllowed($viewer, 'edit');

        if (!$editOverview && (!$subject->overview || is_null($subject->overview))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
        }

        if ($editOverview) {
          if ($subject->overview) {
              $result['button'][0]['name'] = "editoverview";
              $result['button'][0]['lable'] = $this->view->translate("Change Overview");
          } else {
              $result['button'][0]['name'] = "editoverview";
              $result['button'][0]['lable'] = $this->view->translate("Add Overview");
          }
        }
        if ($subject->overview) {
            $result['overview'] = $subject->overview;
        } else {
            $result['overview'] = $this->view->translate("There is currently no overview.");
        }


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
    }
	public function deleteAction(){
		$productId = $this->_getParam('product_id');
        if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$product = $sesproduct = Engine_Api::_()->core()->getSubject();
		}else{
			$product = $sesproduct= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$sesproduct){
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
            Engine_Api::_()->sesproduct()->deleteProduct($sesproduct);
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this product.'),'status' => true)));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function addmorephotosAction(){
        $album_id = $this->_getParam('album_id', false);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('estore_album', $album_id);
            $store_id = $album->store_id;
        } else {

        }

        $form = new Sesstore_Form_Album();
        $store = Engine_Api::_()->getItem('estore_store', $store_id);

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();

        $photoTable = Engine_Api::_()->getDbTable('photos', 'estore');
        $uploadSource = $_FILES['attachmentImage'];


        $photoArray = array(
            'store_id' => $store->store_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'estore')->getAdapter();
        $db->beginTransaction();
        try {
            foreach ($uploadSource['name'] as $name) {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
                $photo = $photo->setAlbumPhoto($images, false, false, $album);
                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['store_id'] = $store_id;
        $_POST['file'] = implode(' ', $uploadSource);
        $form->album->setValue($album_id);
        $album = $form->saveValues();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('album_id' => $album->album_id, 'message' => $this->view->translate('Photo added successfully.'))));
    }
	public function uploadphotoAction(){

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This store does not exist.'), 'result' => array()));
        $photo = $store->photo_id;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store->setPhoto($data, '', 'profile');

        $viewer = Engine_Api::_()->user()->getViewer();
        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'estore')->getPhotoId($store->photo_id);
        $photo = Engine_Api::_()->getItem('estore_photo', $getPhotoId);

        $storelink = '<a href="' . $store->getHref() . '">' . $store->getTitle() . '</a>';
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $photo, 'estore_store_profilephoto', null, array('storename' => $storelink));
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
            	$detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
			  if($detail_id) {
				$detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
				$detailAction->sesresource_id = $store->getIdentity();
				$detailAction->sesresource_type = $store->getType();
				$detailAction->save();
			  }
        }


        if ($action)
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);

        $file = array('main' => $store->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully photo uploaded.'), 'images' => $file)));
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
        throw new Sesstorevideo_Model_Exception($e->getMessage(), $e->getCode());
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
	public function removephotoAction(){

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        } else {
            $store = Engine_Api::_()->core()->getSubject();

        }
        if (!$store)
            $store = Engine_Api::_()->getItem('estore_store', $this->_getparam('store_id', null));

        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (isset($store->photo_id) && $store->photo_id > 0) {
            $store->photo_id = 0;
            $store->save();
        }
        $file = array('main' => $store->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully photo deleted.'), 'images' => $file));

//    echo json_encode(array('file' => $store->getPhotoUrl()));
//    die;
    }
	public function uploadcoverAction(){
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('estore_store', $this->_getparam('store_id', null));
		}else{
			$store = Engine_Api::_()->core()->getSubject();
		}
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $cover_photo = $store->cover;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store->setCoverPhoto($data);

        $viewer = Engine_Api::_()->user()->getViewer();
        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'estore')->getPhotoId($store->cover);
        $photo = Engine_Api::_()->getItem('estore_photo', $getPhotoId);
        $storelink = '<a href="' . $store->getHref() . '">' . $store->getTitle() . '</a>';
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $photo, 'estore_store_coverphoto', null, array('storename' => $storelink));
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
			$detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
      if($detail_id) {
        $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
        $detailAction->sesresource_id = $store->getIdentity();
        $detailAction->sesresource_type = $store->getType();
        $detailAction->save();
      }
        }
        if ($action)
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);
        if ($cover_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
            $im->delete();
        }
        $file['main'] = $store->getCoverPhotoUrl();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully cover photo uploaded.'), 'images' => $file)));
    }
	public function removecoverAction(){

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (isset($store->cover) && $store->cover > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $store->cover);
            $store->cover = 0;
            $store->save();
            $im->delete();
        }

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully deleted cover photo.'))));

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
    //edit photo details from lightbox
	public function editDescriptionAction() {
    $status = true;
    $error = false;

    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
      $status = false;
      $error = true;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('estore_photo');
    if ($status && !$error) {
      $values['title'] = $_POST['title'];
      $values['description'] = $_POST['description'];
      $values['location'] = $_POST['location'];
			//update location data in sesbasic location table
      if ($_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $_POST['photo_id'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesalbum_photo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      $db = $photo->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $photo->setFromArray($values);
        $photo->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array('')));
      }
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array($this->view->translate('Photo edited successfully.'))));
  }
    public function lightboxAction(){

        $photo = Engine_Api::_()->getItem('estore_photo', $this->_getParam('photo_id'));
        $store_id = $this->_getparam('store_id', $photo->store_id);

        if ($photo && !$this->_getParam('album_id', null)) {
            $album_id = $photo->album_id;
        } else {
            $album_id = $this->_getParam('album_id', null);
        }
        $store = Engine_Api::_()->getItem('estore_store', $store_id);

        if ($album_id && null !== ($album = Engine_Api::_()->getItem('estore_album', $album_id))) {
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid Request'), 'result' => array()));
        }

        $photo_id = $photo->getIdentity();

//        if (!$this->_helper->requireSubject('estore_photo')->isValid())
//          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        if (!$this->_helper->requireAuth()->setAuthParams('estore_store', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

        $viewer = Engine_Api::_()->user()->getViewer();

        $albumData = array();
        if ($viewer->getIdentity() > 0) {

            $menu = array();
            $counterMenu = 0;
            $menu[$counterMenu]["name"] = "save";
            $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
            $counterMenu++;

            $canEdit = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'edit');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "edit";
                $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
                $counterMenu++;
            }

            $can_delete = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'delete');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "delete";
                $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");
                $counterMenu++;
            }
            $menu[$counterMenu]["name"] = "report";
            $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");

            $counterMenu++;

            $menu[$counterMenu]["name"] = "makeprofilephoto";
            $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");
            $counterMenu++;
            $albumData['menus'] = $menu;
            $canComment = $store->authorization()->isAllowed($viewer, 'comment') ? true : false;

            $albumData['can_comment'] = $canComment;

            $sharemenu = array();
            if ($viewer->getIdentity() > 0) {
                $sharemenu[0]["name"] = "siteshare";
                $sharemenu[0]["label"] = $this->view->translate("Share");
            }
            $sharemenu[1]["name"] = "share";
            $sharemenu[1]["label"] = $this->view->translate("Share Outside");
            $albumData['share'] = $sharemenu;
        }

        $condition = $this->_getParam('condition');
        if (!$condition) {
            $next = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, ">="), true);
            $previous = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, "<"), true);
            $array_merge = array_merge($previous, $next);

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
                $recArray = array();
                $reactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getPaginator();
                $counterReaction = 0;

                foreach ($reactions as $reac) {
                    if (!$reac->enabled)
                        continue;
                    $albumData['reaction_plugin'][$counterReaction]['reaction_id'] = $reac['reaction_id'];
                    $albumData['reaction_plugin'][$counterReaction]['title'] = $this->view->translate($reac['title']);
                    $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
                    $albumData['reaction_plugin'][$counterReaction]['image'] = $icon['main'];
                    $counterReaction++;
                }

            }
        } else {
            $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, $condition), true);
        }
        $albumData['module_name'] = 'album';
        $albumData['photos'] = $array_merge;

        if (count($albumData['photos']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('No photo created in this album yet.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $albumData)));
    }
	public function nextPreviousImage($photo_id, $album_id, $condition = "<="){

        $photoTable = Engine_Api::_()->getItemTable('estore_photo');
        $select = $photoTable->select()
            ->where('album_id =?', $album_id)
            ->where('photo_id ' . $condition . ' ?', $photo_id)
            ->order('order ASC')
            ->limit(20);
        return $photoTable->fetchAll($select);
    }
	public function getPhotos($paginator, $updateViewCount = false){


        $result = array();
        $counter = 0;

        foreach ($paginator as $photos) {
            $photo = $photos->toArray();
            $photos->view_count = new Zend_Db_Expr('view_count + 1');
            $photos->save();
            $photo['user_title'] = $photos->getOwner()->getTitle();
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer_id = $viewer->getIdentity();
            if ($viewer_id != 0) {
                $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $photo['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($photos);
            }

            $attachmentItem = $photos;
            if ($attachmentItem->getPhotoUrl())
                $photo["shareData"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());

            $photo["shareData"]["title"] = $attachmentItem->getTitle();
            $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());

            $photo["shareData"]['urlParams'] = array(
                "type" => $photos->getType(),
                "id" => $photos->getIdentity()
            );

            if (is_null($photo["shareData"]["title"]))
                unset($photo["shareData"]["title"]);

            $owner = $photos->getOwner();
            $photo['owner']['title'] = $owner->getTitle();
            $photo['owner']['id'] = $owner->getIdentity();
            $photo["owner"]['href'] = $owner->getHref();
            $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");

            $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $photo['module_name'] = 'estore';
            if ($photo['can_comment']) {
                if ($viewer_id) {
                    $itemTable = Engine_Api::_()->getItemTable($photos->getType(), $photos->getIdentity());
                    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
                    $tableMainLike = $tableLike->info('name');
                    $select = $tableLike->select()
                        ->from($tableMainLike)
                        ->where('resource_type = ?', $photos->getType())
                        ->where('poster_id = ?', $viewer_id)
                        ->where('poster_type = ?', 'user')
                        ->where('resource_id = ?', $photos->getIdentity());
                    $resultData = $tableLike->fetchRow($select);
                    if ($resultData) {
                        $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
                        $photo['reaction_type'] = $item_activity_like->type;
                    }
                }

                $photo['resource_type'] = $photos->getType();
                $photo['resource_id'] = $photos->getIdentity();

                $table = Engine_Api::_()->getDbTable('likes', 'core');
                $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
                $coreliketableName = $coreliketable->info('name');
                $recTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->info('name');
                $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $photos->getIdentity())->group('type')->setIntegrityCheck(false);
                $select->where('resource_type =?', $photos->getType());
                $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
                $select->joinLeft($recTable, $recTable . '.reaction_id =' . $coreliketableName . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');
                $resultData = $table->fetchAll($select);
                $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $reactionData = array();
                $reactionCounter = 0;
                if (count($resultData)) {
                    foreach ($resultData as $type) {
                        $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                        $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                        $reactionCounter++;
                    }
                    $photo['reactionData'] = $reactionData;

                }

                if ($photo['is_like']) {
                    $photo['is_like'] = true;
                    $like = true;
                    $type = $photo['reaction_type'];
                    $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                    $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
                } else {
                    $photo['is_like'] = false;
                    $like = false;
                    $type = '';
                    $imageLike = '';
                    $text = 'Like';
                }
                if (empty($like)) {
                    $photo["like"]["name"] = "like";
                } else {
                    $photo["like"]["name"] = "unlike";
                }

                // Get tags
                $tags = array();
                foreach ($photos->tags()->getTagMaps() as $tagmap) {
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

                if ($tags)
                    $photo["tags"] = $tags;
                if ($type)
                    $photo["like"]["type"] = $type;
                if ($imageLike)
                    $photo["like"]["image"] = $imageLike;
                $photo["like"]["label"] = $this->view->translate($text);
                $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(), '', $photos->likes()->getLike($viewer), $viewer);
            }
            if (!count($album_photo['images']))
                $album_photo['images']['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl());
            $result[$counter] = array_merge($photo, $album_photo);
            $counter++;
        }
        return $result;
    }
	public function addAction() {
		$video_id = $this->_getParam('video_id', false);
		if ($video_id) {
		  $params['video_id'] = $video_id;
		  $insertVideo = Engine_Api::_()->estorevideo()->deleteWatchlaterVideo($params);
		}else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
		}
		if($insertVideo['status'] == 'insert'){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully added to watch later.'))));
		}else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully deleted from watch later.'))));
		}
	}
	
	
	
	
	function getVideos($paginator,$checkProfile){
		$counter = 0;
		$result = array();
		foreach ($paginator as $item){
			$result[$counter] = $item->toArray();
			
			if($isProductAdmin){
				$viewer = Engine_Api::_()->user()->getViewer();
				$viewerId = $viewer->getIdentity();
				$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
				if($viewerId>0) {
					$can_edit = Engine_Api::_()->authorization()->getPermission($viewer, 'video', 'edit');
					$can_delete = Engine_Api::_()->authorization()->getPermission($viewer, 'video', 'delete');
					$optionCounter = 0;
					if($can_edit){
						$result[$counter]['options'][$optionCounter]['label'] =$this->view->translate('Edit Video');
						$result[$counter]['options'][$optionCounter]['name'] = 'edit';
						$optionCounter++;
					}
					if($can_delete && $item->status !=2){
						$result[$counter]['options'][$optionCounter]['label'] =$this->view->translate('Delete Video');
						$result[$counter]['options'][$optionCounter]['name'] = 'delete';
						$optionCounter++;
					}
					
				}
				
			}
			
			
			if($item->status == 0)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Your video is in queue to be processed - you will be notified when it is ready to be viewed.');
			elseif($item->status == 2)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Your video is currently being processed - you will be notified when it is ready to be viewed');
			elseif($item->status == 3)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Video conversion failed. Please try %1$suploading again%2$s.', '<a href="'.$this->url(array('action' => 'create','module'=>'sesvideo','controller'=>'index'),'default',true).'/type/3'.'">', '</a>');
			elseif($item->status == 4)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Video conversion failed. Video format is not supported by FFMPEG. Please try %1$sagain%2$s.', '<a href="'.$this->url(array('action' => 'create','module'=>'sesvideo','controller'=>'index'),'default',true).'/type/3'.'">', '</a>');
			elseif($item->status == 5)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Video conversion failed. Audio files are not supported. Please try %1$sagain%2$s.', '<a href="'.$this->url(array('action' => 'create','module'=>'sesvideo','controller'=>'index'),'default',true).'/type/3'.'">', '</a>'); 
			elseif($item->status == 7)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Video conversion failed. You may be over the site upload limit.  Try %1$suploading%2$s a smaller file, or delete some files to free up space.', '<a href="'.$this->url(array('action' => 'create','module'=>'sesvideo','controller'=>'index'),'default',true).'/type/3'.'">', '</a>'); 
			elseif(!$item->approve)
				$result[$counter]['last_videoupload_status'] = $this->view->translate('Your video has been successfully submitted for approval to our adminitrators - you will be notified when it is ready to be viewed.');
			
			
			
			if($item->getType() == 'video'){
			  $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.video.rating',1);
			  $allowShowPreviousRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratevideo.show',1);
			  if($allowRating == 0){
				if($allowShowPreviousRating == 0)
				  $ratingShow = false;
				 else
				  $ratingShow = true;
			  }else
				$ratingShow = true;
			}else if($item->getType() == 'sesvideo_chanel'){
				$allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.chanel.rating',1);
				$allowShowPreviousRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratechanel.show',1);
				if($allowRating == 0){
					if($allowShowPreviousRating == 0)
						$ratingShow = false;
					else
						$ratingShow = true;
				}else
					$ratingShow = true;
			}else
				$ratingShow = true;
			
			$result[$counter]['rating_show'] = $ratingShow;
			$result[$counter]['image'] = $this->getBaseUrl(true,$item->getPhotoUrl());
			
			if(isset($item->duration) && $item->duration ){
				if( $item->duration >= 3600 ) {
                      $duration = gmdate("H:i:s", $item->duration);
				} else {
				  $duration = gmdate("i:s", $item->duration);
				}  
			}
			$result[$counter]['duration'] = $duration;
			
			if(Engine_Api::_()->user()->getViewer()->getIdentity() != '0'){
				if(isset($item->watchlater_id)){
					$result[$counter]['watch_later']['option']['label'] = $this->view->translate('Remove from Watch Later');
					$result[$counter]['watch_later']['option']['name'] = 'removewatchlater';
					$result[$counter]['hasWatchlater'] = true;
				}else{
					$result[$counter]['watch_later']['option']['label'] =$this->view->translate('Add to Watch Later');
					$result[$counter]['watch_later']['option']['name'] = 'addtowatchlater';
					$result[$counter]['hasWatchlater'] = false;
				}
				
				if(isset($item->chanel_id)){
					$itemtype = 'sesvideo_chanel';
					$getId = 'chanel_id';
				}else{
					$itemtype = 'sesvideo_video';
					$getId = 'video_id';
				}
				$canComment =  $item->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
				if($canComment){
					$LikeStatus = Engine_Api::_()->sesvideo()->getLikeStatusVideo($item->$getId,$item->getType());
					$result[$counter]['is_content_like'] = $LikeStatus?true:false;
					if(isset($item->favourite_count) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.favourite', 1)){
						$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesvideo')->isFavourite(array('resource_type'=>$itemtype,'resource_id'=>$item->$getId));
						$result[$counter]['is_content_favourite'] = $favStatus?true:false;
					}
					$addtoplaylist = false;
					if(empty($item->chanel_id)){
						$addtoplaylist = true;
						$result[$counter]['can_add_toplaylist'] = $addtoplaylist;
					}
				}
				
			}
			
			$owner = $item->getOwner();
			$result[$counter]['owner_title'] = $owner->getTitle();
			if($item->category_id != '' && intval($item->category_id) && !is_null($item->category_id)){ 
				$categoryItem = Engine_Api::_()->getItem('sesvideo_category', $item->category_id);
				if($categoryItem){
					$result[$counter]['category_title'] = $categoryItem->category_name; 
				}
			}
			$showMap = false;
			if(isset($item->location) && $item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesvideo_enable_location', 1)){
				$showMap = true;
			}
			
			$result[$counter]['show_location_map'] = $showMap ;
			$result[$counter]['rating'] = $this->view->locale()->toNumber(round($item->rating,1));
			
			
			
			
			
			

			if($item->code && $item->type == 'iframely'){
				$embedded = $item->code;
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				$result[$counter]['video']  = str_replace('//','https://',$match[1]);
			  }else{

				$result[$counter]['video']  = $match[1];
			  }
			}else{
				$embedded = $item->getRichContent(true,array(),'','');
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){

				$result[$counter]['video']  = str_replace('//','https://',$match[1]);
			  }else{

				$result[$counter]['video']  = $match[1];
			  }
			} 
			
			  $counter++;
		}
		return $result;
	}
	public function browsevideoAction(){
		$value['status'] = 1;
		$value['watchLater'] = true;
		$value['search'] = 1;
		$paginator = Engine_Api::_()->getDbTable('videos', 'estorevideo')->getVideo($value,$paginator = true);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$checkProfile['store_id'] = null;
		$data['videos'] = $this->getVideos($paginator,$checkProfile);
			$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
			$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
			$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
			$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
	
	public function profileUpsellAction(){
		$productId = $this->_getParam('product_id',null);
		if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$product = Engine_Api::_()->core()->getSubject();
		}else{
			$product= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$product){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		
		$paginator = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->getSesproductsPaginator(array('product_id'=>$product->product_id,'upsell'=>true,'manage-widget'=>true));
		
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		
		$data['upsell_product'] = count($paginator) ? $this->getProducts($paginator) : array();
	
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
		
		
	}
	public function profileVideosAction(){
		$productId = $this->_getParam('product_id',null);
		if(!$productId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$product = Engine_Api::_()->core()->getSubject();
		}else{
			$product= Engine_Api::_()->getItem('sesproduct',$storeId);
		}
		if(!$product){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		if (!$product->authorization()->isAllowed($viewer, 'view')){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
		}
		
		
		
		$allow_create = true;
		if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesproductpackage') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproductpackage.enable.package', 1)){
			$package = $product->getPackage();
			$viewAllowed = $package->getItemModule('video');
			if(!$viewAllowed)
				Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
			//allow upload video
			$allow_create = $allow_create = $package->allowUploadVideo($product);
		}
		
		$canUpload = Engine_Api::_()->sesproduct()->isProductAdmin($product, 'create');
		/* echo '<pre>';print_r($canUpload); */
		/* echo '<pre>';print_r($allow_create);die; */
		if($canUpload && $allow_create){
			$data['button']['label'] = $this->view->translate('Post New Video');
			$data['button']['name'] = 'create';
		}
		
		
		$paginator = Engine_Api::_()->getDbTable('videos', 'sesvideo')->getVideo(array('parent_id'=>$product->getIdentity(), 'parent_type' => $product->getType()));
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		
		
		/*
		$sort = $this->_getParam('sort', null);
		$search = $this->_getParam('search', null);
		
		$sortCounter = 0;
		$data['sort'][$sortCounter]['name'] = 'creation_date';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Recently Created');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_liked';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Liked');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_viewed';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Viewed');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_commented';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Commented');
		$sortCounter++;*/
		$isProductAdmin = Engine_Api::_()->sesproduct()->checkProductAdmin($product);
		
		$data['videos'] = $this->getVideos($paginator,$isProductAdmin);
		$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
		$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
		$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
		$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
    public function createVideoAction() {
    if (!$this->_helper->requireUser->isValid())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    // Upload video
	  if (isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name']))
      $_POST['id'] = $this->uploadVideoAction();
    $viewer = Engine_Api::_()->user()->getViewer();
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $values['user_id'] = $viewer->getIdentity();
    $parent_id = $parent_id = $this->_getParam('parent_id', null);
    $parent_type = $parent_type = 'estore_store';
    if( $parent_id &&  $parent_type)
        $parentItem = Engine_Api::_()->getItem($parent_type, $parent_id);
    if(!$parentItem)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $paginator = Engine_Api::_()->getApi('core', 'estorevideo')->getVideosPaginator($values);
    $quota = $quota = Engine_Api::_()->authorization()->getPermission($levelId, 'storevideo', 'max');
    $current_count = $currentCount = $paginator->getTotalItemCount();
	if (($current_count >= $quota) && !empty($quota)){
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already uploaded the maximum If you would like to upload a new video, please an old one first.'), 'result' => array()));
	}
    //Create form
    $form = $form = new Sesstorevideo_Form_Video();
	if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));
		$form->removeElement('lat');
		$form->removeElement('map-canvas');
		$form->removeElement('ses_location');
		$form->removeElement('lng');
		if($form->removeElement('is_locked'))
            $form->removeElement('password');
		if($form->removeElement('password'))
            $form->removeElement('password');

	 if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues('url');
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    }

    // Process
    $values = $form->getValues();
    $values['parent_id'] = $parent_id;
    $values['parent_type'] = $parent_type;
    $values['owner_id'] = $viewer->getIdentity();
    $insert_action = false;
    $db = Engine_Api::_()->getDbtable('videos', 'estorevideo')->getAdapter();
    $db->beginTransaction();
    try {
		$viewer = Engine_Api::_()->user()->getViewer();
		$isApproveUploadOption = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('storevideo', $viewer, 'video_approve');
		$approveUploadOption = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('storevideo', $viewer, 'video_approve_type');
		$approve = 1;
		if($isApproveUploadOption){
			foreach($approveUploadOption as $valuesIs){
				if ($values['type'] == 3 && $valuesIs == 'myComputer') {
					//my computer
					$approve = 0;
					break;
				}elseif($valuesIs == "iframely"){
             $approve = 0;
						 break;
          }
				}
			}

		//Create video
		$table = Engine_Api::_()->getDbtable('videos', 'estorevideo');
		if($values['type'] == 'iframely') {
			$information = $this->handleIframelyInformation($values['url']);
			if (empty($information)) {
				$form->addError('We could not find a video there - please check the URL and try again.');
			}
			$values['code'] = $information['code'];
			$values['thumbnail'] = $information['thumbnail'];
			$values['duration'] = $information['duration'];
			$video = $table->createRow();
		}
	  else if ($values['type'] == 3) {
		$video = Engine_Api::_()->getItem('storevideo', $this->_getParam('id'));
	  } else
        $video = $table->createRow();
		  if ($values['type'] == 3 && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
			$values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
		  }
        //disable lock if password not set.
        if (isset($values['is_locked']) && $values['is_locked'] && $values['password'] == '')
          $values['is_locked'] = '0';
		if(empty($_FILES['photo_id']['name'])){
			unset($values['photo_id']);
		}
		$values['approve'] = $approve;
        $video->setFromArray($values);
        $video->save();
        // Add fields
        $customfieldform = $form->getSubForm('fields');
        if (!is_null($customfieldform)) {
          $customfieldform->setItem($video);
          $customfieldform->saveValues();
        }
        $thumbnail = $values['thumbnail'];
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);
        if (@GetImageSize($thumbnail)) {
            $valid_thumb = true;
        } else {
            $valid_thumb = false;
        }
        if(isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '' && $values['type'] != 3 ){
            $video->photo_id = $this->setPhoto($form->photo_id, $video->video_id, true);
            $video->save();
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
                'parent_type' => $video->getType(),
                'parent_id' => $video->getIdentity()
            ));
            // Remove temp file
            @unlink($thumb_file);
            @unlink($tmp_file);
						$video->photo_id = $thumbFileRow->file_id;
						$video->save();
          } catch (Exception $e){
						 @unlink($thumb_file);
             @unlink($tmp_file);
						}
        }
			if($values['type'] == 'iframely') {
				$video->status = 1;
				$video->save();
				$video->type = 'iframely';
				$insert_action = true;
			}
			if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $video->video_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","storevideo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
          }
        if ($values['ignore'] == true) {
          $video->status = 1;
          $video->save();
          $insert_action = true;
        }
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_view']))
          $auth_view = $values['auth_view'];
        else
          $auth_view = "everyone";
        $viewMax = array_search($auth_view, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
        }
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_comment']))
          $auth_comment = $values['auth_comment'];
        else
          $auth_comment = "everyone";
        $commentMax = array_search($auth_comment, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $video->tags()->addTagMaps($viewer, $tags);
        $db->commit();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' ,'result' => array('message'=>$this->view->translate('Video created successfully.'),'video_id' => $video->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage() , 'result' =>array()));
      //throw $e;
    }
    $db->beginTransaction();
    try {
      if ($approve) {
        $owner = $video->getOwner();
        //Create Activity Feed

        if($parent_id && $parent_type) {
		      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $parentItem, 'estore_store_editeventvideo');
	        if ($action != null) {
	          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
	        }
        } else {
	        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'sespgvido_crte');
	        if ($action != null) {
	          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
	        }
        }
				// Rebuild privacy
				$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
				foreach ($actionTable->getActionsByObject($video) as $action) {
					$actionTable->resetActivityBindings($action);
				}
      }
      $db->commit();
	  $values = $form->getValues('url');
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Video created successfully.'),'approve'=>'1','video_id' => $video->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage() , 'result' =>array()));
      //throw $e;
    }
  }
    public function uploadVideoAction() {
    if (!$this->_helper->requireUser()->checkRequire()) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    if (!$this->getRequest()->isPost()) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $values = $this->getRequest()->getPost();
    if (empty($_FILES['Filedata'])) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('No file');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt','zip');
    if (in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $db = Engine_Api::_()->getDbtable('videos', 'estorevideo')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();
      $params = array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
      );
      $video = Engine_Api::_()->estorevideo()->createVideo($params, $_FILES['Filedata'], $values);
      $result['status'] = true;
      $result['name'] = $_FILES['Filedata']['name'];
      $result['code'] = $video->code;
      $result['video_id'] = $video->video_id;
      // sets up title and owner_id now just incase members switch store as soon as upload is completed
      $video->title = $_FILES['Filedata']['name'];
      $video->owner_id = $viewer->getIdentity();
      $video->save();
      $db->commit();
	   return $video->video_id;
    } catch (Exception $e) {
      $db->rollBack();
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('An error occurred.') . $e;
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
  }
    public function handleIframelyInformation($uri) {
        $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
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
        }
        if (!empty($iframely['meta']['title'])) {
            $information['title'] = $iframely['meta']['title'];
        }
        if (!empty($iframely['meta']['description'])) {
            $information['description'] = $iframely['meta']['description'];
        }
        if (!empty($iframely['meta']['duration'])) {
            $information['duration'] = $iframely['meta']['duration'];
        }
        $information['code'] = $iframely['html'];
        return $information;
    }
    public function viewVideoAction(){
		$videoid = $this->_getParam('video_id',null);
        if (Engine_Api::_()->core()->hasSubject()){
            $video = Engine_Api::_()->core()->getSubject('storevideo');
        }
        else if($videoid)
        {
            $video = Engine_Api::_()->getItem('storevideo', $videoid);
        }else{
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        }
		$result = $video->toArray();
		$result['user_title'] = $video->getOwner()->getTitle();
		$owneritem = Engine_Api::_()->getItem('user', $video->owner_id);
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "");
        $thumbimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "thumb.profile");

		$favStatus = Engine_Api::_()->getDbtable('favourites', 'estorevideo')->isFavourite(array('resource_type'=>$video->getType(),'resource_id'=>$video->getIdentity()));
		$LikeStatus = Engine_Api::_()->estorevideo()->getLikeStatusVideo($video->getIdentity(),$video->getType());
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.favourite', 1)){
			$result['is_content_favourite'] = $favStatus?true:false;
		}
		$result['is_content_like'] = $LikeStatus?true:false;
		if ($ownerimage){
			$result['owner_image'] = $ownerimage;

			$result['user_image'] = $thumbimage['main'];
		}

		$viewer = Engine_Api::_()->user()->getViewer();
		 if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
			 $viewer = Engine_Api::_()->user()->getViewer();
			  if ($viewer->getIdentity() == 0)
				$result['level'] = $level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			  else
				$result['level'] = $level = $viewer;
			$viewerId = $viewer->getIdentity();
			$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			  if (!Engine_Api::_()->authorization()->getPermission($levelId, 'storevideo', 'locked') && $video->is_locked) {
				$result['is_locekd'] = $locked = true;
			  } else {
				$result['is_locekd'] = $locked = false;
			  }
			  $result['password'] = $video->password;
		 }else{
			  $result['password'] =  true;
		 }
		 $videoTags = $video->tags()->getTagMaps();
			$can_embed = true;
			if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1)) {
			  $can_embed = false;
			} else if (isset($video->allow_embed) && !$video->allow_embed) {
			  $can_embed = false;
			}
			$result['can-embed'] = $can_embed = $can_embed;
			// increment count
			$embedded = "";
			$mine = true;
			if ($video->status == 1) {
			  if (!$video->isOwner($viewer)) {
				$video->view_count++;
				$video->save();
				$mine = false;
			  }
			 $embe = $video->getRichContent(true,array(),'',$autoPlay);
			  $result['embedded'] = str_replace("//cdn.iframe","https://cdn.iframe",$embe);
			}
			if($video->code && $video->type == 'iframely'){

				$embe = $video->code;
				//$embe = $video->getRichContent(true,array(),'',true);
			  //preg_match('/src="([^"]+)"/', $embe, $match);
			  //if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				//$result['iframeURL']  = str_replace('//','https://',$match[1]);
			 // }else{
				$result['iframeURL']  = $embe;
			  //}
			}else{

				$storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
				if ($storage_file) {
				  $result['iframeURL'] = $this->getBaseUrl('false',$storage_file->map());
				  $result['video_extension'] = $storage_file->extension;
				}
			}
			$photo = $this->getBaseUrl(false,$video->getPhotoUrl());
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.socialshare', 1)){
				if($photo)
			 $result["share"]["imageUrl"] = $photo;
		 	$result["share"]["url"] = $this->getBaseUrl(false,$video->getHref());
			$result["share"]["title"] = $video->getTitle();
			  $result["share"]["description"] = strip_tags($video->getDescription());
			  $result["share"]['urlParams'] = array(
				  "type" => $video->getType(),
				  "id" => $video->getIdentity()
			  );
			}
			if(is_null($response['video']["share"]["title"]))
			  unset($response['video']["share"]["title"]);
			if ($video->type == 3 && $video->status == 1) {
			  if (!empty($video->file_id)) {
				$storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
				if ($storage_file) {
				  $result['video_location']  = $storage_file->map();
				  $result['video_extension']  = $storage_file->extension;
				}
			  }
			}
			 $result['allowShowRating']  = $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.show', 1);
			$result['allowRating'] = $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.video.rating', 1);
			$result['getAllowRating'] = $allowRating;
			if ($allowRating == 0) {
			  if ($allowShowRating == 0)
				$showRating = false;
			  else
				$showRating = true;
			} else
			  $showRating = true;
			$result['showRating']  = $showRating;
			if ($showRating) {
			  $result['canRate'] = $canRate = Engine_Api::_()->authorization()->isAllowed('storevideo', $viewer, 'rating');
			   $result['allowRateAgain'] = $allowRateAgain = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.again', 1);
			  $result['allowRateOwn'] = $allowRateOwn = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.own', 1);
			  if ($canRate == 0 || $allowRating == 0)
				$allowRating = false;
			  else
				$allowRating = true;
			  if ($allowRateOwn == 0 && $mine)
				$allowMine = false;
			  else
				$allowMine = true;
			 $result['allowMine'] = $allowMine;
			 $result['allowRating']  = $allowRating;
			  $result['rating_type']  = $rating_type = 'storevideo';
			  $result['rating_count'] = $rating_count = Engine_Api::_()->getDbTable('ratings', 'estorevideo')->ratingCount($video->getIdentity(), $rating_type);
		  $result['rated'] = $rated = Engine_Api::_()->getDbTable('ratings', 'estorevideo')->checkRated($video->getIdentity(), $viewer->getIdentity(), $rating_type);
		  $rating_sum = Engine_Api::_()->getDbTable('ratings', 'estorevideo')->getSumRating($video->getIdentity(), $rating_type);
		  if ($rating_count != 0) {
			$result['total_rating_average']  = $rating_sum / $rating_count;
		  } else {
			$result['total_rating_average'] = 0;
		  }
		  if (!$allowRateAgain && $rated) {
				$rated = false;
			  } else {
				$rated = true;
			  }
			  $result['ratedAgain'] = $rated;
		}
		 if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.watchlater',1)){
				  if(isset($video->watchlater_id)){
				  $result['watch_later']['option']['label'] = $this->view->translate('Remove from Watch Later');
				  $result['watch_later']['option']['name'] = 'removewatchlater';
				  $result['hasWatchlater'] = true;
				  }else{
					  $result['watch_later']['option']['label'] =$this->view->translate('Add to Watch Later');
					  $result['watch_later']['option']['name'] = 'addtowatchlater';
					   $result['hasWatchlater'] = false;
				  }
			  }

		//$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
		 $result['can_edit'] = 0;
		$result['can_delete'] = 0;
			$videoCounter = 0;
		if($viewer->getIdentity() != 0){
			$resourceItem = Engine_Api::_()->getItem('estore_store', $video->parent_id);
			if(count($resourceItem)>0)
			$result['resourceItem'] = $resourceItem;
			$result['parentedit'] = $parentedit = $resourceItem->authorization()->isAllowed($viewer, 'edit');
			$canEdit = $video->authorization()->isAllowed($viewer, 'edit');
			if(!$parentedit && !$canEdit){
				$result['can_edit'] = false;
			}
			else{
				$result['can_edit'] = true;
				$can[$videoCounter]['name'] = 'edit';
				$can[$videoCounter]['label'] = $this->view->translate('Edit Video');
				$videoCounter++;
			}
			$result['parentDelete'] = $parentDelete = $resourceItem->authorization()->isAllowed($viewer, 'delete');
			$canDelete = $video->authorization()->isAllowed($viewer, 'delete');
			if(!$parentDelete && !$canDelete){
				$result['can_delete'] = false;
			}
			else{
				$result['can_delete'] = true;
				$can[$videoCounter]['name'] = 'delete';
				$can[$videoCounter]['label'] = $this->view->translate('Delete Video');
				$videoCounter++;
			}

			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.report',1) && $viewer_id != $video->owner_id){
				$can[$videoCounter]['name'] = 'report';
				$can[$videoCounter]['label'] = $this->view->translate('Report');
				$videoCounter++;
			}
		}
		$rating['code']  = 100;
        $rating['message']  = '';
        $rating['total_rating_average']  = $video->rating;
		$result['rating'] = $rating;
		$data['video'] = $result;
		$data['menus'] = $can;
        $video = $video;
        if( !$video || $video->status != 1 ){
             Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The video you are looking for does not exist or has not been processed yet.'), 'result' => array()));
        }else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => $data));
		}
	}
	public function geturlAction(){
		$video_id = $this->_getParam('video_id',null);

		if(!$video_id){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' =>array()));
		}
		$video = Engine_Api::_()->getItem('storevideo', $video_id);
		//$embe = $video->code;
        echo $this->view->embe = $video->getRichContent(true,array(),'',true);
        exit;
		//Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' =>array('url'=>$embe)));

	}
    public function rateAction() {
	 if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
	if(!$rating || !$resource_id || !$resource_type)
		 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('parameter_missing'), 'result' => array()));
    $table = Engine_Api::_()->getDbtable('ratings', 'estorevideo');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getDbtable('ratings', 'estorevideo')->setRating($resource_id, $user_id, $rating, $resource_type);
      if ($resource_type && $resource_type == 'storevideo')
        $item = Engine_Api::_()->getItem('storevideo', $resource_id);
      $item->rating = Engine_Api::_()->getDbtable('ratings', 'estorevideo')->getRating($item->getIdentity(), $resource_type);
      $item->save();
      if ($resource_type == 'storevideo') {
        $type = 'sespgvido_rating';
      }
      $result = Engine_Api::_()->getDbtable('actions', 'activity')->fetchRow(array('type =?' => $type, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      if (!$result) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, $type);
        if ($action)
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $total = Engine_Api::_()->getDbtable('ratings', 'estorevideo')->ratingCount($item->getIdentity(), $resource_type);
    $rating_sum = Engine_Api::_()->getDbtable('ratings', 'estorevideo')->getSumRating($item->getIdentity(), $resource_type);
    $data = array();
    $totalTxt = $this->view->translate(array('%s rating', '%s ratings', $total), $total);
    $data = array(
        'total' => $total,
        'rating' => $rating,
        'totalTxt' => str_replace($total, '', $totalTxt),
        'rating_sum' => $rating_sum
    );
	Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('message'=>$this->view->translate('Successfully rated.'))));
  }
    public function editVideoAction() {
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $video = Engine_Api::_()->getItem('storevideo', $this->_getParam('video_id'));
    // Render
    $this->_helper->content->setEnabled();
    $this->view->parentItem = $resourceItem = Engine_Api::_()->getItem('estore_store', $video->parent_id);
    $canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'edit');
    $canEdit = $video->authorization()->isAllowed($viewer, 'edit');
    if(!$canEdit && !$canEditParent)
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $form = new Sesstorevideo_Form_Edit();

	$latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('storevideo',$video->video_id);
	//if($latLng){
        if($form->getElement('lat'))
            $form->removeElement('lat');
        if($form->getElement('lng'))
            $form->removeElement('lng');
        if($form->getElement('ses_location'))
            $form->removeElement('ses_location');
        if($form->getElement('map-canvas'))
            $form->removeElement('map-canvas');
        if($form->removeElement('is_locked'))
           $form->removeElement('password');
        if($form->removeElement('password'))
           $form->removeElement('password');
	//}
	if($form->getElement('location'))
	$form->getElement('location')->setValue($video->location);
    $form->getElement('search')->setValue($video->search);
    $form->getElement('title')->setValue($video->title);
    $form->getElement('description')->setValue($video->description);
    if ($form->getElement('is_locked'))
      $form->getElement('is_locked')->setValue($video->is_locked);
    if ($form->getElement('password'))
      $form->getElement('password')->setValue($video->password);
    // authorization
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    foreach ($roles as $role) {
      if (1 === $auth->isAllowed($video, $role, 'view')) {
        $form->auth_view->setValue($role);
      }
      if (1 === $auth->isAllowed($video, $role, 'comment')) {
        $form->auth_comment->setValue($role);
      }
    }
    // prepare tags
    $videoTags = $video->tags()->getTagMaps();
    $tagString = '';
    foreach ($videoTags as $tagmap) {
      if ($tagString !== '')
        $tagString .= ', ';
      $tagString .= $tagmap->getTag()->getTitle();
    }
    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);
     if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }

    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    //if (!$form->isValid($this->getRequest()->getPost())) {
      // Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
   // }
    // Process
    $db = Engine_Api::_()->getDbtable('videos', 'estorevideo')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      if (isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {

        $values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
      } else {
        if (empty($values['photo_id'])){
          unset($values['photo_id']);
				}
      }
		if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
			//disable lock if password not set.
			if (!$values['is_locked']) {
				$values['is_locked'] = '0';
				$values['password'] = '';
			}else
				unset($values['password']);
		}
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('video_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","storevideo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      $video->setFromArray($values);
      $video->save();
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
        $customfieldform->setItem($video);
        $customfieldform->saveValues();
      }
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_view'])
        $auth_view = $values['auth_view'];
      else
        $auth_view = "everyone";
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
      }
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_comment'])
        $auth_comment = $values['auth_comment'];
      else
        $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
      }
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $video->tags()->setTagMaps($viewer, $tags);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($video) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Video edited successfully.'),'video_id' => $video->getIdentity())));
  }
	public function deleteVideoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
	$videoid = $this->_getParam('video_id');
	if(!$videoid)
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $video = Engine_Api::_()->getItem('storevideo',$videoid);
    $resourceItem = Engine_Api::_()->getItem('estore_store', $video->parent_id);
	if(!$resourceItem)
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('data not found'), 'result' => array()));
    $canEdit = $video->authorization()->isAllowed($viewer, 'delete');
	$canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'delete');
    if(!$canEdit && !$canEditParent)
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$video) {
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Video doesn\'t exists or not authorized to delete'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    $db = $video->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getApi('core', 'estorevideo')->deleteVideo($video);
      $db->commit();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message'=>$this->view->translate('video deleted successfully'))));
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }

  //item liked as per item tye given
	function likeVideoAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
	    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    $type = 'storevideo';
    $dbTable = 'videos';
    $resorces_id = 'video_id';
    $notificationType = 'liked';
    $item_id = $this->_getParam('resource_id');
    if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $itemTable = Engine_Api::_()->getDbtable($dbTable, 'estorevideo');
    $select = $tableLike->select()->from($tableMainLike)->where('resource_type =?', $type)->where('poster_id =?', Engine_Api::_()->user()->getViewer()->getIdentity())->where('poster_type =?', 'user')->where('resource_id =?', $item_id);
    $Like = $tableLike->fetchRow($select);
    if (count($Like) > 0) {
      //delete
      $db = $Like->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Like->delete();
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //$itemTable->update(array(
        //  'like_count' => new Zend_Db_Expr('like_count - 1'),
          //    ), array(
          //$resorces_id . ' = ?' => $item_id,
     // ));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
       //$temp['like_count'] = $item->like_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $like = $tableLike->createRow();
        $like->poster_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $like->resource_type = $type;
        $like->resource_id = $item_id;
        $like->poster_type = 'user';
        $like->save();
        $itemTable->update(array(
            'like_count' => new Zend_Db_Expr('like_count + 1'),
                ), array(
            $resorces_id . '= ?' => $item_id,
        ));
        // Commit
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully liked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem($type, $item_id);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
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
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }

	function favouriteVideoAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    $type = 'storevideo';
    $dbTable = 'videos';
    $resorces_id = 'video_id';
    $notificationType = 'sespgvido_fav';

    $item_id = $this->_getParam('resource_id');
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $Fav = Engine_Api::_()->getDbTable('favourites', 'estorevideo')->getItemfav($type, $item_id);
    $favItem = Engine_Api::_()->getDbtable($dbTable, 'estorevideo');
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully unfavourited.');
      } catch (Exception $e) {
        $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
	  //$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', 'estorevideo')->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', 'estorevideo')->createRow();
        $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $fav->resource_type = $type;
        $fav->resource_id = $item_id;
        $fav->save();
        $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
                ), array(
            $resorces_id . '= ?' => $item_id,
        ));
        // Commit
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully favourited.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem(@$type, @$item_id);
      if ($this->_getParam('type') != 'estorevideo_artist') {
        $subject = $item;
        $owner = $subject->getOwner();
        if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
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
       //$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }

	public function browsealbumAction(){
        // Default param options
        if (count($_POST)) {
            $params = $_POST;
        }
        $searchArray = array();
        if (isset($_POST['searchParams']) && $_POST['searchParams'])
            parse_str($_POST['searchParams'], $searchArray);
        $value['store'] = isset($_POST['store']) ? $_POST['store'] : 1;
        $value['sort'] = isset($searchArray['sort']) ? $searchArray['sort'] : (isset($_GET[' ']) ? $_GET['sort'] : (isset($params['sort']) ? $params['sort'] : $this->_getParam('sort', 'mostSPliked')));
        $value['show'] = isset($searchArray['show']) ? $searchArray['show'] : (isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ? $params['show'] : ''));
        $value['search'] = isset($searchArray['search']) ? $searchArray['search'] : (isset($_GET['search']) ? $_GET['search'] : (isset($params['search']) ? $params['search'] : ''));
        $value['user_id'] = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($params['user_id']) ? $params['user_id'] : '');
        $value['show_criterias'] = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'by', 'title', 'socialSharing', 'view', 'photoCount', 'favouriteCount', 'favouriteButton', 'likeButton', 'featured', 'sponsored'));
        foreach ($value['show_criterias'] as $show_criteria)
            if (isset($value['sort']) && $value['sort'] != '') {
                $value['getParamSort'] = str_replace('SP', '_', $value['sort']);
            } else {
                $value['getParamSort'] = 'creation_date';
            }
        switch ($value['getParamSort']) {
            case 'most_viewed':
                $value['order'] = 'view_count';
                break;
            case 'most_favourite':
                $value['order'] = 'favourite_count';
                break;
            case 'most_liked':
                $value['order'] = 'like_count';
                break;
            case 'most_commented':
                $value['order'] = 'comment_count';
                break;
            case 'featured':
                $value['order'] = 'featured';
                break;
            case 'sponsored':
                $value['order'] = 'sponsored';
                break;
            case 'creation_date':
            default:
                $value['order'] = 'creation_date';
                break;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        //$value['showdefaultalbum'] = 0;
        $value['store_id'] = $this->_getParam('store_id',0);
        $paginator = Engine_Api::_()->getDbTable('albums', 'estore')->getAlbumSelect($value);
        $paginator->setItemCountPerPage($this->_getParam('limit', 1));
        $paginator->setCurrentPageNumber($this->_getParam('store', 10));
        $albumCounter = 0;
        foreach ($paginator as $item) {
            $owner = $item->getOwner();
            $ownertitle = $owner->displayname;
            $result['albums'][$albumCounter] = $item->toArray();
            $result['albums'][$albumCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "") : $result['members'][$counterLoop]['owner_photo'] = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
            $result['albums'][$albumCounter]['user_title'] = $ownertitle;
            $albumLikeStatus = Engine_Api::_()->estore()->getLikeStatus($item->getIdentity(), $item->getType());
            $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'estore')->isFavourite(array('resource_type' => 'album', 'resource_id' => $item->album_id));
            if ($albumLikeStatus) {
                $result['albums'][$albumCounter]['is_content_like'] = true;
            } else {
                $result['albums'][$albumCounter]['is_content_like'] = false;
            }
            if ($albumFavStatus) {
                $result['albums'][$albumCounter]['is_content_favourite'] = true;
            } else {
                $result['albums'][$albumCounter]['is_content_favourite'] = false;
            }
            $albumCounter++;
        }
        $canCreate = Engine_Api::_()->authorization()->isAllowed('album', null, 'create');
        $result['can_create'] = $canCreate?true:false;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function albumsearchformAction(){
        $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created', 'mostSPviewed' => 'Most Viewed', 'mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented', 'mostSPfavourite' => 'Most Favourite'));
        $search_for = $this->_getParam('search_for', 'album');
        $default_search_type = $this->_getParam('default_search_type', 'mostSPliked');
        $searchForm = new Sesstore_Form_AlbumSearch(array('searchTitle' => $this->_getParam('search_title', 'yes'), 'browseBy' => $this->_getParam('browse_by', 'yes'), 'searchFor' => $search_for, 'FriendsSearch' => $this->_getParam('friend_show', 'yes'), 'defaultSearchtype' => $default_search_type));
        if (isset($_GET['tag_name'])) {
            $searchForm->getElement('search')->setValue($_GET['tag_name']);
        }
        if ($this->_getParam('search_type') !== null && $this->_getParam('browse_by', 'yes') == 'yes') {
            $arrayOptions = $filterOptions;
            $filterOptions = array();
            foreach ($arrayOptions as $filterOption) {
                $value = str_replace(array('SP', ''), array(' ', ' '), $filterOption);
                $filterOptions[$filterOption] = ucwords($value);
            }
            $filterOptions = array('' => '') + $filterOptions;
            $searchForm->sort->setMultiOptions($filterOptions);
            $searchForm->sort->setValue($default_search_type);
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $searchForm->setMethod('get')->populate($request->getParams());
        $searchForm->removeElement('loading-img-estore');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
            $this->generateFormFields($formFields);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array())));

        }
    }
	function getButtonMenus($stores){
        $viewer = $this->view->viewer();
        $showLoginformFalse = false;
         if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.contact.details', 1)) {
            $showLoginformFalse = true;
        }
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 0);
        $i = 0;
        if ($stores->store_contact_email || $stores->store_contact_phone || $stores->store_contact_website) {
            if ($stores->store_contact_email) {

                $result[$i]['name'] = 'mail';
                $result[$i]['label'] = 'Send Email';
                $result[$i]['value'] = $stores->store_contact_email;
                $i++;

            }
            if ($stores->store_contact_phone) {
                $result[$i]['name'] = 'phone';
                $result[$i]['label'] = 'Call';
                $result[$i]['value'] = $stores->store_contact_phone;
                $i++;
            }
            if ($stores->store_contact_website) {

                $result[$i]['name'] = 'website';
                $result[$i]['label'] = 'Visit Website';
                $result[$i]['value'] = $stores->store_contact_website;
                $i++;
            }
        }

    if ($stores->is_approved) {
        $result[$i]['name'] = 'contact';
        $result[$i]['label'] = 'Contact';
        $i++;
        if ($shareType) {
            $result[$i]['name'] = 'share';
            $result[$i]['label'] = 'Share';
            $i++;
        }
        if ($viewerId) {
          $row = $stores->membership()->getRow($viewer);
          if (null === $row) {
              if ($stores->membership()->isResourceApprovalRequired()) {
                  $result[$i]['name'] = 'request';
                  $result[$i]['label'] = 'Request Membership';
                  $i++;
              } else {
                  $result[$i]['name'] = 'join';
                  $result[$i]['label'] = 'Join Store';
                  $i++;
              }
          } else if ($row->active) {
              if (!$stores->isOwner($viewer)) {
                  $result[$i]['label'] = 'Leave Store';
                  $result[$i]['name'] = 'leave';
                  $i++;
              }
          } else if (!$row->resource_approved && $row->user_approved) {
              $result[$i]['label'] = 'Cancel Membership Request';
              $result[$i]['name'] = 'cancel';
              $i++;

          } else if (!$row->user_approved && $row->resource_approved) {
              $result[$i]['label'] = 'Accept Membership Request';
              $result[$i]['name'] = 'accept';
              $i++;
              $result[$i]['label'] = 'Ignore Membership Request';
              $result[$i]['name'] = 'reject';
          }
      }

    }
      return $result;
}
	public function createalbumAction(){

        $store_id = $this->_getParam('store_id', false);
        $album_id = $this->_getParam('album_id', 0);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('estore_album', $album_id);
            $store_id = $album->store_id;
        } else {
            $store_id = $store_id;
        }
        $store = Engine_Api::_()->getItem('estore_store', $store_id);
		if(!$store_id)
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $current_count = Engine_Api::_()->getDbTable('albums', 'estore')->getUserAlbumCount($values);
        $quota = $quota = 0;
        // Get form
        $form = new Sesstore_Form_Album();
        $form->removeElement('fancyuploadfileids');
        $form->removeElement('tabs_form_albumcreate');
        $form->removeElement('drag-drop');
        $form->removeElement('from-url');
        $form->removeElement('file_multi');
        $form->removeElement('uploadFileContainer');

        // Render
        $form->populate(array('album' => $album_id));
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'estore_store'));
        }
        if (!$form->isValid($this->getRequest()->getPost())){
          $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        $db = Engine_Api::_()->getItemTable('estore_album')->getAdapter();
        $db->beginTransaction();
        try {
            $photoTable = Engine_Api::_()->getDbTable('photos', 'estore');
			$uploadSource = $_FILES['image'];
			$photoArray = array(
            'store_id' => $store->store_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'estore')->getAdapter();
        $db->beginTransaction();
        try {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
				$albumdata = $album?$album:false;
                $photo = $photo->setAlbumPhoto($uploadSource, false, false, $albumdata);
                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['store_id'] = $store->store_id;
        $_POST['file'] = implode(' ', $photosource);
            $album = $form->saveValues();
            // Add tags
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), album_id => $album->getIdentity()))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array())));
        }
    }
	public function acceptAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject('estore_store')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $membership_status = $subject->membership()->getRow($viewer)->active;
            $subject->membership()->setUserApproved($viewer);
            $row = $subject->membership()->getRow($viewer);
            $row->save();
            // Add activity
            if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $subject, 'estore_store_join');
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have accepted the invite to the store'),'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }

    }
	public function rejectAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('estore_store')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_misssing', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $user = Engine_Api::_()->getItem('user', (int)$this->_getParam('user_id'));
            $subject->membership()->removeMember($user);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'estore_reject');
            // Set the request as handled
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $viewer, $subject, 'estore_invite');
            if ($notification) {
                $notification->mitigated = true;
                $notification->save();
            }
            $db->commit();
            $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the store');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function removeAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        // Get user
        if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('member does not exist.'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store->membership()->isMember($user)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Cannot remove a non-member.'), 'result' => array()));
        }
        $db = $store->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Remove membership
            $store->membership()->removeMember($user);
            // Remove the notification?
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $store->getOwner(), $store, 'estore_approve');
            if ($notification) {
                $notification->delete();
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected member has been removed from this store.'),'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }
	public function approveAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('estore_store')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        // Get user
        if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('user does not exist.'), 'result' => array()));
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $subject->membership()->setResourceApproved($user);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'estore_accepted');
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Store request approved'),'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }

    }
	public function cancelAction(){
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        $user_id = $this->_getParam('user_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        if (!$subject->authorization()->isAllowed($viewer, 'invite') &&
            $user_id != $viewer->getIdentity() &&
            $user_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }

        if ($user_id) {
            $user = Engine_Api::_()->getItem('user', $user_id);
            if (!$user) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
            }
        } else {
            $user = $viewer;
        }

        $subject = Engine_Api::_()->core()->getSubject('estore_store');
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $subject->membership()->removeMember($user);

            // Remove the notification?
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $subject->getOwner(), $subject, 'estore_approve');
            if ($notification) {
                $notification->delete();
            }

            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Your invite request has been cancelled.'),'menus'=>$this->getButtonMenus($subject))));

        } catch (Exception $e) {
            $db->rollBack();
            $message = $e->getMessage();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
        }
    }

}
