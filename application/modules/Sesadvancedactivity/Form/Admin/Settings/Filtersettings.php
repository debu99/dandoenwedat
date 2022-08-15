<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Filtersettings.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Settings_Filtersettings extends Engine_Form
{
  public function init()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this
            ->setTitle('Feeds Filtering Settings')
            ->setDescription('Here, you can configure the settings for the filtering in the feeds on your website. The feeds filtering enables you to provide a filtering of various feeds on different filtering criterias which you can choose from the "Filter Criterias" tab.');
		
    $count = 0;
    $this->addElement('Select', 'sesadvancedactivity_visiblesearchfilter', array(
      'label' => 'Filter Count',
      'description' => 'How many filters do you want to show in the feeds? "More" will display after this number and remaining filters will show on clicking it.',
      'multiOptions' => array(
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
          ++$count => $count,
      ),
      'value' => $settings->getSetting('sesadvancedactivity.visiblesearchfilter', 6),
    ));
    
      $this->addElement('Radio', 'sesadvancedactivity_networkbasedfiltering', array(
      'label' => 'Network Privacy Filters',
      'description' => 'Choose from below the network privacy filter options that will be shown in the feeds.',
      'multiOptions' => array(
          1 => 'Members Joined Networks',
          0 => 'All Networks of Website',
          2 => 'Do not show network filters'
      ),
      'value' => $settings->getSetting('sesadvancedactivity.networkbasedfiltering', 0),
    ));      
    
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}