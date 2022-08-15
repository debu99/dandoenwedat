<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array(
	array(
	  'title' => 'SES - Advanced Events - Buy Event Ticket',
	  'description' => 'Displays a buy event ticket on event view page.',
	  'category' => 'SES - Advanced Events',
	  'type' => 'widget',
	  'name' => 'sesevent.buy-ticket',
	  'defaultParams' => array(
	      'title' => 'Buy Ticket',
	  ),
	  'requirements' => array(
	      'subject' => 'sesevent_event',
	  ),
	  'autoEdit' => true,
	  'adminForm' => array(
			'elements' => array(
			array(
			'Select',
			'type',
			array(
			    'label' => 'Buy Now Type',
			    'multiOptions' => array(
			        'button' => 'Button',
			        'form' => 'Buy Now Form',
			    ),
			    'value' => 'button',
			)
			),
			)
	  ),
	),
	    array(
        'title' => 'SES - Advanced Events - Event Ticket Buyer Details',
        'description' => 'Displays a event ticket buyer list show on event view page.This widget only work when event has ticket.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-ticket-buyer',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'limitdata',
                    array(
                        'label' => 'After how much data want to show view more link.',
                        'value' => '10',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
            ),
        ),
        'requirements' => array(
            'no-subject',
        ),
    ),
    
    array(
        'title' => 'SES - Advanced Events - Buy Event Ticket Mobile',
        'description' => 'Displays a buy event ticket on event view page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.buy-ticket-mobile',
        'defaultParams' => array(
            'title' => 'Buy Ticket',
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
);