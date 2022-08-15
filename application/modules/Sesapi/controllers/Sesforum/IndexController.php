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
class Sesforum_IndexController extends Sesapi_Controller_Action_Standard {

  public function postcreateAction()
  {

    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $topic_id = $this->_getParam('topic_id', null);

    $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id);

    if(!$topic) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = $topic; //Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'post.create')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    if( $topic->closed) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->form = $form = new Sesforum_Form_Post_Create();

    // Remove the file element if there is no file being posted
    if( $this->getRequest()->isPost() && empty($_FILES['photo']) ) {
      $form->removeElement('photo');
    }

    $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_html', 0);

    $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_bbcode', 0);

    $quote_id = $this->getRequest()->getParam('quote_id');
    if( !empty($quote_id) ) {
      $quote = Engine_Api::_()->getItem('sesforum_post', $quote_id);
      if($quote->user_id == 0) {
          $owner_name = Zend_Registry::get('Zend_Translate')->_('Deleted Member');
      } else {
          $owner_name = $quote->getOwner()->__toString();
      }
      if ( !$allowHtml && !$allowBbcode ) {
		$form->body->setValue( strip_tags($this->view->translate('%1$s said:', $owner_name)) . " ''" . strip_tags($quote->body) . "''\n-------------\n" );
	  } elseif( $allowHtml ) {
        $form->body->setValue("<blockquote><strong>" . $this->view->translate('%1$s said:', $owner_name) . "</strong><br />" . $quote->body . "</blockquote><br />");
      } else {
        $form->body->setValue("[quote][b]" . strip_tags($this->view->translate('%1$s said:', $owner_name)) . "[/b]\r\n" . htmlspecialchars_decode($quote->body, ENT_COMPAT) . "[/quote]\r\n");
      }
    }

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }

//     if( !$this->getRequest()->isPost() ) {
//       return;
//     }
//
//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       return;
//     }

    // Process
    $values = $form->getValues(); //print_R($_POST);die;
    $values['body'] = Engine_Text_BBCode::prepare($values['body']);
    $values['user_id'] = $viewer->getIdentity();
    $values['topic_id'] = $topic->getIdentity();
    $values['forum_id'] = $sesforum->getIdentity();

    $topicTable = Engine_Api::_()->getDbtable('topics', 'sesforum');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicwatches', 'sesforum');
    $postTable = Engine_Api::_()->getDbtable('posts', 'sesforum');
    $userTable = Engine_Api::_()->getItemTable('user');
    $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

    $viewer = Engine_Api::_()->user()->getViewer();
    $topicOwner = $topic->getOwner();
    $isOwnTopic = $viewer->isSelf($topicOwner);

    $watch = (bool) $values['watch'];
    $isWatching = $topicWatchesTable
      ->select()
      ->from($topicWatchesTable->info('name'), 'watch')
      ->where('resource_id = ?', $sesforum->getIdentity())
      ->where('topic_id = ?', $topic->getIdentity())
      ->where('user_id = ?', $viewer->getIdentity())
      ->limit(1)
      ->query()
      ->fetchColumn(0)
      ;

    $db = $postTable->getAdapter();
    $db->beginTransaction();

    try {

      $post = $postTable->createRow();
      $post->setFromArray($values);
      $post->save();

      if( !empty($values['photo']) ) {
        try {
          $post->setPhoto($form->photo);
        } catch( Engine_Image_Adapter_Exception $e ) {}
      } 

      // Watch
      if( false === $isWatching ) {
        $topicWatchesTable->insert(array(
          'resource_id' => $sesforum->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $watch,
        ));
      } else if( $watch != $isWatching ) {
        $topicWatchesTable->update(array(
          'watch' => (bool) $watch,
        ), array(
          'resource_id = ?' => $sesforum->getIdentity(),
          'topic_id = ?' => $topic->getIdentity(),
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }
      
      $topicLink = '<a href="' . $topic->getHref() . '">' . $topic->getTitle() . '</a>';
      // Activity
      $action = $activityApi->addActivity($viewer, $topic, 'sesforum_topic_reply',null,  array("topictitle" => $topicLink));
      if( $action ) {
        $action->attach($post, $topic);
      }

      // Notifications
      $notifyUserIds = $topicWatchesTable->select()
        ->from($topicWatchesTable->info('name'), 'user_id')
        ->where('resource_id = ?', $sesforum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('watch = ?', 1)
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN)
        ;

      foreach( $userTable->find($notifyUserIds) as $notifyUser ) {
        // Don't notify self
        if( $notifyUser->isSelf($viewer) ) {
          continue;
        }
        if( $notifyUser->isSelf($topicOwner) ) {
          $type = 'sesforum_topic_response';
        } else {
          $type = 'sesforum_topic_reply';
        }
        $notifyApi->addNotification($notifyUser, $viewer, $topic, $type, array(
          'message' => $this->view->BBCode($post->body),
          'postGuid' => $post->getGuid(),
        ));
      }

      $db->commit();

      if(empty($topic_id) && empty($quote_id)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'success_message' => $this->view->translate('Topic created successfully.'))));
      } elseif(empty($quote_id)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'success_message' => $this->view->translate('Reply posted successfully.'))));
      } elseif(!empty($quote_id)) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'success_message' => $this->view->translate('Quote successfully.'))));
      }
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    //return $this->_redirectCustom($post);
  }

  public function topiccreateAction()
  {

    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $forum_id = $this->_getParam('forum_id', null);
    $sesforum = Engine_Api::_()->getItem('sesforum_forum', $forum_id);

    if( !$sesforum ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

//     // Render
//     $this->_helper->content
//         //->setNoRender()
//         ->setEnabled()
//         ;

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->sesforum = $sesforum = $sesforum; //Engine_Api::_()->core()->getSubject();
    if (!$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.create')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->form = $form = new Sesforum_Form_Topic_Create();

    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields);
    }

    // Remove the file element if there is no file being posted
    if( $this->getRequest()->isPost() && empty($_FILES['photo']) ) {
      $form->removeElement('photo');
    }

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(count($validateFields))
      $this->validateFormFields($validateFields);
    }

//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       return;
//     }

    // Process
    $values = $form->getValues();
    $values['user_id'] = $viewer->getIdentity();
    $values['forum_id'] = $sesforum->getIdentity();

    $topicTable = Engine_Api::_()->getDbtable('topics', 'sesforum');
    $topicWatchesTable = Engine_Api::_()->getDbtable('topicwatches', 'sesforum');
    $postTable = Engine_Api::_()->getDbtable('posts', 'sesforum');

    $db = $topicTable->getAdapter();
    $db->beginTransaction();

    try {

      // Create topic
      $topic = $topicTable->createRow();
      $topic->setFromArray($values);
      $topic->title = $values['title'];
      $topic->description = $values['body'];
      $topic->save();

      // Create post
      $values['topic_id'] = $topic->getIdentity();

      $post = $postTable->createRow();
      $values['body'] = Engine_Text_BBCode::prepare($values['body']);
      $post->setFromArray($values);
      $post->save();

      if( !empty($values['photo']) ) {
        $post->setPhoto($form->photo);
      }

      $auth = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($topic, 'registered', 'create', true);

      // Create topic watch
      $topicWatchesTable->insert(array(
        'resource_id' => $sesforum->getIdentity(),
        'topic_id' => $topic->getIdentity(),
        'user_id' => $viewer->getIdentity(),
        'watch' => (bool) $values['watch'],
      ));

      // Add activity
      $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
      $action = $activityApi->addActivity($viewer, $topic, 'sesforum_topic_create');
      if( $action ) {
        $action->attach($topic);
      }

      $db->commit();

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('topic_id' => $topic->getIdentity(),'success_message' => $this->view->translate('Topic created successfully.'))));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }

    //return $this->_redirectCustom($post);
  }

  public function editpostAction() {

//     if( !$this->_helper->requireUser()->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }

    $post_id = $this->_getParam('post_id', null);
    

    $post = Engine_Api::_()->getItem('sesforum_post', $post_id);
    
    

    if(!$post) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->post = $post = $post; //Engine_Api::_()->core()->getSubject('sesforum_post');
    $this->view->topic = $topic = $post->getParent();
    $this->view->sesforum = $sesforum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($post, null, 'edit')->checkRequire() &&
        !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.edit')->checkRequire() ) {
      //return $this->_helper->requireAuth()->forward();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $this->view->form = $form = new Sesforum_Form_Post_Edit(array('post'=>$post));

    $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_html', 0);
    $allowBbcode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_bbcode', 0);

    if( $allowHtml ) {
      $body = $post->body;
      $body = preg_replace_callback('/href=["\']?([^"\'>]+)["\']?/', function($matches) {
          return 'href="' . str_replace(['&gt;', '&lt;'], '', $matches[1]) . '"';
      }, $body);
    } else {
      $body = htmlspecialchars_decode($post->body, ENT_COMPAT);
    }
    $form->body->setValue($body);
    if($post->file_id)
    $form->photo->setValue($post->file_id);

    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $formFields[1]['name'] = "file";
      $this->generateFormFields($formFields);
    }

    // Check post/form
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }

    // Process
    $table = Engine_Api::_()->getItemTable('sesforum_post');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $values = $form->getValues();

      $post->body = $values['body'];
      $post->body = Engine_Text_BBCode::prepare($post->body);

      $post->edit_id = $viewer->getIdentity();

      //DELETE photo here.
      if( !empty($values['photo_delete']) && $values['photo_delete'] ) {
        $post->deletePhoto();
      }

      if( !empty($values['photo']) ) {
        $post->setPhoto($form->photo);
      }

      $post->save();

      $db->commit();

      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('post_id' => $post->getIdentity(),'success_message' => $this->view->translate('Post edited successfully.'))));

      //return $this->_helper->redirector->gotoRoute(array('post_id'=>$post->getIdentity(), 'topic_id' => $post->getParent()->getIdentity()), 'sesforum_topic', true);
    }

    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      //throw $e;
    }
  }

  public function topicviewpageAction() {

    $topic_id = (int) $this->_getParam('topic_id', null);

    $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id);
