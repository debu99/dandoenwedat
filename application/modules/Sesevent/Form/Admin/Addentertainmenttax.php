<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Addentertainmenttax.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Addentertainmenttax extends Engine_Form {

  public function init() {
		// Init form
    $this
      ->setTitle('Add New Entertainment Tax')
      ->setDescription('')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;
		 $this->addElement('Text', "entertainment_tax", array(
        'label' => 'Entertainment Tax Value(%).',
        'value' => ''
    ));
		
		$checkValidator = new Engine_Validate_Callback(array($this, 'checkValueValidate'), $this->entertainment_tax);
		$checkValidator->setMessage("Please enter valid Entertainment tax value.");
		$this->entertainment_tax->addValidator($checkValidator);
		$this->addElement('Button', 'button', array(
        'type' => 'submit',
				'label'=>'Create',
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
    $this->addDisplayGroup(array('button', 'cancel'), 'buttons');
  }
	 public function checkValueValidate($value)
  {
		if(strpos($value,'.' === FALSE)){
			if(!is_numeric($value))	{
				return false;	
			}
		}else{
			$ex = explode('.',$value);
			if(count($ex)>2)
				return false;
			foreach($ex as $val){
				if(!is_numeric($val))	{
					return false;
				}
			}
		}
			return true;		
  }
}