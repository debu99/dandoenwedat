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
class Classified_IndexController extends Sesapi_Controller_Action_Standard {

  protected $_classifiedEnabled;
  
  public function init() {

		//Only show to member_level if authorized
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'view')->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $this->isClassifiedEnable();
//     $request = Zend_Controller_Front::getInstance()->getRequest();
//     echo "<pre>"; var_dump($request);die;
  }
  
  protected function isClassifiedEnable() {
    $this->_classifiedEnabled = true;
  }
  

  // NONE USER SPECIFIC METHODS
  public function browseAction() {

    // Check auth
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $viewer = Engine_Api::_()->user()->getViewer();

    // Prepare form
    $form = new Classified_Form_Search();
    
    if( !$viewer->getIdentity() ) {
      $form->removeElement('show');
    }

    // Populate form
    $categories = Engine_Api::_()->getDbtable('categories', 'classified')->getCategoriesAssoc();
    if( !empty($categories) && is_array($categories) && $form->getElement('category') ) {
      $form->getElement('category')->addMultiOptions($categories);
    }

    // Process form
    if( $form->isValid($this->_getAllParams()) ) {
      $values = $form->getValues();
    } else {
      $values = array();
    }
    //$this->view->formValues = array_filter($values);

    
    $customFieldValues = array_intersect_key($values, $form->getFieldElements());
    
    // Process options
    $tmp = array();
    foreach( $customFieldValues as $k => $v ) {
      if( null == $v || '' == $v || (is_array($v) && count(array_filter($v)) == 0) ) {
        continue;
      } elseif( false !== strpos($k, '_field_') ) {
        list($null, $field) = explode('_field_', $k);
        $tmp['field_' . $field] = $v;
      } elseif( false !== strpos($k, '_alias_') ) {
        list($null, $alias) = explode('_alias_', $k);
        $tmp[$alias] = $v;
      } else {
        $tmp[$k] = $v;
      }
    }
    $customFieldValues = $tmp;
    
    // Do the show thingy
    if( @$values['show'] == 2 ) {
      // Get an array of friend ids to pass to getClassifiedsPaginator
      $table = Engine_Api::_()->getItemTable('user');
      $select = $viewer->membership()->getMembersSelect('user_id');
      $friends = $table->fetchAll($select);
      // Get stuff
      $ids = array();
      foreach( $friends as $friend ) {
        $ids[] = $friend->user_id;
      }
      //unset($values['show']);
      $values['users'] = $ids;
    }

    // check to see if request is for specific user's listings
    if( ($userId = $this->_getParam('user_id')) ) {
      $values['user_id'] = $userId;
    }

    //$this->view->assign($values);
    
    if(!empty($_POST['user_id']))
      $values["user_id"] = $_POST['user_id'];
      
    if(!empty($_POST['category_id']))
      $values['category'] = $_POST['category_id'];

    // items needed to show what is being filtered in browse page
//     if( !empty($values['tag']) ) {
//       $tag_text = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
//     }
    
//     $view = $this->view;
//     $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    $paginator = Engine_Api::_()->getItemTable('classified')->getClassifiedsPaginator($values);
    $itemsCount = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('classified.page', 10);
    $paginator->setItemCountPerPage($itemsCount);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    $result = $this->resourceResults($paginator);
    
    if(!empty($_POST['user_id'])) {
    
      $viewer = Engine_Api::_()->user()->getViewer();
      $menuoptions= array();
      $canEdit = Engine_Api::_()->authorization()->getPermission($viewer, 'classified', 'edit');
      $counter = 0;
      if($canEdit) {
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit"); 
        $counter++;
      }
      
      $canDelete = Engine_Api::_()->authorization()->getPermission($viewer, 'classified', 'delete');
      if($canDelete) {
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete");
      }
      $result['menus'] = $menuoptions;  
    }
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist classifieds.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams)); 
    
  }
  
  public function categoryAction() {
 
    $params['countClassifieds'] = true;
    $paginator = Engine_Api::_()->getDbTable('categories', 'classified')->getCategoriesAssoc();
    $counter = 0;
    $catgeoryArray = array();
    foreach($paginator as $key => $category) {
    
      if($key == '') continue;
      $catgeoryArray["category"][$counter]["category_id"] = $key;
      $catgeoryArray["category"][$counter]["label"] = $this->getCategoryName(array('column_name' => 'category_name', 'category_id' => $key));

      $catgeoryArray["category"][$counter]["thumbnail"] = $this->getBaseUrl(true, 'application/modules/Sesapi/externals/images/default_category.png');
      
      //Classifieds Count based on category
      $Itemcount = Engine_Api::_()->sesapi()->getCategoryBasedItems(array('category_id' => $category->getIdentity(), 'table_name' => 'classifieds', 'module_name' => 'classified'));
      $catgeoryArray["category"][$counter]["count"] = $this->view->translate(array('%s classified', '%s classifieds', $Itemcount), $this->view->locale()->toNumber($Itemcount));
      
      $counter++;
    }

    if($catgeoryArray <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array())); 
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $catgeoryArray),array())); 
  }
  
  function resourceResults($paginator) {
  
    $result = array();
    $counterLoop = 0;
    $viewer = Engine_Api::_()->user()->getViewer();
    
    foreach($paginator as $item) {
    
      $resource = $item->toArray(); 
      
      $description = strip_tags($item['body']);
      $description = preg_replace('/\s+/', ' ', $description);
      unset($resource['body']);
      $resource['owner_title'] = Engine_Api::_()->getItem('user', $resource['owner_id'])->getTitle();
      $resource['body'] = $description;   
      $resource['resource_type'] = $item->getType();
      $resource['resource_id'] = $item->getIdentity();
      
      // custom fields values
//       $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($item);
//       $customFieldsValues = $this->view->fieldValueLoop($item, $fieldStructure);
//       if($customFieldsValues) {
//         $resource['custom_fields_values'] = $customFieldsValues;
//       }
      
      //Category name
      if(!empty($resource['category_id'])) {
        $category_name = $this->getCategoryName(array('column_name' => 'category_name', 'category_id' => $item->category_id));
        $resource['category_name'] = $category_name;
      }

      // Check content like or not and get like count
      if($this->_classifiedEnabled) {
        if($viewer->getIdentity() != 0) {
          $resource['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($item);
          $resource['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($item);
        }
      }
      $result['classifieds'][$counterLoop] = $resource;
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', '');
      if(!count($images))
        $images['main'] = $this->getBaseUrl(true, $item->getPhotoUrl()). 'application/modules/Classified/externals/images/nophoto_classified_thumb_normal.png';
      $result['classifieds'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }

    return $result;
  }
  
  public function getCategoryName($params = array()) {
    
    $categoryTable = Engine_Api::_()->getDbTable('categories', 'classified');
    $categoryTableName = $categoryTable->info('name');
    
    $select = $categoryTable->select()
            ->from($categoryTableName, $params['column_name']);

    if (isset($params['category_id']))
      $select = $select->where('category_id = ?', $params['category_id']);

    return $select = $select->query()->fetchColumn();
  }
  
  public function getProfileTypeValue($params = array()) {
    $valuesTable = Engine_Api::_()->fields()->getTable('classified', 'values');
    $valuesTableName = $valuesTable->info('name');
    return $valuesTable->select()
                    ->from($valuesTableName, array('value'))
                    ->where($valuesTableName . '.item_id = ?', $params['classified_id'])
                    ->where($valuesTableName . '.field_id = ?', $params['field_id'])->query()
                    ->fetchColumn();
  }
  
  public function viewAction() {
  
    // Check permission
    $viewer = Engine_Api::_()->user()->getViewer();

    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if( $classified ) {
      Engine_Api::_()->core()->setSubject($classified);
    }

//     if( !$this->_helper->requireSubject()->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
//     }
    
    if( !$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
    }
    
//     if( !$classified || !$classified->getIdentity() || (!$classified->isOwner($viewer)) ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 
//     }
    
    // Prepare data
    $classifiedTable = Engine_Api::_()->getDbtable('classifieds', 'classified');
    
    
    $classified_content = $classified->toArray();
    
    $body = @str_replace('src="/', 'src="' . $this->getBaseUrl() . '/', $classified_content['body']);
    $body = preg_replace('/<\/?a[^>]*>/','',$body);
    $classified_content['body'] = "<link href=\"".$this->getBaseUrl(true,'application/modules/Sesapi/externals/styles/tinymce.css')."\" type=\"text/css\" rel=\"stylesheet\">".($body);
    $classified_content['owner_title'] = Engine_Api::_()->getItem('user', $classified_content['owner_id'])->getTitle();
    $classified_content['resource_type'] = $classified->getType();
    $classified_content['resource_id'] = $classified->getType();
    $classified_content['category_id'] = $classified->category_id;
    
    if( !$classified->isOwner($viewer) ) {
      $classifiedTable->update(array(
        'view_count' => new Zend_Db_Expr('view_count + 1'),
      ), array(
        'classified_id = ?' => $classified->getIdentity(),
      ));
    }
    
    // Get tags
    $classifiedTags = $classified->tags()->getTagMaps();
    if (!empty($classifiedTags)) {
      foreach ($classifiedTags as $tag) {
        $classified_content['tags'][$tag->getTag()->tag_id] = $tag->getTag()->text;
      }
    }
    
    //Location and Price profile field values
    $location = $this->getProfileTypeValue(array('classified_id' => $classified->getIdentity(), 'field_id' => 3));
    if($location) {
      $classified_content['location'] = $location;
    }
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $price = $this->getProfileTypeValue(array('classified_id' => $classified->getIdentity(), 'field_id' => 2));
    $givenSymbol = $settings->getSetting('payment.currency', 'USD');
    if($price) {
      $classified_content['price'] = Zend_Registry::get('Zend_View')->locale()->toCurrency($price, $givenSymbol, array());
    }
    //Location and Price profile field values
    
    // Get category
    if( !empty($classified->category_id) ) {
      $category_name = $this->getCategoryName(array('column_name' => 'category_name', 'category_id' => $classified->category_id)); 
      $classified_content['category_title'] = $category_name; //$category->category_name;
    }
    
    if($this->_classifiedEnabled) {
      if($viewer->getIdentity() != 0) {
        $classified_content['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($classified);
        $classified_content['content_like_count'] = (int) Engine_Api::_()->sesapi()->getContentLikeCount($classified);
      }
    }
    
    $classified_content['content_url'] = $this->getBaseUrl(false,$classified->getHref());
    $classified_content['can_favorite'] = false;
    $classified_content['can_share'] = false;

    $result['classified'] = $classified_content;
    
    if($viewer->getIdentity() > 0) {
    
			$result['classified']['permission']['canEdit'] = $canEdit = $viewPermission = $classified->authorization()->isAllowed($viewer, 'edit') ? true : false;
			$result['classified']['permission']['canComment'] =  $classified->authorization()->isAllowed($viewer, 'comment') ? true : false;
			$result['classified']['permission']['canCreate'] = Engine_Api::_()->authorization()->getPermission($viewer, 'sesclassified_classified', 'create') ? true : false;
			$result['classified']['permission']['can_delete'] = $canDelete  = $classified->authorization()->isAllowed($viewer,'delete') ? true : false;
      
      $menuoptions= array();
      $counter = 0;
      if($canEdit) {
//         $menuoptions[$counter]['name'] = "changephoto";
//         $menuoptions[$counter]['label'] = $this->view->translate("Change Main Photo");
//         $counter++;
        $menuoptions[$counter]['name'] = "edit";
        $menuoptions[$counter]['label'] = $this->view->translate("Edit Classified"); 
        $counter++;
      }
      if($canDelete){
        $menuoptions[$counter]['name'] = "delete";
        $menuoptions[$counter]['label'] = $this->view->translate("Delete Classified");
        $counter++;
      }
      //if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesclassified.enable.report', 1)){
        $menuoptions[$counter]['name'] = "report";
        $menuoptions[$counter]['label'] = $this->view->translate("Report Classified");
      //}
      $result['menus'] = $menuoptions;
		}
    
    $result['classified']["share"]["name"] = "share";
    $result['classified']["share"]["label"] = $this->view->translate("Share");
    $photo = $this->getBaseUrl(false,$classified->getPhotoUrl());
    if($photo)
      $result['classified']["share"]["imageUrl"] = $photo;
			$result['classified']["share"]["url"] = $this->getBaseUrl(false,$classified->getHref());
      
    $result['classified']["share"]["title"] = $classified->getTitle();
    $result['classified']["share"]["description"] = strip_tags($classified->getDescription());
    $result['classified']["share"]['urlParams'] = array(
        "type" => $classified->getType(),
        "id" => $classified->getIdentity()
    );
    
    if(is_null($result['classified']["share"]["title"]))
      unset($result['classified']["share"]["title"]);
    
    $images = Engine_Api::_()->sesapi()->getPhotoUrls($classified,'',"");
    if(!count($images))
      $images['main'] = $this->getBaseUrl(true, $classified->getPhotoUrl());
      
    $result['classified']['classified_images'] = $images;
    
    $result['classified']['user_images'] = $this->userImage($classified->owner_id,"thumb.profile");
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
  }

  public function createAction() {

    if( !$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireAuth()->setAuthParams('classified', null, 'create')->isValid()) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // set up data needed to check quota
    $viewer = Engine_Api::_()->user()->getViewer();
    $values['user_id'] = $viewer->getIdentity();
    $paginator = Engine_Api::_()->getItemTable('classified')->getClassifiedsPaginator($values);
    $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'classified', 'max');
    $paginator->getTotalItemCount();
    $current_count = $paginator->getTotalItemCount();

    if (($current_count >= $quota) && !empty($quota)) {
      // return error message
      $message = $this->view->translate('You have already uploaded the maximum number of classifieds allowed.');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error' => '1', 'error_message' => $message, 'result' => array()));
    }
        
    // Prepare form
    $form = new Classified_Form_Create();
    //$form->removeElement('token');

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'classified'));
    }
    
    // If not post or form not valid, return
//     if( !$this->getRequest()->isPost() ) {
//       return;
//     }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      $formFields[4]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }

    // Process
    $table = Engine_Api::_()->getItemTable('classified');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
    
      // Create classified
      $viewer = Engine_Api::_()->user()->getViewer();
      $formValues = $form->getValues();

      if( empty($values['auth_view']) ) {
        $formValues['auth_view'] = 'everyone';
      }

      if( empty($values['auth_comment']) ) {
        $formValues['auth_comment'] = 'everyone';
      }

      $values = array_merge($formValues, array(
        'owner_type' => $viewer->getType(),
        'owner_id' => $viewer->getIdentity(),
        'view_privacy' => $formValues['auth_view'],
      ));

      $classified = $table->createRow();
      $classified->setFromArray($values);
      $classified->save();

       if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
        $this->setPhoto($_FILES['image'],$classified);
      }

      // Auth
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($classified, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($classified, $role, 'comment', ($i <= $commentMax));
      }
      
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $classified->tags()->addTagMaps($viewer, $tags);

      // Add activity only if classified is published
      if( $values['draft'] == 0 ) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $classified, 'classified_new');
        // make sure action exists before attaching the classified to the activity
        if( $action ) {
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $classified);
        }
      }
      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('classified_id' => $classified->getIdentity(),'message' => $this->view->translate('Classified created successfully.'))));
  }

  public function editAction() {
  
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();
    $classified = Engine_Api::_()->getItem('classified', $this->_getParam('classified_id'));
    if(!Engine_Api::_()->core()->hasSubject('classified') ) {
      Engine_Api::_()->core()->setSubject($classified);
    }
    
    if( !$this->_helper->requireSubject()->isValid() ) 
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    if( !$this->_helper->requireAuth()->setAuthParams($classified, $viewer, 'edit')->isValid() )
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    // Prepare form
    $form = new Classified_Form_Edit(array(
      'item' => $classified
    ));
    $form->removeElement('photo');
    
    $form->removeElement('cover');
    
    // Populate form
    $form->populate($classified->toArray());

    $tagStr = '';
    foreach( $classified->tags()->getTagMaps() as $tagMap ) {
      $tag = $tagMap->getTag();
      if( !isset($tag->text) ) continue;
      if( '' !== $tagStr ) $tagStr .= ', ';
      $tagStr .= $tag->text;
    }
    $form->populate(array(
      'tags' => $tagStr,
    ));
    $this->view->tagNamePrepared = $tagStr;

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

    foreach( $roles as $role ) {
      if ($form->auth_view){
        if( $auth->isAllowed($classified, $role, 'view') ) {
         $form->auth_view->setValue($role);
        }
      }

      if ($form->auth_comment){
        if( $auth->isAllowed($classified, $role, 'comment') ) {
          $form->auth_comment->setValue($role);
        }
      }
    }

    // hide status change if it has been already published
//     if( $classified->draft == "0" ) {
//       $form->removeElement('draft');
//     }
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $formFields[4]['name'] = "file";
      $this->generateFormFields($formFields,array('resources_type'=>'classified'));
    }
    
    // Check post/form