//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }


    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    //$topic = Engine_Api::_()->core()->getSubject('sesforum_topic');
    $sesforum = $topic->getParent();

    if( !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }

    $rating_count = Engine_Api::_()->sesforum()->ratingCount($topic->getIdentity());
    $rated = Engine_Api::_()->sesforum()->checkRated($topic->getIdentity(), $viewer->getIdentity());

    // Settings
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $post_id = (int) $this->_getParam('post_id');
    $decode_bbcode = $settings->getSetting('sesforum_bbcode');

    // Views
    if( !$viewer || !$viewer->getIdentity() || $viewer->getIdentity() != $topic->user_id ) {
      $topic->view_count = new Zend_Db_Expr('view_count + 1');
      $topic->save();
    }

    // Check watching
    $isWatching = null;
    if( $viewer->getIdentity() ) {
      $topicWatchesTable = Engine_Api::_()->getDbtable('topicwatches', 'sesforum');
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $sesforum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;
      if( false === $isWatching ) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }
    $isWatching = $isWatching;

    // Auth for topic
    $canPost = false;
    $canEdit = false;
    $canDelete = false;
    if( !$topic->closed && Engine_Api::_()->authorization()->isAllowed('sesforum_forum', null, 'post_create') ) {
      $canPost = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed('sesforum_forum', null, 'topic_edit') ) {
      $canEdit = true;
    }
    if( Engine_Api::_()->authorization()->isAllowed('sesforum_forum', null, 'topic_delete') ) {
      $canDelete = true;
    }

    $isModeratorPost = $sesforum->isModerator($viewer);
    if($isModeratorPost) {
        $canPost = true;
        $canEdit = true;
        $canDelete = true;
    }

    $canPost = $canPost;
    $canEdit = $can_edit = $canEdit;
    $canDelete = $can_delete = $canDelete;

    // Auth for posts
    $canEdit_Post = false;
    $canDelete_Post = false;
    if($viewer->getIdentity()){
      $canEdit_Post = Engine_Api::_()->authorization()->isAllowed('sesforum_forum', $viewer->level_id, 'post_edit');
      $canDelete_Post = Engine_Api::_()->authorization()->isAllowed('sesforum_forum', $viewer->level_id, 'post_delete');
    }
    $canEdit_Post = $canEdit_Post;
    $canDelete_Post = $canDelete_Post;



    // Make form
    if( $canPost ) {
      $form = new Sesforum_Form_Post_Quick();
      $form->setAction($topic->getHref(array('action' => 'post-create')));
      $form->populate(array(
        'topic_id' => $topic->getIdentity(),
        'ref' => $topic->getHref(),
        'watch' => ( false === $isWatching ? '0' : '1' ),
      ));
    }

    // Keep track of topic user views to show them which ones have new posts
    if( $viewer->getIdentity() ) {
      $topic->registerView($viewer);
    }

    $table = Engine_Api::_()->getItemTable('sesforum_post');
    $select = $topic->getChildrenSelect('sesforum_post', array('order'=>'post_id ASC'));
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage($settings->getSetting('sesforum_topic_pagelength'));

    // set up variables for pages
    $page_param = (int) $this->_getParam('page');
    $post = Engine_Api::_()->getItem('sesforum_post', $post_id);

    // if there is a post_id
    if( $post_id && $post && !$page_param )
    {
      $icpp = $paginator->getItemCountPerPage();
      $post_page = ceil(($post->getPostIndex() + 1) / $icpp);

      $paginator->setCurrentPageNumber($post_page);
    }
    // Use specified page
    else if( $page_param )
    {
      $paginator->setCurrentPageNumber($page_param);
    }

    //$post_content = $topic->toArray();
    $counterPost =  0;
    foreach( $paginator as $i => $post ) {
      $post_content = $post->toArray();
      
      $signature = $post->getSignature();
      $signature_body = $signature->body; 
      $doNl2br = false;
      if( strip_tags($signature_body) == $signature_body ) {
        $signature_body = nl2br($signature_body);
      }
      if( !$this->decode_html && $this->decode_bbcode ) {
        $signature_body = $this->BBCode($signature_body, array('link_no_preparse' => true));
      }
      
      $isModeratorPost = $sesforum->isModerator($post->getOwner());

      if( $post->user_id != 0 ) {
        if( $post->getOwner() ) {
          if( $isModeratorPost ) {
            $post_content['moderator_label'] = $this->view->translate('Moderator');
          }
        }

      }
      if($signature_body) {
        $post_content['signature'] = $signature_body;
      }
      
      $post_content['owner_title'] = Engine_Api::_()->getItem('user',$post->user_id)->getTitle();
      $post_content['description'] = $description;
      $post_content['owner_images'] = $this->userImage($post->user_id,"thumb.icon");
      $post_content['resource_type'] = $post->getType();
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.thanks', 1)) {
        $isThank = Engine_Api::_()->getDbTable('thanks', 'sesforum')->isThank(array('post_id' => $post->post_id,'resource_id' => $post->user_id));
        if (empty($isThank) && !empty($viewer_id) && $viewer_id != $post->user_id) {
            $post_content['isThanks'] = true;
        } else {
            $post_content['isThanks'] = false;
        }
      }

        $canLike = 1;
        $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($post, $viewer);
        if ($canLike && !empty($viewer_id)) {
            if(empty($isLike)) {
              $post_content['is_content_like'] = false;
            } else {
              $post_content['is_content_like'] = true;
            }
        }

      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.thanks', 1)) {
        $thanks = Engine_Api::_()->getDbTable('thanks', 'sesforum')->getAllUserThanks($post->user_id);
        if($thanks) {
          $post_content['thanks'] = $this->view->translate("%s Thank(s)", $thanks);
          $post_content['thanks_count'] = $thanks;
        }
      }

      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.reputation', 1)) {
        $getIncreaseReputation = Engine_Api::_()->getDbTable('reputations', 'sesforum')->getIncreaseReputation(array('user_id' => $post->user_id));
        $getDecreaseReputation = Engine_Api::_()->getDbTable('reputations', 'sesforum')->getDecreaseReputation(array('user_id' => $post->user_id));
        $post_content['reputations'] = $this->view->translate("%s - %s", $getIncreaseReputation, $getDecreaseReputation);
      }

      $signature = $post->getSignature();
      if($signature) {
        $post_content['post_count'] = $signature->post_count;
      }
      //$pagedata["share"]["imageUrl"] = $this->getBaseUrl(false, $page->getPhotoUrl());
      $post_content["share"]["url"] = $this->getBaseUrl(false,$post->getHref());
      $post_content["share"]["title"] = $post->getTitle();
      $post_content["share"]["description"] = strip_tags($post->getDescription());
      $post_content["share"]["setting"] = $shareType;
      $post_content["share"]['urlParams'] = array(
        "type" => $post->getType(),
        "id" => $post->getIdentity()
      );
      
      // Auth for topic
      $canPost = 0;
      $canEdit = false;
      $canDelete = false;
      if($viewer->getIdentity())
        $levelId = $viewer->level_id;
      else
        $levelId = 5;
      $canPostPerminsion = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'post_create');
      if(!$topic->closed && $canPostPerminsion) {
        $canPost = $canPostPerminsion->value;
      }
      $canEditPerminsion = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'topic_edit');
      if($canEditPerminsion) {
        $canEdit = $canEditPerminsion->value;
      }
      // echo $canEdit;
      $canDeletePerminsion = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'topic_delete');
      if($canDeletePerminsion) {
        $canDelete = $canDeletePerminsion->value;
      }

      $isModeratorPost = $sesforum->isModerator($viewer);
      if($isModeratorPost) {
          $canPost = 1;
          $canEdit = true;
          $canDelete = true;
      }
      
      // Auth for posts
      $canEdit_Post = false;
      $canDelete_Post = false;
      if($viewer->getIdentity()){
        $canEdit_Post = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'post_edit')->value;
        $canDelete_Post = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'post_delete')->value;
      }
      
      if($topic->closed) {
        $canPost = 0;
      }
      $post_content['canPost'] = $canPost;
      $menuoptions = $options = array();
      $counter = $option_counter = 0;
      if($canPost && !$topic->closed) {
        $menuoptions[$counter]['name'] = "quote";
        $menuoptions[$counter]['label'] = $this->view->translate("Quote");
        $counter++;
      }

      if(!empty($viewer->getIdentity())) {

        $canLike = 1;
        $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($post, $viewer);
        
        if ($canLike && !empty($viewer_id)) {
            if(empty($isLike)) {
              $menuoptions[$counter]['name'] = "like";
              $menuoptions[$counter]['label'] = $this->view->translate("Like");
              $counter++;
            } else {
              $menuoptions[$counter]['name'] = "unlike";
              $menuoptions[$counter]['label'] = $this->view->translate("Unlike");
              $counter++;
            }
        }

        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.thanks', 1)) {
          $isThank = Engine_Api::_()->getDbTable('thanks', 'sesforum')->isThank(array('resource_id' => $post->post_id));
          if (empty($isThank) && !empty($viewer_id)) {
              $menuoptions[$counter]['name'] = "thanks";
              $menuoptions[$counter]['label'] = $this->view->translate("Say Thank");
              $menuoptions[$counter]['isThanks'] = true;
              $counter++;
          } else {
              $menuoptions[$counter]['isThanks'] = false;
              $counter++;
          }
        }

        if($post->user_id != $viewer->getIdentity() ) {
          $options[$option_counter]['name'] = "report";
          $options[$option_counter]['label'] = $this->view->translate("Report");
          $option_counter++;
        }
        
        $isReputation = Engine_Api::_()->getDbTable('reputations', 'sesforum')->isReputation(array('post_id' => $post->getIdentity(), 'resource_id' => $post->user_id));
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.reputation', 1) && empty($isReputation) && $viewer_id != $post->user_id) {
          $options[$option_counter]['name'] = "reputation";
          $options[$option_counter]['label'] = $this->view->translate("Add Reputation");
          $option_counter++;

        }
        
        
        if( $canEdit && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $sesforum->isModerator($viewer)) ) {
          $post_content['canEdit'] = true;
          $post_content['canDelete'] = true;
        } elseif( $post->user_id != 0 && $post->isOwner($viewer) && !$topic->closed  && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $sesforum->isModerator($viewer))) {
          $post_content['post_count'] = $signature->post_count;
          $post_content['canEdit'] = true;
          if( $this->canDelete_Post ) {
            $post_content['canDelete'] = true;
          } else {
            $post_content['canDelete'] = false;
          }
        } else {
          $post_content['canEdit'] = false;
          $post_content['canDelete'] = false;
        }

        if( $canEdit_Post && $canDelete_Post && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $sesforum->isModerator($viewer)) ) {
          $options[$option_counter]['name'] = "edit";
          $options[$option_counter]['label'] = $this->view->translate("Edit");
          $option_counter++;
          $options[$option_counter]['name'] = "delete";
          $options[$option_counter]['label'] = $this->view->translate("Delete");
          $option_counter++;
        } elseif( $post->user_id != 0 && $post->isOwner($viewer) && !$topic->closed  && ($viewer_id == $post->user_id || $viewer->level_id == '1' || $sesforum->isModerator($viewer))) {
          if( $canEdit_Post ) {
            $options[$option_counter]['name'] = "edit";
            $options[$option_counter]['label'] = $this->view->translate("Edit");
            $option_counter++;
          }

          if( $canDelete_Post ) {
            $options[$option_counter]['name'] = "delete";
            $options[$option_counter]['label'] = $this->view->translate("Delete");
            $option_counter++;
          }
        } else if(($canDelete_Post || $canEdit_Post || ($post->user_id != $viewer->getIdentity() || $viewer_id)) && $viewer_id) {
          if(!$post->isOwner($viewer)) {
            if( $canEdit_Post == 2 ) {
              $options[$option_counter]['name'] = "edit";
              $options[$option_counter]['label'] = $this->view->translate("Edit");
              $option_counter++;
            }

            if( $canDelete_Post == 2 ) {
              $options[$option_counter]['name'] = "delete";
              $options[$option_counter]['label'] = $this->view->translate("Delete");
              $option_counter++;
            }
          }
        }
      }
      $post_content['options'] = $options;
      $post_content['menus'] = $menuoptions;

      $result['posts'][$counterPost] = $post_content;

      $counterPost++;
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;

    //Topic Content
    $topicContent['topic_title'] = $topic->getTitle();
    $topicContent['topic_id'] = $topic->getIdentity();
    $topicContent['can_rate'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.rating', 1) ? true : false;
    $topicContent['rating'] = $topic->rating;
    $topicContent['rating_count'] = Engine_Api::_()->sesforum()->ratingCount($topic->getIdentity());
    $topicContent['back_to_topics'] = $this->view->translate("Back to Topics");
    if( $canPost && !$topic->closed) {
      $topicContent['post_reply'] = $this->view->translate("Post Reply");
    }
    
    
    // Check watching
    $isWatching = null;
    if( $viewer->getIdentity() ) {
      $topicWatchesTable = Engine_Api::_()->getDbtable('topicwatches', 'sesforum');
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $sesforum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;
      if( false === $isWatching ) {
        $isWatching = null;
      } else {
        $isWatching = (bool) $isWatching;
      }
    }
    
    
    
    $topicContent['can_subscribe'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.subscribe', 1);
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.subscribe', 1)) { 
      if( $viewer->getIdentity() ) {
        //$isSubscribe = Engine_Api::_()->getDbTable('subscribes', 'sesforum')->isSubscribe(array('resource_id' => $topic->getIdentity()));
        if( !$isWatching ) {
          $topicContent['subscribe'] = $this->view->translate("Subscribe");
          $topicContent['watch'] = 1;
        } else {
          $topicContent['unsubscribe'] = $this->view->translate("Unsubscribe");
          $topicContent['watch'] = 0;
        }
      }
    }
    if($viewer_id && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.rating', 1)) {
      $topicContent['is_rated'] = $rated;
    }
    $topicContent['like_count'] = $topic->like_count;
    if( $viewer->getIdentity() ) {
      $canLike = 1;
      $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($topic, $viewer);
      if ($canLike && !empty($viewer_id)) {
        if(empty($isLike)) {
          $topicContent['is_content_like'] = false;
        } else {
          $topicContent['is_content_like'] = true;
        }
      }
    }
    
    $tags = array();
    foreach ($topic->tags()->getTagMaps() as $tagmap) {
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
      $topicContent['tag'] = $tags;
    }
    
    if( !$topic->sticky ) {
      $topicContent['sticky'] = true;
    } else {
      $topicContent['sticky'] = false;
    }
    if( !$topic->closed ) {
      $topicContent['close'] = true;
    } else {
      $topicContent['close'] = false;
    }
    //$pagedata["share"]["imageUrl"] = $this->getBaseUrl(false, $page->getPhotoUrl());
    $topicContent["share"]["url"] = $this->getBaseUrl(false,$topic->getHref());
    $topicContent["share"]["title"] = $topic->getTitle();
    $topicContent["share"]["description"] = strip_tags($topic->getDescription());
    $topicContent["share"]["setting"] = $shareType;
    $topicContent["share"]['urlParams'] = array(
      "type" => $topic->getType(),
      "id" => $topic->getIdentity(),
    );
    if( $viewer->getIdentity() ) {
      $canLike = 1;
      $isLike = Engine_Api::_()->getDbTable('likes', 'core')->isLike($topic, $viewer);
      
      if ($canLike && !empty($viewer_id)) {
        $topic_menuoptions = array();
        $topic_counter = 0;
        if(empty($isLike)) {
          $topic_menuoptions[$topic_counter]['name'] = "like";
          $topic_menuoptions[$topic_counter]['label'] = $this->view->translate("Like");
          $topic_counter++;
        } else {
          $topic_menuoptions[$topic_counter]['name'] = "unlike";
          $topic_menuoptions[$topic_counter]['label'] = $this->view->translate("Unlike");
          $topic_counter++;
        }
      }
      $topic_menuoptions[$topic_counter]['name'] = "share";
      $topic_menuoptions[$topic_counter]['label'] = $this->view->translate("Share");
      $topic_counter++;


      $topicContent['buttons'] = $topic_menuoptions;
    }
    
    // Auth for topic
    $canEdit = false;
    $canDelete = false;

    $canEditPerminsion = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'topic_edit');
    if($canEditPerminsion) {
      $canEdit = $canEditPerminsion->value;
    }
    // echo $canEdit;
    $canDeletePerminsion = Engine_Api::_()->sesforum()->isAllowed('sesforum_forum',$levelId, 'topic_delete');
    if($canDeletePerminsion) {
      $canDelete = $canDeletePerminsion->value;
    }

    $isModeratorPost = $sesforum->isModerator($viewer);
    if($isModeratorPost) {
        $canEdit = true;
        $canDelete = true;
    }
    
    if( ($canEdit || $canDelete) && ($viewer_id == $topic->user_id || $viewer->level_id == '1' || $sesforum->isModerator($viewer)) || (($canEdit == 2) || ($canDelete == 2))) {
      if($can_edit) {
        $topicContent['canEdit'] = true;
      } else {
        $topicContent['canEdit'] = false;
      }
      if($can_delete) {
        $topicContent['canDelete'] = true;
      } else {
        $topicContent['canDelete'] = false;
      }
    }

    if( ($canEdit || $canDelete) && ($viewer_id == $topic->user_id || $viewer->level_id == '1' || $sesforum->isModerator($viewer)) || (($canEdit == 2) || ($canDelete == 2))) {
      $topic_options = array();
      $topic_opcounter = 0;

      if(($canEdit && $topic->user_id == $viewer->getIdentity()) || $canEdit == 2) {
        if( !$topic->sticky ) {
          $topic_options[$topic_opcounter]['name'] = "sticky";
          $topic_options[$topic_opcounter]['sticky'] = "1";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Make Sticky");
          $topic_opcounter++;
        } else {
          $topic_options[$topic_opcounter]['name'] = "sticky";
          $topic_options[$topic_opcounter]['sticky'] = "0";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Remove Sticky");
          $topic_opcounter++;
        }

        if( !$topic->closed ) {
          $topic_options[$topic_opcounter]['name'] = "forumclose";
          $topic_options[$topic_opcounter]['close'] = "1";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Close");
          $topic_opcounter++;
        } else {
          $topic_options[$topic_opcounter]['name'] = "forumclose";
          $topic_options[$topic_opcounter]['close'] = "0";
          $topic_options[$topic_opcounter]['label'] = $this->view->translate("Open");
          $topic_opcounter++;
        }
        $topic_options[$topic_opcounter]['name'] = "rename";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Rename");
        $topic_opcounter++;
        $topic_options[$topic_opcounter]['name'] = "move";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Move");
        $topic_opcounter++;
      }
      if( ($canDelete && $topic->user_id == $viewer->getIdentity()) || $canDelete == 2 ) {
        $topic_options[$topic_opcounter]['name'] = "delete";
        $topic_options[$topic_opcounter]['label'] = $this->view->translate("Delete");
        $topic_opcounter++;
      }

      $topicContent['options'] = $topic_options;
    }

    $result['topic_content'] = $topicContent;

    //Reply Form
