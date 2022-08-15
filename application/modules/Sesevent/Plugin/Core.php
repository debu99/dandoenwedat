<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Plugin_Core {
  public function onStatistics($event) {
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) { return false;}
    $table = Engine_Api::_()->getItemTable('event');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'event');
  }
	public function onRenderLayoutMobileDefault($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }
	public function onRenderLayoutMobileDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }
	public function onRenderLayoutDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }
	public function onRenderLayoutDefault($event){
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) { return false;}
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$viewer = Engine_Api::_()->user()->getViewer();		
		$request = Zend_Controller_Front::getInstance()->getRequest(); //echo "<pre>";print_r($request);die;
		$moduleName = $request->getModuleName();
		$actionName = $request->getActionName();
		$controllerName = $request->getControllerName();
		
		$viewer = Engine_Api::_()->user()->getViewer();		
		$checkWelcomePage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.check.welcome',2);
		$checkWelcomeEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.welcome',1);
		$checkWelcomePage = (($checkWelcomePage == 1 && $viewer->getIdentity() == 0) ? true : (($checkWelcomePage == 0 && $viewer->getIdentity() != 0) ? true : (($checkWelcomePage == 2) ? true : false)));
		if(!$checkWelcomeEnable)
			$checkWelcomePage = false;
		if($actionName == 'welcome' && $controllerName == 'index' && $moduleName == 'sesevent'){
		  $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			if(!$checkWelcomeEnable) 
		  	$redirector->gotoRoute(array('module' => 'sesevent', 'controller' => 'index', 'action' => 'home'), 'sesevent_general', false);
			else if($checkWelcomeEnable == 2) 
				$redirector->gotoRoute(array('module' => 'sesevent', 'controller' => 'index', 'action' => 'browse'), 'sesevent_general', false);
		}
		
		$headScript = new Zend_View_Helper_HeadScript();
		$headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
								 .'application/modules/Sesevent/externals/scripts/core.js');
		$script = '';
		if($moduleName == 'sesevent'){
			$script .=
"sesJqueryObject(document).ready(function(){
     sesJqueryObject('.core_main_sesevent').parent().addClass('active');
    });
";
		}
		if($moduleName == 'sesevent' && $actionName == 'index' && $controllerName == 'profile'){
				$bagroundImageId = Engine_Api::_()->core()->getSubject('sesevent_event')->background_photo_id;
				if($bagroundImageId != 0 && $bagroundImageId != ''){ 
  			 $backgroundImage =	Engine_Api::_()->storage()->get($bagroundImageId, '')->getPhotoUrl(); 
				 }
				 if(isset($backgroundImage)) {
						$script .=
								"window.addEvent('domready', function() {
										document.getElementById('global_wrapper').style.backgroundImage = \"url('".$backgroundImage."')\";
									});
								";
				 }
			}
		 $script .=
