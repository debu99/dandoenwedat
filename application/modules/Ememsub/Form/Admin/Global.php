<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Global.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Global extends Engine_Form {

  public function init() {
  
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $supportTicket = '<a href="https://socialnetworking.solutions/tickets" target="_blank">Support Ticket</a>';
    $sesSite = '<a href="https://socialnetworking.solutions/" target="_blank">SocialNetworking.Solutions website</a>';
    $descriptionLicense = sprintf('Enter your license key that is provided to you when you purchased this plugin. If you do not know your license key, please drop us a line from the %s section on %s. (Key Format: XXXX-XXXX-XXXX-XXXX)',$supportTicket,$sesSite);

    $this->addElement('Text', "ememsub_licensekey", array(
        'label' => 'Enter License key',
        'description' => $descriptionLicense,
        'allowEmpty' => false,
        'required' => true,
        'value' => $settings->getSetting('ememsub.licensekey'),
    ));
    $this->getElement('ememsub_licensekey')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    if ($settings->getSetting('ememsub.pluginactivated')) {
    
      $this->addElement('Text', 'ememsub_table_row', array(
        'label' => 'Feature Rows Count',
        'description'=>'Enter the number of feature rows that you want for each subscription plan on your website.',
        'value'=> $settings->getSetting('ememsub.table.row',4),
        'validators' => array(
            array('NotEmpty', true),
            array('GreaterThan', false, array(-1)),
            array('Between', false, array('min' => '-1', 'max' =>50, 'inclusive' => false))
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        )
      ));
      $this->addElement('Text', 'ememsub_table_title', array(
        'label' => 'Title of Pricing Table',
        'description'=>'Enter the title of the pricing table for membership subscription plans on your website.',
        'value'=> $settings->getSetting('ememsub.table.title',"")
      ));
      $this->addElement('Text', 'ememsub_table_description', array(
        'label' => 'Description of Pricing Table',
        'description'=>'Enter the description of the pricing table for membership subscription plans on your website.',
        'value'=> $settings->getSetting('ememsub.table.description',"")
      ));
      $this->addElement('Radio', 'ememsub_footer_enable', array(
        'label' => 'Show Note in Pricing Table Footer',
        'description'=>'Do you want to show a note below the pricing table? If you choose yes, then you can enter the note in the setting below which will be displayed in the footer of the pricing table on your website.',
        'multiOptions'=>array('1'=>'YES','0'=>'NO'),
        'value'=> $settings->getSetting('ememsub.footer.enable',1),
        'onchange'=>'showFooterNote(this.value)'
      ));
      $this->addElement('TinyMce', 'ememsub_footer_note', array(
          'label' => 'Note Text',
          'description'=>'Enter the text for the note which will be displayed in the footer of the pricing table on your website.',
          'value'=> $settings->getSetting('ememsub.footer.note',"")
      ));
    
      // Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Save Changes',
          'type' => 'submit',
          'ignore' => true
      ));
    } else {
      //Add submit button
      $this->addElement('Button', 'submit', array(
          'label' => 'Activate Your Plugin',
          'type' => 'submit',
          'ignore' => true
      ));
    }
  }
}
