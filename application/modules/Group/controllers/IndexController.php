<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10265 2014-06-06 22:11:31Z lucas $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_IndexController extends Core_Controller_Action_Standard
{
    public function init()
    {

        if( !$this->_helper->requireAuth()->setAuthParams('group', null, 'view')->isValid() )
            return;

        $id = $this->_getParam('group_id', $this->_getParam('id', null));
        if( $id ) {
            $group = Engine_Api::_()->getItem('group', $id);
            if( $group ) {
                Engine_Api::_()->core()->setSubject($group);
            }
        }
    }

    public function browseAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();

        // Check create
        $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('group', null, 'create');

        // Form
        $this->view->formFilter = $formFilter = new Group_Form_Filter_Browse();
        $defaultValues = $formFilter->getValues();

        if( !$viewer || !$viewer->getIdentity() ) {
            $formFilter->removeElement('view');
        }

        // Populate options
        $categories = Engine_Api::_()->getDbtable('categories', 'group')->getCategoriesAssoc();
        $formFilter->category_id->addMultiOptions($categories);

        // Populate form data
        if( $formFilter->isValid($this->_getAllParams()) ) {
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

        // check to see if request is for specific user's listings
        $user_id = $this->_getParam('user');
        if( $user_id ) {
            $values['user_id'] = $user_id;
        }


        // Make paginator
        $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('group')
            ->getGroupPaginator($values);

        $paginator->setCurrentPageNumber($this->_getParam('page'));

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;
    }

    public function createAction()
    {
        if( !$this->_helper->requireUser->isValid() )
            return;
        if( !$this->_helper->requireAuth()->setAuthParams('group', null, 'create')->isValid() )
            return;

        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $paginator = Engine_Api::_()->getItemTable('group')->getGroupPaginator($values);

        $this->view->quota = $quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'group', 'max');
        $this->view->current_count = $paginator->getTotalItemCount();


        // Create form
        $this->view->form = $form = new Group_Form_Create();

        // Populate with categories
        $categories = Engine_Api::_()->getDbtable('categories', 'group')->getCategoriesAssoc();
        asort($categories, SORT_LOCALE_STRING);
        $categoryOptions = array('0' => '');
        foreach( $categories as $k => $v ) {
            $categoryOptions[$k] = $v;
        }
        $form->category_id->setMultiOptions($categoryOptions);

        if( count($form->category_id->getMultiOptions()) <= 1 ) {
            $form->removeElement('category_id');
        }

        // Check method/data validitiy
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }
        $itemFlood = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('group', $this->view->viewer()->level_id, 'flood');
        if(!empty($itemFlood[0])){
            //get last activity
            $tableFlood = Engine_Api::_()->getDbTable("groups",'group');
            $select = $tableFlood->select()->where("user_id = ?",$this->view->viewer()->getIdentity())->order("creation_date DESC");
            if($itemFlood[1] == "minute"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
            }else if($itemFlood[1] == "day"){
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
            }else{
                $select->where("creation_date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            }
            $floodItem = $tableFlood->fetchAll($select);
            if(count($floodItem) && $itemFlood[0] <= count($floodItem)){
                $message = Engine_Api::_()->core()->floodCheckMessage($itemFlood,$this->view);
                $form->addError($message);
                return;
            }
        }
        // Process
        $values = $form->getValues();
        $values['user_id'] = $viewer->getIdentity();
        if (isset($values['networks'])) {
            $network_privacy = 'network_'. implode(',network_', $values['networks']);
            $values['networks'] = implode(',', $values['networks']);
        }
        if( empty($values['auth_view']) ) {
            $values['auth_view'] = 'everyone';
        }

        if( empty($values['auth_comment']) ) {
            $values['auth_comment'] = 'everyone';
        }

        $values['view_privacy'] =  $values['auth_view'];

        $db = Engine_Api::_()->getDbtable('groups', 'group')->getAdapter();
        $db->beginTransaction();

        try {
            // Create group
            $table = Engine_Api::_()->getDbtable('groups', 'group');
            $group = $table->createRow();
            $group->setFromArray($values);
            $group->save();

            // Add owner as member
            $group->membership()->addMember($viewer)
                ->setUserApproved($viewer)
                ->setResourceApproved($viewer);

            // Set photo
            if( !empty($values['photo']) ) {
                $group->setPhoto($form->photo);
            }

            // Process privacy
            $auth = Engine_Api::_()->authorization()->context;

            $roles = array('officer', 'member', 'registered', 'everyone');

            $viewMax = array_search($values['auth_view'], $roles);
            $commentMax = array_search($values['auth_comment'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);
            $eventMax = array_search($values['auth_event'], $roles);
            $blogMax = array_search($values['auth_blog'], $roles);
            $pollMax = array_search($values['auth_poll'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);
            $inviteMax = array_search($values['auth_invite'], $roles);

            $officerList = $group->getOfficerList();

            foreach( $roles as $i => $role ) {
                if( $role === 'officer' ) {
                    $role = $officerList;
                }
                $auth->setAllowed($group, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($group, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($group, $role, 'photo', ($i <= $photoMax));
                $auth->setAllowed($group, $role, 'event', ($i <= $eventMax));
                $auth->setAllowed($group, $role, 'blog', ($i <= $blogMax));
                $auth->setAllowed($group, $role, 'poll', ($i <= $pollMax));
                $auth->setAllowed($group, $role, 'video', ($i <= $videoMax));
                $auth->setAllowed($group, $role, 'invite', ($i <= $inviteMax));
                // Create some auth stuff for all officers
                $auth->setAllowed($group, $role, 'topic_create', ($i <= $commentMax));
                $auth->setAllowed($group, $role, 'topic_edit', ($i <= $commentMax));
                $auth->setAllowed($group, $role, 'topic_delete', ($i <= $commentMax));
                $auth->setAllowed($group, $role, 'post_create', ($i <= $commentMax));
                $auth->setAllowed($group, $role, 'post_edit', ($i <= $commentMax));
                $auth->setAllowed($group, $role, 'post_delete', ($i <= $commentMax));
            }

            // Add auth for invited users
            $auth->setAllowed($group, 'member_requested', 'view', 1);

            // Add action
            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
            $action = $activityApi->addActivity($viewer, $group, 'group_create', '', array('privacy' => isset($values['networks'])? $network_privacy : null));
            if( $action ) {
                $activityApi->attachActivity($action, $group);
            }

            // Commit
            $db->commit();

            // Redirect
            return $this->_helper->redirector->gotoRoute(array('id' => $group->getIdentity()), 'group_profile', true);
        } catch( Exception $e ) {
            return $this->exceptionWrapper($e, $form, $db);
        }
    }

    public function listAction()
    {

    }

    public function manageAction()
    {
        // Render
        $this->_helper->content
            //->setNoRender()
            ->setEnabled()
        ;

        // Form
        $this->view->formFilter = $formFilter = new Group_Form_Filter_Manage();
        $this->view->formValues = $defaultValues = $formFilter->getValues();

        // Populate form data
        if( $formFilter->isValid($this->_getAllParams()) ) {
            $this->view->formValues = $values = $formFilter->getValues();
        } else {
            $formFilter->populate($defaultValues);
            $this->view->formValues = $values = array();
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $membership = Engine_Api::_()->getDbtable('membership', 'group');
        $select = $membership->getMembershipsOfSelect($viewer);
        $select->where('group_id IS NOT NULL');

        $table = Engine_Api::_()->getItemTable('group');
        $tName = $table->info('name');
        if( $values['view'] == 2 ) {
            $select->where("`{$tName}`.`user_id` = ?", $viewer->getIdentity());
        }
        if( !empty($values['text']) ) {
            $select->where(
                $table->getAdapter()->quoteInto("`{$tName}`.`title` LIKE ?", '%' . $values['text'] . '%') . ' OR ' .
                $table->getAdapter()->quoteInto("`{$tName}`.`description` LIKE ?", '%' . $values['text'] . '%')
            );
        }

        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        $this->view->text = $values['text'];
        $this->view->view = $values['view'];
        $paginator->setCurrentPageNumber($this->_getParam('page'));

        // Check create
        $this->view->canCreate = Engine_Api::_()->authorization()->isAllowed('group', null, 'create');
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
            $album = $table->getSpecialAlbum($viewer, 'group');

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