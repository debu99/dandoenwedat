<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array(
    'package' => array(
        'type' => 'module',
        'name' => 'sesbasic',
        'sku' => 'sesbasic',
        'version' => '5.0.0',
        'path' => 'application/modules/Sesbasic',
        'title' => 'SocialEngineSolutions Basic Required Plugin',
        'description' => 'SocialEngineSolutions Basic Required Plugin',
        'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            'enable',
            'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/Sesbasic/settings/install.php',
            'class' => 'Sesbasic_Installer',
        ),
        'directories' => array(
            'application/modules/Sesbasic',
        ),
        'files' => array(
            'application/languages/en/sesbasic.csv',
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Sesbasic_Plugin_Core',
        ),
				array(
            'event' => 'onRenderLayoutDefaultSimple',
            'resource' => 'Sesbasic_Plugin_Core'
        ),
				array(
            'event' => 'onRenderLayoutMobileDefault',
            'resource' => 'Sesbasic_Plugin_Core'
        ),
				array(
            'event' => 'onRenderLayoutMobileDefaultSimple',
            'resource' => 'Sesbasic_Plugin_Core'
        ),
		array(
            'event' => 'onUserFormSignupAccountInitAfter',
            'resource' => 'Sesbasic_Plugin_Core',
        ),
		array(
            'event' => 'onUserFormSettingsGeneralInitAfter',
            'resource' => 'Sesbasic_Plugin_Core',
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'sesbasic_integrateothermodule','sesbasic_location', 'sesbasic_usergateway','sesbasic_menusicon','sesbasic_userdetail','sesbasic_gateway'
    ),
		 // Routes --------------------------------------------------------------------
    'routes' => array(
        // User - General
        'sesbasic_extended' => array(
          'route' => 'members-details/:controller/:action/*',
          'defaults' => array(
            'module' => 'sesbasic',
            'controller' => 'index',
            'action' => 'account-details'
          ),
        ),
        'sesbasic_get_direction' => array(
            'route' => 'directions/:action/*',
            'defaults' => array(
                'module' => 'sesbasic',
                'controller' => 'index',
                'action' => 'get_direction',
            ),
        ),
		'sesbasic_message' => array(
            'route' => 'sesbasic/index/message/*',
            'defaults' => array(
                'module' => 'sesbasic',
                'controller' => 'my',
                'action' => 'message',
            ),
        ),
	),
);
?>
