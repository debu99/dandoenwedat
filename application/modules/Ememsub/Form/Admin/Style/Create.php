<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Create.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Style_Create extends Engine_Form {

  public function init() {
    $this->setTitle('Change Style')
            ->setDescription('From here, you can change styling for each element of the subscription plan which you have created from the SE plans page.');
    $this->addElement('Text', 'column_title', array(
        'label' => 'Plan Name',
        'description' => 'Enter the name of this plan. This name is for your indication only and will not be shown at the user side.',
        'allowEmpty' => false,
        'required' => true,
    ));
    $this->addElement('Text', 'column_width', array(
        'label' => 'Column Width',
        'description' => 'Enter the width of this column in pixels.'
    ));
    $this->addElement('Text', 'column_margin', array(
        'label' => 'Column Space',
        'description' => 'Enter the margin space to the right of this column in pixels.'
    ));
    $this->addElement('Text', 'row_height', array(
        'label' => 'Rows height',
        'description' => 'Enter the height of the rows in pixels.'
    ));
    $this->addElement('Text', 'row_border_color', array(
        'label' => 'Feature rows border color',
        'class' => 'SEScolor',
        'description' => 'Choose the feature rows border color.'
    ));
    $this->addElement('Text', 'column_row_color', array(
        'label' => 'Background Color of Row Content',
        'description' => 'Choose and enter the background color of row content of this column.',
        'class' => 'SEScolor',
    ));
    $this->addElement('Text', 'column_row_text_color', array(
        'label' => 'Text Color of Row Content',
        'description' => 'Choose and enter the text color of row content of this column.',
        'class' => 'SEScolor',
    ));
    $this->addElement('Radio', 'icon_position', array(
        'label' => 'Content Alignment',
        'description' => 'Choose the alignment of content of this column.',
        'multioptions' => array('1' => 'Center', '0' => 'Left'),
        'value' => '1'
    ));
    $this->addElement('Checkbox', 'show_highlight', array(
        'label' => "Do you want to highlight this column?",
        'description'=> 'Highlight This Column',
        'value' => '',
    ));

    $this->addElement('Radio', 'show_label', array(
        'label' => 'Show Tilted Label',
        'description' => 'Do you want to show tilted label in this column? If you choose “Yes”, then you will be able to configure details for the highlight label.',
        'multioptions' => array('1' => 'Yes', '0' => 'No'),
        'value'=>0,
        'onclick' => 'showLabel(this.value);'
    ));
    
    $this->addElement('Text', 'label_text', array(
        'label' => 'Label Text',
        'description' => 'Enter the text for the label which will be shown as tilted strip.'
    ));
    
    $this->addElement('Text', 'label_color', array(
        'label' => 'Label Background Color',
        'description' => 'Choose and enter the label background color.',
        'class' => 'SEScolor',
    ));
    
    $this->addElement('Text', 'label_text_color', array(
        'label' => 'Label Text Color',
        'description' => 'Choose and enter the label text color.',
        'class' => 'SEScolor',
    ));
    
    $this->addElement('Radio', 'label_position', array(
        'label' => 'Label Alignment',
        'description' => 'Choose the alignment of label.',
        'multioptions' => array('1' => 'Right', '0' => 'Left'),
        'value' => '1',
    ));
    
     $this->addElement('Dummy', 'coulmn_header', array(
        'label' => "Column Header",
    ));
    $this->addElement('Text', 'column_name', array(
        'label' => 'Column Header Title',
        'description' => 'Enter the title of header of this column',
    ));
    $this->addElement('Textarea', 'column_description', array(
        'label' => 'Description',
        'description' => 'Enter the description about this column. [You can choose the height of this field in the “Pricing Table” widget settings in Layout Editor.]',
    ));
    $this->addElement('Text', 'column_descr_height', array(
        'label' => 'Column Description Height',
        'description' => 'Enter Column description height',
    ));
    $this->addElement('Text', 'column_color', array(
        'label' => 'Background Color of Header',
        'description' => 'Choose and enter the background color of header of this column.',
        'class' => 'SEScolor',
    ));
    $this->addElement('Text', 'column_text_color', array(
        'label' => 'Text Color of Header',
        'description' => 'Choose and enter the text color of header of this column.',
        'class' => 'SEScolor',
    ));
     $this->addElement('Dummy', 'column_footer', array(
        'label' => "Column Footer",
    ));
    $this->addElement('Text', 'footer_text', array(
        'label' => 'Column Footer Title',
        'description' => 'Enter the title of footer of this column.'
    ));
    $this->addElement('Text', 'upgrade_footer_text', array(
        'label' => 'Button Text for Upgrade Plan',
        'description' => 'Enter the text for Upgrade Plan button for this Column at Subscription page in Profile Settings.'
    ));
    $this->addElement('Text', 'footer_bg_color', array(
        'label' => 'Background Color for Footer',
        'description' => 'Choose and enter the background color of footer of this column.',
        'class' => 'SEScolor',
    ));
    $this->addElement('Text', 'footer_text_color', array(
        'label' => 'Text Color for Footer',
        'description' => 'Choose and enter the text color of footer of this column.',
        'class' => 'SEScolor',
    ));
    //Add submit button
    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
  }
}
