<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Create.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Group_Form_Create extends Engine_Form
{
    public function init()
    {
        $user = Engine_Api::_()->user()->getViewer();

        $this
            ->setTitle('Create New Group');

        $this->addElement('Text', 'title', array(
            'label' => 'Group Name',
            'allowEmpty' => false,
            'required' => true,
            'validators' => array(
                array('NotEmpty', true),
                array('StringLength', false, array(1, 64)),
            ),
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
            ),
        ));

        $this->addElement('Textarea', 'description', array(
            'label' => 'Description',
            'validators' => array(
                array('NotEmpty', true),
                //array('StringLength', false, array(1, 1027)),
            ),
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_EnableLinks(),
                new Engine_Filter_StringLength(array('max' => 10000)),
            ),
        ));

        $this->addElement('File', 'photo', array(
            'label' => 'Profile Photo'
        ));
        $this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg');

        $this->addElement('Select', 'category_id', array(
            'label' => 'Category',
            'multiOptions' => array(
                '0' => ' '
            ),
        ));

        $this->addElement('Radio', 'search', array(
            'label' => 'Include in search results?',
            'multiOptions' => array(
                '1' => 'Yes, include in search results.',
                '0' => 'No, hide from search results.',
            ),
            'value' => '1',
        ));

        $this->addElement('Radio', 'auth_invite', array(
            'label' => 'Let members invite others?',
            'multiOptions' => array(
                'member' => 'Yes, members can invite other people.',
                'officer' => 'No, only officers can invite other people.',
            ),
            'value' => 'member',
        ));

        $this->addElement('Radio', 'approval', array(
            'label' => 'Approve members?',
            'description' => ' When people try to join this group, should they be allowed '.
                'to join immediately, or should they be forced to wait for approval?',
            'multiOptions' => array(
                '0' => 'New members can join immediately.',
                '1' => 'New members must be approved.',
            ),
            'value' => '0',
        ));

        if (Engine_Api::_()->authorization()->isAllowed('group', $user, 'allow_network')) {
            $networkOptions = array();
            foreach (Engine_Api::_()->getDbTable('networks', 'network')->fetchAll() as $network) {
                $networkOptions[$network->network_id] = $network->getTitle();
            }
            //Networks
            $this->addElement('Multiselect', 'networks', array(
                'label' => "Networks",
                'description' => 'Choose the Networks to which this Group will be displayed.',
                'multiOptions' => $networkOptions,
            ));
        }

        // Privacy
        $availableLabels = array(
            'everyone' => 'Everyone',
            'registered' => 'Registered Members',
            'member' => 'All Group Members',
            'officer' => 'Officers and Owner Only',
            //'owner' => 'Owner Only',
        );


        // View
        $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_view');
        $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));

        if( !empty($viewOptions) && count($viewOptions) >= 1 ) {
            // Make a hidden field
            if(count($viewOptions) == 1) {
                $this->addElement('hidden', 'auth_view', array('order' => 101, 'value' => key($viewOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_view', array(
                    'label' => 'View Privacy',
                    'description' => 'Who may see this group?',
                    'multiOptions' => $viewOptions,
                    'value' => key($viewOptions),
                ));
                $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        // Comment
        $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_comment');
        $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));

        if( !empty($commentOptions) && count($commentOptions) >= 1 ) {
            // Make a hidden field
            if(count($commentOptions) == 1) {
                $this->addElement('hidden', 'auth_comment', array('order' => 102, 'value' => key($commentOptions)));
                // Make select box
            } else {
                $this->addElement('Select', 'auth_comment', array(
                    'label' => 'Comment Privacy',
                    'description' => 'Who may post on this group\'s wall?',
                    'multiOptions' => $commentOptions,
                    'value' => key($commentOptions),
                ));
                $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
            }
        }

        $viewer = Engine_Api::_()->user()->getViewer();

        if(Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'photo')){
            // Photo
            $photoOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_photo');
            $photoOptions = array_intersect_key($availableLabels, array_flip($photoOptions));

            if( !empty($photoOptions) && count($photoOptions) >= 1 ) {
            // Make a hidden field
                if(count($photoOptions) == 1) {
                    $this->addElement('hidden', 'auth_photo', array('order' => 103, 'value' => key($photoOptions)));
                // Make select box
                } else {
                    $this->addElement('Select', 'auth_photo', array(
                        'label' => 'Photo Uploads',
                        'description' => 'Who may upload photos to this group?',
                        'multiOptions' => $photoOptions,
                        'value' => key($photoOptions),
                    ));
                    $this->auth_photo->getDecorator('Description')->setOption('placement', 'append');
                }
            }
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('event') && Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'event')) {
        // Event
            $eventOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_event');
            $eventOptions = array_intersect_key($availableLabels, array_flip($eventOptions));

            if( !empty($eventOptions) && count($eventOptions) >= 1 ) {
            // Make a hidden field
                if(count($eventOptions) == 1) {
                    $this->addElement('hidden', 'auth_event', array('order' => 104, 'value' => key($eventOptions)));
                // Make select box
                } else {
                    $this->addElement('Select', 'auth_event', array(
                        'label' => 'Event Creation',
                        'description' => 'Who may create events for this group?',
                        'multiOptions' => $eventOptions,
                        'value' => key($eventOptions),
                    ));
                    $this->auth_event->getDecorator('Description')->setOption('placement', 'append');
                }
            }
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('blog') && Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'blog')) {
            // Blog
            $blogOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_blog');
            $blogOptions = array_intersect_key($availableLabels, array_flip($blogOptions));

            if( !empty($blogOptions) && count($blogOptions) >= 1 ) {
                // Make a hidden field
                if(count($blogOptions) == 1) {
                    $this->addElement('hidden', 'auth_blog', array('order' => 105, 'value' => key($blogOptions)));
                    // Make select box
                } else {
                    $this->addElement('Select', 'auth_blog', array(
                        'label' => 'Blog Creation',
                        'description' => 'Who may create blogs for this group?',
                        'multiOptions' => $blogOptions,
                        'value' => key($blogOptions),
                    ));
                    $this->auth_blog->getDecorator('Description')->setOption('placement', 'append');
                }
            }
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('poll') && Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'poll')) {
            // Poll
            $pollOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_poll');
            $pollOptions = array_intersect_key($availableLabels, array_flip($pollOptions));

            if( !empty($pollOptions) && count($pollOptions) >= 1 ) {
                // Make a hidden field
                if(count($pollOptions) == 1) {
                    $this->addElement('hidden', 'auth_poll', array('order' => 107, 'value' => key($pollOptions)));
                    // Make select box
                } else {
                    $this->addElement('Select', 'auth_poll', array(
                        'label' => 'Poll Creation',
                        'description' => 'Who may create polls for this group?',
                        'multiOptions' => $pollOptions,
                        'value' => key($pollOptions),
                    ));
                    $this->auth_poll->getDecorator('Description')->setOption('placement', 'append');
                }
            }
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video') && Engine_Api::_()->authorization()->isAllowed('group', $viewer, 'video')) {
            // Poll
            $videoOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'auth_video');
            $videoOptions = array_intersect_key($availableLabels, array_flip($videoOptions));

            if( !empty($videoOptions) && count($videoOptions) >= 1 ) {
                // Make a hidden field
                if(count($videoOptions) == 1) {
                    $this->addElement('hidden', 'auth_video', array('order' => 108, 'value' => key($videoOptions)));
                    // Make select box
                } else {
                    $this->addElement('Select', 'auth_video', array(
                        'label' => 'Video Creation',
                        'description' => 'Who may create videos for this group?',
                        'multiOptions' => $videoOptions,
                        'value' => key($videoOptions),
                    ));
                    $this->auth_video->getDecorator('Description')->setOption('placement', 'append');
                }
            }
        }

        // Buttons
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array(
                'ViewHelper',
            ),
        ));

        $this->addElement('Cancel', 'cancel', array(
            'label' => 'cancel',
            'link' => true,
            'prependText' => ' or ',
            'decorators' => array(
                'ViewHelper',
            ),
        ));

        $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
            'decorators' => array(
                'FormElements',
                'DivDivDivWrapper',
            ),
        ));
    }
}