//     if( $canPost && $form ) {
//       $result['reply_form'] = $topicContent;
//
//     }

    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));

  }

    public function thankAction() {

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (empty($viewer_id))
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        $topicuser_id = $this->_getParam('topicuser_id');

        $thank_id = $this->_getParam('thank_id');

        $resource_id = $this->_getParam('resource_id', null);
        $resource_type = $this->_getParam('resource_type', null);
        $resource = Engine_Api::_()->getItem($resource_type, $resource_id);

        $topic = Engine_Api::_()->getItem('sesforum_topic', $resource->topic_id);

        $thankTable = Engine_Api::_()->getDbTable('thanks', 'sesforum');
        
        //$tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $thankTable->info('name');
        $select = $thankTable->select()
            ->from($tableMainLike)
            //->where('resource_type = ?', $resource_type)
            ->where('poster_id = ?', $viewer_id)
           // ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $resource_id);

        $result = $thankTable->fetchRow($select);

        if (count($result) == 0) {

        //if (empty($thank_id)) {
            $db = $thankTable->getAdapter();
            $db->beginTransaction();
            try {

                $row = $thankTable->createRow();
                $row->poster_id = $viewer_id;
                $row->resource_id = $topicuser_id;
                $row->post_id = $resource_id;
                $row->save();
                $resource->thanks_count++;
                $resource->save();
                $this->view->thank_id = $row->thank_id;
                $owner = Engine_Api::_()->getItem('user', $resource->user_id);
                if($owner->getIdentity() != $viewer_id) {
                    if ($resource_type == 'sesforum_post') {
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $topic, 'sesforum_post_thanks', array('label' => $topic->getShortType()));
                        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $topic, 'sesforum_post_thanks');
                        if ($action)
                            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $topic);
                    }
                }
                $db->commit();
                $temp['message'] = $this->view->translate('Done');
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
        }
    }


  public function likeAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
    $like_id = $this->_getParam('like_id');

    $item = Engine_Api::_()->getItem($resource_type, $resource_id);

    $likeTable = Engine_Api::_()->getDbTable('likes', 'core');
    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
    $activityStrameTable = Engine_Api::_()->getDbtable('stream', 'activity');
    
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $select = $tableLike->select()
        ->from($tableMainLike)
        ->where('resource_type = ?', $resource_type)
        ->where('poster_id = ?', $viewer_id)
        ->where('poster_type = ?', 'user')
        ->where('resource_id = ?', $resource_id);

    $result = $tableLike->fetchRow($select);

    if (count($result) == 0) {
      $isLike = $likeTable->isLike($item, $viewer);

      if (empty($isLike)) {
        $db = $likeTable->getAdapter();
        $db->beginTransaction();
        try {
          if (!empty($item))
            $like_id = $likeTable->addLike($item, $viewer)->like_id;
          //$this->view->like_id = $like_id;
          $owner = $item->getOwner();
          if($owner->getIdentity() != $viewer_id) {
            if ($resource_type == 'sesforum_topic') {
              Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'liked', array('label' => $item->getShortType()));

              $action = $activityTable->addActivity($viewer, $item, 'sesforum_like_topic');
              if ($action)
                $activityTable->attachActivity($action, $item);
            } else if ($resource_type == 'sesforum_post') {
              $topic = Engine_Api::_()->getItem('sesforum_topic', $item->topic_id);
              Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $topic, 'liked', array('label' => $item->getShortType()));

              $action = $activityTable->addActivity($viewer, $topic, 'sesforum_like_post');
              if ($action)
                $activityTable->attachActivity($action, $item);
            }
          }

          $db->commit();
          $temp['message'] = array('like_id' => $like_id);
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
        } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
      } else {
        $temp['message'] = array('like_id' => $isLike);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
        //$this->view->like_id = $isLike;
      }
    } else {
      if ($resource_type == 'sesforum_topic') {
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "liked", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => 'sesforum_topic', "object_id = ?" => $item->getIdentity()));
       $action = $activityTable->fetchRow(array('type =?' => "sesforum_like_topic", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      }
      if (!empty($action)) {
        $action->delete();
      }

      $likeTable->removeLike($item, $viewer);
      $temp['message'] = array('like_id' => 0);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));

      //$this->view->like_id = 0;
    }
  }


  public function addreputationAction() {

      if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();

      $resource_id = $this->_getParam('resource_id', null);
      $post_id = $this->_getParam('post_id', null);

      $this->view->form = $form = new Sesforum_Form_Reputation();
      if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
      }

      if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
      }

      if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (count($validateFields))
            $this->validateFormFields($validateFields);
      }

      $values = $form->getValues();

      // Process
      $table = Engine_Api::_()->getDbTable('reputations', 'sesforum');
      $db = $table->getAdapter();
      $db->beginTransaction();
      try {
        $row = $table->createRow();
        $row->resource_id = $resource_id;
        $row->post_id = $post_id;
        $row->poster_id = $viewer_id;
        $row->reputation = $values['reputation'];
        $row->save();
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully reputed this post.'))));
      } catch( Exception $e ) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
  }

  public function stickyAction()
  {

//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       return;
//     }
    $topic_id = $this->_getParam('topic_id', null);
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic =  Engine_Api::_()->getItem('sesforum_topic', $topic_id); //  Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
//     if( !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.edit')->isValid() ) {
//       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
//     }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      //$topic = Engine_Api::_()->core()->getSubject();
      $topic->sticky = ( null === $this->_getParam('sticky') ? !$topic->sticky : (bool) $this->_getParam('sticky') );
      $topic->save();
      $db->commit();
      $temp['message'] = $this->view->translate('Done');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }

  public function closeAction()
  {

//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       return;
//     }
    $topic_id = $this->_getParam('topic_id', null);
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id); // Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
//     if( !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.edit')->isValid() ) {
//       return;
//     }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      //$topic = Engine_Api::_()->core()->getSubject();
      $topic->closed = ( null === $this->_getParam('closed') ? !$topic->closed : (bool) $this->_getParam('closed') );
      $topic->save();
      $db->commit();
      $temp['message'] = $this->view->translate('Done');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

    //$this->_redirectCustom($topic);
  }

  public function deletepostAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
