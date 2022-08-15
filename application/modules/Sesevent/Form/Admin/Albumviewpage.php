<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Albumviewpage.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Albumviewpage extends Engine_Form
{
  public function init()
  {
		
		$this->addElement('Radio', "view_type", array(
			'label' => "Choose the View Type for photos.",
        'multiOptions' => array(
            'masonry' => 'Masonry View',
						'grid' => 'Grid View',
        ),
        'value' => 'masonry',
    ));
		$this->addElement('Select', "insideOutside", array(
			'label' => 'Choose where do you want to show the statistics of photos.',
        'multiOptions' => array(
            'inside' => 'Inside the Photo Block',
						'outside' => 'Outside the Photo Block',
        ),
        'value' => 'inside',
    ));
		$this->addElement('Select', "fixHover", array(
			'label' => 'Show photo statistics Always or when users Mouse-over on photos (this setting will work only if you choose to show information inside the Photo block.)',
        'multiOptions' => array(
           'fix' => 'Always',
					 'hover' => 'On Mouse-over',
					),
						'value' => 'fix',
    ));
	
		$this->addElement('MultiCheckbox', "show_criteria", array(
       
		'label' => "Choose from below the details that you want to show for Photos in this widget.",
        'multiOptions' => array(
						'like' => 'Likes Count',
						'comment' => 'Comments Count',
						'view' => 'Views Count',
						'title' => 'Title',
						'by' => 'Owner\'s Name',
						'socialSharing' =>'Social Share Buttons <a class="smoothbox" href="admin/sesbasic/settings/faqwidget">[FAQ]</a>',
						'likeButton' =>'Like Button',
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
			'label' => 'count (number of photos to show).',
        'value' => 20,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));		
		$this->addElement('Radio', "pagging", array(
			'label' => "Do you want the photos to be auto-loaded when users scroll down the page?",
					'multiOptions' => array(
					'auto_load' => 'Yes, Auto Load.',
					'button' => 'No, show \'View more\' link.',
					'pagging' =>'No, show \'Pagination\'.'
        ),
        'value' => 'auto_load',
    ));		
		$this->addElement('Text', "title_truncation", array(
			'label' => 'Enter photo title truncation limit.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
		
		$this->addElement('Text', "height", array(
			'label' => 'Enter the height of one photo block (in pixels).',
        'value' => '160',
				'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
        
    ));
		$this->addElement('Text', "width", array(
			'label' => 'Enter the width of one photo block for \'Grid View\' (in pixels).',
        'value' => '140',
				'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
	}
}
?>