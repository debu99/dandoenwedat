<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
return array(
    array(
        'title' => 'SES - Reviews & Ratings - Content Review Profile',
        'description' => 'This widget display on event profile page.',
        'category' => 'SES - Event Reviews & Ratings Plugin',
        'type' => 'widget',
        'name' => 'seseventreview.content-profile-reviews',
				'autoEdit'=>true,
				'adminForm' => array(
            'elements' => array(
               array(
								'MultiCheckbox',
								'stats',
								array(
										'label' => 'Choose the options that you want to be displayed in this widget.',
										'multiOptions' => array(
												"likeCount" => "Likes Count",
												"commentCount" => "Comments Count",
												"viewCount" => "Views Count",
												"title" => "Review Title",
												"share" => "Share Button",
												"report" => "Report Button",
												"pros" => "Pros",
												"cons" => "Cons",
												"description" => "Description",
												"recommended" => "Recommended",
												'postedBy' => "Posted By",		
												'parameter'=>'Review Parameters',
												"creationDate" => "Creation Date",
												'rating' => 'Rating Stars',
										)
								),
						)
            ),
        ),
    ),
    array(
        'title' => 'SES - Reviews & Ratings - Review View Page',
        'description' => 'Displays breadcrumb for Review. This widget should be placed on the SES - Reviews & Ratings - Review View page of the selected content type.',
        'category' => 'SES - Event Reviews & Ratings Plugin',
        'autoEdit' => false,
        'type' => 'widget',
        'name' => 'seseventreview.breadcrumb',
    ),
    array(
        'title' => 'SES - Reviews & Ratings - Profile Options for Content',
        'description' => 'Displays a menu of actions (edit, report, share, etc) that can be performed on a content on its profile. The recommended page for this widget is "SES - Reviews & Ratings - Profile Review Page".',
        'category' => 'SES - Event Reviews & Ratings Plugin',
        'type' => 'widget',
        'name' => 'seseventreview.profile-options',
        'autoEdit' => false,
    ),
    array(
        'title' => 'SES - Reviews & Ratings - Profile Review',
        'description' => 'Displays a member\'s review entries on their profile.',
        'category' => 'SES - Event Reviews & Ratings Plugin',
        'type' => 'widget',
        'name' => 'seseventreview.profile-review',
        'autoedit' => 'true',
        'adminForm' => array(
            'elements' => array(
               array(
								'MultiCheckbox',
								'stats',
								array(
										'label' => 'Choose the options that you want to be displayed in this widget.',
										'multiOptions' => array(
												"likeCount" => "Likes Count",
												"commentCount" => "Comments Count",
												"viewCount" => "Views Count",
												"title" => "Review Title",
												"pros" => "Pros",
												"cons" => "Cons",
												"description" => "Description",
												"recommended" => "Recommended",
												'postedin' => "Posted In",
												"creationDate" => "Creation Date",
												'parameter'=>'Review Parameters',
												'rating' => 'Rating Stars',
												'customfields' => 'Custom Fields Data',
										)
								),
						)
            ),
        ),
    ),
    array(
        'title' => "SES - Reviews & Ratings - Owner's Photo",
        'description' => 'This widget display on "SES - Reviews & Ratings - Review View Page".',
        'category' => 'SES - Event Reviews & Ratings Plugin',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'seseventreview.owner-photo',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'showTitle',
                    array(
                        'label' => 'Member’s Name',
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No'
                        ),
                        'value' => 1,
                    )
                ),
            )
        ),
    ),
);