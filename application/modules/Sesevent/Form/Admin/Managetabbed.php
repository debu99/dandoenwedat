<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Managetabbed.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Managetabbed extends Engine_Form {
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
        'value' => 'list',
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
        'value' => 'list',
    ));
   /*$this->addElement('Radio', "tabOption", array(
        'label' => 'Show Tab Type?',
       	'multiOptions' => array(
            'default' => 'Default',
            'advance' => 'Advanced',
						'filter'=>'Filter',
						'vertical'=>'Vertical',
        ),
        'value' => 'advance',
   ));*/
    $this->addElement('MultiCheckbox', "show_criteria", array(
        'label' => "Choose from below the details that you want to show in this widget.",
        'multiOptions' => array(
					'featuredLabel' => 'Featured Label',
					'sponsoredLabel' => 'Sponsored Label',
					'verifiedLabel' => 'Verified Label',
					'favouriteButton' => 'Favourite Button',
					'listButton'=>'List Button',
					'likeButton' => 'Like Button',
					'joinedcount'=>'Joined Guest Counts',
					'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
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
					'eventcount'=>'Events Count in Lists',
					'share'=>'Share in Lists',
					'showEventsList'=>'Show Events Lists / Hosts',
        ),
        'escape' => false,
    ));
    
    
    
    //Social Share Plugin work
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sessocialshare')) {
      
      $this->addElement('Select', "socialshare_enable_plusicon", array(
        'label' => "Enable More Icon for social share buttons?",
          'multiOptions' => array(
          '1' => 'Yes',
          '0' => 'No',
        ),
        'value' => 1,
      ));
      
      $this->addElement('Text', "socialshare_icon_limit", array(
          'label' => 'Count (number of social sites to show). If you enable More Icon, then other social sharing icons will display on clicking this plus icon.',
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
    $this->addElement('Radio', "pagging", array(
        'label' => "Do you want the videos to be auto-loaded when users scroll down the page?",
        'multiOptions' => array(
            'button' => 'View more',
            'auto_load' => 'Auto Load',
            'pagging' => 'Pagination'
        ),
        'value' => 'auto_load',
    ));
		$this->addElement('Text', "advgrid_title_truncation", array(
        'label' => 'Title truncation limit for Advanced Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
    $this->addElement('Text', "grid_title_truncation", array(
        'label' => 'Title truncation limit for Grid View.',
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
		$this->addElement('Text', "width_lists", array(
        'label' => 'Enter the width of Lists block (in pixels).',
        'value' => '140px',
    ));
		
		$this->addElement('Text', "width_hosts", array(
			'label' => 'Enter the width of Hosts grid block (in pixels).',
			'value' => '344',
		 ));
			$this->addElement('Text', "height_hosts", array(
				'label' => 'Enter the height of Hosts grid block (in pixels).',
				'value' => '222',
			));
		
		$this->addElement('Text', "advgrid_width", array(
			'label' => 'Enter the width of advanced grid block (in pixels).',
			'value' => '344',
		 ));
			$this->addElement('Text', "advgrid_height", array(
				'label' => 'Enter the height of advanced grid block (in pixels).',
				'value' => '222',
			));
    $this->addElement('Text', "height", array(
        'label' => 'Enter the height of List block (in pixels).',
        'value' => '160px',
    ));
    $this->addElement('Text', "width", array(
        'label' => 'Enter the width of List block (in pixels).',
        'value' => '140px',
    ));
    $this->addElement('Text', "photo_height", array(
        'label' => 'Enter the height of grid photo block (in pixels).',
        'value' => '160px',
    ));
        $this->addElement('Text', "photo_width", array(
        'label' => 'Enter the width of grid photo block (in pixels).',
        'value' => '250px',
    ));
        $this->addElement('Text', "info_height", array(
        'label' => 'Enter the height of grid info block (in pixels).',
        'value' => '160px',
    ));
        $this->addElement('Text', "pinboard_width", array(
        'label' => 'Enter the width of pinboard block (in pixels).',
        'value' => '250px',
    ));
        $this->addElement('Text', "masonry_height", array(
        'label' => 'Enter the height of masonry block (in pixels).',
        'value' => '250px',
    ));
		$this->addElement('MultiCheckbox', "search_type", array(
			'label' => "Choose from below the tab that you want to show in this widget.",
			'multiOptions' => array(
				'all' => 'Events',
				'joinedEvents'=>'Joined Events Only',
				'hostedEvents'=>'Hosted Events Only',
				'save' => 'Saved Events',
				'like' => 'Liked Events' ,
				'favourite' => 'Favourite Events',
				'featured'=>'Featured Events',
				'sponsored'=>'Sponsored Events',
				'verified'=>'Verified Events',
				'lists'=>'My Lists',
				'hosts'=>'My Hosts',
			),
		));
		// all Events
    $this->addElement('Text', "all_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '1',
    ));
    $this->addElement('Text', "all_label", array(
        'value' => 'All Events',
    ));
		// setting for Joined
    $this->addElement('Text', "joinedEvents_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '2',
    ));
    $this->addElement('Text', "joinedEvents_label", array(
        'value' => 'Joined Events Only',
    ));
		// setting for Hosted
    $this->addElement('Text', "hostedEvents_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '3',
    ));
    $this->addElement('Text', "hostedEvents_label", array(
        'value' => 'Hosted Events Only',
    ));
		 // setting for Saveevent
    $this->addElement('Text', "save_order", array(
         'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '4',
    ));
    $this->addElement('Text', "save_label", array(
        'value' => 'Saved Events',
    ));
		 // setting for Liked
    $this->addElement('Text', "like_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '5',
    ));
    $this->addElement('Text', "like_label", array(
        'value' => 'Liked Events',
    ));
    // setting for Favourite
    $this->addElement('Text', "favourite_order", array(
         'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '6',
    ));
    $this->addElement('Text', "favourite_label", array(
        'value' => 'Favourite Events',
    ));		
			// setting for Featured
    $this->addElement('Text', "featured_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '7',
    ));
    $this->addElement('Text', "featured_label", array(
        'value' => 'Featured Events',
    ));
		// setting for Sponsored
    $this->addElement('Text', "sponsored_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '8',
    ));
    $this->addElement('Text', "sponsored_label", array(
        'value' => 'Sponsored Events',
    ));		
		// setting for Verified
    $this->addElement('Text', "verified_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '9',
    ));
    $this->addElement('Text', "verified_label", array(
        'value' => 'Verified Events',
    ));
		// setting for Lists
    $this->addElement('Text', "lists_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '10',
    ));
    $this->addElement('Text', "lists_label", array(
        'value' => 'My Lists',
    ));		
		//setting for Hosts Events
   $this->addElement('Text', "hosts_order", array(
        'label' => "Enter The order & text for tabs to be shown in this widget. ",
        'value' => '11',
    ));
    $this->addElement('Text', "hosts_label", array(
        'value' => 'My Hosts',
    ));
  }
}