<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Tabbed.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Tabbed extends Engine_Form {

  public function init() {
  
    $this->addElement('MultiCheckbox', "enableTabs", array(
        'label' => "Choose the View Type.",
        'multiOptions' => array(
            'list' => 'List View',
            'grid' => 'Grid View',
						'advgrid' => 'Advanced Grid View',
            'pinboard' => 'Pinboard View',
            'masonry' => 'Masonry View',
            'map' => 'Map View',
        ),
        'value' => '',
    ));
    $this->addElement('Select', "openViewType", array(
        'label' => " Default open View Type (apply if select more than one option in above tab)?",
        'multiOptions' => array(
            'list' => 'List View',
            'grid' => 'Grid View',
						'advgrid' => 'Advanced Grid View',
            'pinboard' => 'Pinboard View',
            'masonry' => 'Masonry View',
            'map' => 'Map View',
        ),
        'value' => '',
    ));
    $this->addElement('Radio', "tabOption", array(
        'label' => 'Show Tab Type?',
       	'multiOptions' => array(
            'default' => 'Default',
            'advance' => 'Advanced',
						'filter'=>'Filter',
						'vertical'=>'Vertical',
        ),
        'value' => 'advance',
   ));
	 $this->addElement('Select', "show_item_count", array(
			'label' => 'Show Events count in this widget',
			'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
        'value' => '0',
    ));
    $this->addElement('MultiCheckbox', "show_criteria", array(
        'label' => "Choose from below the details that you want to show in this widget.",
        'multiOptions' => array(
					'featuredLabel' => 'Featured Label',
					'sponsoredLabel' => 'Sponsored Label',
					'verifiedLabel' => 'Verified Label',
					'favouriteButton' => 'Favourite Button',
					'listButton' => "Add List Button",
					'likeButton' => 'Like Button',
					'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
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
    ));
    
    //Social Share Plugin work
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare')) {
      
      $this->addElement('Select', "socialshare_enable_listviewplusicon", array(
        'label' => "Enable plus (+) icon for social share buttons in List View?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_listviewlimit", array(
          'label' => 'Enter the number of Social Share Buttons after which plus (+) icon will come in List View. Other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          )
      ));

      $this->addElement('Select', "socialshare_enable_gridviewplusicon", array(
        'label' => "Enable plus (+) icon for social share buttons in Grid View?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_gridviewlimit", array(
          'label' => 'Enter the number of Social Share Buttons after which plus (+) icon will come in Grid View. Other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          )
      ));

      $this->addElement('Select', "socialshare_enable_advgridviewplusicon", array(
        'label' => "Enable plus (+) icon for social share buttons in Advanced Grid View?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_advgridviewlimit", array(
          'label' => 'Enter the number of Social Share Buttons after which plus (+) icon will come in Advanced Grid View. Other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          )
      ));

      $this->addElement('Select', "socialshare_enable_pinviewplusicon", array(
        'label' => "Enable plus (+) icon for social share buttons in Pinboard View?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_pinviewlimit", array(
          'label' => 'Enter the number of Social Share Buttons after which plus (+) icon will come in Pinboard View. Other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          )
      ));

      $this->addElement('Select', "socialshare_enable_masonryviewplusicon", array(
        'label' => "Enable plus (+) icon for social share buttons in Masonry View?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_masonryviewlimit", array(
          'label' => 'Enter the number of Social Share Buttons after which plus (+) icon will come in Masonry View. Other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          )
      ));

      $this->addElement('Select', "socialshare_enable_mapviewplusicon", array(
        'label' => "Enable plus (+) icon for social share buttons in Map View?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_mapviewlimit", array(
          'label' => 'Enter the number of Social Share Buttons after which plus (+) icon will come in Map View. Other social sharing icons will display on clicking this plus icon.',
          'value' => 2,
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          )
      ));
    }
    //Social Share Plugin work
		
    $this->addElement('Text', "limit_data", array(
        'label' => 'count (number of events to show).',
        'value' => 20,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Select', "show_limited_data", array(
			'label' => 'Show only the number of events entered in above setting. [If you choose No, then you can choose how do you want to show more events in this widget in below setting.]',
			'multiOptions' => array(
            'yes' => 'Yes',
            'no' => 'No',
        ),
        'value' => 'no',
    ));
    $this->addElement('Radio', "pagging", array(
        'label' => "Do you want the videos to be auto-loaded when users scroll down the page?",
        'multiOptions' => array(
            'button' => 'View more',
            'auto_load' => 'Auto Load',
            'pagging' => 'Pagination'
        ),
        'value' => 'auto_load',
    ));
    $this->addElement('Text', "grid_title_truncation", array(
        'label' => 'Title truncation limit for Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Text', "advgrid_title_truncation", array(
        'label' => 'Title truncation limit for Advanced Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Text', "list_title_truncation", array(
        'label' => 'Title truncation limit for List View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Text', "pinboard_title_truncation", array(
        'label' => 'Title truncation limit for Pinboard View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Text', "masonry_title_truncation", array(
        'label' => 'Title truncation limit for Masonry View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Text', "list_description_truncation", array(
        'label' => 'Description truncation limit for List View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		
		$this->addElement('Text', "masonry_description_truncation", array(
        'label' => 'Description truncation limit for Masonry View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Text', "grid_description_truncation", array(
        'label' => 'Description truncation limit for Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Text', "pinboard_description_truncation", array(
        'label' => 'Description truncation limit for Pinboard View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		
		
    $this->addElement('Text', "height", array(
        'label' => 'Enter the height of List block (in pixels).',
        'value' => '160',
    ));
    $this->addElement('Text', "width", array(
        'label' => 'Enter the width of List block (in pixels).',
        'value' => '140',
    ));
    
    $this->addElement('Text', "photo_height", array(
        'label' => 'Enter the height of grid photo block (in pixels).',
        'value' => '160',
    ));
        $this->addElement('Text', "photo_width", array(
        'label' => 'Enter the width of grid photo block (in pixels).',
        'value' => '250',
    ));
        $this->addElement('Text', "info_height", array(
        'label' => 'Enter the height of grid info block (in pixels).',
        'value' => '160',
    ));
		
		 $this->addElement('Text', "advgrid_width", array(
			'label' => 'Enter the width of advanced grid block (in pixels).',
			'value' => '344',
		 ));
			$this->addElement('Text', "advgrid_height", array(
				'label' => 'Enter the height of advanced grid block (in pixels).',
				'value' => '222',
			));
		
      $this->addElement('Text', "pinboard_width", array(
        'label' => 'Enter the width of pinboard block (in pixels).',
        'value' => '250',
    	));
        $this->addElement('Text', "masonry_height", array(
        'label' => 'Enter the height of masonry block (in pixels).',
        'value' => '250',
    ));
    
    $this->addElement('MultiCheckbox', "search_type", array(
		'label' => "Choose from below the tab that you want to show in this widget.",
		'multiOptions' => array(
			'ongoingSPupcomming'=>'Upcoming & Ongoing',
			'upcoming'=>'Upcoming',
			'ongoing'=>'Ongoing',
			'past'=>'Past',
			'week'=>'This Week',
			'weekend'=>'This Weekend',
			'month'=>'This Month',
			'mostSPjoined' => 'Most Joined Event',
			'recentlySPcreated' => 'Recently Created',
			'mostSPviewed' => 'Most Viewed',
			'mostSPliked' => 'Most Liked',
			'mostSPcommented' => 'Most Commented',
			'mostSPrated' => 'Most Rated',
			'mostSPfavourite' => 'Most Favourite',
			'featured' => 'Featured',
			'sponsored' => 'Sponsored',
			'verified' => 'Verified',
     ),
    ));
		$counter =1;
		// setting for Upcomming & Ongoing
    $this->addElement('Text', "ongoingSPupcomming_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter,
    ));
    $this->addElement('Text', "ongoingSPupcomming_label", array(
        'value' => 'Upcoming & Ongoing',
    ));
		// setting for Upcomming
    $this->addElement('Text', "upcoming_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "upcoming_label", array(
        'value' => 'Upcoming',
    ));
		
		// setting for today
    $this->addElement('Text', "ongoing_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "ongoing_label", array(
        'value' => 'Ongoing',
    ));
		// setting for past
    $this->addElement('Text', "past_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "past_label", array(
        'value' => 'Past',
    ));
		
		// setting for Week
    $this->addElement('Text', "week_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "week_label", array(
        'value' => 'This Week',
    ));
		
		// setting for Weekend
    $this->addElement('Text', "weekend_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "weekend_label", array(
        'value' => 'This Weekend',
    ));
		
		// setting for Month
    $this->addElement('Text', "month_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' =>$counter++,
    ));
    $this->addElement('Text', "month_label", array(
        'value' => 'This Month',
    ));		
		 // setting for Most Joined Events
    $this->addElement('Text', "mostSPjoined_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "mostSPjoined_label", array(
        'value' => 'Most Joined Events',
    ));
		
		
    // setting for Recently Updated
    $this->addElement('Text', "recentlySPcreated_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => $counter++,
    ));
    $this->addElement('Text', "recentlySPcreated_label", array(
        'value' => 'Recently Created',
    ));
    // setting for Most Viewed
    $this->addElement('Text', "mostSPviewed_order", array(
        'label' => 'Most Viewed',
        'value' => $counter++,
    ));
    $this->addElement('Text', "mostSPviewed_label", array(
        'value' => 'Most Viewed',
    ));
    // setting for Most Liked
    $this->addElement('Text', "mostSPliked_order", array(
        'label' => 'Most Liked',
        'value' => $counter++,
    ));
    $this->addElement('Text', "mostSPliked_label", array(
        'value' => 'Most Liked',
    ));
    // setting for Most Commented
    $this->addElement('Text', "mostSPcommented_order", array(
        'label' => 'Most Commented',
        'value' => $counter++,
    ));
    $this->addElement('Text', "mostSPcommented_label", array(
        'value' => 'Most Commented',
    ));
    // setting for Most Rated
    $this->addElement('Text', "mostSPrated_order", array(
        'label' => 'Most Rated',
        'value' => $counter++,
    ));
    $this->addElement('Text', "mostSPrated_label", array(
        'value' => 'Most Rated',
    ));

    // setting for Most Favourite
    $this->addElement('Text', "mostSPfavourite_order", array(
        'label' => 'Most Favourite',
        'value' => $counter++,
    ));
    $this->addElement('Text', "mostSPfavourite_label", array(
        'value' => 'Most Favourite',
    ));
    
    // setting for Featured
    $this->addElement('Text', "featured_order", array(
        'label' => 'Featured',
        'value' => $counter++,
    ));
    $this->addElement('Text', "featured_label", array(
        'value' => 'Featured',
    ));
    // setting for Sponsored
    $this->addElement('Text', "sponsored_order", array(
        'label' => 'Sponsored',
        'value' => $counter++,
    ));
    $this->addElement('Text', "sponsored_label", array(
        'value' => 'Sponsored',
    ));
		// setting for Verified
    $this->addElement('Text', "verified_order", array(
        'label' => 'Verified',
        'value' => $counter++,
    ));
    $this->addElement('Text', "verified_label", array(
        'value' => 'Verified',
    ));
  }

}
