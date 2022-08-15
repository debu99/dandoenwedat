<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Profileblogs.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Profileblogs extends Engine_Form {

  public function init() {

    $this->addElement('Radio', "pagging", array(
        'label' => "Do you want the videos to be auto-loaded when users scroll down the page?",
        'multiOptions' => array(
            'button' => 'View more',
            'auto_load' => 'Yes, Auto Load.',
            'pagging' => 'No, show \'Pagination\'.'
        ),
        'value' => 'auto_load',
    ));

    $this->addElement('Text', "title_truncation_list", array(
        'label' => 'Title truncation limit for List View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));

    $this->addElement('Text', "title_truncation_grid", array(
        'label' => 'Title truncation limit for Grid View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));

    $this->addElement('Text', "DescriptionTruncationList", array(
        'label' => 'Description truncation limit for List View.',
        'value' => 45,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));

    $this->addElement('Text', "height", array(
        'label' => 'Enter the height of one video block (for Grid view in pixels).',
        'value' => '160',
    ));
    $this->addElement('Text', "width", array(
        'label' => 'Enter the width of one video block (for Grid view in pixels).',
        'value' => '140',
    ));

    $this->addElement('Text', "limit_data", array(
        'label' => 'count (number of videos to show).',
        'value' => 20,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
  }

}
