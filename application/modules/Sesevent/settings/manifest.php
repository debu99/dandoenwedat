<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
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
$eventsRoute = $eventRoute = "";
if (empty($request) || !($module1 == 'default' && (strpos($_SERVER['REQUEST_URI'],'/install/') !== false))) {
  $setting = Engine_Api::_()->getApi('settings', 'core');
  $eventsRoute = $setting->getSetting('sesevent.events.manifest', 'events');
  $eventRoute = $setting->getSetting('sesevent.event.manifest', 'event');
}
return array(
    // Package -------------------------------------------------------------------
	  'package' =>
	  array (
	    'type' => 'module',
	    'name' => 'sesevent',
	    //'sku' => 'sesevent',
	    'version' => '5.0.0p1',
		'dependencies' => array(
            array(
                'type' => 'module',
                'name' => 'core',
                'minVersion' => '5.0.0',
            ),
        ),
	    'path' => 'application/modules/Sesevent',
	    'title' => 'SES - Advanced Events Plugin',
	    'description' => 'SES - Advanced Events Plugin',
	    'author' => '<a href="https://socialnetworking.solutions" style="text-decoration:underline;" target="_blank">SocialNetworking.Solutions</a>',
	    'callback' => array(
	      'path' => 'application/modules/Sesevent/settings/install.php',
	      'class' => 'Sesevent_Installer',
	    ),
	    'actions' =>
	    array (
	      0 => 'install',
	      1 => 'upgrade',
	      2 => 'refresh',
	      3 => 'enable',
	      4 => 'disable',
	    ),
	    'directories' =>
	    array (
	      0 => 'application/modules/Sesevent',
	      1 => 'application/modules/Seseventreview',
	    ),
	    'files' =>
	    array (
	      0 => 'application/languages/en/sesevent.csv',
	      1 => 'application/languages/en/seseventreview.csv',
	    ),
	  ),
		// Compose
    'composer' => array(
        'seseventphoto' => array(
            'script' => array('_composeSeseventAlbum.tpl', 'sesevent'),
            'plugin' => 'Sesevent_Plugin_Albumcomposer',
        ),
				'sesevent' => array(
            'script' => array('_composeEvent.tpl', 'sesevent'),
            'auth' => array('sesevent_event', 'create'),
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onStatistics',
            'resource' => 'Sesevent_Plugin_Core'
        ),
        array(
            'event' => 'onUserDeleteBefore',
            'resource' => 'Sesevent_Plugin_Core',
        ),
        array(
            'event' => 'getActivity',
            'resource' => 'Sesevent_Plugin_Core',
        ),
        array(
            'event' => 'addActivity',
            'resource' => 'Sesevent_Plugin_Core',
        ),
        array(
            'event' => 'onRenderLayoutDefault',
            'resource' => 'Sesevent_Plugin_Core',
        ),
        array(
            'event' => 'onUserUpdateAfter',
            'resource' => 'Sesevent_Plugin_Core',
        ),
				array(
            'event' => 'onRenderLayoutDefaultSimple',
            'resource' => 'Sesevent_Plugin_Core'
        ),
				array(
            'event' => 'onRenderLayoutMobileDefault',
            'resource' => 'Sesevent_Plugin_Core'
        ),
				array(
            'event' => 'onRenderLayoutMobileDefaultSimple',
            'resource' => 'Sesevent_Plugin_Core'
        ),
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'event',
        'sesevent_event',
        'sesevent_order',
        'sesevent_album',
        'sesevent_category',
        'sesevent_photo',
        'sesevent_ticket',
				'sesevent_orderticket',
        'sesevent_post',
        'sesevent_topic',
        'sesevent_host', '
				 sesevent_orders',
        'sesevent_gateway',
        'sesevent_usergateway',
        'sesevent_userpayrequest',
        'sesevent_remainingpayment',
				'sesevent_sponsorship',
				'sesevent_sponsorshiporder',
				'sesevent_sponsorshipmember',
				'sesevent_usersponsorshippayrequest',
				'sesevent_sponsorshiprequest',
				'sesevent_sponsorshipdetail',
				'sesevent_dashboards',
				'sesevent_list',
				'sesevent_listevent',
				'sesevent_speakers',
				'sesevent_slidephoto',
				'sesevent_integrateothermodule',
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
				'sesevent_profile' => array(
            'route' => $eventRoute.'/:id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'profile',
                'action' => 'index',
            ),
        ),
        /*'sesevent_speakers' => array(
            'route' => $eventsRoute.'/:controller/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'speaker',
                'action' => 'index',
            ),
            'reqs' => array(
                'speaker_id' => '\d+',
            )
        ),*/
        'sesevent_extended' => array(
            'route' => $eventsRoute.'/:controller/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'index',
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
        'sesevent_ticket' => array(
            'route' => $eventsRoute.'/ticket/:action/:event_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'ticket',
                'action' => 'buy',
            ),
            'reqs' => array(
                'action' => '(buy)',
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
        'sesevent_my_ticket' => array(
            'route' => $eventsRoute.'/tickets/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'ticket',
                'action' => 'my-tickets',
            ),
            'reqs' => array(
                'action' => '(my-tickets)',
            )
        ),
        'sesevent_order' => array(
            'route' => $eventsRoute.'/order/:action/:event_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'order',
                'action' => 'index',
            ),
            'reqs' => array(
                'action' => '(index|checkout|process|return|finish|success|view|print-ticket|free-order|print-invoice|checkorder|email-ticket)',
            )
        ),
        'sesevent_dashboard' => array(
            'route' => $eventsRoute.'/dashboard/:action/:event_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'dashboard',
                'action' => 'edit',
            ),
            'reqs' => array(
                'action' => '(edit|manage-ticket|contact-information|create-ticket|edit-ticket|delete-ticket|currency-converter|seo|event-termcondition|account-details|sales-stats|manage-orders|sales-reports|payment-requests|payment-request|delete-payment|detail-payment|payment-transaction|show-blog-request|ticket-information|search-ticket|style|overview|mainphoto|remove-mainphoto|edit-photo|remove-photo|backgroundphoto|remove-backgroundphoto)',
            )
        ),
				'sesevent_sponsorship' => array(
            'route' => $eventsRoute.'/sponsorships/:action/:event_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'sponsorship',
                'action' => 'create',
            ),
            'reqs' => array(
                'action' => '(edit|manage-sponsorship|create|delete-sponsorship|sales-stats|sales-reports|payment-requests|payment-request|delete-payment|detail-payment|payment-transaction|manage-orders|checkout|free-sponsorship|success|process|return|finish|ping-plus|view|payment-request|delete-payment|payment-transaction|sponsorship-request|request-sponsorship|delete-request|email-user|view-request|details|view-sponsorship)',
            )
        ),
				'sesevent_sponsorship_view' => array(
            'route' => $eventsRoute.'/sponsorship/:event_id/:id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'sponsorship',
                'action' => 'view-sponsorship',
            )
        ),
        'sesevent_general' => array(
            'route' => $eventsRoute.'/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'welcome',
            ),
            'reqs' => array(
                'action' => '(welcome|index|home|browse|create|delete|list|manage|edit|locations|tags|calender|saved-event|link-blog|browse-host)',
            )
        ),
        'sesevent_specific' => array(
            'route' => $eventsRoute.'/:action/:event_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'event',
                'action' => 'index',
            ),
            'reqs' => array(
                'action' => '(edit|delete|join|leave|invite|accept|style|reject|message)',
                'event_id' => '\d+',
            )
        ),
        'sesevent_upcoming' => array(
            'route' => $eventsRoute.'/upcoming/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'upcoming',
                'filter' => 'future'
            )
        ),
        'sesevent_past' => array(
            'route' => $eventsRoute.'/past/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'past',
                'filter' => 'past'
            )
        ),
        'sesevent_category_view' => array(
            'route' => $eventsRoute.'/category/:category_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'category',
                'action' => 'index',
            )
        ),
        'sesevent_category' => array(
            'route' => $eventsRoute.'/categories/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'category',
                'action' => 'browse',
            ),
            'reqs' => array(
                'action' => '(index|browse)',
            )
        ),
        'sesevent_photo' => array(
            'route' => $eventRoute.'/index/:action',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'upload-photo',
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
				 'sesevent_photo_view' => array(
            'route' => $eventRoute.'/photo/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'photo',
                'action' => 'view',
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
        'sesevent_specific_album' => array(
					'route' =>  $eventRoute.'-album/:action/:album_id',
					'defaults' => array(
							'module' => 'sesevent',
							'controller' => 'album',
							'action' => 'view',
						),
							'reqs' => array(
							'album_id' => '\d+'
						)
        ),
        'sesevent_viewhost' => array(
            'route' => $eventRoute.'/host/:action/:host_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'viewhost',
            )
        ),
				'sesevent_host' => array(
            'route' => $eventsRoute.'/host/:action/:host_id/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'host',
                'action' => 'edit',
            ),
            'reqs' => array(
                'action' => '(edit|delete)',
								'host_id' => '\d+'
            )
        ),
        'sesevent_list' => array(
            'route' => $eventsRoute.'/lists/:action',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'list',
                'action' => 'browse',
            ),
            'reqs' => array(
                'action' => '(add)',
            )
        ),
        'sesevent_list_view' => array(
            'route' => $eventsRoute.'/list/:list_id/:slug/:action/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'list',
                'action' => 'view',
                'slug' => '',
            ),
            'reqs' => array(
                'list_id' => '\d+',
                'action' => '(edit|delete)',
            )
        ),
        'sesevent_account_details' => array(
          'route' => $eventsRoute . '/:event_id/gateway_type/*',
          'defaults' => array(
              'module' => 'sesevent',
              'controller' => 'dashboard',
              'action' => 'account-details',
          ),
        ),
        'sesevent_event_favourite' => array(
            'route' => $eventsRoute . '/favourite/*',
            'defaults' => array(
                'module' => 'sesevent',
                'controller' => 'index',
                'action' => 'favourite',
            )
        ),
    )
);
?>
