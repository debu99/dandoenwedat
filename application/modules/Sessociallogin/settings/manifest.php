<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

return array(
	'package' => array(
			'type' => 'module',
			'name' => 'sessociallogin',
			'version' => '5.0.1',
            'dependencies' => array(
                array(
                    'type' => 'module',
                    'name' => 'core',
                    'minVersion' => '5.0.0',
                ),
            ),
			'path' => 'application/modules/Sessociallogin',
			'title' => 'SES - Social Media Login - 1 Click Social Connect Plugin',
			'description' => 'SES - Social Media Login - 1 Click Social Connect Plugin',
			'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
			'actions' => array(
					'install',
					'upgrade',
					'refresh',
					'enable',
					'disable',
			),
			'callback' => array(
					'path' => 'application/modules/Sessociallogin/settings/install.php',
					'class' => 'Sessociallogin_Installer',
			),
			'directories' =>
			array(
					0 => 'application/modules/Sessociallogin',
			),
			'files' => array(
        'application/languages/en/sessociallogin.csv',
			),
	),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onUserCreateAfter',
      'resource' => 'Sessociallogin_Plugin_Core',
    ),
    array(
        'event' => 'onUserLogoutAfter',
        'resource' => 'Sessociallogin_Plugin_Core',
    ),
    array(
        'event' => 'onRenderLayoutDefault',
        'resource' => 'Sessociallogin_Plugin_Core',
    ),
    array(
        'event' => 'onUserDeleteAfter',
        'resource' => 'Sessociallogin_Plugin_Core',
    ),
  ),
  // Routes --------------------------------------------------------------------
    'routes' => array(
        'sessocial_quick_login' => array(
            'route' => 'sessociallogin/quick/signup/',
            'defaults' => array(
                'module' => 'sessociallogin',
                'controller' => 'quick',
                'action' => 'signup'
            ),
        ),
    )
);
