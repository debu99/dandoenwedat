<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SettingsController.php 10003 2013-03-26 22:48:26Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_SettingsController extends Core_Controller_Action_User
{
    protected $_user;

    public function init()
    {
        // Can specifiy custom id
        $id = $this->_getParam('id', null);
        $subject = null;

        if( null === $id )
        {
            if(!Engine_Api::_()->core()->hasSubject($subject)) {
                $subject = Engine_Api::_()->user()->getViewer();
                Engine_Api::_()->core()->setSubject($subject);
            }
        }
        else
        {
            $subject = Engine_Api::_()->getItem('user', $id);
            Engine_Api::_()->core()->setSubject($subject);
        }

        // Set up require's
        $this->_helper->requireUser();
        $this->_helper->requireSubject();
        $this->_helper->requireAuth()->setAuthParams(
            $subject,
            null,
            'edit'
        );

        // Set up navigation
        // $this->view->navigation = $navigation = Engine_Api::_()
        //   ->getApi('menus', 'core')
        //   ->getNavigation('user_settings', ( $id ? array('params' => array('id'=>$id)) : array()));

        $contextSwitch = $this->_helper->contextSwitch;
        $contextSwitch
            //->addActionContext('reject', 'json')
            ->initContext();
        if(empty($_SESSION['requirepassword'] )) {
            // Render
            $this->_helper->content
                // ->setNoRender()
                ->setEnabled();
        }
    }

    public function generalAction()
    {
        // Config vars
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $userSettings = Engine_Api::_()->getDbtable('settings', 'user');
        $user = Engine_Api::_()->core()->getSubject();
        $this->view->form = $form = new User_Form_Settings_General(array(
            'item' => $user
        ));

        // Set up profile type options
        /*
        $aliasedFields = $user->fields()->getFieldsObjectsByAlias();
        if( isset($aliasedFields['profile_type']) )
        {
          $options = $aliasedFields['profile_type']->getElementParams($user);
          unset($options['options']['order']);
          $form->accountType->setOptions($options['options']);
        }
        else
        { */
        $form->removeElement('accountType');
        /* } */

        // Removed disabled features
        if( $form->getElement('username') && (!Engine_Api::_()->authorization()->isAllowed('user', $user, 'username') ||
                Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) <= 0) ) {
            $form->removeElement('username');
        }

        //Set names of those elements that need to be removed and are also dependent on POST
        $removeElements = array();
        // Facebook
        if( 'none' != $settings->getSetting('core.facebook.enable', 'none') ) {
            $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
            $facebook = $facebookTable->getApi();
            if( $facebook && $facebook->getUser() ) {
                $removeElements['facebook'] = 'facebook';
                $form->getElement('facebook_id')->setAttrib('checked', true);
            } else {
                $removeElements['facebook_id'] = 'facebook_id';
            }
        } else {
            // these should already be removed inside the form, but lets do it again.
            @$form->removeElement('facebook');
            @$form->removeElement('facebook_id');
        }

        if ( in_array('facebook_id', $removeElements) && $this->_getParam('already_integrated_fb_account') ) {
            $form->facebook->addError('Facebook account you\'re trying to connect is already connected to another account.');
        }
        // Twitter
        if( 'none' != $settings->getSetting('core.twitter.enable', 'none') ) {
            $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
            $twitter = $twitterTable->getApi();
            if( $twitter && $twitterTable->isConnected() ) {
                $form->removeElement('twitter');
                $form->getElement('twitter_id')->setAttrib('checked', true);
            } else {
                $form->removeElement('twitter_id');
            }
        } else {
            // these should already be removed inside the form, but lets do it again.
            @$form->removeElement('twitter');
            @$form->removeElement('twitter_id');
        }


        // Check if post and populate
        if( !$this->getRequest()->isPost() ) {
            foreach($removeElements as $elementName) {
                $form->removeElement($elementName);
            }
            $form->populate($user->toArray());

            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
            return;
        }

        // Check if valid
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        // -- Process --

        $values = $form->getValues();

        // Check email against banned list if necessary
        if( ($emailEl = $form->getElement('email')) &&
            isset($values['email']) &&
            $values['email'] != $user->email ) {
            $bannedEmailsTable = Engine_Api::_()->getDbtable('BannedEmails', 'core');
            if( $bannedEmailsTable->isEmailBanned($values['email']) ) {
                return $emailEl->addError('This email address is not available, please use another one.');
            }
        }

        // Check username against banned list if necessary
        if( ($usernameEl = $form->getElement('username')) &&
            isset($values['username']) &&
            $values['username'] != $user->username ) {
            $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
            if( $bannedUsernamesTable->isUsernameBanned($values['username']) ) {
                return $usernameEl->addError('This profile address is not available, please use another one.');
            }
        }

        // Set values for user object
        $user->setFromArray($values);

        // If username is changed
        $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
        $user->setDisplayName($aliasValues);

        $user->save();

        $regionValues = $form->getValue('region');
        $regionValueTable = Engine_Api::_()->getDbtable('regionvalues', 'user');
        $db = $regionValueTable->getAdapter();
        $db->beginTransaction();
        $regionValueTable->delete(array('user_id = ?' => $user->getIdentity()));
        foreach ($regionValues as $value){
            $row = $regionValueTable->createRow();
            $row->user_id = $user->getIdentity();
            $row->region_id = $value;
            $row->save();
        }
        $db->commit();

        // Update account type
        /*
        $accountType = $form->getValue('accountType');
        if( isset($aliasedFields['profile_type']) )
        {
          $valueRow = $aliasedFields['profile_type']->getValue($user);
          if( null === $valueRow ) {
            $valueRow = Engine_Api::_()->fields()->getTable('user', 'values')->createRow();
            $valueRow->field_id = $aliasedFields['profile_type']->field_id;
            $valueRow->item_id = $user->getIdentity();
          }
          $valueRow->value = $accountType;
          $valueRow->save();
        }
         *
         */

        // Update facebook settings
        if( isset($facebook) && $form->getElement('facebook_id') ) {
            if( $facebook->getUser() ) {
                if( empty($values['facebook_id']) ) {
                    // Remove integration
                    $facebookTable->delete(array(
                        'user_id = ?' => $user->getIdentity(),
                    ));
                    $facebook->clearAllPersistentData();
                    unset($removeElements['facebook']);
                    $removeElements['facebook_id'] = 'facebook_id';
                }
            }
        }

        // Update twitter settings
        if( isset($twitter) && $form->getElement('twitter_id') ) {
            if( $twitterTable->isConnected() ) {
                if( empty($values['twitter_id']) ) {
                    // Remove integration
                    $twitterTable->delete(array(
                        'user_id = ?' => $user->getIdentity(),
                    ));
                    unset($_SESSION['twitter_token2']);
                    unset($_SESSION['twitter_secret2']);
                    unset($_SESSION['twitter_token']);
                    unset($_SESSION['twitter_secret']);
                }
            }
        }

        foreach($removeElements as $elementName) {
            $form->removeElement($elementName);
        }
        // Send success message
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Settings saved.');
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.'));
    }

    public function privacyAction()
    {

        $user = Engine_Api::_()->core()->getSubject();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $auth = Engine_Api::_()->authorization()->context;

        $this->view->form = $form = new User_Form_Settings_Privacy(array(
            'item' => $user,
        ));

        $form->removeElement('publishTypes');
        // Init blocked
        $this->view->blockedUsers = array();

        if( Engine_Api::_()->authorization()->isAllowed('user', $user, 'block') ) {
            foreach ($user->getBlockedUsers() as $blocked_user_id) {
                $this->view->blockedUsers[] = Engine_Api::_()->user()->getUser($blocked_user_id);
            }
        } else {
            $form->removeElement('blockList');
        }

        if( !Engine_Api::_()->getDbtable('permissions', 'authorization')->isAllowed($user, $user, 'search') ) {
            $form->removeElement('search');
        }


        // Hides options from the form if there are less then one option.
        if( count($form->privacy->options) <= 1 ) {
            $form->removeElement('privacy');
        }
        if( count($form->lastLoginDate->options) <= 1 ) {
            $form->removeElement('lastLoginDate');
        }
        if( count($form->lastUpdateDate->options) <= 1 ) {
            $form->removeElement('lastUpdateDate');
        }
        if( count($form->inviteeName->options) <= 1 ) {
            $form->removeElement('inviteeName');
        }
        if( count($form->profileType->options) <= 1 ) {
            $form->removeElement('profileType');
        }
        if( count($form->memberLevel->options) <= 1 ) {
            $form->removeElement('memberLevel');
        }
        if( count($form->profileViews->options) <= 1 ) {
            $form->removeElement('profileViews');
        }
        if( count($form->joinedDate->options) <= 1 ) {
            $form->removeElement('joinedDate');
        }
        if( count($form->friendsCount->options) <= 1 ) {
            $form->removeElement('friendsCount');
        }
        if( count($form->comment->options) <= 1 ) {
            $form->removeElement('comment');
        }

        // Populate form
        $form->populate($user->toArray());

        // Set up activity options
        $defaultPublishTypes = array('post', 'signup', 'status');
        if( $form->getElement('publishTypes') ) {
            $actionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getEnabledActionTypesAssoc();
            foreach( $defaultPublishTypes as $key ) {
                unset($actionTypes[$key]);
            }

            foreach( array_keys($actionTypes) as $key ) {
                if( substr($key, 0, 5) == 'post_' ) {
                    $defaultPublishTypes[] = $key;
                    unset($actionTypes[$key]);
                }
            }

            $form->publishTypes->setMultiOptions($actionTypes);
            $actionTypesEnabled = Engine_Api::_()->getDbtable('actionSettings', 'activity')->getEnabledActions($user);
            $form->publishTypes->setValue($actionTypesEnabled);
        }

        // Check if post and populate
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        $form->save();
        $values = $form->getValues();
        $values['view_privacy'] =  $values['privacy'];
        $user->setFromArray($values)
            ->save();

        // Update 
        if( $form->getElement('publishTypes') ) {
            $publishTypes = array_merge($form->publishTypes->getValue(), $defaultPublishTypes);
            Engine_Api::_()->getDbtable('actionSettings', 'activity')->setEnabledActions($user, (array) $publishTypes);
        }

        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
    }
    public function requirePasswordAction(){
        return $this->_forward('password', null, null, array('format' => 'html','require_password'=>1));
    }
    public function passwordAction()
    {
        $user = Engine_Api::_()->core()->getSubject();
        $this->view->form = $form = new User_Form_Settings_Password();
        $form->populate($user->toArray());
        if( !$this->getRequest()->isPost() ){
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }
        // Check conf
        if( $form->getValue('passwordConfirm') !== $form->getValue('password') ) {
            $form->getElement('passwordConfirm')->addError(Zend_Registry::get('Zend_Translate')->_('Passwords did not match'));
            return;
        }
        // Process form
        $userTable = Engine_Api::_()->getItemTable('user');
        $db = $userTable->getAdapter();
        // Check old password
        $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');
        $valid = false;
        $matchOldPassword = false;
        if(strlen($user->password) > 32){
            $select = $userTable->select()
                ->from($userTable, 'password')
                ->where('user_id = ?', $user->getIdentity())
                ->limit(1)
            ;
            $result = $select
                ->query()
                ->fetchColumn()
            ;
            if($result) {
                $valid = password_verify($form->getValue('oldPassword'),$result);
                if($valid){
                    if(password_verify($form->getValue('password'),$result)) {
                        $matchOldPassword = true;
                    }
                }
            }
        }else{
            $password = new Zend_Db_Expr(sprintf('MD5(CONCAT(%s, %s, salt))', $db->quote($salt), $db->quote($form->getValue('oldPassword'))));
            $select = $userTable->select()
                ->from($userTable, new Zend_Db_Expr('TRUE'))
                ->where('user_id = ?', $user->getIdentity())
                ->where('password = ?', $password)
                ->limit(1)
            ;
            $valid = $select
                ->query()
                ->fetchColumn()
            ;
        }
        if($matchOldPassword){
            $form->getElement('password')->addError(Zend_Registry::get('Zend_Translate')->_('It seems that you have used an old password. Choose a new password, to protect your account.'));
            return;
        }else if( !$valid ) {
            $form->getElement('oldPassword')->addError(Zend_Registry::get('Zend_Translate')->_('Old password did not match'));
            return;
        }
        // Save
        $db->beginTransaction();
        try {
            $user->setFromArray($form->getValues());
            $user->last_password_reset = date('Y-m-d H:i:s');
            $user->save();
            $db->commit();
        } catch( Exception $e ) {
            $db->rollBack();
            throw $e;
        }
        if(!empty($_SESSION['requirepassword']))
            $this->_helper->redirector->gotoRoute(array());
        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Settings were successfully saved.'));
    }

    public function networkAction()
    {

        $viewer = Engine_Api::_()->user()->getViewer();

        $this->view->available_networks = Network_Model_Network::getUserNetworks($viewer);

        $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($viewer)
            ->order('engine4_network_networks.title ASC');
        $this->view->networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);

        // Get networks to suggest
        $network_suggestions = array();
        $table = Engine_Api::_()->getItemTable('network');
        $select = $table->select()
            ->where('assignment = ?', 0)
            ->order('title ASC');

        if( null !== ($text = $this->_getParam('text', $this->_getParam('text'))))
        {
            $select->where('`'.$table->info('name').'`.`title` LIKE ?', '%'. $text .'%');
        }

        $data = array();
        foreach( $table->fetchAll($select) as $network )
        {
            if( !$network->membership()->isMember($viewer) )
            {
                $network_suggestions[] = $network;
            }
        }
        $this->view->network_suggestions = $network_suggestions;


        $this->view->form = $form = new User_Form_Settings_Network();

        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        // Process
        $viewer = Engine_Api::_()->user()->getViewer();

        if( $form->getValue('join_id') ) {
            $network = Engine_Api::_()->getItem('network', $form->getValue('join_id'));
            if( null === $network ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else if( $network->assignment != 0 ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else {
                $network->membership()->addMember($viewer)
                    ->setUserApproved($viewer)
                    ->setResourceApproved($viewer);

                if (!$network->hide){
                    // Activity feed item
                    Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $network, 'network_join');
                }
            }
        } else if( $form->getValue('leave_id') ) {
            $network = Engine_Api::_()->getItem('network', $form->getValue('leave_id'));
            if( null === $network ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else if( $network->assignment != 0 ) {
                $form->addError(Zend_Registry::get('Zend_Translate')->_('Network not found'));
            } else {
                $network->membership()->removeMember($viewer);
            }
        }

        $this->_helper->redirector->gotoRoute(array());
    }

    public function notificationsAction()
    {

        $user = Engine_Api::_()->core()->getSubject();

        // Build the different notification types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
        $notificationTypes = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationTypes();
        $notificationSettings = Engine_Api::_()->getDbtable('notificationSettings', 'activity')->getEnabledNotifications($user);

        $hideNotificationsType = array(
            'commented',
            'commented_commented',
            'liked',
            'liked_commented',
            'message_new',
            'post_user',
            'shared',
            'tagged',
            'sesadvancedactivity_reacted_angr',
            'sesadvancedactivity_reacted_haha',
            'sesadvancedactivity_reacted_love',
            'sesadvancedactivity_reacted_sad',
            'sesadvancedactivity_reacted_wow',
            'sesadvancedactivity_scheduled_li',
            'sesadvancedactivity_tagged_item',
            'sesadvancedactivity_tagged_peopl',
            'sesevent_accepted',
            'sesevent_approve',
            'sesevent_eventfollow',
            'sesevent_eventsave',
            'sesevent_favourite_event',
            'sesevent_favourite_eventhost',
            'sesevent_favourite_eventlist',
            'sesevent_favourite_eventspeaker',
            'sesevent_like_event',
            'sesevent_like_eventalbum',
            'sesevent_like_eventhost',
            'sesevent_like_eventlist',
            'sesevent_like_eventspeaker',
            'sesevent_sitefriend_ashost'
        );
        $notificationTypesAssoc = array();
        $notificationSettingsAssoc = array();
        foreach ($notificationTypes as $type) {
            if (isset($modules[$type->module])) {
                $category = 'ACTIVITY_CATEGORY_TYPE_' . strtoupper($type->module);
                $translateCategory = Zend_Registry::get('Zend_Translate')->_($category);
                if ($translateCategory === $category) {
                    $elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', $type->module);
                    $category = $modules[$type->module]->title;
                    $category = str_replace(array("SES - ", " Plugin", "SNS: ", "Advanced ", "Professional "), array("", "", "", "", ""), $category);
                } else {
                    $elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($translateCategory));
                }
            } else {
                $elementName = 'misc';
                $category = 'Misc';
            }
            if (!in_array($type->type, $hideNotificationsType)) {
                $notificationTypesAssoc[$elementName]['category'] = $category;
                $notificationTypesAssoc[$elementName]['types'][$type->type] = 'ACTIVITY_TYPE_' . strtoupper($type->type);
            }
            if (in_array($type->type, $notificationSettings)) {
                $notificationSettingsAssoc[$elementName][] = $type->type;
            }
        }

        ksort($notificationTypesAssoc);

        $notificationTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $notificationTypesAssoc));

        // Make form
        $this->view->form = $form = new Engine_Form(array(
            'title' => '',
            'description' => 'Which of the these do you want to receive notification alerts about?',
        ));

        foreach( $notificationTypesAssoc as $elementName => $info ) {
            $form->addElement('MultiCheckbox', $elementName, array(
                'label' => $info['category'],
                'multiOptions' => $info['types'],
                'value' => (array) @$notificationSettingsAssoc[$elementName],
            ));
        }

        $form->addElement('Button', 'execute', array(
            'label' => 'Save Changes',
            'type' => 'submit',
        ));

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        // Process
        $values = array();
        foreach( $form->getValues() as $key => $value ) {
            if( !is_array($value) ) continue;

            foreach( $value as $skey => $svalue ) {
                if( !isset($notificationTypesAssoc[$key]['types'][$svalue]) ) {
                    continue;
                }
                $values[] = $svalue;
            }
        }

        // enable for type hide notifications
        $values = array_merge($values, $hideNotificationsType);

        // Set notification setting
        Engine_Api::_()->getDbtable('notificationSettings', 'activity')
            ->setEnabledNotifications($user, $values);

        $form->addNotice('Your changes have been saved.');
    }


    public function emailsAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();

        // Build the different email types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
        $emailTypes = Engine_Api::_()->getDbTable('mailTemplates', 'core')->getEmailTypes();
        $emailSettings = Engine_Api::_()->getDbtable('emailSettings', 'user')->getEnabledEmails($user);

        $hideEmailsType = array(
            'notify_commented',
            'notify_commented_commented',
            'notify_liked',
            'notify_liked_commented',
            'notify_friend_follow_request',
            'notify_post_user',
            'notify_tagged',
            'notify_sesevent_approve',
            'notify_sesevent_discussion_response',
            'notify_sesevent_discussion_reply',
            'sesevent_ticketpurchased_eventowner',
            'sesevent_ticketpayment_requestadmin',
            'sesevent_ticketpayment_adminrequestcancel',
            'sesevent_ticketpayment_adminrequestapproved',
            'sesevent_sponsorshippurchased_eventowner',
            'sesevent_sponsorshippayment_requestadmin',
            'sesevent_sponsorshippayment_adminrequestcancel',
            'sesevent_sponsorshippayment_adminrequestapproved',
            'sesevent_rsvp_change',
            'sesevent_payment_ticket_pending'
        );
        $emailTypesAssoc = array();
        $emailSettingsAssoc = array();
        foreach( $emailTypes as $type ) {
            if (isset($modules[$type->module])) {
                $category = 'ACTIVITY_CATEGORY_TYPE_' . strtoupper($type->module);
                $translateCategory = Zend_Registry::get('Zend_Translate')->_($category);
                if ($translateCategory === $category) {
                    $elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', $type->module);
                    $category = $modules[$type->module]->title;
                    $category = str_replace(array("SES - ", " Plugin", "SNS: ", "Advanced ", "Professional "), array("", "", "", "", ""), $category);
                } else {
                    $elementName = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($translateCategory));
                }
            } else {
                $elementName = 'misc';
                $category = 'Misc';
            }
            if (!in_array($type->type, $hideEmailsType)) {
                $emailTypesAssoc[$elementName]['category'] = $category;
                $emailTypesAssoc[$elementName]['types'][$type->type] = '_EMAIL_' . strtoupper($type->type) . '_TITLE';
            }
            if (in_array($type->type, $emailSettings)) {
                $emailSettingsAssoc[$elementName][] = $type->type;
            }
        }

        ksort($emailTypesAssoc);

        $emailTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $emailTypesAssoc));

        // Make form
        $this->view->form = $form = new Engine_Form(array(
            'title' => 'Email Settings',
            'description' => 'Which of the these do you want to receive email alerts about?',
        ));

        // Disable all Email
        $form->addElement('Checkbox', 'disable_email', array(
            'label' => 'Disable all site emails?',
            'value' => !empty($user->disable_email) ? $user->disable_email : 0,
            'onclick' => "disableEmail(this);",
        ));
        // Disable all admin Email
        $form->addElement('Checkbox', 'disable_adminemail', array(
            'label' => 'Disable all admin emails',
            'value' => !empty($user->disable_adminemail) ? $user->disable_adminemail : 0,
        ));
        foreach( $emailTypesAssoc as $elementName => $info ) {
            $form->addElement('MultiCheckbox', $elementName, array(
                'label' => $info['category'],
                'multiOptions' => $info['types'],
                'value' => (array) @$emailSettingsAssoc[$elementName],
                'class' => 'email_settings'
            ));
        }

        $form->addElement('Button', 'execute', array(
            'label' => 'Save Changes',
            'type' => 'submit',
        ));

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        // Process
        $values = array();
        foreach( $form->getValues() as $key => $value ) {
            if( !is_array($value) ) continue;

            foreach( $value as $skey => $svalue ) {
                if( !isset($emailTypesAssoc[$key]['types'][$svalue]) ) {
                    continue;
                }
                $values[] = $svalue;
            }
        }

        //enable for hide emails
        $values = array_merge($values,$hideEmailsType);

        // Disable all email
        $user->disable_email = !empty($form->getElement('disable_email')->getValue()) ? $form->getElement('disable_email')->getValue() : '0';
        $user->disable_adminemail = !empty($form->getElement('disable_adminemail')->getValue()) ? $form->getElement('disable_adminemail')->getValue() : '0';
        $user->save();

        if(empty($user->disable_email)) {
            // Set email setting
            Engine_Api::_()->getDbtable('emailSettings', 'user')->setEnabledEmails($user, $values);
        }

        if(!empty($user->disable_email)) {
            foreach( $emailTypesAssoc as $elementName => $info ) {
                $form->addElement('MultiCheckbox', $elementName, array(
                    'label' => $info['category'],
                    'multiOptions' => $info['types'],
                    'value' => (array) @$emailSettingsAssoc[$elementName],
                    'class' => 'email_settings'
                ));
            }
        }
        $form->addNotice('Your changes have been saved.');
    }

    public function deleteAction()
    {

        $user = Engine_Api::_()->core()->getSubject();
        if( !$this->_helper->requireAuth()->setAuthParams($user, null, 'delete')->isValid() ) return;

        $this->view->isSuperAdmin = false;
        if( 1 === $user->level_id ) {
            $this->view->isSuperAdmin = true;
            return;
        }

        $otpfeatures = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.spam.otpfeatures',1);
        // Form
        $this->view->form = $form = new User_Form_Settings_Delete();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            if ($form->getElement('step')->getValue() == 1){
                if(!empty($otpfeatures)) {
                    $email = $user->email;
                    $codeTable = Engine_Api::_()->getDbTable('codes', 'user');
                    $isEmailExist = $codeTable->isEmailExist($email);
                    if($isEmailExist) {
                        $isEmailExist->delete();
                    }
                    $code = rand(100000, 999999);
                    $row = $codeTable->createRow();
                    $row->email = $email;
                    $row->code = $code;
                    $row->creation_date = date('Y-m-d H:i:s');
                    $row->modified_date = date('Y-m-d H:i:s');
                    $row->save();
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_deleteotp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
                }
                $form->getElement('step')->setValue(2);
                $form->addElement('Text', "code", array(
                    'label' => 'Enter Verification Code',
                    'description' => '',
                    'allowEmpty' => false,
                    'required' => true,
                    'order' => 1
                ));
                $form->getElement('execute')->setLabel('Delete my Account');
                $form->setDescription('');
                return;
            }
            if ($form->getElement('step')->getValue() == 2){
                if(!empty($otpfeatures)) {
                    $inputcode = $_POST['code'];
                    $email = $user->email;
                    $code_id = Engine_Api::_()->getDbtable('codes', 'user')->isExist($inputcode, $email);
                    if(empty($code_id)) {
                        $form->addError("The verification code you entered is invalid. Please enter the correct verification code.");
                        return;
                    } else {
                        $code = Engine_Api::_()->getItem('user_code', $code_id);
                        $code->delete();
                    }
                }
                // Process
                $db = Engine_Api::_()->getDbtable('users', 'user')->getAdapter();
                $db->beginTransaction();

                try {
                    $user->delete();

                    $db->commit();
                } catch( Exception $e ) {
                    $db->rollBack();
                    throw $e;
                }
                // Unset viewer, remove auth, clear session
                Engine_Api::_()->user()->setViewer(null);
                Zend_Auth::getInstance()->getStorage()->clear();
                Zend_Session::destroy();

                return $this->_helper->redirector->gotoRoute(array(), 'default', true);
            }
        }
    }
}
