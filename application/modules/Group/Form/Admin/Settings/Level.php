<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Level.php 9802 2012-10-20 16:56:13Z pamela $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract
{
    public function init()
    {
        parent::init();

        // My stuff
        $this
        ->setTitle('Member Level Settings')
        ->setDescription('GROUP_FORM_ADMIN_LEVEL_DESCRIPTION');

        // Element: view
        $this->addElement('Radio', 'view', array(
            'label' => 'Allow Viewing of Groups?',
            'description' => 'GROUP_FORM_ADMIN_LEVEL_VIEW_DESCRIPTION',
            'multiOptions' => array(
                2 => 'Yes, allow members to view all groups, even private ones.',
                1 => 'Yes, allow viewing and subscription of groups.',
                0 => 'No, do not allow groups to be viewed.',
            ),
            'value' => ( $this->isModerator() ? 2 : 1 ),
        ));
        if( !$this->isModerator() ) {
            unset($this->view->options[2]);
        }

        if( !$this->isPublic() ) {

            // Element: create
            $this->addElement('Radio', 'create', array(
                'label' => 'Allow Creation of Groups?',
                'description' => 'GROUP_FORM_ADMIN_LEVEL_CREATE_DESCRIPTION',
                'multiOptions' => array(
                    1 => 'Yes, allow creation of groups.',
                    0 => 'No, do not allow groups to be created.',
                ),
                'value' => 1,
            ));

            // Element: edit
            $this->addElement('Radio', 'edit', array(
                'label' => 'Allow Editing of Groups?',
                'description' => 'Do you want to let users edit and delete groups?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to edit everyone\'s groups.',
                    1 => 'Yes, allow  members to edit their own groups.',
                    0 => 'No, do not allow groups to be edited.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->edit->options[2]);
            }

            // Element: delete
            $this->addElement('Radio', 'delete', array(
                'label' => 'Allow Deletion of Groups?',
                'description' => 'Do you want to let members delete groups? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow members to delete all groups.',
                    1 => 'Yes, allow members to delete their own groups.',
                    0 => 'No, do not allow members to delete their groups.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->delete->options[2]);
            }

            // Element: comment
            $this->addElement('Radio', 'comment', array(
                'label' => 'Allow Commenting on Groups?',
                'description' => 'Do you want to let members of this level comment on groups?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to comment on all groups, including private ones.',
                    1 => 'Yes, allow members to comment on groups.',
                    0 => 'No, do not allow members to comment on groups.',
                ),
                'value' => ( $this->isModerator() ? 2 : 1 ),
            ));
            if( !$this->isModerator() ) {
                unset($this->comment->options[2]);
            }

            // Element: auth_view
            $this->addElement('MultiCheckbox', 'auth_view', array(
                'label' => 'Group Privacy',
                'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHVIEW_DESCRIPTION',
                'multiOptions' => array(
                    'everyone' => 'Everyone',
                    'registered' => 'Registered Members',
                    'member' => 'Members Only',
                    //'officer' => 'Officers and Owner Only',
                    //'owner' => 'Owner Only'
                )
            ));

            // Element: auth_comment
            $this->addElement('MultiCheckbox', 'auth_comment', array(
                'label' => 'Group Posting Options',
                'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHCOMMENT_DESCRIPTION',
                'multiOptions' => array(
                    'registered' => 'Registered Members',
                    'member' => 'All Members',
                    'officer' => 'Officers and Owner Only',
                    //'owner' => 'Owner Only',
                )
            ));


            // Element: create
            $this->addElement('Radio', 'photo', array(
                'label' => 'Allow Creation of Photo Albums?',
                'description' => 'ALBUM_FORM_ADMIN_LEVEL_CREATE_DESCRIPTION',
                'value' => 1,
                'multiOptions' => array(
                    1 => 'Yes, allow creation of photo albums.',
                    0 => 'No, do not allow photo albums to be created.'
                ),
                'value' => 1,
                'order' => 101,
            ));

            // Element: edit
            $this->addElement('Radio', 'photo_edit', array(
                'label' => 'Allow Editing of Photo Albums?',
                'description' => 'Do you want to let members of this level edit photo albums?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to edit all albums.',
                    1 => 'Yes, allow members to edit their own albums.',
                    0 => 'No, do not allow photo albums to be edited.',
                ),
                'value' => ($this->isModerator() ? 2 : 1),
                'order' => 102,
            ));
            if (!$this->isModerator()) {
                unset($this->photo_edit->options[2]);
            }

            // Element: delete
            $this->addElement('Radio', 'photo_delete', array(
                'label' => 'Allow Deletion of Photo Albums?',
                'description' => 'Do you want to let members of this level delete photo albums?',
                'multiOptions' => array(
                    2 => 'Yes, allow members to delete all photo albums.',
                    1 => 'Yes, allow members to delete their own photo albums.',
                    0 => 'No, do not allow members to delete their photo albums.',
                ),
                'value' => ($this->isModerator() ? 2 : 1),
                'order' => 103,
            ));
            if (!$this->isModerator()) {
                unset($this->photo_delete->options[2]);
            }

            // Element: auth_photo
            $this->addElement('MultiCheckbox', 'auth_photo', array(
                'label' => 'Photo Upload Options',
                'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHPHOTO_DESCRIPTION',
                'multiOptions' => array(
                    'registered' => 'Registered Members',
                    'member' => 'All Members',
                    'officer' => 'Officers and Owner Only',
                    //'owner' => 'Owner Only',
                ),
                'order' => 104,
            ));

            // Element: allow_network
            $this->addElement('Radio', 'allow_network', array(
                'label' => 'Allow to Choose Network Privacy?',
                'description' => 'Do you want to let members of this level choose Network Privacy for their Groups? These options appear on your members\' "Add Entry" and "Edit Entry" pages.',
                'multiOptions' => array(
                    1 => 'Yes, allow to choose Network Privacy.',
                    0 => 'No, do not allow to choose Network Privacy. '
                ),
                'value' => 1,
            ));

            // Element: style
            $this->addElement('Radio', 'style', array(
                'label' => 'Allow Group Style',
                'required' => true,
                'multiOptions' => array(
                    1 => 'Yes, allow custom group styles.',
                    0 => 'No, do not allow custom group styles.'
                ),
                'value' => 1,
            ));

            // Element: create
            $this->addElement('Radio', 'coverphotoupload', [
                'label' => 'Allow to Upload Cover Photos?',
                'description' => 'Do you want to let members upload Cover Photos for their groups?',
                'multiOptions' => array(
                    1 => 'Yes, allow user to upload cover photos',
                    0 => 'No, do not allow users to upload cover photos.'
                ),
                'value' => 1,
            ]);

            // Element: max
            $this->addElement('Text', 'max', array(
                'label' => 'How many groups to be allowed?',
                'description' => 'Enter the maximum number of groups that are allowed to be created. This field must contain an integer between 1 and 999, or 0 for unlimited.',
                'validators' => array(
                    array('Int', true),
                    new Engine_Validate_AtLeast(0),
                ),
                'value' => 0
            ));



            // Element: event create
            $this->addElement('Radio', 'event', array(
                'label' => 'Allow Creation of Events?',
                'description' => 'Do you want to let users create events? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    1 => 'Yes, allow creation of events.',
                    0 => 'No, do not allow events to be created.'
                ),
                'order' => 200,
                'value' => 1,
            ));

            // Element: auth_event
            $this->addElement('MultiCheckbox', 'auth_event', array(
                'label' => 'Event Creation Options',
                'description' => 'GROUP_FORM_ADMIN_LEVEL_AUTHEVENT_DESCRIPTION',
                'multiOptions' => array(
                    'registered' => 'Registered Members',
                    'member' => 'All Members',
                    'officer' => 'Officers and Owner Only',
                        //'owner' => 'Owner Only',
                ),
                'order' => 205,
            ));

            /* Forum Settings*/
            // Element: topic_create
            $this->addElement('Radio', 'topic_create', array(
                'label' => 'Allow Creation of Topics?',
                'description' => 'Do you want to let users create topics? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow creation of topics in forums, even private ones.',
                    1 => 'Yes, allow creation of topics.',
                    0 => 'No, do not allow topics to be created.'
                ),
                'order' => 301,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->topic_create->options[2]);
            }

            // Element: topic_edit
            $this->addElement('Radio', 'topic_edit', array(
                'label' => 'Allow Editing of Topics?',
                'description' => 'Do you want to let users edit topics?',
                'multiOptions' => array(
                    2 => 'Yes, allow editing of topics in forums, including other members\' topics.',
                    1 => 'Yes, allow editing of topics.',
                    0 => 'No, do not allow topics to be edited.'
                ),
                'order' => 302,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->topic_edit->options[2]);
            }

            // Element: topic_edit
            $this->addElement('Radio', 'topic_delete', array(
                'label' => 'Allow Deletion of Topics?',
                'description' => 'Do you want to let users delete topics?',
                'multiOptions' => array(
                    2 => 'Yes, allow deletion of topics in forums, including other members\' topics.',
                    1 => 'Yes, allow deletion of topics.',
                    0 => 'No, do not allow topics to be deleted.'
                ),
                'order' => 304,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->topic_delete->options[2]);
            }

            // Element: post_create
            $this->addElement('Radio', 'post_create', array(
                'label' => 'Allow Posting?',
                'description' => 'Do you want to allow users to post to the forums? If set to no, some other settings on this page may not apply.',
                'multiOptions' => array(
                    2 => 'Yes, allow posting to forums, even private ones.',
                    1 => 'Yes, allow posting to forums.',
                    0 => 'No, do not allow forum posts.'
                ),
                'order' => 305,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->post_create->options[2]);
            }

            // Element: post_edit
            $this->addElement('Radio', 'post_edit', array(
                'label' => 'Allow Editing of Posts?',
                'description' => 'Do you want to allow users to edit posts in forums?',
                'multiOptions' => array(
                    2 => 'Yes, allow editing of posts, including other members\' posts.',
                    1 => 'Yes, allow editing of posts.',
                    0 => 'No, do not allow forum posts to be edited.'
                ),
                'order' => 306,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->post_edit->options[2]);
            }

            // Element: post_edit
            $this->addElement('Radio', 'post_delete', array(
                'label' => 'Allow Deletion of Posts?',
                'description' => 'Do you want to allow users to delete posts in forums?',
                'multiOptions' => array(
                    2 => 'Yes, allow deletion of posts, including other members\' posts.',
                    1 => 'Yes, allow deletion of posts.',
                    0 => 'No, do not allow forum posts to be deleted.'
                ),
                'order' => 307,
                'value' => ($this->isModerator() ? 2 : 1),
            ));
            if (!$this->isModerator()) {
                unset($this->post_delete->options[2]);
            }

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {
            // Element: create
                $this->addElement('Radio', 'video', array(
                    'label' => 'Allow Creation of Videos?',
                    'description' => 'Do you want to let members create videos? If set to no, some other settings on this page may not apply. This is useful if you want members to be able to view videos, but only want certain levels to be able to create videos.',
                    'multiOptions' => array(
                        1 => 'Yes, allow creation of videos.',
                        0 => 'No, do not allow video to be created.'
                    ),
                    'value' => 1,
                    'order' => 601,
                ));
                
                $this->addElement('Radio', 'videoupload', array(
                  'label' => 'Allow Video Upload?',
                  'description' => 'Do you want to let members to upload their own videos?',
                  'multiOptions' => array(
                    1 => 'Yes',
                    0 => 'No',
                  ),
                  'value' => 1,
                  'order' => 602,
                ));
                
                // Element: auth_video
                $this->addElement('MultiCheckbox', 'auth_video', array(
                    'label' => 'Video Creation Options',
                    'description' => 'Your users can choose from any of the options checked below when they decide who can create videos in their groups. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
                    'multiOptions' => array(
                        'registered' => 'Registered Members',
                        'member' => 'All Members',
                        'officer' => 'Officers and Owner Only',
                        //'owner' => 'Owner Only',
                    ),
                    'order' => 605,
                ));
                
                
            }
            

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('poll')) {
                /* Polls */
            // Element: create
                $this->addElement('Radio', 'poll', array(
                    'label' => 'Allow Creation of Polls?',
                    'description' => 'Do you want to allow members to create polls?',
                    'multiOptions' => array(
                        1 => 'Yes, allow this member level to create polls',
                        0 => 'No, do not allow this member level to create polls',
                    ),
                    'value' => 1,
                    'order' => 401,
                ));

            // Element: auth_poll
                $this->addElement('MultiCheckbox', 'auth_poll', array(
                    'label' => 'Poll Creation Options',
                    'description' => 'Your users can choose from any of the options checked below when they decide who can create polls in their groups. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
                    'multiOptions' => array(
                        'registered' => 'Registered Members',
                        'member' => 'All Members',
                        'officer' => 'Officers and Owner Only',
                        //'owner' => 'Owner Only',
                    ),
                    'order' => 404,
                ));
            }

            $this->addElement('dummy', 'coverphoto_dummy', [
                'label' => 'Default Group Cover Photo',
            ]);

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('blog')) {
            // Element: create
                $this->addElement('Radio', 'blog', array(
                    'label' => 'Allow Creation of Blogs?',
                    'description' => 'Do you want to let members create blogs? If set to no, some other settings on this page may not apply. This is useful if you want members to be able to view blogs, but only want certain levels to be able to create blogs.',
                    'multiOptions' => array(
                        1 => 'Yes, allow creation of blogs.',
                        0 => 'No, do not allow blogs to be created.'
                    ),
                    'value' => 1,
                    'order' => 501,
                ));

                // Element: auth_blog
                $this->addElement('MultiCheckbox', 'auth_blog', array(
                    'label' => 'Blog Creation Options',
                    'description' => 'Your users can choose from any of the options checked below when they decide who can create blogs in their groups. If you do not check any options, settings will default to the last saved configuration. If you select only one option, members of this level will not have a choice.',
                    'multiOptions' => array(
                        'registered' => 'Registered Members',
                        'member' => 'All Members',
                        'officer' => 'Officers and Owner Only',
                        //'owner' => 'Owner Only',
                    ),
                    'order' => 505,
                ));
            }

            $this->coverphoto_dummy->addDecorator('Description', [
                'placement' => 'PREPEND',
                'class' => 'description',
                'escape' => false,
                'order' => 998
            ]);

            $this->addElement('FloodControl', 'flood', array(
                'label' => 'Maximum Allowed Groups to create per Duration',
                'description' => 'Enter the maximum number of groups allowed for the selected duration (per minute / per hour / per day) for members of this level. The field must contain an integer between 1 and 9999, or 0 for unlimited.',
                'required' => true,
                'allowEmpty' => false,
                'value' => array(0, 'minute'),
            ));
        }
        // Element: commentHtml
        $this->addElement('Text', 'commentHtml', array(
            'label' => 'Allow HTML in posts?',
            'description' => 'GROUP_FORM_ADMIN_LEVEL_CONTENTHTML_DESCRIPTION',
            'order' => 999
        ));
    }
}
