<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Rsvp.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Rsvp extends Engine_Form
{
  public function init()
  {
		$headScript = new Zend_View_Helper_HeadScript();
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
		
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$script = "var hashSign = '#';";
		$view->headScript()->appendScript($script);		
		$this->addElement('Text', "attntextColor", array(
        'label' => '"Attending" Selected Text Color',
        'value' => '#fff',
				'class' => 'SEScolor',
    ));
		$this->addElement('Text', "attnbagcolor", array(
        'label' => '"Attending" Selected Background Color',
        'value' => '#ea623d',
				'class' => 'SEScolor',
    ));
		
		$this->addElement('Text', "mbattntextColor", array(
        'label' => '"May Be Attending" Selected Text Color',
        'value' => '#fff',
				'class' => 'SEScolor',
    ));
		$this->addElement('Text', "mbattnbagcolor", array(
        'label' => '"May Be Attending" Selected Background Color',
        'value' => '#ea623d',
				'class' => 'SEScolor',
    ));
		
		$this->addElement('Text', "nattntextColor", array(
        'label' => '"Not Attending" Selected Text Color',
        'value' => '#fff',
				'class' => 'SEScolor',
    ));
		$this->addElement('Text', "nattnbagcolor", array(
        'label' => '"Not Attending" Selected Background Color',
        'value' => '#ea623d',
				'class' => 'SEScolor',
    ));
		
	}
}
?>