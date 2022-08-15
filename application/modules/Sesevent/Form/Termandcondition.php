<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Termandcondition.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Termandcondition extends Engine_Form {

  public function init() {
    $this->setTitle('Change Event Term & Condition')
            ->setAttrib('class', 'global_form_popup')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setMethod('POST');
    ;

    $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';
    $editorOptions = array(
        'html' => (bool) $allowed_html,
    );
    $editorOptions['plugins'] = array(
        'table', 'fullscreen', 'preview', 'paste',
        'code', 'image', 'textcolor', 'jbimages', 'link'
    );
    $editorOptions['toolbar1'] = array(
        'undo', 'redo', 'removeformat', 'pastetext', '|', 'code',
        'media', 'image', 'link', 'fullscreen',
        'preview'
    );

    $this->addElement('TinyMce', 'custom_term_condition', array(
        'label' => 'Terms & Conditions',
        'description' => 'Enter detailed Terms & Conditions about the Event.',
        'editorOptions' => $editorOptions,
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
        'label' => 'Save',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }

}
