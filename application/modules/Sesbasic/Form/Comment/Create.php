<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Create.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Comment_Create extends Engine_Form {

  public function init() {
    $this->clearDecorators()
            ->addDecorator('FormElements')
            ->addDecorator('Form')
            ->setAttrib('class', null)
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    //$allowed_html = Engine_Api::_()->getApi('settings', 'core')->core_general_commenthtml;
    // Member Level specific 
     $viewer = Engine_Api::_()->user()->getViewer();
     $allowed_html = "";
     if($viewer->getIdentity()){
      $allowed_html = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'commentHtml');
      } 
     $this->addElement('Textarea', 'body', array(
      'rows' => 1,
			'placeholder' => 'Post a Comment...',
			'class'=>'commentTextarea',
			'onkeyup'=>"if(isEnterPressed(event,this)){}",
      'decorators' => array(
      'ViewHelper'
      ),
      'filters' => array(
      new Engine_Filter_Html(array('AllowedTags'=>$allowed_html)),
      //new Engine_Filter_HtmlSpecialChars(),
      //new Engine_Filter_EnableLinks(),
      new Engine_Filter_Censor(),
      ),
      )); 
//    $this->addElement('Text', 'body', array(
//        'style' => 'width:100%'
//    ));
    //if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ) {
    //$this->addElement('captcha', 'captcha', Engine_Api::_()->core()->getCaptchaOptions());
    // }

    /* $this->addElement('Button', 'submit', array(
      'type' => 'submit',
      'ignore' => true,
      'label' => 'Post Comment',
      'decorators' => array(
      'ViewHelper',
      )
      )); */

    $this->addElement('Hidden', 'type', array(
        'order' => 990,
        'validators' => array(
        // @todo won't work now that item types can have underscores >.>
        // 'Alnum'
        ),
    ));

    $this->addElement('Hidden', 'identity', array(
        'order' => 991,
        'validators' => array(
            'Int'
        ),
    ));
    $this->addElement('Hidden', 'widget_identity', array(
        'order' => 992,
        'validators' => array(
            'Int'
        ),
    ));
  }

}