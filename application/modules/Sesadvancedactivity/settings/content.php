<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

return array(
  array(
    'title' => 'SNS - Welcome Tab Sections',
    'description' => 'This widget displays the welcome tab sections as chosen by editing the widget. You can place this widget anywhere on your website.',
    'category' => 'SNS - Professional Activity & Nested Comments Plugin',
    'type' => 'widget',
    'name' => 'sesadvancedactivity.welcometab-sections',
    'autoEdit' => true,
    'adminForm' => array(
      'elements' => array(
        array(
          'hidden',
          'title',
          array(
            'label' => ''
          )
        ),
        array(
          'Select',
          'displaysections',
          array(
            'label' => 'Choose the section to be shown in this widget.',
            'multiOptions' => array(
              1 => 'Profile Photo Upload',
              2 => 'Friend Requests',
              3 => 'Find Friends',
              4 => "Tab Heading",
            ),
            'value' => 1,
          )
        ),
      ),
    ),
  ),
  array(
    'title' => 'SNS - Advanced Activity Profile Links',
    'description' => 'Displays a member\'s, group\'s, or event\'s links posted on their profiles.',
    'category' => 'SNS - Professional Activity & Nested Comments Plugin',
    'type' => 'widget',
    'name' => 'sesadvancedactivity.profile-links',
    'isPaginated' => true,
    'defaultParams' => array(
      'title' => 'Links',
      'titleCount' => true,
    ),
    'requirements' => array(
      'subject',
    ),
  ),
  array(
    'title' => 'SNS - Advanced Activity Feed',
    'description' => 'Displays the advanced news and activity feeds.',
    'category' => 'SNS - Professional Activity & Nested Comments Plugin',
    'type' => 'widget',
    'name' => 'sesadvancedactivity.feed',
    'defaultParams' => array(
      'title' => 'What\'s New',
    ),
    'autoEdit' => true,
    'adminForm' => 'Sesadvancedactivity_Form_Admin_Settings_FeedSettings',
  ),
  array(
    'title' => 'SNS - Memories On This Day Banner',
    'description' => 'Displays the banner on the Memories On This Day page.',
    'category' => 'SNS - Professional Activity & Nested Comments Plugin',
    'type' => 'widget',
    'name' => 'sesadvancedactivity.onthisday-banner',
    'defaultParams' => array(
      'title' => '',
    ),
    'autoEdit' => false,
  ),
  array(
    'title' => 'SNS - Trending Hashtags',
    'description' => 'Displays the top trending hashtags.',
    'category' => 'SNS - Professional Activity & Nested Comments Plugin',
    'type' => 'widget',
    'name' => 'sesadvancedactivity.top-trends',
    'defaultParams' => array(
      'title' => 'Top Trends',
    ),
    'autoEdit' => true,
    'adminForm' => array(
        'elements' => array(
            array(
                'Text',
                'limit',
                array(
                    'label' => 'How Many treding hashtags do you want to show in this widget?',
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                    'value'=>10,
                ),
            ),
        ),
    ),
  ),
);