//     if( !$this->_helper->requireSubject('sesforum_post')->isValid() ) {
//       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
//     }

    $post_id = $this->_getParam('post_id', null);

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->post = $post = Engine_Api::_()->getItem('sesforum_post',$post_id);  //Engine_Api::_()->core()->getSubject('sesforum_post');
    $this->view->topic = $topic = $post->getParent();
    $this->view->sesforum = $sesforum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams($post, null, 'delete')->checkRequire() &&
        !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.delete')->checkRequire() ) {
      //Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
      //return $this->_helper->requireAuth()->forward();
    }

    $this->view->form = $form = new Sesforum_Form_Post_Delete();
    if (!$this->getRequest()->isPost()) {
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       return;
//     }

    // Process
    $table = Engine_Api::_()->getItemTable('sesforum_post');
    $db = $table->getAdapter();
    $db->beginTransaction();

    $topic_id = $post->topic_id;

    try
    {
      $post->delete();

      $db->commit();

      $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id);
      $href = ( null === $topic ? $sesforum->getHref() : $topic->getHref() );

      $status['status'] = true;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this post.'))));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

//     $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id);
//     $href = ( null === $topic ? $sesforum->getHref() : $topic->getHref() );
//     return $this->_forward('success', 'utility', 'core', array(
//       'closeSmoothbox' => true,
//       'parentRedirect' => $href,
//       'messages' => array(Zend_Registry::get('Zend_Translate')->_('Post deleted.')),
//       'format' => 'smoothbox'
//     ));
  }

  public function renameAction()
  {
//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       return;
//     }
    $topic_id = $this->_getParam('topic_id', null);
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id); // Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
//     if( !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.edit')->isValid() ) {
//       return;
//     }

    $this->view->form = $form = new Sesforum_Form_Topic_Rename();
      if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
      }
    if( !$this->getRequest()->isPost() )
    {
      $form->title->setValue(htmlspecialchars_decode(($topic->title)));
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $title = $form->getValue('title');
      $topic = $topic; //Engine_Api::_()->core()->getSubject();
      $topic->title = $title;
      $topic->save();
      $db->commit();

      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have rename topic.'))));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

//     return $this->_forward('success', 'utility', 'core', array(
//       'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic renamed.')),
//       'layout' => 'default-simple',
//       'parentRefresh' => true,
//     ));
  }


  public function moveAction()
  {
//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       return;
//     }
    $topic_id = $this->_getParam('topic_id', null);
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id); // Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
//     if( !$this->_helper->requireAuth()->setAuthParams($sesforum, null, 'topic.edit')->isValid() ) {
//       return;
//     }

    $this->view->form = $form = new Sesforum_Form_Topic_Move();

    // Populate with options
    $multiOptions = array();
    foreach( Engine_Api::_()->getItemTable('sesforum_forum')->fetchAll() as $sesforum ) {
      $multiOptions[$sesforum->getIdentity()] = $this->view->translate($sesforum->getTitle());
    }
    $form->getElement('forum_id')->setMultiOptions($multiOptions);
      if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
      }
    if( !$this->getRequest()->isPost() ) {
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $values = $form->getValues();

    $table = $topic->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      // Update topic
      $topic->forum_id = $values['forum_id'];
      $topic->save();

      $db->commit();

      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Topic moved.'))));
    }

    catch( Exception $e )
    {
      $db->rollBack();
      //throw $e;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }

//     return $this->_forward('success', 'utility', 'core', array(
//       'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic moved.')),
//       'layout' => 'default-simple',
//       //'parentRefresh' => true,
//       'parentRedirect' => $topic->getHref(),
//     ));
  }


  public function deletetopicAction()
  {
//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       return;
//     }
    $topic_id = $this->_getParam('topic_id', null);

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id); //Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
    if( !$this->_helper->requireAuth()->setAuthParams('sesforum_forum', null, 'topic.delete')->isValid() ) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }

    $this->view->form = $form = new Sesforum_Form_Topic_Delete();

    if (!$this->getRequest()->isPost()) {
      $status['status'] = false;
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
    }

//     if( !$form->isValid($this->getRequest()->getPost()) ) {
//       return;
//     }

    // Process
    $table = Engine_Api::_()->getItemTable('sesforum_topic');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $topic->delete();
      $db->commit();
      $status['status'] = true;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this topic.'), $status, 'href' => $sesforum->getHref())));
    } catch( Exception $e ) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
