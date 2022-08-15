<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ColorChooser.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Admin_Settings_ColorChooser extends Engine_Form {

  public function init() {

    $this->setTitle('Color Chooser')
            ->setDescription('Here, you can get a color code for the color of any element. To get the color, click on the Color icon below and click on the desired color. Now, copy the color from the text box.');

    $this->addElement('Text', 'sesbasic_color', array(
      'label' => 'Color',
      'decorators' => array(array('ViewScript', array(
	'viewScript' => '_colorchooser.tpl',
	'class' => 'form element'
      )))
    ));
  }

}