<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

  
return array(
   array(
    'title' => 'Social Login Buttons for Sidebar',
    'description' => 'This widget displays all the social media login service networks in the sidebar columns of your website. All the providers which are enabled from the admin panel of this plugin are displayed in the icon design only. This widget should be displayed anywhere on your website in the Left or Right column only.',
    'category' => 'SES - Social Media Login - 1 Click Social Connect',
    'type' => 'widget',
    'name' => 'sessociallogin.sidebar-social-login',
    'autoEdit' => true,
   ),
	 array(
    'title' => 'Social Login Buttons for Login / Signup Pages',
    'description' => 'This widget displays the login buttons for the social media networks which enabled from the admin panel of this plugin. You can edit this widget to configure various settings and choose buttons design. This widget should be placed on the Sign-in Page, Sign-in Required Page or Sign-up Page of your website.',
    'category' => 'SES - Social Media Login - 1 Click Social Connect',
    'type' => 'widget',
    'name' => 'sessociallogin.social-login-buttons',
    'autoEdit' => true,
    'adminForm' => array(
	      'elements' => array(
					array(
						'Radio',
						'design',
						array(
							'label' => 'Choose the design of the social login buttons to be shown in this widget.',
							'multiOptions' => array('1'=>'Design 1','2'=>'Design 2', '3' => "Design 3"),
						  'value' => 1,
						)
					),
					array(
						'Radio',
						'position',
						array(
							'label' => 'Where do you want to show this widget in the Login / Signup form?',
							'multiOptions' => array('1'=>'Left the form','2'=>'Right the form','3'=>'Below the form'),
						  'value' => 1,
						)
					),
					array(
						'Select',
						'label',
						array(
							'label'=>'Do you want to show buttons with icon only or text and icon both?',
              'multiOptions' => array('0'=>'Buttons with Icons Only','1'=>'Buttons with Icons and Text Both'),
						  'value' => '1',
						)
					),
					array(
						'Text',
						'btnwidth',
						array(
							'label' => 'Enter width for the Button when shown with Icon and Text both (in pixels).',
						  'value' => '250',
						)
					),
					array(
						'Text',
						'butontext',
						array(
							'label' => 'Enter the text that you want to show in the buttons. (for the social login provider name use %s.)',
						  'value' => 'Login with %s',
						)
					),
	      )
      ),
   ),
  array(
    'title' => 'Social Login Buttons',
    'description' => 'This widget displays the login buttons for the social media networks which enabled from the admin panel of this plugin. You can edit this widget to configure various settings and choose buttons design. This widget can be placed anywhere on your website.',
    'category' => 'SES - Social Media Login - 1 Click Social Connect',
    'type' => 'widget',
    'name' => 'sessociallogin.socialbuttons',
    'autoEdit' => true,
    'adminForm' => array(
	      'elements' => array(
					array(
						'Radio',
						'design',
						array(
							'label' => 'Choose the design of the social login buttons to be shown in this widget.',
							'multiOptions' => array('1'=>'Design 1','2'=>'Design 2', '3' => "Design 3"),
						  'value' => 1,
						)
					),
					array(
						'Select',
						'label',
						array(
							'label'=>'Do you want to show buttons with icon only or text and icon both?',
              'multiOptions' => array('0'=>'Buttons with Icons Only','1'=>'Buttons with Icons and Text Both'),
						  'value' => '1',
						)
					),
					array(
						'Text',
						'btnwidth',
						array(
							'label' => 'Enter width for the Button when shown with Icon and Text both (in pixels).',
						  'value' => '250',
						)
					),
					array(
						'Text',
						'butontext',
						array(
							'label' => 'Enter the text that you want to show in the buttons. (for the social login provider name use %s.)',
						  'value' => 'Login with %s',
						)
					),
	      )
      ),
   ),
);