"var eventURLsesevent = '" . Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.events.manifest', 'events') . "';
var showAddnewEventIconShortCut = ".Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.addeventshortcut',1).";
";
		if($viewer->getIdentity() != 0 && Engine_Api::_()->authorization()->isAllowed('sesevent_event', $viewer, 'create')){
			$script .= 'sesJqueryObject(document).ready(function() {
			if(sesJqueryObject("body").attr("id").search("sesevent") > -1 && typeof showAddnewEventIconShortCut != "undefined" && showAddnewEventIconShortCut ){
				sesJqueryObject("<a class=\'sesbasic_create_button sesevent_quick_create_button sesbasic_animation\' href=\''.$view->url(array('action'=>'create'), 'sesevent_general').'\' title=\'Add New Event\'><i class=\'fa fa-plus\'></i></a>").appendTo("body");
			}
		});';		
		}
		 $view->headScript()->appendScript($script);
	}
  public function onUserDeleteBefore($event) {
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) { return false;}
    $payload = $event->getPayload();
    if ($payload instanceof User_Model_User) {
      // Delete posts
      $postTable = Engine_Api::_()->getDbtable('posts', 'sesevent');
      $postSelect = $postTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach ($postTable->fetchAll($postSelect) as $post) {
        $post->delete();
      }
      // Delete topics
      $topicTable = Engine_Api::_()->getDbtable('topics', 'sesevent');
      $topicSelect = $topicTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach ($topicTable->fetchAll($topicSelect) as $topic) {
        $topic->delete();
      }
      // Delete photos
      $photoTable = Engine_Api::_()->getDbtable('photos', 'sesevent');
      $photoSelect = $photoTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach ($photoTable->fetchAll($photoSelect) as $photo) {
        $photo->delete();
      }
      // Delete memberships
      $membershipApi = Engine_Api::_()->getDbtable('membership', 'sesevent');
      foreach ($membershipApi->getMembershipsOf($payload) as $event) {
        $membershipApi->removeMember($event, $payload);
      }
      // Delete events
      $eventTable = Engine_Api::_()->getDbtable('events', 'sesevent');
      $eventSelect = $eventTable->select()->where('user_id = ?', $payload->getIdentity());
      foreach ($eventTable->fetchAll($eventSelect) as $event) {
        $event->delete();
				$event->save();
      }
    }
  }

  public function addActivity($event) {
		if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) { return false;}
    $payload = $event->getPayload();
    $subject = $payload['subject'];
    $object = $payload['object'];

    // Only for object=event
    if ($object instanceof Sesevent_Model_Event &&
            Engine_Api::_()->authorization()->context->isAllowed($object, 'member', 'view')) {
      $event->addResponse(array(
          'type' => 'sesevent_event',
          'identity' => $object->getIdentity()
      ));
    }
  }

  public function getActivity($event) {
  
	  if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.pluginactivated')) {
	    // Detect viewer and subject
	    $payload = $event->getPayload();
	    $user = null;
	    $subject = null;
	    if ($payload instanceof User_Model_User) {
	      $user = $payload;
	    } else if (is_array($payload)) {
	      if (isset($payload['for']) && $payload['for'] instanceof User_Model_User) {
	        $user = $payload['for'];
	      }
	      if (isset($payload['about']) && $payload['about'] instanceof Core_Model_Item_Abstract) {
	        $subject = $payload['about'];
	      }
	    }
	    if (null === $user) {
	      $viewer = Engine_Api::_()->user()->getViewer();
	      if ($viewer->getIdentity()) {
	        $user = $viewer;
	      }
	    }
	    if (null === $subject && Engine_Api::_()->core()->hasSubject()) {
	      $subject = Engine_Api::_()->core()->getSubject();
	    }

	    // Get feed settings
	    $content = Engine_Api::_()->getApi('settings', 'core')
	            ->getSetting('activity.content', 'everyone');

	    // Get event memberships
	    if ($user) {
	      $data = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembershipsOfIds($user);
	      if (!empty($data) && is_array($data)) {
	        $event->addResponse(array(
	            'type' => 'sesevent_event',
	            'data' => $data,
	        ));
	      }
	    }
	  }
  }
  
  public function onUserUpdateAfter($event) {
    $item = $event->getPayload();
    $front = Zend_Controller_Front::getInstance();
    $controller = $front->getRequest()->getControllerName();
    $action = $front->getRequest()->getActionName();
    
		if ($controller == 'edit' && ($action == 'profile' || $action == 'photo')) {
      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$user = Engine_Api::_()->core()->getSubject();
			$fieldsAliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
			
			$eventHostsTable = Engine_Api::_()->getDbTable('hosts', 'sesevent');
			$eventHostsTableName = $eventHostsTable->info('name');
			$select = $eventHostsTable->select()
								->from($eventHostsTableName, array('host_id'))
								->where($eventHostsTableName . '.user_id = ?', $user->user_id);
			$result = $select->query()->fetchColumn();
			if($result && $action == 'profile') {
				Engine_Api::_()->getDbtable('hosts', 'sesevent')->update(array('host_name'=>  $user->displayname), array('user_id =?' => $user->user_id));
			} else {
				Engine_Api::_()->getDbtable('hosts', 'sesevent')->update(array('photo_id'=>  $user->photo_id), array('user_id =?' => $user->user_id));
			}
	  }
	}	
}
