<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Login.php  2018-10-05 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Login extends Engine_Form_Email
{
  protected $_mode;

  public function setMode($mode)
  {
    $this->_mode = $mode;
    return $this;
  }

  public function getMode()
  {
    if( null === $this->_mode ) {
      $this->_mode = 'page';
    }
    return $this->_mode;
  }

  public function init()
  {
    $tabindex = rand();
    $this->_emailAntispamEnabled = (Engine_Api::_()->getApi('settings', 'core')
          ->getSetting('core.spam.email.antispam.login', 1) == 1);

    // Used to redirect users to the correct page after login with Facebook
    $_SESSION['redirectURL'] = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

    $description = Zend_Registry::get('Zend_Translate')->_("If you already have an account, please enter your details below. If you don't have one yet, please <a href='%s'>sign up</a> first.");
    $description= sprintf($description, Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true));

    // Init form
    $this->setTitle('Member Sign In');
    $this->setDescription($description);
    $this->setAttrib('id', 'sesabasic_form_login');
    $this->setAttrib('class', 'global_form sesbasic_login_form');
		$this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $email = Zend_Registry::get('Zend_Translate')->_('Email Address');
    $labelEmail = '';
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seselegant')) {
        $labelEmail = $email;
    }
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfbstyle')) {
        $labelEmail = $email;
    }
    // Init email
    $this->addEmailElement(array(
			'placeholder' => $email,
      'label' => $email,
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
      'validators' => array(
        'EmailAddress'
      ),

      // Fancy stuff
      'tabindex' => $tabindex++,
      'autofocus' => 'autofocus',
      'inputType' => 'email',
      'class' => 'text',
    ));

    $password = Zend_Registry::get('Zend_Translate')->_('Password');
    $labelPass = '';
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('seselegant')) {
        $labelPass = $password;
    }
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfbstyle')) {
        $labelPass = $password;
    }
    // Init password
    $this->addElement('Password', 'password', array(
			'placeholder' => $password,
      'label' => $password,
      'required' => true,
      'allowEmpty' => false,
      'tabindex' => $tabindex++,
      'filters' => array(
        'StringTrim',
      ),
    ));

    $this->addElement('Hidden', 'return_url', array(
      'value'=> Zend_Controller_Front::getInstance()->getRouter()->assemble(array()),
    ));

    //if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sestoz')) {
        //SES Work For Show and Hide Password
        $this->addElement('dummy', 'showhidepassword', array(
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'application/modules/Sesbasic/views/scripts/showhidepassword.tpl',
            ))),
            'tabindex' => $tabindex++,
        ));
    //}
    //SES Work For Show and Hide Password
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->core_spam_login ) {
        $this->addElement('captcha', 'captcha', Engine_Api::_()->core()->getCaptchaOptions(array(
            'tabindex' => $tabindex++,
            'size' => ($this->getMode() == 'column') ? 'normal' : 'normal',
        )));
    }

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Sign In',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => $tabindex++,
    ));

    // Init remember me
    $this->addElement('Checkbox', 'remember', array(
      'label' => 'Remember Me',
      'tabindex' => $tabindex++,
    ));

    $content = Zend_Registry::get('Zend_Translate')->_("<span><a href='%s'>Forgot Password?</a></span>");
    $content= sprintf($content, Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'forgot'), 'default', true));


    // Init forgot password link
    $this->addElement('Dummy', 'forgot', array(
      'content' => $content,
    ));

    $this->addDisplayGroup(array('remember', 'forgot'), 'buttons');

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login'));
  }
}
