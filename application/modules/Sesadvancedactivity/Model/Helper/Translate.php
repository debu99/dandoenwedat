<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Translate.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */


class Sesadvancedactivity_Model_Helper_Translate extends Sesadvancedactivity_Model_Helper_Abstract
{
  /**
   *
   * @param string $value
   * @return string
   */
  public function direct($value, $noTranslate = false,$separator = ' &rarr; ')
  {
    $translate = Zend_Registry::get('Zend_Translate');
    if( !$noTranslate && $translate instanceof Zend_Translate ) {
      $tmp = $translate->translate($value);
      return $tmp;
    } else {
      return $value;
    }
  }
}
