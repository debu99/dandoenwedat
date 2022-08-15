<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Subscription.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Signup_Subscription extends Engine_Form
{
  protected $_isSignup = true;
  
  protected $_packages;
  
  public function setIsSignup($flag)
  {
    $this->_isSignup = (bool) $flag;
  }
  
  public function init()
  {
    $this
      ->setTitle('Subscription Plan')
      ->setDescription('Please select a subscription plan from the list below.')
      ;

    // Get available subscriptions
    $multiOptions = array();
    $this->_packages = Engine_Api::_()->getDbtable('packages', 'payment')->getEnabledPackages($this->_isSignup);
    foreach( $this->_packages as $package ) {
      $multiOptions[$package->package_id] = $package->title
        . ' (' . $package->getPackageDescription() . ')'
        ;
    }
    if ($this->_isSignup) {
      $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_signup', true));
    }
    $this->addElement('Radio', 'package_id', array(
      'label' => 'Choose Plan:',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => $multiOptions,
    ));
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Continue',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
  
  public function getPackages()
  {
    return $this->_packages;
  }
  public function setPackages($packages)
  {
    $this->_packages = $packages;
  }
}