//     if( !$this->getRequest()->isPost() ) {
//       return;
//     }
    
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    
    // Process
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      $values = $form->getValues();

      if( empty($values['auth_view']) ) {
        $values['auth_view'] = 'everyone';
      }
      if( empty($values['auth_comment']) ) {
        $values['auth_comment'] = 'everyone';
      }

      $values['view_privacy'] = $values['auth_view'];

      $classified->setFromArray($values);
      $classified->modified_date = date('Y-m-d H:i:s');
      $classified->save();

      // Add photo
//       if( !empty($_FILES['image']['name']) &&  !empty($_FILES['image']['size']) ) {
//         $this->setPhoto($_FILES['image'],$classified);
//       }

      // Auth
      $viewMax = array_search($values['auth_view'], $roles);
      $commentMax = array_search($values['auth_comment'], $roles);

      foreach( $roles as $i => $role ) {
        $auth->setAllowed($classified, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($classified, $role, 'comment', ($i <= $commentMax));
      }

      // handle tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $classified->tags()->setTagMaps($viewer, $tags);

      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach( $actionTable->getActionsByObject($classified) as $action ) {
        $actionTable->resetActivityBindings($action);
      }
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('classified_id' => $classified->getIdentity(),'message' => $this->view->translate('Classified edited successfully.'))));
  }
  

  public function deleteAction() {

    $classified = Engine_Api::_()->getItem('classified', $this->getRequest()->getParam('classified_id'));
    
    if( !$this->_helper->requireAuth()->setAuthParams($classified, null, 'delete')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array())); 

    $form = new Classified_Form_Delete();
    
    if( !$classified ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_("Classified entry doesn't exist or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array())); 
    }

    if( !$this->getRequest()->isPost() ) {
      $status = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array())); 
    }
    
