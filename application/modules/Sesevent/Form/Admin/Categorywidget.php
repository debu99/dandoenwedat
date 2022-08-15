<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Categorywidget.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Categorywidget extends Engine_Form
{
  public function init()
  {
		$this->addElement('textarea', "description", array(
			'label' => "Category Description."
    ));
		 $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        //New File System Code
        $banner_options = array('' => '');
        $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
        foreach( $files as $file ) {
          $banner_options[$file->storage_path] = $file->name;
        }
		$fileLink = $view->baseUrl() . '/admin/files/';
		if (count($banner_options) > 1){
      $this->addElement('Select', 'sesevent_categorycover_photo', array(
          'label' => 'Event Category Default Cover Photo',
          'description' => 'Choose a default cover photo for the event categories on your website. [Note: You can add a new photo from the "File & Media Manager" section from here: File & Media Manager. Leave the field blank if you do not want to change event category default cover photo.]',
          'multiOptions' => $banner_options,
      ));
    }else{
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('There are currently no photo to add for category cover. Image to be chosen for category cover should be first uploaded from the "Layout" >> "<a href="' . $fileLink . '" target="_blank">File & Media Manager</a>" section.') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'category_cover', array(
          'label' => 'Event Category Default Cover Photo',
          'description' => $description,
      ));
      $this->category_cover->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
		
		$this->addElement('Select', "show_popular_events", array(
			'label' => "Do you want to show popular events in this widget",
			'multiOptions'=>array('1'=>'Yes,want to show popular event',0=>'No,don\'t want to show popular events'),
			'value'=>1,
    ));
			$this->addElement('Text', "title_pop", array(
			'label' => "Title for events",
			'value'=>'Popular Events',
    ));
		$this->addElement(
				'Select',
				'view',
				array(
						'label' => "Choose options of event to be show in this widget .",
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
		);
		$this->addElement(
				'Select',
				'info',
				array(
						'label' => "choose criteria by which event shown in this widget.",
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
		);
	}
}