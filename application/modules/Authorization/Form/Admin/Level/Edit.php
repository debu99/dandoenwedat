<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 10086 2013-09-16 19:27:24Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_Form_Admin_Level_Edit extends Authorization_Form_Admin_Level_Abstract
{
  public function init()
  {
    parent::init();

    // My stuff
    $this
        ->setTitle('Member Level Settings')
        ->setDescription("AUTHORIZATION_FORM_ADMIN_LEVEL_EDIT_DESCRIPTION");
        
    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'allowEmpty' => false,
      'required' => true,
    ));

    $this->addElement('Textarea', 'description', array(
      'label' => 'Description',
      'allowEmpty' => true,
      'required' => false,
    ));

    if( !$this->isPublic() ) {

      // Element: edit
      if( $this->isModerator() ) {
        $this->addElement('Radio', 'edit', array(
          'label' => 'Allow Profile Moderation',
          'required' => true,
          'multiOptions' => array(
            2 => 'Yes, allow members in this level to edit other profiles and settings.',
            1 => 'No, do not allow moderation.'
          ),
          'value' => 0,
        ));
      }

      // Element: style
      $this->addElement('Radio', 'style', array(
        'label' => 'Allow Profile Style',
        'required' => true,
        'multiOptions' => array(
          2 => 'Yes, allow members in this level to edit other custom profile styles.',
          1 => 'Yes, allow custom profile styles.',
          0 => 'No, do not allow custom profile styles.'
        ),
        'value' => 1,
      ));
      if( !$this->isModerator() ) {
        unset($this->getElement('style')->options[2]);
      }

      // Element: delete
      $this->addElement('Radio', 'delete', array(
        'label' => 'Allow Account Deletion?',
        'multiOptions' => array(
          2 => 'Yes, allow members in this level to delete other users.',
          1 => 'Yes, allow members to delete their account.',
          0 => 'No, do not allow account deletion.',
        ),
        'value' => 1,
      ));
      if( !$this->isModerator() ) {
        unset($this->getElement('delete')->options[2]);
      }
      $this->delete->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: activity
      if( $this->isModerator() ) {
        $this->addElement('Radio', 'activity', array(
          'label' => 'Allow Activity Feed Moderation',
          'required' => true,
          'multiOptions' => array(
            1 => 'Yes, allow members in this level to delete any feed item.',
            0 => 'No, do not allow moderation.'
          ),
          'value' => 0,
        ));
      }

      // Element: block
      $this->addElement('Radio', 'block', array(
        'label' => 'Allow Blocking?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_BLOCK_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));
      $this->block->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: auth_view
      $this->addElement('MultiCheckbox', 'auth_view', array(
        'label' => 'Profile Viewing Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHVIEW_DESCRIPTION',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->auth_view->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Profile Commenting Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'multiOptions' => array(
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        )
      ));
      $this->auth_comment->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: lastLoginShow
      $this->addElement('Radio', 'lastLoginShow', array(
        'label' => 'Show Last Login Date?',
        'description' => 'Do you want to enable members of this level to choose to show their Last Login Dates to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their last login date.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->lastLoginShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: lastLoginDate
      $this->addElement('MultiCheckbox', 'lastLoginDate', array(
        'label' => 'Last Login Date Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their last login date. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->lastLoginDate->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: lastUpdateShow
      $this->addElement('Radio', 'lastUpdateShow', array(
        'label' => 'Show Last Update Date?',
        'description' => 'Do you want to enable members of this level to choose to show their Last Update Date to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their last update date.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->lastUpdateShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: lastUpdateDate
      $this->addElement('MultiCheckbox', 'lastUpdateDate', array(
        'label' => 'Last Update Date Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their last update date. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->lastUpdateDate->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: inviteeShow
      $this->addElement('Radio', 'inviteeShow', array(
        'label' => 'Show Invitee Name?',
        'description' => 'Do you want to enable members of this level to choose to show who invited them on your website to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their invitee name.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->inviteeShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: InviteeName
      $this->addElement('MultiCheckbox', 'inviteeName', array(
        'label' => 'Invitee Name Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their invitee name. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->inviteeName->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: profileTypeShow
      $this->addElement('Radio', 'profileTypeShow', array(
        'label' => 'Show Profile Type?',
        'description' => 'Do you want to enable members of this level to choose to show their Profile Type to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their profile type.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->profileTypeShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: profileType
      $this->addElement('MultiCheckbox', 'profileType', array(
        'label' => 'Profile Type Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their profile type. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->profileType->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: memberLevelShow
      $this->addElement('Radio', 'memberLevelShow', array(
        'label' => 'Show Member Level?',
        'description' => 'Do you want to enable members of this level to choose to show their Member Level to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their member level.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->memberLevelShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: memberLevel
      $this->addElement('MultiCheckbox', 'memberLevel', array(
        'label' => 'Member Level Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their member level. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->memberLevel->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: profileViewsShow
      $this->addElement('Radio', 'profileViewsShow', array(
        'label' => 'Show Profile Views?',
        'description' => 'Do you want to enable members of this level to choose to show their Profile Views to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their profile views.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->profileViewsShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: profileViews
      $this->addElement('MultiCheckbox', 'profileViews', array(
        'label' => 'Profile View Count Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their profile views. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->profileViews->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: joinedDateShow
      $this->addElement('Radio', 'joinedDateShow', array(
        'label' => 'Show Joined Date on site?',
        'description' => 'Do you want to enable members of this level to choose to show their Joining date on the site to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their joining date on your website.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->joinedDateShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: joinedDate
      $this->addElement('MultiCheckbox', 'joinedDate', array(
        'label' => 'Joined Date View Options',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their joined date on site. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->joinedDate->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: friendsCountShow
      $this->addElement('Radio', 'friendsCountShow', array(
        'label' => 'Show Friends Count?',
        'description' => 'Do you want to enable members of this level to choose to show their Friends Count to other members in "Profile Info" widget placed on their Member Profile page? If you choose \'Yes\', then you can enable members of this level to choose who can view their friends count.',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
        'value' => 1
      ));
      $this->friendsCountShow->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: friendsCount
      $this->addElement('MultiCheckbox', 'friendsCount', array(
        'label' => 'Friends Count',
        'description' => 'Your members can choose from any of the options checked below when they decide who can see their friends count. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
        'multiOptions' => array(
          'everyone'    => 'Everyone',
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        ),
      ));
      $this->friendsCount->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: auth_comment
      $this->addElement('MultiCheckbox', 'auth_comment', array(
        'label' => 'Profile Commenting Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_AUTHCOMMENT_DESCRIPTION',
        'multiOptions' => array(
          'registered'  => 'All Registered Members',
          'network'     => 'My Network',
          'member'      => 'My Friends',
          'owner'       => 'Only Me',
        )
      ));
      $this->auth_comment->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: search
      $this->addElement('Radio', 'search', array(
        'label' => 'Search Privacy Options',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_SEARCH_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        ),
      ));
      $this->search->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: status
      $this->addElement('Radio', 'status', array(
        'label' => 'Allow status messages?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_STATUS_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));

      $this->addElement('Text', 'activity_edit_time', array(
        'label' => 'Maximum Allowed time for editing status posts?',
        'description' => 'Enter the maximum allowed time (in minutes) for which members will be able to edit their status posts via activity feed.'
        . ' The field must contain an integer between 1 and 1000000, or 0 for unlimited.',
        'validators' => array(
          array('Int', true),
          new Engine_Validate_AtLeast(0),
        ),
      ));

      // Element: username
      $this->addElement('Radio', 'username', array(
        'label' => 'Allow username changes?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_USERNAME_DESCRIPTION',
        'multiOptions' => array(
          1 => 'Yes',
          0 => 'No'
        )
      ));
      $this->username->getDecorator('Description')->setOption('placement', 'PREPEND');

      // Element: quota
      $this->addElement('Select', 'quota', array(
        'label' => 'Storage Quota',
        'required' => true,
        'multiOptions' => Engine_Api::_()->getItemTable('storage_file')->getStorageLimits(),
        'value' => 0, // unlimited
        'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_QUOTA_DESCRIPTION'
      ));

      // Element: commenthtml
      $this->addElement('Text', 'commenthtml', array(
        'label' => 'Allow HTML in Comments?',
        'description' => 'CORE_FORM_ADMIN_SETTINGS_GENERAL_COMMENTHTML_DESCRIPTION'
      ));

      // Element: messages_auth
      $this->addElement('Radio', 'messages_auth', array(
        'label' => 'Allow messaging?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_MESSAGESAUTH_DESCRIPTION',
        'multiOptions' => array(
          'everyone' => 'Everyone',
          'friends' => 'Friends Only',
          'none' => 'Disable messaging',
        )
      ));
      
      // Element: messages_editor
      $this->addElement('Radio', 'messages_editor', array(
        'label' => 'Use editor for messaging?',
        'description' => 'USER_FORM_ADMIN_SETTINGS_LEVEL_MESSAGEEDITOR_DESCRIPTION',
        'multiOptions' => array(
          'editor' => 'Editor',
          'plaintext' => 'Plain Text',
        )
      ));

      // Element: create
      $this->addElement('Radio', 'coverphotoupload', [
        'label' => 'Allow Cover Photo Uploads ?',
        'description' => 'Do you want to allow members to upload their cover photos?',
        'multiOptions' => array(
          1 => 'Yes, allow user to upload cover photos',
          0 => 'No, do not allow users to upload cover photos.'
        ),
        'value' => 1,
      ]);

      $this->addElement('dummy', 'coverphoto_dummy', [
        'label' => 'Default User Cover Photo',
      ]);

        $this->addElement('FloodControl', 'activity_flood', array(
            'label' => 'Maximum Allowed Status Messages per Duration',
            'description' => 'Enter the maximum number of status messages allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
            'required' => true,
            'allowEmpty' => false,
            'value' => array(0, 'minute'),
        ));

        $this->addElement('FloodControl', 'messages_flood', array(
            'label' => 'Maximum Allowed Messages per Duration',
            'description' => 'Enter the maximum number of messages allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
            'required' => true,
            'allowEmpty' => false,
            'value' => array(0, 'minute'),
        ));

      $this->coverphoto_dummy->addDecorator('Description', [
        'placement' => 'PREPEND',
        'class' => 'description',
        'escape' => false
      ]);

      $this->messages_auth->getDecorator('Description')->setOption('placement', 'PREPEND');
    }
  }
}
