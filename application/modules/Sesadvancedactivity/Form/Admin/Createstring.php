<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Createstring.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Createstring extends Engine_Form {

  public function init() {
  
    $headScript = new Zend_View_Helper_HeadScript();
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
    
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->setTitle('Add New String')
            ->setDescription('');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    
    // Add submit button
    $this->addElement('Text', 'string', array(
      'label' => 'Enter a string',
      'allowEmpty' => false,
      'required' => true,
      'value' => '',
    ));
    $this->addElement('Text', 'color', array(
      'label' => 'Choose color for this string.',
      'class' => 'SEScolor',
      'allowEmpty' => false,
      'required' => true,
    ));
    $this->addElement('dummy', 'stringhover1', array(
      'content'=>'<span id="stringhover"></span>'      
    ));
    $animations = array(''=>'No Effect','sesadvancedactivity-special-link'=>'Animation Type 1');
    for($i=2;$i<18;$i++)
      $animations['sesadvancedactivity-animation-'.($i-1)] = "Animation Type ".$i;
		$this->addElement('Select', 'animation', array(
      'label' => 'Choose Animation Effect for this string.',
      'multiOptions'=>$animations,
      'onChange'=>'showanimation(this)',
      'allowEmpty' => true,
      'required' => false,
    ));
    $this->addElement('Button', 'submit', array(
      'type' => 'submit',
      'label' => 'Add',
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
