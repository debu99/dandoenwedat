<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: FeedSettings.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Settings_FeedSettings extends Engine_Form {

  public function init() {

    $this->addElement('Select', 'design', array(
      'label' => 'Choose the Status Box Design.',
      'multiOptions' => array(
        '1' => 'All Attachments Inside',
        '2' => 'All Attachments in Popup',
        '3' => 'All Attachments on Top',
        '4' => 'All Attachments Inside - Big Icons',
      ),
      'value' => '2',
    ));

    $this->addElement('Select', 'upperdesign', array(
      'label' => 'Do you want to show media attachments above the status update box?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => '0',
    ));

    $this->addElement('Select', 'enablestatusbox', array(
      'label' => 'Do you want to enable status update box in this widget?',
      'multiOptions' => array(
        '2' => 'Yes, enable for all users.',
        '1' => 'Yes, enable for Profile owner only.',
        '0' => 'No, do not enable.',
      ),
      'value' => '2',
    ));

    $this->addElement('Select', 'feeddesign', array(
      'label' => 'Choose the Feed Design',
      'multiOptions' => array(
        '1' => 'Simple Design',
        '2' => 'Pinboard Design',
      ),
      'value' => '1',
    ));

    $this->addElement('Text', "sesact_pinboard_width", array(
      'label' => "Pinboard Width (in pixels)",
      'value' => '300',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));


    $this->addElement('Select', 'scrollfeed', array(
      'label' => 'Do you want the feeds to be auto-loaded when users scroll down the page?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => '1',
    ));

    $this->addElement('Text', 'autoloadTimes', array(
      'label' => 'Enter the feed auto-load cycle count. (If you select 3, then the feeds will be auto-loaded for 3 times as user scroll down the page,)',
      'validators' => array(
          array('Int', true),
      ),
      'value' => 3,
    ));

    $this->addElement('Text', 'statusplacehoder', array(
      'label' => 'Enter status box placeholder text.',
      'value' => "Post Something...",
    ));


    $this->addElement('Select', 'userphotoalign', array(
      'label' => 'Choose the alignment of the Member Photos in the activity feeds.',
      'multiOptions' => array(
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
      ),
      'value' => 'left',
    ));

    $this->addElement('Select', 'enablefeedbgwidget', array(
      'label' => 'Do you want to enable users to add background images to their status updates posted from this widget?',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
      'value' => 1,
    ));

    $this->addElement('Select', 'feedbgorder', array(
      'label' => 'Choose the order of background images to be shown in this widget.',
      'multiOptions' => array(
        'adminorder' => 'Admin selected order for Background Images',
        'random' => 'Show Random Background Images',
      ),
      'value' => 'random',
    ));

    $this->addElement('Select', 'enablewidthsetting', array(
      'label' => 'Do you want to manually set the height and width of photos in activity feeds? If Yes, then below settings will apply on the photos. If No, then height and width will be automatically set.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
      'value' => 0,
    ));

    //Image 1
    $this->addElement('Dummy', "sesact_image1", array(
      'label' => "Max height & max width for 1 photo",
    ));
    $this->addElement('Text', "sesact_image1_width", array(
      'label' => "Max width (in pixels)",
      'value' => '500',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image1_height", array(
      'label' => "Max height (in pixels)",
      'value' => '450',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image1', 'sesact_image1_width', 'sesact_image1_height'), 'sesact_grp1', array('disableLoadDefaultDecorators' => true));
    $sesact_grp1 = $this->getDisplayGroup('sesact_grp1');
    $sesact_grp1->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp1'))));


    //Image 2
    $this->addElement('Dummy', "sesact_image2", array(
      'label' => "Height & width for 2 photos",
    ));
    $this->addElement('Text', "sesact_image2_width", array(
      'label' => "Width (in pixels)",
      'value' => '289',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image2_height", array(
      'label' => "Height (in pixels)",
      'value' => '200',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image2', 'sesact_image2_width', 'sesact_image2_height'), 'sesact_grp2', array('disableLoadDefaultDecorators' => true));
    $sesact_grp2 = $this->getDisplayGroup('sesact_grp2');
    $sesact_grp2->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp2'))));


    //Image 3
    $this->addElement('Dummy', "sesact_image3", array(
      'label' => "Height & width for 3 photos",
    ));
    $this->addElement('Text', "sesact_image3_bigwidth", array(
      'label' => "Width (in pixels)",
      'value' => '328',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image3_bigheight", array(
      'label' => "Height (in pixels)",
      'value' => '300',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image3_smallwidth", array(
      'label' => "Width (in pixels)",
      'value' => '250',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image3_smallheight", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image3', 'sesact_image3_bigwidth', 'sesact_image3_bigheight', 'sesact_image3_smallwidth', 'sesact_image3_smallheight'), 'sesact_grp3', array('disableLoadDefaultDecorators' => true));
    $sesact_grp3 = $this->getDisplayGroup('sesact_grp3');
    $sesact_grp3->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp3'))));


    //Image 4
    $this->addElement('Dummy', "sesact_image4", array(
      'label' => "Height & width for 4 photos",
    ));
    $this->addElement('Text', "sesact_image4_bigwidth", array(
      'label' => "Width (in pixels)",
      'value' => '578',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image4_bigheight", array(
      'label' => "Height (in pixels)",
      'value' => '300',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image4_smallwidth", array(
      'label' => "Width (in pixels)",
      'value' => '192',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image4_smallheight", array(
      'label' => "Height (in pixels)",
      'value' => '100',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image4', 'sesact_image4_bigwidth', 'sesact_image4_bigheight', 'sesact_image4_smallwidth', 'sesact_image4_smallheight'), 'sesact_grp4', array('disableLoadDefaultDecorators' => true));
    $sesact_grp4 = $this->getDisplayGroup('sesact_grp4');
    $sesact_grp4->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp4'))));

    //Image 5
    $this->addElement('Dummy', "sesact_image5", array(
      'label' => "Height & width for 5 photos",
    ));
    $this->addElement('Text', "sesact_image5_bigwidth", array(
      'label' => "Width (in pixels)",
      'value' => '289',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image5_bigheight", array(
      'label' => "Height (in pixels)",
      'value' => '260',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image5_smallwidth", array(
      'label' => "Width (in pixels)",
      'value' => '289',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image5_smallheight", array(
      'label' => "Height (in pixels)",
      'value' => '130',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image5', 'sesact_image5_bigwidth', 'sesact_image5_bigheight', 'sesact_image5_smallwidth', 'sesact_image5_smallheight'), 'sesact_grp5', array('disableLoadDefaultDecorators' => true));
    $sesact_grp5 = $this->getDisplayGroup('sesact_grp5');
    $sesact_grp5->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp5'))));


    //Image 6
    $this->addElement('Dummy', "sesact_image6", array(
      'label' => "Height & width for 6 photos",
    ));
    $this->addElement('Text', "sesact_image6_width", array(
      'label' => "Width (in pixels)",
      'value' => '289',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image6_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image6', 'sesact_image6_width', 'sesact_image6_height'), 'sesact_grp6', array('disableLoadDefaultDecorators' => true));
    $sesact_grp6 = $this->getDisplayGroup('sesact_grp6');
    $sesact_grp6->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp6'))));

    //Image 7
    $this->addElement('Dummy', "sesact_image7", array(
      'label' => "Height & width for 7 photos",
    ));
    $this->addElement('Text', "sesact_image7_bigwidth", array(
      'label' => "Width (in pixels)",
      'value' => '192',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image7_bigheight", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image7_smallwidth", array(
      'label' => "Width (in pixels)",
      'value' => '144',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image7_smallheight", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image7', 'sesact_image7_bigwidth', 'sesact_image7_bigheight', 'sesact_image7_smallwidth', 'sesact_image7_smallheight'), 'sesact_grp7', array('disableLoadDefaultDecorators' => true));
    $sesact_grp7 = $this->getDisplayGroup('sesact_grp7');
    $sesact_grp7->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp7'))));


    //Image 8
    $this->addElement('Dummy', "sesact_image8", array(
      'label' => "Height & width for 8 photos",
    ));
    $this->addElement('Text', "sesact_image8_width", array(
      'label' => "Width (in pixels)",
      'value' => '144',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image8_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image8', 'sesact_image8_width', 'sesact_image8_height'), 'sesact_grp8', array('disableLoadDefaultDecorators' => true));
    $sesact_grp8 = $this->getDisplayGroup('sesact_grp8');
    $sesact_grp8->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp8'))));

    //Image 9
    $this->addElement('Dummy', "sesact_image9", array(
      'label' => "Height & width for 9 photos",
    ));
    $this->addElement('Text', "sesact_image9_width", array(
      'label' => "Width (in pixels)",
      'value' => '192',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addElement('Text', "sesact_image9_height", array(
      'label' => "Height (in pixels)",
      'value' => '150',
      'validators' => array(
          array('Int', true),
          array('GreaterThan', true, array(0)),
      )
    ));
    $this->addDisplayGroup(array('sesact_image9', 'sesact_image9_width', 'sesact_image9_height'), 'sesact_grp9', array('disableLoadDefaultDecorators' => true));
    $sesact_grp9 = $this->getDisplayGroup('sesact_grp9');
    $sesact_grp9->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'sesact_grp9'))));

  }
}
