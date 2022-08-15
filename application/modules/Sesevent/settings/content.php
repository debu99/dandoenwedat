<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: content.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


  $socialshare_enable_plusicon = array(
      'Select',
      'socialshare_enable_plusicon',
      array(
          'label' => "Enable More Icon for social share buttons?",
          'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
          ),
      )
  );
  $socialshare_icon_limit = array(
    'Text',
    'socialshare_icon_limit',
    array(
      'label' => 'Count (number of social sites to show). If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
      'value' => 2,
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      ),
    ),
  );


$viewType = array(
    'MultiCheckbox',
    'enableTabs',
    array(
        'label' => "Choose the View Type.",
        'multiOptions' => array(
            'list' => 'List View',
            'grid' => 'Grid View',
						'advgrid' => 'Advanced Grid View',
            'pinboard' => 'Pinboard View',
            'masonry' => 'Masonry View',
            'map' => 'Map View',
        ),
    )
);
$defaultType = array(
    'Select',
    'openViewType',
    array(
        'label' => "Default open View Type (apply if select Both View option in above tab)?",
        'multiOptions' => array(
            'list' => 'List View',
            'grid' => 'Grid View',
						'advgrid' => 'Advanced Grid View',
            'pinboard' => 'Pinboard View',
            'masonry' => 'Masonry View',
            'map' => 'Map View',
        ),
        'value' => 'list',
    )
);
$menuOption = array(
    'Radio',
    'showTabType',
    array(
        'label' => 'Show Tab Type?',
        'multiOptions' => array(
            '0' => 'Default',
            '1' => 'Custom'
        ),
        'value' => 1,
    )
);
$limitData = array(
    'Text',
    'limit_data',
    array(
        'label' => 'count (number of events to show).',
        'value' => 20,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$pagging = array(
    'Radio',
    'pagging',
    array(
        'label' => "Do you want the events to be auto-loaded when users scroll down the page?",
        'multiOptions' => array(
            'button' => 'View more',
            'auto_load' => 'Auto Load',
            'pagging' => 'Pagination'
        ),
        'value' => 'auto_load',
    )
);
$titleTruncationList = array(
    'Text',
    'list_title_truncation',
    array(
        'label' => 'Title truncation limit for List View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$titleTruncationGrid = array(
    'Text',
    'grid_title_truncation',
    array(
        'label' => 'Title truncation limit for Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$titleTruncationAdvGrid = array(
    'Text',
    'advgrid_title_truncation',
    array(
        'label' => 'Title truncation limit for Advanced Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$titleTruncationPinboard = array(
    'Text',
    'pinboard_title_truncation',
    array(
        'label' => 'Title truncation limit for Pinboard View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$titleTruncationMasonry = array(
    'Text',
    'masonry_title_truncation',
    array(
        'label' => 'Title truncation limit for Masonry View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$DescriptionTruncationList = array(
    'Text',
    'list_description_truncation',
    array(
        'label' => 'Description truncation limit for List View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$DescriptionTruncationMasonry = array(
    'Text',
    'masonry_description_truncation',
    array(
        'label' => 'Description truncation limit for Masonry View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$DescriptionTruncationGrid = array(
    'Text',
    'grid_description_truncation',
    array(
        'label' => 'Description truncation limit for Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$DescriptionTruncationPinboard = array(
    'Text',
    'pinboard_description_truncation',
    array(
        'label' => 'Description truncation limit for Pinboard View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
);
$heightOfContainer = array(
    'Text',
    'height',
    array(
        'label' => 'Enter the height of List block (in pixels).',
        'value' => '160',
    )
);
$widthOfContainer = array(
    'Text',
    'width',
    array(
        'label' => 'Enter the width of List block (in pixels).',
        'value' => '250',
    )
);
$heightOfGridPhotoContainer = array(
    'Text',
    'photo_height',
    array(
        'label' => 'Enter the height of grid photo block (in pixels).',
        'value' => '160',
    )
);
$widthOfGridPhotoContainer = array(
    'Text',
    'photo_width',
    array(
        'label' => 'Enter the width of grid photo block (in pixels).',
        'value' => '250',
    )
);
$heightOfGridInfoContainer = array(
    'Text',
    'info_height',
    array(
        'label' => 'Enter the height of grid info block (in pixels).',
        'value' => '160',
    )
);

$heightOfAdvGridPhotoContainer = array(
    'Text',
    'advgrid_height',
    array(
        'label' => 'Enter the height of advanced grid block (in pixels).',
        'value' => '322',
    )
);
$widthOfAdvGridPhotoContainer = array(
    'Text',
    'advgrid_width',
    array(
        'label' => 'Enter the width of advanced grid block (in pixels).',
        'value' => '322',
    )
);


$widthOfPinboardContainer = array(
    'Text',
    'pinboard_width',
    array(
        'label' => 'Enter the width of pinboard block (in pixels).',
        'value' => '250',
    )
);
$heightOfMasonryContainer = array(
    'Text',
    'masonry_height',
    array(
        'label' => 'Enter the height of masonry block (in pixels).',
        'value' => '250',
    )
);
$showCustomData = array(
    'MultiCheckbox',
    'show_criteria',
    array(
        'label' => "Data show in widget ?",
        'multiOptions' => array(
            'featuredLabel' => 'Featured Label',
            'sponsoredLabel' => 'Sponsored Label',
			'verifiedLabel' => 'Verified Label',
			'listButton' => "Add List Button",
            'favouriteButton' => 'Favourite Button',
            'likeButton' => 'Like Button',
            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
			'listButton'=>'List Button',
			'joinedcount'=>'Joined Guest Counts',
            'like' => 'Likes',
			'location' => 'Location',
            'comment' => 'Comments',
            'favourite' => 'Favourite Count',
			'buy' => 'Buy Button',
            'rating' => 'Ratings',
            'view' => 'Views',
            'title' => 'Titles',
			'startenddate'=>'Start End Date of Event',
            'category' => 'Category',
            'by' => 'Item Owner Name',
			'host' =>'Item Host Name',
            'listdescription' => 'Description (List View)',
			'griddescription' => 'Description (Grid View)',
			'pinboarddescription' => 'Description (Pinboard View)',
			'commentpinboard' =>'Comment in Pinboard',
        ),
        'escape' => false,
    )
);
return array(
    array(
        'title' => 'SES - Advanced Events - Popular / Featured / Sponsored Event Hosts',
        'description' => "Displays hosts as chosen by you based on chosen criteria for this widget. The placement of this widget depends on the criteria chosen for this widget.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.featured-sponsored-host',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'grid' => 'Grid View',
                        ),
                    )
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Display Content",
                        'multiOptions' => array(
                            '5' => 'All including Featured and Sponsored',
                            '1' => 'Only Featured',
                            '2' => 'Only Sponsored',
														'6' => 'Only Verified',
                            '3' => 'Both Featured and Sponsored',
                            '4' => 'All except Featured and Sponsored',
                        ),
                        'value' => 5,
                    )
                ),
                array(
                    'Select',
                    'info',
                    array(
                        'label' => 'Choose Popularity Criteria.',
                        'multiOptions' => array(
                            "creation_date" => "Recently Created",
                            "most_viewed" => "Most Viewed",
                            "favourite_count" => "Most Favourite",
														"most_event" =>'Maximum Events Hosted',
                        )
                    ),
                    'value' => 'creation_date',
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => 'Choose the options that you want to be displayed in this widget".',
                        'multiOptions' => array(
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
														'follow' => 'Follow Count',
														'hostEventCount' =>'Associated Event Count',
                            'host' =>'Host Name',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'favouriteButton' => 'Favourite Button',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
								    'Text',
								    'grid_title_truncation',
								    array(
								        'label' => 'Title truncation limit for Grid View.',
								        'value' => 45,
								        'validators' => array(
								            array('Int', true),
								            array('GreaterThan', true, array(0)),
								        )
								    )
								),
                array(
								    'Text',
								    'list_title_truncation',
								    array(
								        'label' => 'Title truncation limit for List View.',
								        'value' => 45,
								        'validators' => array(
								            array('Int', true),
								            array('GreaterThan', true, array(0)),
								        )
								    )
								),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Select',
                    'contentInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                  'Select',
                  'mouseOver',
                  array(
                    'label' => "Show Grid View Data on Mouse Over",
                    'multiOptions' => array(
                        '1' => 'Yes,show data on Mouse Over',
                        '0' => 'No,don\'t show data on Mouse Over',
                    ),
										'value'=>'1',
                  )
                ),
            )
        ),
    ),
		array(
        'title' => 'SES - Advanced Events - Popular / Featured / Sponsored Event Hosts Carousel',
        'description' => "Displays hosts carousel as chosen by you based on chosen criteria for this widget. The placement of this widget depends on the criteria chosen for this widget.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.featured-sponsored-host-carousel',
        'adminForm' => array(
            'elements' => array(
								array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "Carousel View Type",
                        'multiOptions' => array(
                            'horizontal' => 'Horizontal',
                            'vertical' => 'Vertical',
                        ),
                        'value' => 'horizontal',
                    ),
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Display Content",
                        'multiOptions' => array(
                            '5' => 'All including Featured and Sponsored',
                            '1' => 'Only Featured',
                            '2' => 'Only Sponsored',
														'6' => 'Only Verified',
                            '3' => 'Both Featured and Sponsored',
                            '4' => 'All except Featured and Sponsored',
                        ),
                        'value' => 5,
                    )
                ),
                array(
                    'Select',
                    'info',
                    array(
                        'label' => 'Choose Popularity Criteria.',
                        'multiOptions' => array(
                            "creation_date" => "Recently Created",
                            "most_viewed" => "Most Viewed",
                            "favourite_count" => "Most Favourite",
														"most_event" =>'Maximum Events Hosted',
                        )
                    ),
                    'value' => 'creation_date',
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => 'Choose the options that you want to be displayed in this widget".',
                        'multiOptions' => array(
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
														'follow' => 'Follow Count',
														'hostEventCount' =>'Associated Event Count',
                            'host' =>'Host Name',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'favouriteButton' => 'Favourite Button',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
								    'Text',
								    'grid_title_truncation',
								    array(
								        'label' => 'Title truncation limit for Grid View.',
								        'value' => 45,
								        'validators' => array(
								            array('Int', true),
								            array('GreaterThan', true, array(0)),
								        )
								    )
								),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Select',
                    'contentInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                  'Select',
                  'mouseOver',
                  array(
                    'label' => "Show Grid View Data on Mouse Over",
                    'multiOptions' => array(
                        '1' => 'Yes,show data on Mouse Over',
                        '0' => 'No,don\'t show data on Mouse Over',
                    ),
										'value'=>'1',
                  )
                ),
            )
        ),
		),
    array(
        'title' => 'SES - Advanced Events - Host of the Day',
        'description' => 'This widget displays host as \'Host Member of the Day\' randomly as choosen by you from the admin panel of this plugin.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.hostoftheday',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'infoshow',
                    array(
                        'label' => 'Choose the options that you want to be displayed in this widget".',
                        'multiOptions' => array(
                          'featured' => 'Featured Label',
                          'sponsored' => 'Sponsored Label',
                          'verified' => 'Verified Label',
									        'view' => 'Views Count',
                          'favourite' => 'Favourite Count',
                          'follow' => 'Follow Count',
                          'hostEventCount' => 'Associated Event Count',
									        'favouriteButton' => 'Favourite Button',
                          'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height (in pixels).',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width (in pixels).',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                array(
                    'Select',
                    'contentInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                  'Select',
                  'mouseOver',
                  array(
                    'label' => "Show Grid View Data on Mouse Over",
                    'multiOptions' => array(
                        '1' => 'Yes,show data on Mouse Over',
                        '0' => 'No,don\'t show data on Mouse Over',
                    ),
										'value'=>'1',
                  )
                ),
            ),
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Host Browse Search',
        'description' => 'Displays a search form in the hosts browse page. Edit this widget to choose the search option to be shown in the search form.',
        'category' => 'SES - Advanced Events',
        'autoEdit' => true,
        'type' => 'widget',
        'name' => 'sesevent.host-browse-search',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'searchOptionsType',
                    array(
                        'label' => "Choose from below the searching options that you want to show in this widget.",
                        'multiOptions' => array(
                            'searchBox' => 'Search host',
                            'show' => 'List By',
                        ),
                    )
                ),
								array(
                    'MultiCheckbox',
                    'search_type',
                    array(
                        'label' => "Choose options to be shown in \'List By\' search fields.",
                        'multiOptions' => array(
                            "creationSPdate" => "Recently Created",
														 "featured" => "Featured Hosts",
														 "sponsored" => "Sponsored Hosts",
														 "viewSPcount" => "Most Viewed",
														 "favouriteSPcount" => "Most Favourite",
														 "verified" =>'Only Verified',
														 'mostSPevent'=>'Maximum Events Hosted',
                        ),
                    )
                ),
                array(
                    'Select',
                    'default_search_type',
                    array(
                        'label' => "Default \'List By\' search fields.",
                        'multiOptions' => array(
														 "" => "",
                             "creationSPdate" => "Recently Created",
														 "featured" => "Featured Hosts",
														 "sponsored" => "Sponsored Hosts",
														 "viewSPcount" => "Most Viewed",
														 "favouriteSPcount" => "Most Favourite",
														 "verified" =>'Only Verified',
														 'mostSPevent'=>'Maximum Events Hosted',
                        ),
                    )
                ),
            )
        ),
    ),
		array(
        'title' => 'SES - Advanced Events - Browse Hosts',
        'description' => 'Displays all hosts on your website. The recommended page for this widget is "Advanced Events - Browse Hosts Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.browse-hosts',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'paginationType',
                    array(
                        'label' => 'Do you want hosts to be auto-loaded when users scroll down the page?',
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No, show \'View More\'',
														'pagging' =>'Pagging',
                        ),
                        'value' => 1,
                    )
                ),
								array(
                    'Select',
                    'list_count',
                    array(
                        'label' => 'Do you want to show host count?',
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No',
                        ),
                        'value' => 1,
                    )
                ),
								array(
										'Text',
										'title_truncation',
										array(
												'label' => 'Title truncation limit for Host name.',
												'value' => 45,
												'validators' => array(
														array('Int', true),
														array('GreaterThan', true, array(0)),
												)
										)
								),
								array(
                    'Select',
                    'popularity',
                    array(
                        'label' => 'Choose Popularity Criteria.',
                        'multiOptions' => array(
                            "creation_date" => "Recently Created",
                            "featured" => "Featured Hosts",
                            "sponsored" => "Sponsored Hosts",
                            "view_count" => "Most Viewed",
                            "favourite_count" => "Most Favourite",
														"verified" =>'Only Verified',
														'most_event'=>'Maximum Events Hosted',
                        )
                    ),
                    'value' => 'creation_date',
                ),
                array(
                    'MultiCheckbox',
                    'information',
                    array(
                        'label' => 'Choose the options that you want to be displayed in this widget".',
                        'multiOptions' => array(
									        'featuredLabel' => 'Featured Label',
									        'sponsoredLabel' => 'Sponsored Label',
									        'verifiedLabel' => 'Verified Label',
									        'view' => 'Views Count',
                          'favourite' => 'Favourite Count',
                          'follow' => 'Follow Count',
                          'hostEventCount' => 'Associated Event Count',
									        'favouriteButton' => 'Favourite Button',
                          'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height for Grid View (in pixels).',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width for Grid View (in pixels).',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                array(
                    'Select',
                    'contentInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                  'Select',
                  'mouseOver',
                  array(
                    'label' => "Show Grid View Data on Mouse Over",
                    'multiOptions' => array(
                        '1' => 'Yes,show data on Mouse Over',
                        '0' => 'No,don\'t show data on Mouse Over',
                    ),
										'value'=>'1',
                  )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => 'Count (number of content to show)',
                        'value' => 2,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Host Profile Page',
        'description' => 'This widget displays all details of a non-site host member.The recommended page for this widget is "SES - Advanced Events - Non-Site Host Profile Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.profile-nonsitehost',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'infoshow',
                    array(
                        'label' => 'Choose from below the details that you want to show in this widget. Some options do not show in case site member.',
                        'multiOptions' => array(
                            'profilePhoto' => "Profile Photo",
                            'displayname' => 'Display Name',
                            'detaildescription' => 'About Member (Detailed Description)',
                            'phone' => 'Phone',
                            'email' => 'Email',
                            'view' => 'View Count',
                            'favourite' => 'Favourite Count',
                            'hostEventCount' => 'Associated Event Count',
                            'follow' => 'Follow Count',
                            'website' => 'Website',
                            'facebook' => 'Facebook Icon',
                            'twitter' => 'Twitter Icon',
                            'linkdin' => 'Linkdin Icon',
                            'googleplus' => 'Google Plus Icon',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'followButton' => 'Follow Button',
                            'favouriteButton' => 'Favourite Button',
	                          'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Events Photos',
        'description' => 'Displays a event\'s photos on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-photos',
    ),
    array(
        'title' => 'SES - Advanced Events - Popular Events Carousel',
        'description' => 'Displays events based on chosen criteria for this widget. The placement of this widget depends on the criteria chosen for this widget.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.popular-events-carousel',
        'adminForm' => array(
            'elements' => array(
                array(
									'Radio',
									'showOptionsType',
									array(
											'label' => "Show",
											'multiOptions' => array(
													'all' => 'Popular Event [With this option, place this widget anywhere on your website. Choose criteria from "Popularity Criteria" setting below.]',
													'recommanded' => 'Recommended Event [With this option, place this widget anywhere on your website.]',
													'other' => 'Member’s Other Events [With this option, place this widget on Advanced Events - Event View Page.]',
											),
											'value' => 'all',
									),
                ),

                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoing' => 'Ongoing Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
													'past' => 'Past Events',
													'week' => 'This Week',
													'weekend' => 'This Weekends',
													'future' => 'Upcomming Events',
													'month' => 'This Month',
											),
											'value' => '',
									)
                ),
                array(
									'Select',
									'popularity',
									array(
											'label' => 'Popularity Criteria',
											'multiOptions' => array(
													'featured' => 'Only Featured',
													'sponsored' => 'Only Sponsored',
													'verified' => 'Only Verified',
													'view_count' => 'Most Viewed',
													'favourite_count' => "Most Favorite",
													'comment_count' => "Most Commented",
													'like_count' => "Most Liked",
													'creation_date' => 'Most Recent',
													'modified_date' => 'Recently Updated',
													'most_rated' => 'Most Rated (work if rating extention enable otherwise creation_date criteria work)'
											),
											'value' => 'creation_date',
									)
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                        ),
                        'escape' => false,
                    )
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
                array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'horizontal' => 'Horizontal',
                            'vertical' => 'Vertical',
                        ),
                        'value' => 'horizontal',
                    ),
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event People Also Liked',
        'description' => 'Displays a list of other events that the people who liked this event also liked.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.show-also-liked',
        'defaultParams' => array(
            'title' => 'People Also Liked',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                 array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'gridInside' => 'Grid View',
														'gridOutside'=>'Advanced Grid View',
                        ),
                    )
                ),
								  array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside (setting work only if you select \"Grid View\" in above setting) ",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
                            'host' =>'Item Host Name',
                            'startenddate'=>'Start End Date of Event',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
														'buy'=>'Buy Button (grid outside view only)',
														'rating'=>'Rating Stars',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Other Events From Member',
        'description' => 'Displays a list of other events that the member that uploaded this event uploaded. This widget is placed on Event Profile Page Only.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.show-same-poster',
        'defaultParams' => array(
            'title' => 'From the same member',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                 array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'gridInside' => 'Grid View',
														'gridOutside'=>'Advanced Grid View',
                        ),
                    )
                ),
								  array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside (setting work only if you select \"Grid View\" in above setting) ",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
                            'host' =>'Item Host Name',
                            'startenddate'=>'Start End Date of Event',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
														'buy'=>'Buy Button (grid outside view only)',
														'rating'=>'Rating Stars',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Similar Events',
        'description' => 'Displays a list of other events that are similar to the current event, based on tags.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.show-same-tags',
        'defaultParams' => array(
            'title' => 'Similar Events',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                 array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'gridInside' => 'Grid View',
														'gridOutside'=>'Advanced Grid View',
                        ),
                    )
                ),
								  array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside (setting work only if you select \"Grid View\" in above setting) ",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
                            'host' =>'Item Host Name',
                            'startenddate'=>'Start End Date of Event',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
														'buy'=>'Buy Button (grid outside view only)',
														'rating'=>'Rating Stars',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Calender',
        'description' => 'SES - Advanced Events - Calender : Displays all the created events at Event Calender Page. The recommended page is Event Calender Page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.calender',
        'autoEdit' => true,
				 'adminForm' => array(
            'elements' => array(
                 array(
                    'Select',
                    'viewmore',
                    array(
                        'label' => "Show view more after how much data?",
                        'multiOptions' => array(
                            '1' => '1',
                            '2' => '2',
														'3' => '3',
														'4' => '4',
														'5' => '5',
                        ),
                    )
                ),
                 array(
                    'Select',
                    'loadData',
                    array(
                        'label' => "Choose load content option?",
                        'multiOptions' => array(
                            'nextprev' => 'Next Previous Button',
                            'viewmore' => 'View More',
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event tags',
        'description' => 'Displays all event tags on your website. The recommended page for this widget is "SES - Advanced Events - Browse Tags Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.tag-events',
    ),
    array(
        'title' => 'SES - Advanced Events - Events Tags Cloud',
        'description' => 'Displays all tags of events in cloud view. Edit this widget to choose various other settings.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.tag-cloud',
        'autoEdit' => true,
        'adminForm' => 'Sesevent_Form_Admin_Tagcloud',
    ),
    array(
        'title' => 'SES - Advanced Events - Breadcrumb for Event View Page',
        'description' => 'Displays breadcrumb for Event. This widget should be placed on the Advanced Event - View page of the selected content type.',
        'category' => 'SES - Advanced Events',
        'autoEdit' => true,
        'type' => 'widget',
        'name' => 'sesevent.breadcrumb',
        'autoEdit' => true,
    ),
    array(
        'title' => 'SES - Advanced Events - Alphabetic Filtering of Events',
        'description' => "This widget displays all the alphabets for alphabetic filtering of events which will enable users to filter content on the basis of selected alphabet.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.alphabet-search',
        'defaultParams' => array(
            'title' => "",
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Popular / Featured / Sponsored / Verified Events',
        'description' => "Displays events as chosen by you based on chosen criteria for this widget. The placement of this widget depends on the criteria chosen for this widget.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.featured-sponsored',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'gridInside' => 'Grid View',
														'gridOutside'=>'Advanced Grid View',
                        ),
                    )
                ),
								  array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside (setting work only if you select \"Grid View\" in above setting) ",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoing' => 'Ongoing Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
													'past' => 'Past Events',
													'week' => 'This Week',
													'weekend' => 'This Weekends',
													'future' => 'Upcomming Events',
													'month' => 'This Month',
											),
											'value' => '',
									)
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Display Content",
                        'multiOptions' => array(
                            '5' => 'All including Featured and Sponsored',
                            '1' => 'Only Featured',
                            '2' => 'Only Sponsored',
                            '6' => 'Only Verified',
                            '3' => 'Both Featured and Sponsored',
                            '4' => 'All except Featured and Sponsored',
                        ),
                        'value' => 5,
                    )
                ),
                array(
                    'Select',
                    'info',
                    array(
                        'label' => 'Choose Popularity Criteria.',
                        'multiOptions' => array(
                            "creation_date" => "Recently Created",
                            "most_viewed" => "Most Viewed",
                            "most_liked" => "Most Liked",
                            "most_rated" => "Most Rated",
                            "most_commented" => "Most Commented",
                            "favourite_count" => "Most Favourite",
														'most_joined'=>'Most Joined',
                        )
                    ),
                    'value' => 'recently_updated',
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
                            'host' =>'Item Host Name',
														'joinedcount'=>'Joined Guest Counts',
                            'startenddate'=>'Start End Date of Event',
                            'rating' => 'Ratings',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
														'buy'=>'Buy Button (grid outside view only)',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
		 array(
        'title' => 'SES - Advanced Events - Popular / Featured / Sponsored / Verified Events Carousel',
        'description' => "Displays events as chosen by you based on chosen criteria for this widget. The placement of this widget depends on the criteria chosen for this widget.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.featured-sponsored-carousel',
        'adminForm' => array(
            'elements' => array(
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoing' => 'Ongoing Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
													'past' => 'Past Events',
													'week' => 'This Week',
													'weekend' => 'This Weekends',
													'future' => 'Upcomming Events',
													'month' => 'This Month',
											),
											'value' => '',
									)
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Display Content",
                        'multiOptions' => array(
                            '5' => 'All including Featured and Sponsored',
                            '1' => 'Only Featured',
                            '2' => 'Only Sponsored',
                            '6' => 'Only Verified',
                            '3' => 'Both Featured and Sponsored',
                            '4' => 'All except Featured and Sponsored',
                        ),
                        'value' => 5,
                    )
                ),
                array(
                    'Select',
                    'info',
                    array(
                        'label' => 'Choose Popularity Criteria.',
                        'multiOptions' => array(
                            "creation_date" => "Recently Created",
                            "most_viewed" => "Most Viewed",
                            "most_liked" => "Most Liked",
                            "most_rated" => "Most Rated",
                            "most_commented" => "Most Commented",
                            "favourite_count" => "Most Favourite",
														'most_joined'=>'Most Joined',
                        )
                    ),
                    'value' => 'recently_updated',
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
                            'host' =>'Item Host Name',
														'joinedcount'=>'Joined Guest Counts',
                            'startenddate'=>'Start End Date of Event',
                            'rating' => 'Ratings',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationList,
								  array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
								array(
                    'Text',
                    'imageheight',
                    array(
                        'label' => 'Enter the height of image block.',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
								 array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'horizontal' => 'Horizontal',
                            'vertical' => 'Vertical',
                        ),
                        'value' => 'horizontal',
                    ),
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of event to show).',
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Home No Event Message',
        'description' => 'Displays a message when there is no Event on your website. The recommended page for this widget is "Advanced Event - Event Home Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-home-error',
    ),
    array(
        'title' => 'SES - Advanced Events - Event Location Page',
        'description' => 'This widget displays events location.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-location',
				'autoEdit' => true,
    		'adminForm' => 'Sesevent_Form_Admin_Location',
    ),
    array(
        'title' => 'SES Advanced Events - Events of the Day',
        'description' => "This widget displays events of the day as chosen by you from the Edit Settings of this widget.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.of-the-day',
        'adminForm' => array(
            'elements' => array(
                 array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'gridInside' => 'Grid View',
														'gridOutside'=>'Advanced Grid View',
                        ),
                    )
                ),
								  array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside (setting work only if you select \"Grid View\" in above setting) ",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
														'joinedcount'=>'Joined Guest Counts',
                            'host' =>'Item Host Name',
                            'startenddate'=>'Start End Date of Event',
                            'rating' => 'Ratings',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
														'buy'=>'Buy Button (grid outside view only)',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES Advanced Events - Category View Page for All Category Levels',
        'description' => 'Displays banner, 2nd-level or 3rd level categories, events associated with the current category\'s view page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.category-view',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'show_subcat',
                    array(
                        'label' => "Show 2nd-level or 3rd level categories blocks.",
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No'
                        ),
                    ),
                ),
								array('Text', "subcategory_title", array(
										'label' => "Sub-Categories Title for events",
										'value'=>'Sub-Categories of this catgeory',
								)),
                array(
                    'MultiCheckbox',
                    'show_subcatcriteria',
                    array(
                        'label' => "Choose from below the details that you want to show on each categpory block.",
                        'multiOptions' => array(
                            'title' => 'Category title',
                            'icon' => 'Category icon',
                            'countEvents' => 'Event count in each category',
                        ),
                    )
                ),
                array(
                    'Text',
                    'heightSubcat',
                    array(
                        'label' => 'Enter the height of one 2nd-level or 3rd level categor\'s block (in pixels).',
                        'value' => '160px',
                    )
                ),
                array(
                    'Text',
                    'widthSubcat',
                    array(
                        'label' => 'Enter the width of one 2nd-level or 3rd level category\'s block (in pixels).',
                        'value' => '250px',
                    )
                ),
								array('Select', "show_popular_events", array(
										'label' => "Do you want to show popular events in  banner widget",
										'multiOptions'=>array('1'=>'Yes,want to show popular event',0=>'No,don\'t want to show popular events'),
										'value'=>1,
									)),
								array('Text', "pop_title", array(
										'label' => "Title for events",
										'value'=>'Popular Events',
								)),
								array(
									'Select',
									'view',
									array(
											'label' => "Choose options of event to be show in banner widget .",
											'multiOptions' => array(
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
													'ongoing' => 'Ongoing Events',
													'past' => 'Past Events',
													'week' => 'This Week',
													'weekend' => 'This Weekends',
													'future' => 'Upcomming Events',
													'month' => 'This Month',
											),
											'value'=>'ongoingSPupcomming',
									)
								),
								array(
									'Select',
									'info',
									array(
											'label' => "choose criteria by which event shown in banner widget.",
											'multiOptions' => array(
													'creationSPdate' => 'Recently Created',
													'mostSPviewed' => 'Most Viewed',
													'mostSPliked' => 'Most Liked',
													'mostSPcommented' => 'Most Commented',
													'mostSPrated' => 'Most Rated',
													'favouriteSPcount' => 'Most Favourite',
													'featured' => 'Only Featured',
													'sponsored' => 'Only Sponsored',
													'verified' => 'Only Verified'
											),
											'value'=>'creationSPdate',
									)
								),
                array(
									'dummy',
									'dummy1',
									array(
											'label' => "Event Settings"
									)
                ),
								array('Text', "event_title", array(
										'label' => "Events Title for events",
										'value'=>'Events of this catgeory',
								)),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show on each event block.",
                        'multiOptions' => array(
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
														'joinedcount'=>'Joined Guest Counts',
														'startenddate'=>'Start End Date of Event',
                            'rating' => 'Rating Stars',
                            'view' => 'Views Count',
                            'title' => 'Event Title',
                            'by' => 'Event Owner\'s Name',
                            'favourite' => 'Favourites Count',
                        ),
                    )
                ),
                array(
                    'Radio',
                    'pagging',
                    array(
                        'label' => "Do you want the events to be auto-loaded when users scroll down the page?",
                        'multiOptions' => array(
                            'auto_load' => 'Yes, Auto Load.',
                            'button' => 'No, show \'View more\' link.',
                            'pagging' => 'No, show \'Pagination\'.'
                        ),
                        'value' => 'auto_load',
                    )
                ),
                array(
                    'Text',
                    'event_limit',
                    array(
                        'label' => 'count (number of events to show).',
                        'value' => '10',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels). [Note: This setting will not affect the event blocks displayed in Advanced View.]',
                        'value' => '160px',
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels). [Note: This setting will not affect the event blocks displayed in Advanced View.]',
                        'value' => '160px',
                    )
                )
            )
        ),
    ),
    array(
        'title' => 'SES Advanced Events - Event Category Block',
        'description' => 'Displays event categories in block view with their icon, and statistics. We recommend you to place this widget on "SES - Advanced Events - Browse Categories Page", but if you want, then you can place this widget on any widgetized page as per your requirement.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.event-category',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one block (in pixels).',
                        'value' => '160px',
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one block (in pixels).',
                        'value' => '160px',
                    )
                ),
                array(
                    'Select',
                    'alignContent',
                    array(
                        'label' => "Where you want to show content of this widget?",
                        'multiOptions' => array(
                            'center' => 'In Center',
                            'left' => 'In Left',
                            'right' => 'In Right',
                        ),
                        'value' => 'center',
                    ),
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Choose Popularity Criteria.",
                        'multiOptions' => array(
                            'alphabetical' => 'Alphabetical order',
                            'most_event' => 'Categories with maximum events first',
                            'admin_order' => 'Admin selected order for categories',
                        ),
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show on each block.",
                        'multiOptions' => array(
                            'title' => 'Category title',
                            'icon' => 'Category icon',
                            'countEvents' => 'Event count in each category',
                        ),
                    )
                )
            ),
        ),
    ),

		 array(
        'title' => 'SES Advanced Events - Event Category Icons Block',
        'description' => 'Displays event categories in block view with their icon, and statistics. We recommend you to place this widget anywhere on your website',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.event-category-icons',
        'adminForm' => array(
            'elements' => array(
								array(
                    'Text',
                    'titleC',
                    array(
                        'label' => 'Enter the title for this block.',
                        'value' => 'What are you in the mood for?',
                    )
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one block (in pixels).',
                        'value' => '160px',
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one block (in pixels).',
                        'value' => '160px',
                    )
                ),
								 array(
                    'Select',
                    'alignContent',
                    array(
                        'label' => "Where you want to show content of this widget?",
                        'multiOptions' => array(
                            'center' => 'In Center',
                            'left' => 'In Left',
                            'right' => 'In Right',
                        ),
                        'value' => 'center',
                    ),
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Choose Popularity Criteria.",
                        'multiOptions' => array(
                            'alphabetical' => 'Alphabetical order',
                            'most_event' => 'Categories with maximum events first',
                            'admin_order' => 'Admin selected order for categories',
                        ),
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show on each block.",
                        'multiOptions' => array(
                            'title' => 'Category title',
                            'countEvents' => 'Event count in each category',
                        ),
                    )
                ),
								array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of categories to show.)',
                        'value' => 10,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            ),
        ),
    ),


    array(
        'title' => 'SES Advanced Events - Category Banner Widget',
        'description' => 'Displays a banner for categories. You can place this widget at browse page of category on your site.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.banner-category',
        'adminForm' => 'Sesevent_Form_Admin_Categorywidget',
    ),
    array(
        'title' => 'SES - Advanced Events - Event Browse Page',
        'description' => 'Displays event browse page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.browse-events',
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => array(
            'elements' => array(
                $viewType,
                $defaultType,
                $showCustomData,
                array(
                    'Select',
                    'socialshare_enable_listviewplusicon',
                    array(
                        'label' => "Enable More Icon for social share buttons in List View?",
                        'multiOptions' => array(
                          '1' => 'Yes',
                          '0' => 'No',
                        ),
                    )
                ),
                array(
                  'Text',
                  'socialshare_icon_listviewlimit',
                  array(
                    'label' => 'Count (number of social sites to show) in List View. If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
                    'value' => 2,
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                  ),
                ),
                array(
                    'Select',
                    'socialshare_enable_gridviewplusicon',
                    array(
                        'label' => "Enable More Icon for social share buttons in Grid View?",
                        'multiOptions' => array(
                          '1' => 'Yes',
                          '0' => 'No',
                        ),
                    )
                ),
                array(
                  'Text',
                  'socialshare_icon_gridviewlimit',
                  array(
                    'label' => 'Count (number of social sites to show) in Grid View. If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
                    'value' => 2,
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                  ),
                ),
                array(
                    'Select',
                    'socialshare_enable_advgridviewplusicon',
                    array(
                        'label' => "Enable More Icon for social share buttons in Advanced Grid View?",
                        'multiOptions' => array(
                          '1' => 'Yes',
                          '0' => 'No',
                        ),
                    )
                ),
                array(
                  'Text',
                  'socialshare_icon_advgridviewlimit',
                  array(
                    'label' => 'Count (number of social sites to show) in Advanced Grid View. If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
                    'value' => 2,
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                  ),
                ),
                array(
                    'Select',
                    'socialshare_enable_pinviewplusicon',
                    array(
                        'label' => "Enable More Icon for social share buttons in Pinboard View?",
                        'multiOptions' => array(
                          '1' => 'Yes',
                          '0' => 'No',
                        ),
                    )
                ),
                array(
                  'Text',
                  'socialshare_icon_pinviewlimit',
                  array(
                    'label' => 'Count (number of social sites to show) in Pinboard View. If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
                    'value' => 2,
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                  ),
                ),
                array(
                    'Select',
                    'socialshare_enable_masonryviewplusicon',
                    array(
                        'label' => "Enable More Icon for social share buttons in Masonry View?",
                        'multiOptions' => array(
                          '1' => 'Yes',
                          '0' => 'No',
                        ),
                    )
                ),
                array(
                  'Text',
                  'socialshare_icon_masonryviewlimit',
                  array(
                    'label' => 'Count (number of social sites to show) in Masonry View. If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
                    'value' => 2,
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                  ),
                ),
                array(
                    'Select',
                    'socialshare_enable_mapviewplusicon',
                    array(
                        'label' => "Enable More Icon for social share buttons in Map View?",
                        'multiOptions' => array(
                          '1' => 'Yes',
                          '0' => 'No',
                        ),
                    )
                ),
                array(
                  'Text',
                  'socialshare_icon_mapviewlimit',
                  array(
                    'label' => 'Count (number of social sites to show) in Map View. If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
                    'value' => 2,
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                  ),
                ),
                $limitData,
                $pagging,
                array(
                    'Radio',
                    'order',
                    array(
                        'label' => 'Choose Event Display Criteria.',
                        'multiOptions' => array(
                            "ongoingSPupcomming" => "Ongoing & Upcomming",
                            "recentlySPcreated" => "Recently Created",
                            "mostSPviewed" => "Most Viewed",
                            "mostSPliked" => "Most Liked",
                            "mostSPrated" => "Most Rated",
														"mostSPjoined" => "Most Joined Event",
                            "starttime"=>'Start Time',
                            "mostSPcommented" => "Most Commented",
                            "mostSPfavourite" => "Most Favourite",
                            'featured' => 'Only Featured',
                            'sponsored' => 'Only Sponsored',
														'verified' =>'Only Verified',
                        ),
                        'value' => 'most_liked',
                    )
                ),
								 array(
                    'Select',
                    'show_item_count',
                    array(
                        'label' => 'Show Events count in this widget',
												'multiOptions' => array(
															'1' => 'Yes',
															'0' => 'No',
													),
													'value' => '0',
                    ),
                ),
                $titleTruncationList,
                $titleTruncationGrid,
                $titleTruncationAdvGrid,
								$titleTruncationPinboard,
								$titleTruncationMasonry,
                $DescriptionTruncationList,
								$DescriptionTruncationGrid,
								$DescriptionTruncationPinboard,
								$DescriptionTruncationMasonry,
                $heightOfContainer,
                $widthOfContainer,
                $heightOfGridPhotoContainer,
                $widthOfGridPhotoContainer,
                $heightOfGridInfoContainer,
								$heightOfAdvGridPhotoContainer,
								$widthOfAdvGridPhotoContainer,
                $widthOfPinboardContainer,
                $heightOfMasonryContainer
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Tabbed Widget',
        'description' => 'Displays a tabbed widget for events. You can place this widget anywhere on your site.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.tabbed-events',
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => 'Sesevent_Form_Admin_Tabbed',
    ),
		 array(
        'title' => 'SES - Advanced Events - Country Tabbed Widget',
        'description' => 'Displays a country tabbed widget for events. You can place this widget anywhere on your site.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.country-tabbed-events',
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => 'Sesevent_Form_Admin_Tabbedcountry',
    ),
		array(
        'title' => 'SES - Advanced Events - Manage Page Widget',
        'description' => 'Displays a manage page widget for events. You can place this widget on event manage on your site.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.manage-events',
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => 'Sesevent_Form_Admin_Managetabbed',
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Discussions',
        'description' => 'Displays a event\'s discussions on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-discussions',
        'isPaginated' => false,
        'defaultParams' => array(
            'title' => 'Discussions',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Term & Condition',
        'description' => 'Displays a event\'s term & condition on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-termsandconditions',
        'defaultParams' => array(
            'title' => 'Terms & Conditions',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Map',
        'description' => 'Displays a event\'s location on map on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-map',
        'defaultParams' => array(
            'title' => 'Map',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Host Information',
        'description' => 'Displays a event\'s host details on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-host',
        'defaultParams' => array(
            'title' => 'Event Host',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Members',
        'description' => 'Displays a event\'s members on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-members',
        'defaultParams' => array(
            'title' => 'Guests',
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
				'autoEdit' => true,
				'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'viewtype',
                    array(
                        'label' => "Do you want the albums to be auto-loaded when users scroll down the page?",
                        'multiOptions' => array(
                            'loadmore' => 'show \'View more\' link.',
                            'pagging' => 'No, show \'Pagination\'.'
                        ),
                        'value' => 'loadmore',
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of member to show.)',
                        'value' => 20,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Options',
        'description' => 'Displays a menu of actions (edit, report, join, invite, etc) that can be performed on a event on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-options',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Photo',
        'description' => 'Displays a event\'s photo on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-photo',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Albums',
        'description' => 'Displays a event\'s albums on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
				'autoEdit' => true,
        'name' => 'sesevent.profile-photos',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Photos',
            'titleCount' => false,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'load_content',
                    array(
                        'label' => "Do you want the albums to be auto-loaded when users scroll down the page?",
                        'multiOptions' => array(
                            'auto_load' => 'Yes, Auto Load.',
                            'button' => 'No, show \'View more\' link.',
                            'pagging' => 'No, show \'Pagination\'.'
                        ),
                        'value' => 'auto_load',
                    )
                ),
                array(
                    'Radio',
                    'sort',
                    array(
                        'label' => 'Choose Album Display Criteria.',
                        'multiOptions' => array(
                            "recentlySPcreated" => "Recently Created",
                            "mostSPviewed" => "Most Viewed",
                            "mostSPliked" => "Most Liked",
                            "mostSPcommented" => "Most Commented",
                        ),
                        'value' => 'most_liked',
                    )
                ),
                array(
                    'Select',
                    'insideOutside',
                    array(
                        'label' => "Choose where do you want to show the statistics of albums.",
                        'multiOptions' => array(
                            'inside' => 'Inside the Album Block',
                            'outside' => 'Outside the Album Block',
                        ),
                        'value' => 'inside',
                    )
                ),
                array(
                    'Select',
                    'fixHover',
                    array(
                        'label' => "Show album statistics Always or when users Mouse-over on album blocks (this setting will work only if you choose to show information inside the Album block.)",
                        'multiOptions' => array(
                            'fix' => 'Always',
                            'hover' => 'On Mouse-over',
                        ),
                        'value' => 'fix',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for albums in this widget.",
                        'multiOptions' => array(
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'title' => 'Album Title',
                            'by' => 'Owner\'s Name',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'photoCount' => 'Photos Count',
                            'likeButton' => 'Like Button',
                        ),
                        'escape' => false,
                    //'value' => array('like','comment','view','rating','title','by','socialSharing'),
                    )
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
                    'Text',
                    'title_truncation',
                    array(
                        'label' => 'Album title truncation limit.',
                        'value' => 45,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of albums to show.)',
                        'value' => 20,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one album block (in pixels).',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one album block (in pixels).',
                        'value' => 236,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            )
        ),
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile RSVP',
        'description' => 'Displays options for RSVP\'ing to an event on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-rsvp',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
				'autoEdit' => true,
				 'adminForm' => 'Sesevent_Form_Admin_Rsvp',
    ),

    array(
        'title' => 'SES - Advanced Events - Event Profile Join Leave Buttons',
        'description' => 'Displays Join Leave event buttions',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-join-leave',
        'requirements' => array(
            'subject' => 'sesevent_event',
        )
    ),

    array(
        'title' => 'SES - Advanced Events - Event Title Status',
        'description' => 'Displays a event\'s title on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-status',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Link to Blog',
        'description' => 'SES - Advanced Events - Link to Blog : You can link your events with the blogs Created on your website. The recommended page is Event Profile Page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.link-blog',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Info Sidebar Widget',
        'description' => 'Displays a event\'s info on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-info',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
				'autoEdit' => true,
				 'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'criteria',
                    array(
                        'label' => 'Choose from below the content show in this widget.',
                        'multiOptions' => array(
                            'location' => 'Location',
                            'date' => 'Start & End date',
														'like'=>'Like Counts',
														'comment'=>'Comment Counts',
														'favourite'=>'Favourites Counts',
														'view'=>'View Counts',
														'rating'=>'Rating Stars',
														'guestinfo'=>'Guest Info',
														'tag'=>'Show Tags',
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Location Sidebar Widget',
        'description' => 'Displays a event\'s location , Start and End date widget  on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-location-sidebar',
        'requirements' => array(
            'subject' => 'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Browse Search',
        'description' => 'Displays a search form in the event browse page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.browse-search',
        'requirements' => array(
            'no-subject',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'view_type',
                    array(
                        'label' => "Choose the View Type.",
                        'multiOptions' => array(
                            'horizontal' => 'Horizontal',
                            'vertical' => 'Vertical'
                        ),
                        'value' => 'vertical',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'search_type',
                    array(
                        'label' => "Choose options to be shown in \'Browse By\' search fields.",
                        'multiOptions' => array(
                            'recentlySPcreated' => 'Recently Created',
                            'mostSPviewed' => 'Most Viewed',
                            'mostSPliked' => 'Most Liked',
                            'starttime' => 'Start Time',
                            'mostSPcommented' => 'Most Commented',
                            'mostSPrated' => 'Most Rated',
                            'mostSPfavourite' => 'Most Favourite',
														'mostSPjoined'=>'Most Joined',
                            'featured' => 'Only Featured',
                            'sponsored' => 'Only Sponsored',
                            'verified' => 'Only Verified',
                        ),
                    )
                ),
								array(
                    'MultiCheckbox',
                    'view',
                    array(
                        'label' => "Choose options to be shown in \'View\' search fields.",
                        'multiOptions' => array(
                            '0' => 'Everyone\'s Events',
                            '1' => 'Only My Friend\'s Events',
                            'ongoing' => 'Ongoing Events',
                            'past' => 'Past Events',
                            'week' => 'This Week',
                            'weekend' => 'This Weekends',
                            'future' => 'Upcomming Events',
                            'month' => 'This Month',
                            "ongoingSPupcomming" => "Ongoing & Upcomming",
                        ),
                    )
                ),
                array(
                    'Select',
                    'default_search_type',
                    array(
                        'label' => "Default \'Browse By\' search fields.",
                        'multiOptions' => array(
                            'creation_date ASC' => 'Recently Created',
                            'view_count DESC' => 'Most Viewed',
                            'like_count DESC' => 'Most Liked',
                            'comment_count DESC' => 'Most Commented',
                            'rate_count DESC' => 'Most Rated',
                            'starttime DESC' => 'Start Time',
                            'favourite_count DESC' => 'Most Favourite',
                            'featured' => 'Only Featured',
                            'sponsored' => 'Only Sponsored',
                            'verified' => 'Only Verified'
                        ),
                    )
                ),
								array(
                    'Radio',
                    'show_advanced_search',
                    array(
                        'label' => "Show \'Advanced Settings Button\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No'
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'alphabet',
                    array(
                        'label' => "Show \'Alphabet\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
                array(
                    'Radio',
                    'friend_show',
                    array(
                        'label' => "Show \'View\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
                array(
                    'Radio',
                    'search_title',
                    array(
                        'label' => "Show \'Search Events /Keyword\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
                array(
                    'Radio',
                    'browse_by',
                    array(
                        'label' => "Show \'Browse By\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
                array(
                    'Radio',
                    'categories',
                    array(
                        'label' => "Show \'Categories\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
                array(
                    'Radio',
                    'location',
                    array(
                        'label' => "Show \'Location\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
                array(
                    'Radio',
                    'kilometer_miles',
                    array(
                        'label' => "Show \'Kilometer or Miles\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'start_date',
                    array(
                        'label' => "Show \'Start Date\' field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'end_date',
                    array(
                        'label' => "Show \'End Date\' field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'country',
                    array(
                        'label' => "Show \'Country\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'state',
                    array(
                        'label' => "Show \'State\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'city',
                    array(
                        'label' => "Show \'City\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'zip',
                    array(
                        'label' => "Show \'Zip\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
								array(
                    'Radio',
                    'venue',
                    array(
                        'label' => "Show \'Venue\' search field?",
                        'multiOptions' => array(
                            'yes' => 'Yes',
                            'no' => 'No',
														'hide'=>'show this option in hide',
                        ),
                        'value' => 'yes',
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Browse Menu',
        'description' => 'Displays a menu in the event browse page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.browse-menu',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Add to calander',
        'description' => 'Displays a add to calander.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.add-to-calendar',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'options',
                    array(
                        'label' => "Choose the Calander options to show in this widget.",
                        'multiOptions' => array('google' => 'google', 'yahoo' => 'yahoo', 'msn' => 'msn', 'outlook' => 'outlook', 'ical' => 'ical'),
                    )
                ),
            ),
        ),
    ),
		array(
				'title' => 'SES - Advanced Events - Event Cover Photo',
				'description' => 'Displays a event cover photo placed on event view page.',
				'category' => 'SES - Advanced Events',
				'type' => 'widget',
				'name' => 'sesevent.event-cover',
				'autoEdit' => true,
				'adminForm' => array(
						'elements' => array(
								array(
										'MultiCheckbox',
										'showCriterias',
									array(
                                        'label' => "Choose options to be shown in this widget.",
                                        'multiOptions' => array(
                                                'minimalisticCover' => 'Minimalistic Cover <small>(disables all other options in this list)</small>',
                                                'title' =>'Event Title',
                                                'createdby' =>'Created By',
                                                'createdon' =>'Created On',
                                                'mainPhoto' => 'Main Photo',
                                                'hostedby' => 'Hosted By',
                                                'startEndDate' => 'Start End Date',
                                                'location' => 'Location',
                                                'commentCount' =>'Comment Count',
                                                'likeCount' =>'Like Count',
                                                'favouriteCount' =>'Favourite Count',
                                                'viewCount' =>'Views Count',
                                                'guestCount' =>'Guests Count',
                                                'advShare'=>'Advance Share Button',
                                                'likeBtn'=>'Like Button',
                                                'favouriteBtn'=>'Favourite Button',
                                                'socialShare'=>'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                                                'listBtn'=>'List Button',
                                                'join'=>'Join Now button',
                                                'addtocalender'=>'Add To Calander',
                                                'bookNow'=>'Book Now',
                                                'venue'=>'Venue Name',
                                                'tag' =>'Show Tags',
                                        ),
                                        'escape' => false,
									)
								),
								$socialshare_enable_plusicon,
								$socialshare_icon_limit,
								array(
									'Select',
										'photo',
										array(
												'label' => "Choose photo shown in main photo in this widget (work only if you choose main photo option in above setting).",
												'multiOptions' => array('oPhoto'=>'Owner\'s Photo','mPhoto'=>'Main Photo','hPhoto'=>'Host Photo'),
										)
								),
								array(
									'Select',
										'fullwidth',
										array(
												'label' => "Show this widget in full width?",
												'multiOptions' => array('1'=>'Yes,want to show this widget in full width','0'=>'No,don\'t want to show this widget in full width'),
										)
								),
								array(
                    'Text',
                    'padding',
                    array(
                        'label' => 'Margin top setting px(setting work if you choose fullwidth yes from above setting)',
                        'value' => 0,
                    ),
                ),
								array(
										'MultiCheckbox',
										'showCalander',
										array(
												'label' => "Choose options to be shown in Add To Calander widget (if you select yes in above setting).",
												'multiOptions' => array('google' => 'Google', 'yahoo' => 'Yahoo', 'msn' => 'Msn', 'outlook' => 'Outlook', 'ical' => 'Ical'),
										)
								),
								array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Height of Cover Photo Container',
                        'value' => 300,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
								array(
										'Select',
										'optionInsideOutside',
										array(
												'label' => "Setting to show tab inside/outside",
												'multiOptions' => array(1=>'Inside',0=>'Outside'),
										),
										'value'=>1,
								),
						),
				),
		),
    array(
        'title' => 'SES - Advanced Events - Advance Share Widget placed on event view page',
        'description' => 'Placed on view page',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.advance-share',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'advShareOptions',
                    array(
                        'label' => "Choose options to be shown in Advance Share in this widget.",
                        'multiOptions' => array(
                            'privateMessage' => 'Private Message',
                            'siteShare' => 'Site Share',
                            'quickShare' => 'Quick Share',
														'tellAFriend'=>'Tell A Friend',
                            'addThis' => 'Add This Share Links',
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Browse Quick Menu',
        'description' => 'Displays a small menu in the event browse page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.browse-menu-quick',
				 'autoEdit' => true,
        'requirements' => array(
            'no-subject',
        ),
				'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'popup',
                    array(
                        'label' => "Do you want to show popup.",
                        'multiOptions' => array(
                            '1' => 'Yes,want to show popup',
                            '0' => 'No,don\'t want to show poup',
                        ),
                        'value'=> 1,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Status',
        'description' => 'Displays a event status on event profile page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-profile-status',
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Overview ',
        'description' => 'Displays a Event overview on event view page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-overview',
        'requirements' => array(
            'sesevent_event',
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Event Profile Information',
        'description' => 'Displays a Event about on event view page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-info',
        'requirements' => array(
            'sesevent_event',
        ),
    ),
		array(
        'title' => 'SES - Advanced Events - SlideShow',
        'description' => 'This widget displays slideshow.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.main-slideshows',
        'adminForm' => 'Sesevent_Form_Admin_Slideshow',
    ),
    array(
        'title' => 'SES Advanced Events - Category Based Events Slideshow',
        'description' => 'Displays events in slideshow on the basis of their categories. This widget can be placed any where on your website.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.category-associate-event',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'view_type',
                    array(
                        'label' => "Select the view type.",
                        'multiOptions' => array(
                            '1' => 'Slideshow',
                            '0' => 'Advanced Grid View with 5 photos',
                            '2' => 'Grid View',
														'5' => 'Advanced Grid View',
                        ),
                        'value' => 1,
                    ),
                ),
								array('Text', "photo_height", array(
										'label' => 'Enter the height of grid photo block (in pixels).',
										'value' => '160',
								)),
								array('Text', "photo_width", array(
										'label' => 'Enter the width of grid photo block /Advanced grid width (in pixels).',
										'value' => '250',
								)),
								array('Text', "info_height", array(
										'label' => 'Enter the height of grid info block  / Advanced grid height (in pixels).',
										'value' => '160',
								)),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for albums in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
														'host'=>'Hosted By',
                            'description' => 'Event Description',
														'category'=>'Show Category',
                            'like' => 'Likes Count',
                            'view' => 'Views Count',
                            'comment' => 'Comments Count',
                            'favourite' => 'Favourites Count',
                            'featuredLabel' => 'Featured Label',
														'verifiedLabel' => 'Verified Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            //'eventPhoto' => 'Current Event\'s Main Photo',
														'joinedcount'=>'Joined Guest Counts',
                            'photoThumbnail' => 'Event Thumbnails below category name',
								            'favouriteButton' => 'Favourite Button',
								            'likeButton' => 'Like Button',
														'listButton' => 'Add To List Button',
								            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
														'location' => 'Location',
														'startenddate'=>'Start End Date of Event',
								            'by' => 'Item Owner Name',
														'rating' => 'Rating Stars',
														'buy' =>'Buy Button',
                        ),
                        'escape' => false,
                    )
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $heightOfGridPhotoContainer,
                $widthOfGridPhotoContainer,
                $heightOfGridInfoContainer,
                array(
                    'Radio',
                    'pagging',
                    array(
                        'label' => "Do you want the events to be auto-loaded when users scroll down the page?",
                        'multiOptions' => array(
                            'auto_load' => 'Yes, Auto Load.',
                            'button' => 'No, show \'View more\' link.',
                            'pagging' => 'No, show \'Pagination\'.'
                        ),
                        'value' => 'auto_load',
                    )
                ),
                array(
                    'Select',
                    'count_event',
                    array(
                        'label' => "Show events count in each category.",
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No'
                        ),
                    ),
                ),
                array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Choose Popularity Criteria.",
                        'multiOptions' => array(
                            'alphabetical' => 'Alphabetical order',
                            'most_event' => 'Categories with maximum events first',
                            'admin_order' => 'Admin selected order for categories',
                        ),
                    ),
                ),
                array(
                    'Text',
                    'category_limit',
                    array(
                        'label' => 'count (number of categories to show).',
                        'value' => '10',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'event_limit',
                    array(
                        'label' => 'count (number of events to show in each category).',
                        'value' => '10',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'seemore_text',
                    array(
                        'label' => 'Enter the text for "+ See All" link. Leave blank if you don\'t want to show this link. (Use[category_name] variable to show the associated category name).',
                        'value' => '+ See all [category_name]',
                    )
                ),
                array(
                    'Select',
                    'allignment_seeall',
                    array(
                        'label' => "Choose alignment of \"+ See All\" field",
                        'multiOptions' => array(
                            'left' => 'Left',
                            'right' => 'Right'
                        ),
                    ),
                ),
                array(
                    'Text',
                    'title_truncation',
                    array(
                        'label' => 'Event title truncation limit.',
                        'value' => '150',
                    )
                ),
                array(
                    'Text',
                    'description_truncation',
                    array(
                        'label' => 'Event description truncation limit.',
                        'value' => '200',
                    )
                ),
            )
        ),
    ),
    array(
      'title' => 'Save Event Profile Button',
      'description' => 'This widget display on Event Profile page only.',
      'category' => 'SES - Advanced Events',
      'type' => 'widget',
      'name' => 'sesevent.save-button',
    ),
		array(
      'title' => 'My Location Detect',
      'description' => 'This widget display on Event browse page only.',
      'category' => 'SES - Advanced Events',
      'type' => 'widget',
      'name' => 'sesevent.location-detect',
      'autoEdit' => true,
    ),
    array(
      'title' => 'SES Advanced Event - Album View Page Options',
      'description' => "Album View Page",
      'category' => 'SES - Advanced Events',
      'type' => 'widget',
      'autoEdit' => true,
      'name' => 'sesevent.album-view-page',
      'adminForm' => 'Sesevent_Form_Admin_Albumviewpage',
    ),
		array(
        'title' => 'SES Advanced Event - Photo View Page Options',
        'description' => 'This widget enables you to choose various options to be shown on photo view page like Slideshow of other photos associated with same album as the current photo, etc.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.photo-view-page',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'criteria',
                    array(
                        'label' => 'Slideshow of other photos associated with same album?',
                        'multiOptions' =>
                        array(
                            '1' => 'Yes',
														'0' =>'No'
                        ),
												'value' => 1
                    ),
                ),
                array(
                    'Text',
                    'maxHeight',
                    array(
                        'label' => 'Enter the height of photo.',
                        'value' => 550,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Browse Lists',
        'description' => 'Displays all lists on your website.  The recommended page for this widget is "Advanced Events - Browse Lists Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.browse-lists',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => 'Popularity Criteria',
                        'multiOptions' => array(
													'creation_date' => 'Most Recent',
													'featured' => "Only Featured",
													'sponsored' => "Only Sponsored",
													'view_count' => 'Most Viewed',
													'event_count' => 'Most Event List',
													'favourite_count' => 'Most Favorite',
													'like_count' => 'Most Liked',
                        ),
                        'value' => 'creation_date',
                    )
                ),
                array(
                    'Select',
                    'listCount',
                    array(
                        'label' => 'Do you want to show list count?',
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No'
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Select',
                    'Type',
                    array(
                        'label' => 'Do you want the lists to be auto-loaded when users scroll down the page?',
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No, show \'View More\'',
														'pagging'=>'Pagging'
                        ),
                        'value' => 1,
                    )
                ),

                array(
                    'MultiCheckbox',
                    'information',
                    array(
                        'label' => 'Choose the options that you want to be displayed in this widget.',
                        'multiOptions' => array(
                            "viewCount" => "Views Count",
                            "title" => "List Title",
                            "postedby" => "Posted By",
                            "share" => "Share Button",
														"eventcount"=>'Event Counts',
														'favouriteButton'=>'Favourite Button',
														'favouriteCount'=>'Favourite counts',
														'featuredLabel'=>'Featured label',
														'sponsoredLabel'=>'Sponsored label',
														'likeButton'=>'Like Button',
														'socialSharing' =>'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
														'likeCount'=>'Like Counts',
														'viewCount'=>'View Counts',
                            'showEventsList' => "Show events of each list",
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
								array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of list (px).',
                        'value' => '200',
                    )
                ),
								array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of list (px).',
                        'value' => '295',
                    )
                ),
                array(
										'Text',
										'titletruncation',
										array(
												'label' => 'Title truncation limit.',
												'value' => 16,
												'validators' => array(
														array('Int', true),
														array('GreaterThan', true, array(0)),
												)
										)
								),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => 'Count (number of content to show)',
                        'value' => 10,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - List Browse Search',
        'description' => 'Displays a search form in the list browse page. Edit this widget to choose the search option to be shown in the search form.',
        'category' => 'SES - Advanced Events',
        'autoEdit' => true,
        'type' => 'widget',
        'name' => 'sesevent.list-browse-search',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'searchOptionsType',
                    array(
                        'label' => "Choose from below the searching options that you want to show in this widget.",
                        'multiOptions' => array(
                            'searchBox' => 'Search List',
                            'view' => 'View',
                            'show' => 'List By',
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - List Details',
        'description' => 'This widget displays list details and various options. The recommended page for this widget is "Advanced Event - List View Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.list-view-page',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'informationList',
                    array(
                        'label' => 'Choose from below the details that you want to show for "List" shown in this widget.',
                        'multiOptions' => array(
                            "editButton" => "Edit Button List",
                            "deleteButton" => "Delete Button List",
                            "viewCountList" => "Views Count List",
                            "eventCountList" => "Event Count List",
                            "descriptionList" => "Description List",
                            "postedby" => "Posted By List",
                            "shareList" => "Share Button List",
                            "favouriteButtonList" => "Add to Favorite List",
														'favouriteCountList'=>'Favourite Count List',
														'likeButtonList'=>'Like Button List',
														'featuredLabelList'=>'Featured label List',
														'sponsoredLabelList'=>'Sponsored label List',
														'socialSharingList'=>'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
														'likeCountList' =>'Like Counts',
														'reportList'=>'Report List',
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Popular Lists Carousel',
        'description' => 'Displays lists based on chosen criteria for this widget. The placement of this widget depends on the criteria chosen for this widget.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.popular-lists',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'showOptionsType',
                    array(
                        'label' => "Show",
                        'multiOptions' => array(
                            'all' => 'Popular List [With this option, place this widget anywhere on your website. Choose criteria from "Popularity Criteria" setting below.]',
                            'recommanded' => 'Recommended List [With this option, place this widget anywhere on your website.]',
                            'other' => 'Member’s Other Lists [With this option, place this widget on Advanced Event - List View Page.]',
                        ),
                        'value' => 'all',
                    ),
                ),
                array(
                    'Select',
                    'showType',
                    array(
                        'label' => "Do you want to show carousel?",
                        'multiOptions' => array(
                            'carouselview' => 'Yes',
                            'gridview' => 'No',
                        ),
                        'value' => 'horizontal',
                    ),
                ),
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => 'Popularity Criteria',
                        'multiOptions' => array(
                            'featured' => 'Only Featured',
                            'view_count' => 'Most Viewed',
														'like_count' => 'Most Liked',
                            'creation_date' => 'Most Recent',
                            'modified_date' => 'Recently Updated',
                            'favourite_count' => "Most Favorite",
                            'event_count' => "Maximum Event",
                        ),
                        'value' => 'creation_date',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'information',
                    array(
                        'label' => "Choose the options that you want to be displayed in this widget.",
                        'multiOptions' => array(
                            "viewCount" => "Views Count",
                            "title" => "List Title",
                            "postedby" => "Posted By",
                            "share" => "Share Button",
														"eventcount"=>'Event Counts',
														'favouriteButton'=>'Favourite Button',
														'favouriteCount'=>'Favourite counts',
														'featuredLabel'=>'Featured label',
														'sponsoredLabel'=>'Sponsored label',
														'likeButton'=>'Like Button',
														'socialSharing' =>'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
														'likeCount'=>'Like Counts',
														'viewCount'=>'View Counts',
                            'showEventsList' => "Show events of each list",
                        ),
                        'escape' => false,
                    )
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
                    'Select',
                    'viewType',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'horizontal' => 'Horizontal',
                            'vertical' => 'Vertical',
                        ),
                        'value' => 'horizontal',
                    ),
                ),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one block.',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                $widthOfContainer,
                array(
										'Text',
										'titletruncation',
										array(
												'label' => 'Title truncation limit.',
												'value' => 16,
												'validators' => array(
														array('Int', true),
														array('GreaterThan', true, array(0)),
												)
										)
								),
                array(
                    'Text',
                    'limit',
                    array(
                        'label' => 'Count (number of content to show)',
                        'value' => 3,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
            )
        ),
    ),
    array(
        'title' => 'SES - Advanced Events - Lists of the Day',
        'description' => "This widget displays lists of the day as chosen by you from the Edit Settings of this widget.",
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.of-the-day-list',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'information',
                    array(
                        'label' => "Choose from below the details that you want to show for lists in this widget.",
                        'multiOptions' => array(
                            "viewCount" => "Views Count",
                            "title" => "List Title",
                            "postedby" => "Posted By",
                            "share" => "Share Button",
														"eventcount"=>'Event Counts',
														'favouriteButton'=>'Favourite Button',
														'favouriteCount'=>'Favourite counts',
														'featuredLabel'=>'Featured label',
														'sponsoredLabel'=>'Sponsored label',
														'likeButton'=>'Like Button',
														'socialSharing' =>'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
														'likeCount'=>'Like Counts',
														'viewCount'=>'View Counts',
                            'showEventsList' => "Show events of each list",
                        ),
                        'escape' => false,
                    )
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one block.',
                        'value' => 200,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                array(
										'Text',
										'titletruncation',
										array(
												'label' => 'Title truncation limit.',
												'value' => 16,
												'validators' => array(
														array('Int', true),
														array('GreaterThan', true, array(0)),
												)
										)
								),
            )
        ),
    ),
    array(
      'title' => 'SES - Advanced Events - Content Profile Events',
      'description' => 'This widget enables you to allow users to create events on different content on your website like Groups. Place this widget on the content profile page, for example SE Group to enable group owners to create events in their Groups. You can choose the visibility of the events created in a content to only that content or show in this plugin as well from the "Events Created in Content Visibility" setting in Global setting of this plugin.',
      'category' => 'SES - Advanced Events',
      'type' => 'widget',
      'name' => 'sesevent.other-modules-profile-events',
      'autoEdit' => true,
      'defaultParams' => array(
        'title' => 'Events',
        'titleCount' => true,
      ),
      'adminForm' => 'Sesevent_Form_Admin_OtherModulesProfileevents',
    ),
		array(
        'title' => 'SES Advanced Events - Profile Events',
        'description' => 'Displays a member\'s events on their profile. The recommended page for this widget is "Member Profile Page".',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.profile-events',
        'autoEdit' => true,
        'adminForm' => 'Sesevent_Form_Admin_Profileevents',
        'requirements' => array(
            'subject' => 'user',
        ),
    ),
		array(
        'title' => 'SES Advanced Events - Event Labels ',
        'description' => 'Displays a featured, sponsored , verified and offtheday labels on a event on it\'s profile.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-label',
				'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
						 array(
												'MultiCheckbox',
												'option',
												array(
														'label' => "Choose options to be shown in this widget.",
														'multiOptions' => array(
																'featured' => 'Featured',
																'sponsored' => 'Sponsored',
																'verified' => 'Verified',
																'offtheday' => 'Of The Day',
														),
												)
										),
						   ),
				 ),
    ),
		 array(
        'title' => 'SES - Advanced Events - Category Carousel',
        'description' => 'Displays category in this widget. The placement of this widget depends on the criteria chosen for this widget.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.category-carousel',
        'adminForm' => array(
            'elements' => array(
								array(
										'Text',
										'title_truncation_grid',
										array(
												'label' => 'Title truncation limit.',
												'value' => 45,
												'validators' => array(
														array('Int', true),
														array('GreaterThan', true, array(0)),
												)
										)
								),
								array(
										'Text',
										'description_truncation_grid',
										array(
												'label' => 'Description truncation limit.',
												'value' => 45,
												'validators' => array(
														array('Int', true),
														array('GreaterThan', true, array(0)),
												)
										)
								),
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
								array(
                    'Text',
                    'speed',
                    array(
                        'label' => 'Auto play change interval.',
                        'value' => '300',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
								 array(
                    'Select',
                    'autoplay',
                    array(
                        'label' => "Enables auto play of slides",
                        'multiOptions' => array(
                            1=>'Yes',
														0=>'No'
                        ),
                    ),
                ),
                 array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
								 array(
                    'Select',
                    'criteria',
                    array(
                        'label' => "Choose Popularity Criteria.",
                        'multiOptions' => array(
                            'alphabetical' => 'Alphabetical order',
                            'most_event' => 'Categories with maximum events first',
                            'admin_order' => 'Admin selected order for categories',
                        ),
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show on each block.",
                        'multiOptions' => array(
                            'title' => 'Category title',
														'description' => 'Category description',
                            'countEvents' => 'Event count in each category',
														'socialshare' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                        ),
                        'escape' => false,
                    )
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
								array(
                    'Select',
                    'isfullwidth',
                    array(
                        'label' => 'Want to show category carousel in full width?',
												'multiOptions'=>array(
												1=>'Yes,want to show this widget in full width.',
												0=>'No,don\'t want to show this widget in full width.'
												),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of category to show in this widget,put 0 for unlimited).',
                        'value' => 10,
                    )
                ),
            )
        ),
    ),
		array(
        'title' => 'SES - Advanced Events - Host / Speakers / List Event Widget',
        'description' => 'Display a hosts / speakers widget for events.You can place this widget on hosts / speakers view page on your site.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.host-speaker-events',
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => 'Sesevent_Form_Admin_Tabbed',
    ),
		array(
        'title' => 'SES - Advanced Events - Event Contact Information',
        'description' => 'Displays event contact information in this widget. The placement of this widget depends on the event profile page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sesevent.event-contact-information',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show in this widget.",
                        'multiOptions' => array(
                            'name' => 'Contact Name',
														'email' => 'Contact Eamail',
                            'phone' => 'Contact Phone Number',
														'facebook' =>'Contact Facebook',
														'linkedin'=>'Contact Linkedin',
														'twitter'=>'Contact Twitter',
														'website'=>'Contact Website',
                        ),
                    )
                ),
            )
        ),
		),
		array(
        'title' => 'SES - Advanced Events - Event Guest Info',
        'description' => 'Displays event guest information in this widget. The placement of this widget depends on the event profile page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.event-guest-information',
				'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'guestCount',
                    array(
                        'label' => "Enter the guest count to show in this widget",
												'value'=>4
                    )
                ),
								array(
										'Text',
                    'height',
                    array(
                        'label' => "Enter the guest count to show in this widget",
												'value'=>'45'
                    )
                ),
								array(
										'Text',
                    'width',
                    array(
                        'label' => "Enter the guest count to show in this widget",
												'value'=>'40'
                    )
                ),
            )
        ),
		),
		array(
        'title' => 'SES - Advanced Events - Recently Viewed Events',
        'description' => 'This widget displays the recently viewed events by the user who is currently viewing your website or by the logged in members friend or by all the members of your website. Edit this widget to choose whose recently viewed content will show in this widget.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.recently-viewed-item',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
								array(
                    'Select',
                    'view_type',
                    array(
                        'label' => "View Type",
                        'multiOptions' => array(
                            'list' => 'List View',
                            'gridInside' => 'Grid View',
														'gridOutside'=>'Advanced Grid View',
                        ),
                    )
                ),
               array(
                    'Select',
                    'gridInsideOutside',
                    array(
                        'label' => "Grid View Inside/Outside",
                        'multiOptions' => array(
                            'in' => 'Inside View',
                            'out' => 'Outside View',
                        ),
												'value'=>'in'
                    )
                ),
								 array(
                    'Select',
                    'mouseOver',
                    array(
                        'label' => "Show Grid View Data on Mouse Over (setting work only if you select \"Inside View\" in above setting) ",
                        'multiOptions' => array(
                            'over' => 'Yes,show data on Mouse Over',
                            '' => 'No,don\'t show data on Mouse Over',
                        ),
												'value'=>'over',
                    )
                ),
                array(
									'Select',
									'order',
									array(
											'label' => 'Events Criteria to show in this widget',
											'multiOptions' => array(
													''=>'All Events',
													'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
											),
											'value' => '',
									)
                ),
								array(
                    'Select',
                    'criteria',
                    array(
                        'label' => 'Display Criteria',
                        'multiOptions' =>
                        array(
                            'by_me' => 'Viewed By logged-in member',
                            'by_myfriend' => 'Viewed By logged-in member\'s friend',
                            'on_site' => 'Viewed by all members of website'
                        ),
                    ),
                ),
								 array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Choose from below the details that you want to show for event in this widget.",
                        'multiOptions' => array(
                            'title' => 'Event Title',
                            'by' => 'Owner\'s Name',
                            'category' => 'Category',
                            'like' => 'Likes Count',
                            'comment' => 'Comments Count',
                            'view' => 'Views Count',
                            'favourite' => 'Favourite Count',
                            'location' => 'Location',
                            'host' =>'Item Host Name',
														'joinedcount'=>'Joined Guest Counts',
                            'startenddate'=>'Start End Date of Event',
                            'rating' => 'Ratings',
                            'featuredLabel' => 'Featured Label',
                            'sponsoredLabel' => 'Sponsored Label',
                            'verifiedLabel' => 'Verified Label',
                            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
                            'likeButton' => 'Like Button',
                            'favouriteButton' => 'Favourite Button',
                            'listButton' => "Add List Button",
                        ),
                        'escape' => false,
                    ),
                ),
                $socialshare_enable_plusicon,
                $socialshare_icon_limit,
                $titleTruncationGrid,
                $titleTruncationList,
								array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'width',
                    array(
                        'label' => 'Enter the width of one event block (in pixels).',
                        'value' => '180',
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
                array(
                    'Text',
                    'limit_data',
                    array(
                        'label' => 'Count (number of events to show.)',
                        'value' => 20,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    )
                ),
            ),
        ),
    ),
array(
        'title' => 'SES - Advanced Events - My Ticket info page',
        'description' => 'My ticket info page.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.my-tickets',
    ),
		array(
        'title' => 'SES - Advanced Events - Custom Layout Widget',
        'description' => 'Custom Layout Widget.',
        'category' => 'SES - Advanced Events',
        'type' => 'widget',
        'name' => 'sesevent.custom-layout',
    ),
		array(
		'title' => 'SES - Advanced Events - Browse Reviews',
		'description' => 'Displays all reviews for events on your webiste. This widget is placed on "SES - Advanced Events - Browse Reviews Page".',
		'category' => 'SES - Advanced Events',
		'type' => 'widget',
		'autoEdit' => true,
		'name' => 'sesevent.browse-reviews',
		'defaultParams' => array(
		),
		'adminForm' => array(
				'elements' => array(
					array(
						'MultiCheckbox',
						'stats',
						array(
							'label' => 'Choose options to show in this widget.',
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
								'parameter' => 'Review Parameters',
								"creationDate" => "Creation Date",
								'rating' => 'Rating Stars',
								//'likeButton' => 'Like Button',
                //'socialSharing' =>'Social Share Buttons',
							),
						)
					),
					/*array(
						'MultiCheckbox',
						'show_criteria',
						array(
								'label' => "Choose from below the details that you want to show for blog in this widget.",
								'multiOptions' => array(
										'likemainButton' => 'Like Button with Social Sharing Button',
										'featuredLabel' => 'Featured Label',
										'verifiedLabel' => 'Verified Label',
								),
						),
					),*/
					$pagging,
					array(
						'Text',
						'limit_data',
						array(
								'label' => 'Count (number of reviews to show).',
								'value' => 5,
								'validators' => array(
										array('Int', true),
										array('GreaterThan', true, array(0)),
								)
						)
					),
				),
		),
  ),
		array(
		'title' => 'SES - Advanced Events - Review Browse Search',
		'description' => 'Displays a search form in the review browse page as configured by you.',
		'category' => 'SES - Advanced Events',
		'type' => 'widget',
		'name' => 'sesevent.browse-review-search',
		'requirements' => array(
				'no-subject',
		),
		'autoEdit' => true,
		'adminForm' => array(
			'elements' => array(
				array(
					'Radio',
					'view_type',
					array(
						'label' => "Choose the View Type.",
						'multiOptions' => array(
								'horizontal' => 'Horizontal',
								'vertical' => 'Vertical'
						),
						'value' => 'vertical',
					)
				),
				array(
					'Radio',
					'review_title',
					array(
						'label' => "Show \'Review Title\' search field?",
						'multiOptions' => array(
								'1' => 'Yes',
								'0' => 'No'
						),
						'value' => '1',
					)
				),
				array(
					'Radio',
					'review_search',
					array(
						'label' => "Show \'Browse By\' search field?",
						'multiOptions' => array(
								'1' => 'Yes',
								'0' => 'No'
						),
						'value' => '1',
					)
				),
				/*array(
					'MultiCheckbox',
					'view',
					array(
						'label' => "Choose options to be shown in \'Browse By\' search fields.",
						'multiOptions' => array(
							'mostSPliked' => 'Most Liked',
							'mostSPviewed' => 'Most Viewed',
							'mostSPcommented' => 'Most Commented',
							'mostSPrated' => 'Most Rated',
							'verified' => 'Verified Only',
							'featured' => 'Featured Only',
						),
					)
				),*/
				array(
					'Radio',
					'review_stars',
					array(
						'label' => "Show \'Review Stars\' search field?",
						'multiOptions' => array(
								'1' => 'Yes',
								'0' => 'No'
						),
						'value' => '1',
					)
				),
				array(
					'Radio',
					'review_recommendation',
					array(
						'label' => "Show \'Recommended Review\' search field?",
						'multiOptions' => array(
								'1' => 'Yes',
								'0' => 'No',
						),
						'value' => '1',
					)
				),
			)
		),
  ),
);
?>
