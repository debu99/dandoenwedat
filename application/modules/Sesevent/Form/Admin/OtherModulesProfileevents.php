<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: OtherModulesProfileevents.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_OtherModulesProfileevents extends Engine_Form
{
  public function init()
  {
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
    ));
		$this->addElement('Select', "openViewType", array(
			 'label' => "Default open View Type (apply if select Both View option in above tab)?",
			 'multiOptions' => array(
            'list' => 'List View',
            'grid' => 'Grid View',
            'pinboard' => 'Pinboard View',
						'advgrid' => 'Advanced Grid View',
            'masonry' => 'Masonry View',
            'map' => 'Map View',
        ),
			 'value' => 'list',
    ));
		$this->addElement('MultiCheckbox', "show_criteria", array(
			 'label' => "Data show in widget ?",
				'multiOptions' => array(
           'featuredLabel' => 'Featured Label',
            'sponsoredLabel' => 'Sponsored Label',
						'verifiedLabel' => 'Verified Label',
            'favouriteButton' => 'Favourite Button',
            'likeButton' => 'Like Button',
						'joinedcount'=>'Joined Guest Counts',
            'socialSharing' => 'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
						'listButton'=>'List Button',
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
    
		$this->addElement('Select', "show_item_count", array(
			'label' => 'Show Events count in this widget',
			'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
        'value' => '0',
    ));
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement('Text',
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
		$this->addElement('Text', "advgrid_title_truncation", array(
        'label' => 'Title truncation limit for Advanced Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement(
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
		$this->addElement(
		'Text',
    'height',
    array(
        'label' => 'Enter the height of List block (in pixels).',
        'value' => '160',
    )
		);
		$this->addElement(
		'Text',
    'width',
    array(
        'label' => 'Enter the width of List block (in pixels).',
        'value' => '250',
    )
		);
		 $this->addElement('Text', "advgrid_width", array(
			'label' => 'Enter the width of advanced grid block (in pixels).',
			'value' => '344',
		 ));
			$this->addElement('Text', "advgrid_height", array(
				'label' => 'Enter the height of advanced grid block (in pixels).',
				'value' => '222',
			));
		$this->addElement(
			'Text',
    'photo_height',
    array(
        'label' => 'Enter the height of grid photo block (in pixels).',
        'value' => '160',
    )
		);
		$this->addElement(
			'Text',
    'photo_width',
    array(
        'label' => 'Enter the width of grid photo block (in pixels).',
        'value' => '250',
    )
		);
		$this->addElement(
			'Text',
    'info_height',
    array(
        'label' => 'Enter the height of grid info block (in pixels).',
        'value' => '160',
    )
		);
		$this->addElement(
		'Text',
    'pinboard_width',
    array(
        'label' => 'Enter the width of pinboard block (in pixels).',
        'value' => '250',
    )
		);
		$this->addElement(
			'Text',
    'masonry_height',
    array(
        'label' => 'Enter the height of masonry block (in pixels).',
        'value' => '250',
    )
		);
		$this->addElement('MultiCheckbox', "search_type", array(
			 'label' => "Choose from below the Tabs that you want to show in this widget.",
			'multiOptions' => array(
					'events' => 'Events',
					'hosted' => 'Hosted Events',
					'spoked' => 'Spoked In',
					'sponsored' => 'Events Sponsored',
			),
		));
		$this->addElement('Dummy', "dummy", array(
			 'label' => "Enter the order of the Tabs to be shown in this widget. ",
    ));
		//setting for my events
		$this->addElement('Text', "events_order", array(
			 'label' => "Events",
			'value' => '1',
    ));
		// setting for hosted events
		$this->addElement('Text', "hosted_order", array(
			'label' =>'Hosted Events',
			'value' => '2',
    ));
		// setting for spoked in events
		$this->addElement('Text', "spoked_order", array(
			'label' =>'Spoked In',
			'value' => '3',
    ));
		//setting for sponsored events
		$this->addElement('Text', "sponsored_order", array(
			 'label' => "Events Sponsored",
			'value' => '4',
    ));
  }
}
