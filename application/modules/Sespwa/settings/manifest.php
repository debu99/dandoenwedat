<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

return array (
    'package' =>
    array(
        'type' => 'module',
        'name' => 'sespwa',
        //'sku' => 'sespwa',
        'version' => '5.0.0p1',
		'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '5.0.0',
            ),
        ),
        'path' => 'application/modules/Sespwa',
        'title' => 'SES - Progressive Web App (PWA) Plugin - Interactive Mobile & Tablet Interface',
        'description' => 'SES - Progressive Web App (PWA) Plugin - Interactive Mobile & Tablet Interface',
        'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
        'callback' => array(
            'path' => 'application/modules/Sespwa/settings/install.php',
            'class' => 'Sespwa_Installer',
        ),
        'actions' =>
        array(
            0 => 'install',
            1 => 'upgrade',
            2 => 'refresh',
            3 => 'enable',
            4 => 'disable',
        ),
        'directories' =>
        array(
            'application/modules/Sespwa',
            'sespwa/images',
            'application/themes/sespwa',
        ),
        'files' =>
        array(
            'application/languages/en/sespwa.csv',
            'sespwa-service-worker.js',
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'getAdminNotifications',
            'resource' => 'Sespwa_Plugin_Core',
        ),
        array(
            'event' => 'onCorePageDeleteBefore',
            'resource' => 'Sespwa_Plugin_Core',
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'sespwa_slide', 'sespwa_banner',
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(

    )
);
