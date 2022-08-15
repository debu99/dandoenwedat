<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Style.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Style extends Engine_Form {
  public function init() {
    $this
            ->setTitle('Event Styles')
            ->setMethod('post')
						->setAttrib('id', 'sesevent_ajax_form_submit')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('class', 'global_form_popup');

    $this->removeDecorator('FormWrapper');

    $this->addElement('Textarea', 'style', array(
        'label' => 'Custom Event Styles',
        'description' => 'You can change the colors, fonts, and styles of your event by adding CSS code below. The contents of the text area below will be output between <style> tags on your event.'
    ));
    $this->style->getDecorator('Description')->setOption('placement', 'APPEND');
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
		$request = Zend_Controller_Front::getInstance()->getRequest();
    $controllerName = $request->getControllerName();
		if($controllerName != 'dashboard'){
				$this->addElement('Cancel', 'cancel', array(
						'label' => 'cancel',
						'link' => true,
						'prependText' => ' or ',
						'onclick' => 'parent.Smoothbox.close();',
						'decorators' => array(
								'ViewHelper',
						),
				));
		
				$this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
		}
    $this->addElement('Hidden', 'id');
  }

}
