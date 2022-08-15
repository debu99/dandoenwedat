<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Compose.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Compose extends Engine_Form {

  public function init() {
  
    $this->setTitle('Compose Message');
    $this->setDescription('Create your new message with the form below.')
            ->setAttrib('id', 'messages_compose');

    
    // init title
    $this->addElement('Text', 'title', array(
        'label' => 'Subject',
        'order' => 3,
        'filters' => array(
            new Engine_Filter_Censor(),
            new Engine_Filter_HtmlSpecialChars(),
        ),
    ));

    // init body - plain text
    $this->addElement('Textarea', 'body', array(
        'label' => 'Message',
        'order' => 4,
        'required' => true,
        'allowEmpty' => false,
        'filters' => array(
            new Engine_Filter_HtmlSpecialChars(),
            new Engine_Filter_Censor(),
            new Engine_Filter_EnableLinks(),
        ),
    ));
    $this->addElement('Button', 'submit', array(
        'label' => 'Send Message',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'link' => true,
        'prependText' => ' or ',
        'decorators' => array(
            'ViewHelper',
        ),
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons', array(
        'order' => 5,
        'decorators' => array(
        ),
    ));
  }

}