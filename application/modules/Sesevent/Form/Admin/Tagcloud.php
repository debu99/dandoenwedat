<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Tagcloud.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Admin_Tagcloud extends Engine_Form {

  public function init() {
		$headScript = new Zend_View_Helper_HeadScript();
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
    $headScript->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
		
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$script = "var hashSign = '#';";
		$view->headScript()->appendScript($script);
    $this->addElement('Text', "color", array(
        'label' => 'The color of tag text.',
        'value' => '#00f',
				'class' => 'SEScolor',
    ));
    $this->getElement('color')->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
		
    $this->addElement('Text', "text_height", array(
        'label' => "Choose height of tag text.",
        'value' => '15',
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));

    $this->addElement('Text', "height", array(
        'label' => "Choose height of tag container (in pixels).",
        'value' => '300',
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));

    $this->addElement('Text', "itemCountPerPage", array(
        'label' => "Count (number of tags to show).",
        'value' => '50',
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    ));
  }

}
