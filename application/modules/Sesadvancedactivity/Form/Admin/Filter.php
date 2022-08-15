<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Filter.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Form_Admin_Filter extends Engine_Form
{
	
  public function init()
  {
		parent::init();
    $this
      ->clearDecorators()
      ->addDecorator('FormElements')
      ->addDecorator('Form')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'search'))
      ->addDecorator('HtmlTag2', array('tag' => 'div', 'class' => 'clear'))
      ;

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setMethod('GET');

    $titlename = new Zend_Form_Element_Text('title');
    $titlename
      ->setLabel('Title')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'));
	
	$this->addElements(array($titlename));
		
   
	 
		$active = new Zend_Form_Element_Select('active');
    $active
      ->setLabel('Status')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
      ->addDecorator('HtmlTag', array('tag' => 'div'))
      ->setMultiOptions(array(
        '' =>'',
				'1' => 'Yes',
        '0' => 'No',
      ))
      ->setValue('');
	$this->addElements(array($active));
 

  $date = new Zend_Form_Element_Text('date');
  $date
    ->setLabel('Creation Date Ex (yyyy-mm-dd)')
    ->clearDecorators()
    ->addDecorator('ViewHelper')
    ->addDecorator('Label', array('tag' => null, 'placement' => 'PREPEND'))
    ->addDecorator('HtmlTag', array('tag' => 'div'));
  
  $this->addElements(array($date));



		$submit = new Zend_Form_Element_Button('search', array('type' => 'submit'));
    $submit
      ->setLabel('Search')
      ->clearDecorators()
      ->addDecorator('ViewHelper')
      ->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'buttons'))
      ->addDecorator('HtmlTag2', array('tag' => 'div'));
		$this->addElements(array($submit));
  }
}