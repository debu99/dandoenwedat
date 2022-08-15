<?php

class Sesbasic_Form_Friends_Add extends User_Form_Friends
{
  public function init() 
  {
    $this->setTitle('Add Friend')
      ->setDescription('Would you like to add this member as a friend?')
      ->setAttrib('class', 'global_form_popup')
      ->setAction($_SERVER['REQUEST_URI'])
      ;

    parent::init();

    $this->addElement('Button', 'submit', array(
      'label' => 'Add Friend',
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