/*
    return $this->_forward('success', 'utility', 'core', array(
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Topic deleted.')),
      'layout' => 'default-simple',
      'parentRedirect' => $sesforum->getHref(),
    ));*/
  }


  public function rateAction() {

      $viewer = Engine_Api::_()->user()->getViewer();
      $user_id = $viewer->getIdentity();

      $rating = $this->_getParam('rating');
      $topic_id =  $this->_getParam('topic_id');

      $table = Engine_Api::_()->getDbtable('ratings', 'sesforum');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try {
          Engine_Api::_()->sesforum()->setRating($topic_id, $user_id, $rating);

          $forum_topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id);
          $forum_topic->rating = Engine_Api::_()->sesforum()->getRating($forum_topic->getIdentity());
          $forum_topic->save();

          if($forum_topic->user_id != $viewer->getIdentity()) {
              $owner = Engine_Api::_()->getItem('user', $forum_topic->user_id);
              Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $forum_topic, 'sesforum_rating');
          }

          $db->commit();
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }

      $total = Engine_Api::_()->sesforum()->ratingCount($forum_topic->getIdentity());

      $data = array();
      $data = array(
        'total' => $total,
        'rating' => $rating,
      );
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Successfully rated.'))));
  }

  public function indexAction() {
  
    if ( !$this->_helper->requireAuth()->setAuthParams('sesforum_forum', null, 'view')->isValid() ) {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $categoryTable = Engine_Api::_()->getItemTable('sesforum_category');
    $limit_data = 50;
    $select = $categoryTable->getCategoriesAssoc(array('limit'=>$limit_data));
    $categories = Zend_Paginator::factory($select);
    
    $sesforumTable = Engine_Api::_()->getItemTable('sesforum_forum');
    $sesforumSelect = $sesforumTable->select()->order('order ASC');

    $sesforums = array();
    foreach( $sesforumTable->fetchAll() as $sesforum ) {
      if( Engine_Api::_()->authorization()->isAllowed($sesforum, null, 'view') ) {
        $order = $sesforum->order;
        while( isset($sesforums[$sesforum->category_id][$order]) ) {
          $order++;
        }
        $sesforums[$sesforum->category_id][$order] = $sesforum;
        ksort($sesforums[$sesforum->category_id]);
      }
    }
    $sesforums = $sesforums;

    $result = array();
    $counter = 0;

    foreach($categories as $category) {
      $category_id = $category->getIdentity();
      $result['categories'][$counter] = $category->toArray();
      
      $result['categories'][$counter]['category_name'] = $category->title;
      $result['categories'][$counter]['category_id'] = $category->getIdentity();
      $result['categories'][$counter]['type'] = 'category';
      if($category->cat_icon != '' && !is_null($category->cat_icon) && intval($category->cat_icon)) {
        $cat_icon = Engine_Api::_()->storage()->get($category->cat_icon, '');
        if($cat_icon) {
          $cat_icon = $this->getBaseUrl(true, $cat_icon->map());
        } else {
          $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $result["categories"][$counter]["cat_icon"] = $cat_icon;
      } 
      else {
          $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
          $result["categories"][$counter]["cat_icon"] = $cat_icon;
      }
        
      
      
      
      $subCatCounter = 0;
      $subcat = array();
      if($viewer->getIdentity()){
          $levelId = $viewer->level_id;
      } else {
          $levelId = 5;
      } 
      
      $subCategories = Engine_Api::_()->getItemTable('sesforum_category')->fetchAll(Engine_Api::_()->getItemTable('sesforum_category')->select()->where('subcat_id = ?', $category->category_id)->where("privacy LIKE ? ", '%' . $levelId. '%')->order('order ASC'));

      foreach($subCategories as $subCategorie) {  
        $subcat[$subCatCounter] = $subCategorie->toArray();
        
        $subcat[$subCatCounter]['category_name'] = $subCategorie->title;
        $subcat[$subCatCounter]['category_id'] = $subCategorie->getIdentity();
        $subcat[$subCatCounter]['type'] = 'subcat';
        if($subCategorie->cat_icon != '' && !is_null($subCategorie->cat_icon) && intval($subCategorie->cat_icon)) {
          $cat_icon = Engine_Api::_()->storage()->get($subCategorie->cat_icon, '');
          if($cat_icon) {
            $cat_icon = $this->getBaseUrl(true, $cat_icon->map());
          } else {
            $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
          }
          $subcat[$subCatCounter]["cat_icon"] = $cat_icon;
        } else {
            $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
            $subcat[$subCatCounter]["cat_icon"] = $cat_icon;
          }
          
        $subCatCounter++;
      }
      $result['categories'][$counter]['subcat'] = $subcat;
      $counter++;
    }

    //Stats
    $forumTable = Engine_Api::_()->getDbTable('forums', 'sesforum');
    $forumTableName = $forumTable->info('name');

    $select = $forumTable->select()
                        ->from($forumTableName, array("COUNT(*) as forumCount", "SUM(post_count) as postCount", "SUM(topic_count) as topicCount"));

    $results = $forumTable->fetchRow($select);

    $userTable = Engine_Api::_()->getDbTable('users', 'user');
    $userTableName = $userTable->info('name');
    $totalusers = $userTable->select()
                ->from($userTableName, array("COUNT(*) as userCount"))
                ->where('verified = ?', 1)
                ->where('enabled = ?', 1)
                ->where('approved = ?', 1)
                ->query()
                ->fetchColumn();

    $postsTable = Engine_Api::_()->getDbTable('posts', 'sesforum');
    $postsTableName = $postsTable->info('name');
    $activeUsers = $postsTable->select()
                        ->from($postsTableName, array('user_id'))->group('user_id')
                        ->query()
                        ->fetchColumn();
    if(empty($activeUsers)) {
      $activeUsers = 0;
    }
    $result['stats'] = array('forum_count' => $results->forumCount, 'topic_count' => $results->topicCount, 'post_count' => $results->postCount, 'total_users' => $totalusers, 'total_active_users' => $activeUsers);
    $result['dashboard_url'] = $this->getBaseUrl(true, $this->view->url(array('action' => 'dashboard','type'=> 'my-topics', 'sesforum_extend', true)));
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist events.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result)));

  }

  public function categoryviewAction() {

    $category_id = $this->_getParam('category_id', null);
    if(empty($category_id))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $category = Engine_Api::_()->getItem('sesforum_category', $category_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $result = array();

    $subCatCounter = 0;
    $subcat = array();
    
    if($viewer->getIdentity()){
        $levelId = $viewer->level_id;
    } else {
        $levelId = 5;
    } 
    
    $subCategories = Engine_Api::_()->getItemTable('sesforum_category')->fetchAll(Engine_Api::_()->getItemTable('sesforum_category')->select()->where('subcat_id = ?', $category->category_id)->where("privacy LIKE ? ", '%' . $levelId. '%')->order('order ASC'));
    $result['subcat']['heading'] = $this->view->translate("Sub Categories");
    foreach($subCategories as $subCategorie) {  
      $subcat['subcat'][$subCatCounter] = $subCategorie->toArray();
      $subcat['subcat'][$subCatCounter]['type'] = 'subcat';
      $subcat['subcat'][$subCatCounter]['category_name'] = $subCategorie->title;
      $subcat['subcat'][$subCatCounter]['category_id'] = $subCategorie->getIdentity();
      if($subCategorie->cat_icon != '' && !is_null($subCategorie->cat_icon) && intval($subCategorie->cat_icon)) {
        $cat_icon = Engine_Api::_()->storage()->get($subCategorie->cat_icon, '');
        if($cat_icon) {
          $cat_icon = $this->getBaseUrl(true, $cat_icon->map());
        } else {
          $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $subcat['subcat'][$subCatCounter]["cat_icon"] = $cat_icon;
      } else {
          $subcat['subcat'][$subCatCounter]["cat_icon"] = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
      }

      $subsubCatCounter = 0;
      $subsubcat = array();
      $subsubCategories = Engine_Api::_()->getItemTable('sesforum_category')->fetchAll(Engine_Api::_()->getItemTable('sesforum_category')->select()->where('subsubcat_id = ?', $subCategorie->category_id)->where("privacy LIKE ? ", '%' . $levelId. '%')->order('order ASC'));
      foreach($subsubCategories as $subsubCategorie) {  
        $subsubcat[$subsubCatCounter] = $subsubCategorie->toArray();
        
        $subsubcat[$subsubCatCounter]['category_name'] = $subsubCategorie->title;
        $subsubcat[$subsubCatCounter]['type'] = 'subsubcat';
        $subsubcat[$subsubCatCounter]['category_id'] = $subsubCategorie->getIdentity();
        if($subsubCategorie->cat_icon != '' && !is_null($subsubCategorie->cat_icon) && intval($subsubCategorie->cat_icon)) {
          $cat_icon = Engine_Api::_()->storage()->get($subsubCategorie->cat_icon, '');
          if($cat_icon) {
            $cat_icon = $this->getBaseUrl(true, $cat_icon->map());
          } else {
            $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
          }
          $subsubcat[$subsubCatCounter]["cat_icon"] = $cat_icon;
        } else {
            $subsubcat[$subsubCatCounter]["cat_icon"] = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $subsubCatCounter++;
      }
      
      $subcat['subcat'][$subCatCounter]['subsubcat'] = $subsubcat;
      $subCatCounter++;
    }
    
    $result = $subcat;

    
    $sesforumTable = Engine_Api::_()->getItemTable('sesforum_forum');
    $sesforumSelect = $sesforumTable->select()->where('category_id =?', $category_id)->order('order ASC');

    $forums = $forumIds = array();
    $counterSesforum =  0;
    foreach($sesforumTable->fetchAll($sesforumSelect) as $sesforum) {

      $allForums = $sesforum->toArray();
      
      $forums['forums'][$counterSesforum] = $allForums;
      
      if($sesforum->forum_icon != '' && !is_null($sesforum->forum_icon) && intval($sesforum->forum_icon)) {
        $forum_icon = Engine_Api::_()->storage()->get($sesforum->forum_icon, '');
        if($forum_icon) {
          $forum_icon = $this->getBaseUrl(true, $forum_icon->map());
        } else {
          $forum_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $forums['forums'][$counterSesforum]["forum_icon"] = $forum_icon;
      } else {
          $forums['forums'][$counterSesforum]["forum_icon"] = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
      }
      
      $forumIds[] = $sesforum->forum_id;
      $counterSesforum++;
    }
    
    $topics = array();
    $topicCounter =  0;
    if(count($forumIds) > 0) {
      $topicTable = Engine_Api::_()->getItemTable('sesforum_topic');
      $topicTableName = $topicTable->info('name');
      
      $forumTable = Engine_Api::_()->getItemTable('sesforum_forum')->info('name');
      $topicSelect = $topicTable->select()
                                ->from($topicTableName,'*')
                                ->setIntegrityCheck(false)->joinLeft($forumTable, "$forumTable.forum_id = $topicTableName.forum_id",null)
                                ->where($topicTableName.".forum_id IN (?)",$forumIds);
      $topicPaginator = Zend_Paginator::factory($topicSelect);
      $topicPaginator->setItemCountPerPage(10);
      $topicPaginator->setCurrentPageNumber(1);

      foreach( $topicPaginator as $i => $topic ) {
        $alltopics = $topic->toArray();
        $topics['topics'][$topicCounter]['description'] = strip_tags($topic->description);
        unset($alltopics['description']);
        $topics['topics'][$topicCounter] = $alltopics;
        $owner = Engine_Api::_()->getItem('user', $topic->user_id);
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
        if(empty($owner->photo_id)) {
          $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
          $ownerimage['main'] = $defPhoto;
          $ownerimage['icon'] = $defPhoto;
          $ownerimage['normal'] = $defPhoto;
          $ownerimage['profile'] = $defPhoto;
        }
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
        if(empty($owner->photo_id)) {
          $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
          $ownerimage['main'] = $defPhoto;
          $ownerimage['icon'] = $defPhoto;
          $ownerimage['normal'] = $defPhoto;
          $ownerimage['profile'] = $defPhoto;
        }
        $topics['topics'][$topicCounter]['owner_image'] = $ownerimage;
        $topics['topics'][$topicCounter]['owner_title'] = $owner->getTitle();
        $lastPoster = Engine_Api::_()->getItem('user', $topic->lastposter_id);
        $lastPostCount = 0;
        if( $lastPoster) {
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_images'] = $this->userImage($lastPoster->user_id,"thumb.icon");
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_id'] = $lastPoster->user_id;
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_title'] = $lastPoster->getTitle();
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['creation_date'] = $topic->modified_date;
          $lastPostCount++;
        }
        
        
        $topics['topics'][$topicCounter]['last_poster_username'] = $this->view->translate("Last post by %s ", $lastPoster->getTitle());
        $topicCounter++;
      }
    }
    
    $breadcrumb = array();
    $breadcrumbCounter = 0;
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['forum'] = $this->view->translate("Forum");
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['category_name'] = $category->title;
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['category_id'] = $category->category_id;
    $breadcrumbCounter++;

    $final = array_merge(array_merge($subcat, $forums), $topics);
    $final = array_merge(array('category_description' => $category->description), $final);
    

    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist events.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => array_merge($breadcrumb, $final))));
  }
  
  public function subcategoryviewAction() {

    $category_id = $this->_getParam('subcat_id', null);
    if(empty($category_id))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $category = Engine_Api::_()->getItem('sesforum_category', $category_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $result = array();
    $category_description = $category->description;
    $result['category_description'] = $category->description;
    if($viewer->getIdentity()){
        $levelId = $viewer->level_id;
    } else {
        $levelId = 5;
    } 
    $subsubCatCounter = 0;
    $subsubcat = array();
    $subsubCategories = Engine_Api::_()->getItemTable('sesforum_category')->fetchAll(Engine_Api::_()->getItemTable('sesforum_category')->select()->where('subsubcat_id = ?', $category->category_id)->where("privacy LIKE ? ", '%' . $levelId. '%')->order('order ASC'));
    
    foreach($subsubCategories as $subsubCategorie) {  
      $subsubcat[$subsubCatCounter] = $subsubCategorie->toArray();
      
      $subsubcat[$subsubCatCounter]['category_name'] = $subsubCategorie->title;
      $subsubcat[$subsubCatCounter]['category_id'] = $subsubCategorie->getIdentity();
      $subsubcat[$subsubCatCounter]['type'] = 'subsubcat';
      if($subsubCategorie->cat_icon != '' && !is_null($subsubCategorie->cat_icon) && intval($subsubCategorie->cat_icon)) {
        $cat_icon = Engine_Api::_()->storage()->get($subsubCategorie->cat_icon, '');
        if($cat_icon) {
          $cat_icon = $this->getBaseUrl(true, $cat_icon->map());
        } else {
          $cat_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $subsubcat[$subsubCatCounter]["cat_icon"] = $cat_icon;
      } else {
          $subsubcat[$subsubCatCounter]["cat_icon"] = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
      }
      $subsubCatCounter++;
    }
    $result['subsubcat'] = $subsubcat;

    $sesforumTable = Engine_Api::_()->getItemTable('sesforum_forum');
    $sesforumSelect = $sesforumTable->select()->where('category_id =?', $category_id)->order('order ASC');

    $forums = $forumIds = array();
    $counterSesforum =  0;
    foreach($sesforumTable->fetchAll($sesforumSelect) as $sesforum) {

        $allForums = $sesforum->toArray();
        
        $forums['forums'][$counterSesforum] = $allForums;
        
        if($sesforum->forum_icon != '' && !is_null($sesforum->forum_icon) && intval($sesforum->forum_icon)) {
          $forum_icon = Engine_Api::_()->storage()->get($sesforum->forum_icon, '');
          if($forum_icon) {
            $forum_icon = $this->getBaseUrl(true, $forum_icon->map());
          } else {
            $forum_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
          }
          $forums['forums'][$counterSesforum]["forum_icon"] = $forum_icon;
        } else {
            $forums['forums'][$counterSesforum]["forum_icon"] = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $forumIds[] = $sesforum->forum_id;
        $counterSesforum++;
    }
    
    $topics = array();
    $topicCounter =  0;
    if(count($forumIds) > 0) {
      $topicTable = Engine_Api::_()->getItemTable('sesforum_topic');
      $topicTableName = $topicTable->info('name');
      
      $forumTable = Engine_Api::_()->getItemTable('sesforum_forum')->info('name');
      $topicSelect = $topicTable->select()
                                ->from($topicTableName,'*')
                                ->setIntegrityCheck(false)->joinLeft($forumTable, "$forumTable.forum_id = $topicTableName.forum_id",null)
                                ->where($topicTableName.".forum_id IN (?)",$forumIds);
      $topicPaginator = Zend_Paginator::factory($topicSelect);
      $topicPaginator->setItemCountPerPage(10);
      $topicPaginator->setCurrentPageNumber(1);

      foreach( $topicPaginator as $i => $topic ) {
        $alltopics = $topic->toArray();
        $topics['topics'][$topicCounter]['description'] = strip_tags($topic->description);
        unset($alltopics['description']);
        $topics['topics'][$topicCounter] = $alltopics;
        $owner = Engine_Api::_()->getItem('user', $topic->user_id);
        $topics['topics'][$topicCounter]['owner_title'] = $owner->getTitle();
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
        if(empty($owner->photo_id)) {
          $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
          $ownerimage['main'] = $defPhoto;
          $ownerimage['icon'] = $defPhoto;
          $ownerimage['normal'] = $defPhoto;
          $ownerimage['profile'] = $defPhoto;
        }
        $topics['topics'][$topicCounter]['owner_image'] = $ownerimage;
        $lastPoster = Engine_Api::_()->getItem('user', $topic->lastposter_id);
        $lastPostCount = 0;
        if( $lastPoster) {
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_images'] = $this->userImage($lastPoster->user_id,"thumb.icon");
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_id'] = $lastPoster->user_id;
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_title'] = $lastPoster->getTitle();
          $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['creation_date'] = $topic->modified_date;
          $lastPostCount++;
        }
        $topics['topics'][$topicCounter]['last_poster_username'] = $this->view->translate("Last post by %s ", $lastPoster->getTitle());
        $topicCounter++;
      }
    }
    
    $breadcrumb = array();
    $breadcrumbCounter = 0;
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['forum'] = $this->view->translate("Forum");
    if(!empty($category->subcat_id)) {
      $maincategory = Engine_Api::_()->getItem('sesforum_category', $category->subcat_id);
      $breadcrumb['breadcrumb'][$breadcrumbCounter]['category_name'] = $maincategory->title;
      $breadcrumb['breadcrumb'][$breadcrumbCounter]['category_id'] = $maincategory->category_id;
    }
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['subcate_name'] = $category->title;
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['subcate_id'] = $category->category_id;
    $breadcrumbCounter++;
    
    $final = array_merge(array_merge($result, $forums), $topics);
    
    $final = array_merge(array('category_description' => $category_description), $final);
    
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist events.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => array_merge($breadcrumb, $final))));
  }
  
  public function subscribeAction() {
  
//     if( !$this->_helper->requireSubject('sesforum_topic')->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }
    $topic_id = $this->_getParam('resource_id', null);
    
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->topic = $topic = Engine_Api::_()->getItem('sesforum_topic', $topic_id); // Engine_Api::_()->core()->getSubject('sesforum_topic');
    $this->view->sesforum = $sesforum = $topic->getParent();
//     if( !$this->_helper->requireAuth()->setAuthParams($sesforum, $viewer, 'view')->isValid() ) {
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
//     }

    $watch = $this->_getParam('watch', true);

    $topicWatchesTable = Engine_Api::_()->getDbtable('topicwatches', 'sesforum');
    $db = $topicWatchesTable->getAdapter();
    $db->beginTransaction();

    try
    {
      $isWatching = $topicWatchesTable
        ->select()
        ->from($topicWatchesTable->info('name'), 'watch')
        ->where('resource_id = ?', $sesforum->getIdentity())
        ->where('topic_id = ?', $topic->getIdentity())
        ->where('user_id = ?', $viewer->getIdentity())
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;

        if($topic->user_id != $viewer->getIdentity() && $watch == 1) {
            $owner = Engine_Api::_()->getItem('user', $topic->user_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $topic, 'sesforum_topicsubs');
        }
      if( false === $isWatching ) {
        $topicWatchesTable->insert(array(
          'resource_id' => $sesforum->getIdentity(),
          'topic_id' => $topic->getIdentity(),
          'user_id' => $viewer->getIdentity(),
          'watch' => (bool) $watch,
        ));
      } else if( $watch != $isWatching ) {
        $topicWatchesTable->update(array(
          'watch' => (bool) $watch,
        ), array(
          'resource_id = ?' => $sesforum->getIdentity(),
          'topic_id = ?' => $topic->getIdentity(),
          'user_id = ?' => $viewer->getIdentity(),
        ));
      }

      $db->commit();
      
      if($watch) {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Successfully Subscribed.'), 'subscribe_id' => $watch, 'watch' => 0, 'unsubscribe' => $this->view->translate('Unsubscribe'))));
      } else {
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Successfully Unsubscribed.'), 'subscribe_id' => $watch,'watch' => 1, 'subscribe' => $this->view->translate('Subscribe'))));
      }
    }

    catch( Exception $e )
    {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }


//     $viewer = Engine_Api::_()->user()->getViewer();
//     $viewer_id = $viewer->getIdentity();
//     if (empty($viewer_id))
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
// 
//     $resource_id = $this->_getParam('resource_id');
//     $resource_type = $this->_getParam('resource_type');
//     $subscribe_id = $this->_getParam('subscribe_id');
// 
//     $item = Engine_Api::_()->getItem($resource_type, $resource_id);
// 
//     $subscribeTable = Engine_Api::_()->getDbTable('subscribes', 'sesforum');
//     $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
//     $activityStrameTable = Engine_Api::_()->getDbtable('stream', 'activity');
// 
//     if (empty($subscribe_id)) {
//       $isSubscribe = $subscribeTable->isSubscribe(array('resource_id' => $resource_id));
//       if (empty($isSubscribe)) {
//         $db = $subscribeTable->getAdapter();
//         $db->beginTransaction();
//         try {
//           $row = $subscribeTable->createRow();
//           $row->poster_id = $viewer_id;
//           $row->resource_id = $resource_id;
//           $row->save();
//           $this->view->subscribe_id = $row->subscribe_id;
// 
//           $owner = $item->getOwner();
//           if($owner->getIdentity() != $viewer_id) {
//             if ($resource_type == 'sesforum_forum') {
//               $owner = $item->getOwner();
// 
//               //Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'subscribed', array('label' => $item->getShortType()));
// 
// //               $action = $activityTable->addActivity($viewer, $item, 'sesmusic_subscribealbum');
// //               if ($action)
// //                 $activityTable->attachActivity($action, $item);
//             }
//           }
// 
//           $db->commit();
//           Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Successfully Subscribed.'), 'subscribe_id' => $row->subscribe_id, 'unsubscribe' => $this->view->translate('Unsubscribe'))));
//         } catch (Exception $e) {
//           $db->rollBack();
//           //throw $e;
//           Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
//         }
//       } else {
//         $this->view->subscribe_id = $isSubscribe;
//       }
//     } else {
//         $subsitem = Engine_Api::_()->getItem('sesforum_subscribe', $subscribe_id);
//         //Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => "sesmusic_subscribe_musicalbum", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
//        // $action = $activityTable->fetchRow(array('type =?' => "sesmusic_subscribealbum", "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
// //       if (!empty($action)) {
// //         $action->deleteItem();
// //         $action->delete();
// //       }
//       $subsitem->delete();
//       $this->view->subscribe_id = 0;
//       Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('success_message'=>$this->view->translate('Successfully Unsubscribed.'), 'subscribe' => $this->view->translate("Subscribe"))));
//     }
  }
  
  public function subsubcategoryviewAction() {

    $category_id = $this->_getParam('subsubcat_id', null);
    if(empty($category_id))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $category = Engine_Api::_()->getItem('sesforum_category', $category_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $result = array();
    $result['category_description'] = $category->description;
    $category_description = $category->description;
    $sesforumTable = Engine_Api::_()->getItemTable('sesforum_forum');
    $sesforumSelect = $sesforumTable->select()->where('category_id =?', $category_id)->order('order ASC');

    $forums = array();
    $counterSesforum =  0;
    $forums['forums'][$counterSesforum]['heading'] = $this->view->translate("Forums");
    foreach($sesforumTable->fetchAll($sesforumSelect) as $sesforum) {
        $allForums = $sesforum->toArray();
        $forums['forums'][$counterSesforum] = $allForums;
        if($sesforum->forum_icon != '' && !is_null($sesforum->forum_icon) && intval($sesforum->forum_icon)) {
          $forum_icon = Engine_Api::_()->storage()->get($sesforum->forum_icon, '');
          if($forum_icon) {
            $forum_icon = $this->getBaseUrl(true, $forum_icon->map());
          } else {
            $forum_icon = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
          }
          $forums['forums'][$counterSesforum]["forum_icon"] = $forum_icon;
        } else {
            $forums['forums'][$counterSesforum]["forum_icon"] = $this->getBaseUrl(true, 'application/modules/Sesforum/externals/images/topic-icon.png');
        }
        $counterSesforum++;
    }
    
    $topicTable = Engine_Api::_()->getItemTable('sesforum_topic');
    $topicTableName = $topicTable->info('name');
    
    $forumTable = Engine_Api::_()->getItemTable('sesforum_forum')->info('name');
    $topicSelect = $topicTable->select()->from($topicTableName,'*')
        ->setIntegrityCheck(false)->joinLeft($forumTable, "$forumTable.forum_id = $topicTableName.forum_id",null)
        ->where($forumTable.".category_id = ?",$category_id);
    $topicPaginator = Zend_Paginator::factory($topicSelect);
    $topicPaginator->setItemCountPerPage(10);
    $topicPaginator->setCurrentPageNumber(1);

    $topics = array();
    $topicCounter =  0;
    
    foreach( $topicPaginator as $i => $topic ) {
      $alltopics = $topic->toArray();
      $topics['topics'][$topicCounter]['description'] = strip_tags($topic->description);
      unset($alltopics['description']);
      $topics['topics'][$topicCounter] = $alltopics;
      $owner = Engine_Api::_()->getItem('user', $topic->user_id);
      $topics['topics'][$topicCounter]['owner_title'] = $owner->getTitle();

      $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
      if(empty($owner->photo_id)) {
        $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
        $ownerimage['main'] = $defPhoto;
        $ownerimage['icon'] = $defPhoto;
        $ownerimage['normal'] = $defPhoto;
        $ownerimage['profile'] = $defPhoto;
      }
      $topics['topics'][$topicCounter]['owner_image'] = $ownerimage;
      $lastPoster = Engine_Api::_()->getItem('user', $topic->lastposter_id);
      $lastPostCount = 0;
      if( $lastPoster) {
        $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_images'] = $this->userImage($lastPoster->user_id,"thumb.icon");
        $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_id'] = $lastPoster->user_id;
        $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['user_title'] = $lastPoster->getTitle();
        $topics['topics'][$topicCounter]['last_post'][$lastPostCount]['creation_date'] = $topic->modified_date;
        $lastPostCount++;
      }
      $topics['topics'][$topicCounter]['last_poster_username'] = $this->view->translate("Last post by %s ", $lastPoster->getTitle());
      $topicCounter++;
    }
    
    $breadcrumb = array();
    $breadcrumbCounter = 0;
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['forum'] = $this->view->translate("Forum");
    
    if(!empty($category->subsubcat_id)) {
      $subcategory = Engine_Api::_()->getItem('sesforum_category', $category->subsubcat_id);
      $breadcrumb['breadcrumb'][$breadcrumbCounter]['sub_name'] = $subcategory->title;
      $breadcrumb['breadcrumb'][$breadcrumbCounter]['subcat_id'] = $subcategory->category_id;
      
      if(!empty($subcategory->subcat_id)) {
        $maincategory = Engine_Api::_()->getItem('sesforum_category', $subcategory->subcat_id);
        $breadcrumb['breadcrumb'][$breadcrumbCounter]['category_name'] = $maincategory->title;
        $breadcrumb['breadcrumb'][$breadcrumbCounter]['category_id'] = $maincategory->category_id;
      }
    }
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['subsubcate_name'] = $category->title;
    $breadcrumb['breadcrumb'][$breadcrumbCounter]['subsubcate_id'] = $category->category_id;
    $breadcrumbCounter++;
    
    $final = array_merge($topics, $forums);
    $final = array_merge(array('category_description' => $category_description), $final);
    
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist events.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => array_merge($breadcrumb, $final))));
  }
  
  public function searchAction() {

    $search = $this->_getParam('search', false);
    $search_type = $this->_getParam('search_type', 'topics');  
    

    if(empty($search))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();

     // Get params
    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }
    
    if($search_type == 'topics') {
    
      // Make paginator
      $table = Engine_Api::_()->getItemTable('sesforum_topic');
      $select = $table->select()
        ->order('sticky DESC')
        ->order($order . ' DESC');

      if ($this->_getParam('search', false)) {
        $select->where('title LIKE ? OR description LIKE ?', '%'.$this->_getParam('search').'%');
      }

      $paginator = Zend_Paginator::factory($select);

      $page = (int)  $this->_getParam('page', 1);

      $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_forum_pagelength'));
      $paginator->setCurrentPageNumber($page);

      $result = array();
      $counterLoop = 0;

      foreach($paginator as $topics) {

        $topic = $topics->toArray();
        $description = strip_tags($topics['description']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($topic['description']);
        //$topic["comment_count"] = Engine_Api::_()->sesadvancedcomment()->commentCount($topics,'subject');
        $topic['owner_title'] = Engine_Api::_()->getItem('user',$topics->user_id)->getTitle();
        $topic['description'] = $description;
        
        
        $owner = Engine_Api::_()->getItem('user',$topics->user_id);
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
        if(empty($owner->photo_id)) {
          $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
          $ownerimage['main'] = $defPhoto;
          $ownerimage['icon'] = $defPhoto;
          $ownerimage['normal'] = $defPhoto;
          $ownerimage['profile'] = $defPhoto;
        }
        $topic['owner_image'] = $ownerimage; //$this->userImage($topics->user_id,"thumb.icon");
        $topic['resource_type'] = $topics->getType();
        
        $topic['show_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.rating', 1) ? true : false;

        $last_post = $topics->getLastCreatedPost();
        if( $last_post ) {
          $last_user = Engine_Api::_()->getItem('user', $last_post->user_id); //$this->user($last_post->user_id);
        } else {
          $last_user = Engine_Api::_()->getItem('user', $topics->user_id); //$this->user($topics->user_id);
        }
        $lastPostCount = 0;
        if( $last_post) {
          $topic['last_post'][$lastPostCount]['user_images'] = $this->userImage($last_user->user_id,"thumb.icon");
          $topic['last_post'][$lastPostCount]['user_id'] = $last_user->getIdentity();
          $topic['last_post'][$lastPostCount]['user_title'] = $last_user->getTitle();
          $topic['last_post'][$lastPostCount]['creation_date'] = $topics->modified_date;
          $lastPostCount++;
        }
        $result['topics'][$counterLoop] = $topic;
        $images = Engine_Api::_()->sesapi()->getPhotoUrls($topics,'','');
        if(!count($images))
          $images['main'] = $this->getBaseUrl(true,$topics->getPhotoUrl());
        $result['topics'][$counterLoop]['images'] = $images;
        $counterLoop++;
      }

//       $sesforum = Engine_Api::_()->getItem('sesforum_forum', $topics->forum_id);
//       $canPost = Engine_Api::_()->authorization()->isAllowed('sesforum_forum', $viewer, 'topic_create');
//       $list = $sesforum->getModeratorList();
//       $moderators = $list->getAllChildren();
// 
//       $moderator = $this->view->fluentList($moderators);
//       $moderator_count = 0;
//       $result['moderators'][$moderator_count]['label'] = $this->view->translate("Moderators");
//       $result['moderators'][$moderator_count]['moderators'] = $moderator;
//       if($canPost) {
//         $result['moderators'][$moderator_count]['topic_create'] = $this->view->translate("Post New Topic");
//       }
//       $moderator_count++;
    
    } else {
    
      $postTable = Engine_Api::_()->getDbtable('posts', 'sesforum');
      $postTableName =  $postTable->info('name');
      
      $postsSelect = $postTable->select()->setIntegrityCheck(false)->from($postTableName);
      if(!empty($search) && isset($search)) {
          $postsSelect->where($postTableName.".body LIKE ? ", '%' . $search . '%');
      }
      $paginator = Zend_Paginator::factory($postsSelect);
      $page = (int)  $this->_getParam('page', 1);
      $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_forum_pagelength'));
      $paginator->setCurrentPageNumber($page);
      
      $result = array();
      $counterLoop = 0;
      
      foreach($paginator as $posts) {
        $post = $posts->toArray();
        $topic = Engine_Api::_()->getItem('sesforum_topic', $posts->topic_id);
        $description = strip_tags($posts['body']);
        $description = preg_replace('/\s+/', ' ', $description);
        unset($post['body']);
        $post['description'] = $description;
        $post['resource_type'] = $posts->getType();
        $post['topic_title'] = $this->view->translate('in the topic %s', $topic->title);
        $thanks = Engine_Api::_()->getDbTable('thanks', 'sesforum')->getAllUserThanks($posts->user_id);
        $post['thanks_count'] = $thanks;
        $result['topics'][$counterLoop] = $post;
        $counterLoop++;
      }
    }
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist topics.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }

  public function forumviewAction() {

    $forum_id = $this->_getParam('forum_id', null);
    //$query = $this->_getParam('query', null);
    if(empty($forum_id))
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    $viewer = Engine_Api::_()->user()->getViewer();

    $sesforum = Engine_Api::_()->getItem('sesforum_forum', $forum_id);

    if(!$this->_helper->requireAuth->setAuthParams($sesforum, null, 'view')->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

     // Get params
    switch($this->_getParam('sort', 'recent')) {
      case 'popular':
        $order = 'view_count';
        break;
      case 'recent':
      default:
        $order = 'modified_date';
        break;
    }

    // Make paginator
    $table = Engine_Api::_()->getItemTable('sesforum_topic');
    $select = $table->select()
      ->where('forum_id = ?', $sesforum->getIdentity())
      ->order('sticky DESC')
      ->order($order . ' DESC');

    if ($this->_getParam('search', false)) {
      $select->where('title LIKE ? OR description LIKE ?', '%'.$this->_getParam('search').'%');
    }

    $paginator = Zend_Paginator::factory($select);

    $page = (int)  $this->_getParam('page', 1);

    $paginator->setItemCountPerPage(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum_forum_pagelength'));
    $paginator->setCurrentPageNumber($page);

    $result = array();
    $counterLoop = 0;

    foreach($paginator as $topics) {

      $topic = $topics->toArray();
      $description = strip_tags($topics['description']);
      $description = preg_replace('/\s+/', ' ', $description);
      unset($topic['description']);
      //$topic["comment_count"] = Engine_Api::_()->sesadvancedcomment()->commentCount($topics,'subject');
      
      $owner = Engine_Api::_()->getItem('user',$topics->user_id);
      $topic['owner_title'] = $owner->getTitle();
      $topic['description'] = $description;
      $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owner, "", "");
      if(empty($owner->photo_id)) {
        $defPhoto = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
        $ownerimage['main'] = $defPhoto;
        $ownerimage['icon'] = $defPhoto;
        $ownerimage['normal'] = $defPhoto;
        $ownerimage['profile'] = $defPhoto;
      }
      $topic['owner_image'] = $ownerimage; //$this->userImage($topics->user_id,"thumb.icon");
      $topic['resource_type'] = $topics->getType();
      
      $topic['show_rating'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesforum.rating', 1) ? true : false;

      $last_post = $topics->getLastCreatedPost();
      if( $last_post ) {
        $last_user = Engine_Api::_()->getItem('user', $last_post->user_id); //$this->user($last_post->user_id);
      } else {
        $last_user = Engine_Api::_()->getItem('user', $topics->user_id); //$this->user($topics->user_id);
      }
      $lastPostCount = 0;
      if( $last_post) {
        $topic['last_post'][$lastPostCount]['user_images'] = $this->userImage($last_user->user_id,"thumb.icon");
        $topic['last_post'][$lastPostCount]['user_id'] = $last_user->getIdentity();
        $topic['last_post'][$lastPostCount]['user_title'] = $last_user->getTitle();
        $topic['last_post'][$lastPostCount]['creation_date'] = $topics->modified_date;
        $lastPostCount++;
      }
      $result['topics'][$counterLoop] = $topic;
      $images = Engine_Api::_()->sesapi()->getPhotoUrls($topics,'','');
      if(!count($images))
        $images['main'] = $this->getBaseUrl(true,$topics->getPhotoUrl());
      $result['topics'][$counterLoop]['images'] = $images;
      $counterLoop++;
    }


    $canPost = Engine_Api::_()->authorization()->isAllowed('sesforum_forum', $viewer, 'topic_create');
    $list = $sesforum->getModeratorList();
    $moderators = $list->getAllChildren();

    $moderator = $this->view->fluentList($moderators);
    $moderator_count = 0;
    $result['moderators'][$moderator_count]['label'] = $this->view->translate("Moderators");
    $result['moderators'][$moderator_count]['moderators'] = $moderator;
    if($canPost) {
      $result['moderators'][$moderator_count]['topic_create'] = $this->view->translate("Post New Topic");
    }
    $result['moderators'][$moderator_count]['forum_title'] = $sesforum->title;
    $moderator_count++;
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    if($result <= 0)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('Does not exist topics.'), 'result' => array()));
    else
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),$extraParams));
  }

  public function uploadPhotoAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->_helper->layout->disableLayout();

    if( !Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create') ) {
      return false;
    }

    if( !$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid() ) return;

    if( !$this->_helper->requireUser()->checkRequire() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    if( !isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }

    $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();

      $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
        'owner_type' => 'user',
        'owner_id' => $viewer->getIdentity()
      ));
      $photo->save();

      $photo->setPhoto($_FILES['userfile']);

      $this->view->status = true;
      $this->view->name = $_FILES['userfile']['name'];
      $this->view->photo_id = $photo->photo_id;
      $this->view->photo_url = $photo->getPhotoUrl();

      $table = Engine_Api::_()->getDbtable('albums', 'album');
      $album = $table->getSpecialAlbum($viewer, 'sesforum');

      $photo->album_id = $album->album_id;
      $photo->save();

      if( !$album->photo_id )
      {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }

      $auth      = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($photo, 'everyone', 'view',    true);
      $auth->setAllowed($photo, 'everyone', 'comment', true);
      $auth->setAllowed($album, 'everyone', 'view',    true);
      $auth->setAllowed($album, 'everyone', 'comment', true);


      $db->commit();

    } catch( Album_Model_Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = $this->view->translate($e->getMessage());
      throw $e;
      return;

    } catch( Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
      throw $e;
      return;
    }
  }
}
