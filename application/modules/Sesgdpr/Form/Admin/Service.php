<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Service.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Admin_Service extends Engine_Form {

  public function init() {
    
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this
            ->setTitle('Add New Service')
            ->setDescription('Below you can add a new 3rd party service which is used on your website directly or in any plugin / module installed on your website.');

   
    $this->addElement('Text', 'name', array(
        'label' =>'Service Name',
        'required'=>true,
        'allowEmpty'=>false
    ));
    
    $this->addElement('Textarea', 'description', array(
        'label' =>'Reason for Use',
        'required'=>true,
        'allowEmpty'=>false
    ));
    
    $this->addElement('Text', 'url', array(
        'label' =>'Website URL',
        'required'=>true,
        'allowEmpty'=>false
    ));
    
    $this->addElement('Checkbox', 'enabled', array(
        'label' =>'Enable this service',
        'required'=>true,
        'allowEmpty'=>false,
        'value'=>1
    ));
    
    
    $this->addElement('Button', 'submit', array(
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }

}