//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
//       //$formFields[4]['name'] = "file";
//       if(count($validateFields))
//       $this->validateFormFields($validateFields);
//     }

    $db = $classified->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $classified->delete();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $message = Zend_Registry::get('Zend_Translate')->_('Your classified listing has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
  
  public function setPhoto($photo,$classified) {
  
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
    } elseif( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
    } elseif( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
    } else {
      throw new Classified_Model_Exception('Invalid argument passed to setPhoto: ' . print_r($photo, 1));
    }

    $name = basename($photo['name']);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => 'classified',
      'parent_id' => $classified->getIdentity()
    );

    // Save
    $storage = Engine_Api::_()->storage();

    // Resize image (main)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(720, 720)
      ->write($path . '/m_' . $name)
      ->destroy();

    // Resize image (profile)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(200, 400)
      ->write($path . '/p_' . $name)
      ->destroy();

    // Resize image (normal)
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(140, 160)
      ->write($path . '/in_' . $name)
      ->destroy();

    // Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($path . '/is_' . $name)
      ->destroy();

    // Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iProfile = $storage->create($path . '/p_' . $name, $params);
    $iIconNormal = $storage->create($path . '/in_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);

    $iMain->bridge($iProfile, 'thumb.profile');
    $iMain->bridge($iIconNormal, 'thumb.normal');
    $iMain->bridge($iSquare, 'thumb.icon');

    // Remove temp files
    @unlink($path . '/p_' . $name);
    @unlink($path . '/m_' . $name);
    @unlink($path . '/in_' . $name);
    @unlink($path . '/is_' . $name);

    // Update row
    $classified->modified_date = date('Y-m-d H:i:s');
    $classified->photo_id = $iMain->getIdentity();
    $classified->save();

    return $classified;
  }
  
  
  public function searchFormAction() {
  
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesclassified_enable_location', 1))
      $location = 'yes';	
    else
      $location = 'no';

    $form = new Classified_Form_Search(array('searchTitle' => 'yes','browseBy' => 'yes','categoriesSearch' => 'yes','searchFor'=>'classified','FriendsSearch'=>'yes','defaultSearchtype'=>'mostSPliked','locationSearch' => $location,'kilometerMiles' => 'yes','hasPhoto' => 'yes'));

    $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created','mostSPviewed' => 'Most Viewed','mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented','mostSPfavourite' => 'Most Favourite','featured' => 'Featured','sponsored' => 'Sponsored','verified' => 'Verified','mostSPrated'=>'Most Rated'));
      
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
    if($form->sort) {
      $form->sort->setMultiOptions($filterOptions);
      $form->sort->setValue('recentlySPcreated');       
    }
    $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form,true);
    $this->generateFormFields($formFields); 
  }
}
