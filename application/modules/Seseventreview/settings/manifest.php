<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manifest.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
$module1 = null;
$controller = null;
$action = null;
$request = Zend_Controller_Front::getInstance()->getRequest();
if (!empty($request)) {
  $module1 = $request->getModuleName();
  $action = $request->getActionName();
  $controller = $request->getControllerName();
}
if (empty($request) || !($module1 == 'default' && (strpos($_SERVER['REQUEST_URI'],'/install/') !== false))) {
  $setting = Engine_Api::_()->getApi('settings', 'core');
  $eventsRoute = $setting->getSetting('sesevent.events.manifest', 'events');
  $eventRoute = $setting->getSetting('sesevent.event.manifest', 'event');
}
return array(
    'package' => array(
        'type' => 'module',
        'name' => 'seseventreview',
        'version' => '4.9.4',
        'path' => 'application/modules/Seseventreview',
        'title' => '<span style="color:#DDDDDD">SES- Events Reviews and Ratings Extension</span>',
        'description' => '<span style="color:#DDDDDD">SES- Events Reviews and Ratings Extension</span>',
        'author' => '<a href="http://www.socialenginesolutions.com" style="text-decoration:underline;" target="_blank">SocialEngineSolutions</a>',
        'actions' => array(
            'install',
            'upgrade',
            'refresh',
            'enable',
            'disable',
        ),
        'callback' => array(
            'path' => 'application/modules/Seseventreview/settings/install.php',
            'class' => 'Seseventreview_Installer',
        ),
        'directories' => array(
            'application/modules/Seseventreview',
        ),
        'files' => array(
            'application/languages/en/seseventreview.csv',
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
     'eventreview', 'seseventreview_parameter'
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        'seseventreview_extended' => array(
            'route' => $eventsRoute.'/reviews/:action/:type',
            'defaults' => array(
                'module' => 'seseventreview',
                'controller' => 'index',
                'action' => 'index',
								'type'=>'',
            ),
            'reqs' => array(
                'action' => '(create|delete|browse)',
								'reqs' => array(
                'controller' => '\D+',
            	)
            )
        ),        
        'seseventreview_view' => array(
            'route' => $eventsRoute.'/reviews/:action/:review_id/:slug',
            'defaults' => array(
                'module' => 'seseventreview',
                'controller' => 'index',
                'action' => 'view',
                'slug' => ''
            ),
            'reqs' => array(
						'action' => '(edit|view)',
						'review_id' => '\d+'
            )
        ),
    )
);
?>