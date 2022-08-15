<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Tabbedcountry.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Tabbedcountry extends Engine_Form {

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
    $this->addElement('MultiCheckbox', "show_criteria", array(
        'label' => "Choose from below the details that you want to show in this widget.",
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
		
    $this->addElement('Text', "limit_data", array(
        'label' => 'count (number of events to show).',
        'value' => 20,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		$this->addElement('Select', "show_item_count", array(
			'label' => 'Show Events count in this widget',
			'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
        'value' => '0',
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
		 $this->addElement('Text', "advgrid_width", array(
			'label' => 'Enter the width of advanced grid block (in pixels).',
			'value' => '344',
		 ));
			$this->addElement('Text', "advgrid_height", array(
				'label' => 'Enter the height of advanced grid block (in pixels).',
				'value' => '222',
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
		$locale = Zend_Registry::get('Zend_Translate')->getLocale();
		$territories = Zend_Locale::getTranslationList('territory', $locale, 2);
		asort($territories);
		$arrayTerr = array(''=>'');
		foreach($territories as $key=>$val)
			$arrayTerr[$val] = $val;
			
		$this->addElement('Multiselect', "country", array(
        'label' => "Choose country to be show in this widget?",
        'multiOptions' => $arrayTerr
    ));
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$headScript = new Zend_View_Helper_HeadScript();
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
		$script='sesJqueryObject(document).ready(function(){
				var params = parent.pullWidgetParams();
				sesJqueryObject("#country").val(params["country"]);
				sesJqueryObject("#country").trigger("refresh");
		})';
		$view->headScript()->appendScript($script);
     $this->addElement('Select', "criteria", array(
        'label' => "criteria by which data show in this widget.",
       'multiOptions' => array(
								'0' => 'Everyone\'s Events',
								'1' => 'Only My Friend\'s Events (only worked for logged in member)',
								'featured'=>'Featured Only',
								'sponsored'=>'Sponsored Only',
								'verified'=>'Verified Only',
								'ongoing' => 'Ongoing Events',
								'ongoingSPupcomming' => 'Ongoing & Upcomming Events',
								'past' => 'Past Events',
								'week' => 'This Week',
								'weekend' => 'This Weekends',
								'future' => 'Upcomming Events',
								'month' => 'This Month',
						),
        'value' => 'ongoing',
    ));
     
		
  }

}
