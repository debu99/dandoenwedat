<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sescredit_Api_Core extends Core_Api_Abstract {
    public function getTypes(){
        $array = array('0' => 'All Types','1' => 'By Activity', '2' => 'On Activity Deletion', '3' => 'Inviter Affiliation','4' => 'Transferred to Friends','5' => 'Received from Friends', '6' => 'On Membership Upgrade', '7' => 'Buy from site');

        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesproduct')){
            $array[8] = "Product Purchased";
        }

        return $array;
    }
  public function getWidgetParams($widgetId) {
    $db = Engine_Db_Table::getDefaultAdapter();
    $params = $db->select()
            ->from('engine4_core_content', 'params')
            ->where('`content_id` = ?', $widgetId)
            ->query()
            ->fetchColumn();
    return json_decode($params, true);
  }
  public function validateCreditPurchase($module = "",$cartTotalPrice = 0,$redeemCreditValue = 0){
      $session = new Zend_Session_Namespace('sescredit_redeem_purchase');
      $session->unsetAll();
      //fetch user credit points
      $totalUserCredit = Engine_Api::_()->getDbTable('details','sescredit')->getCurrentUserPoint(array('owner_id'=>Engine_Api::_()->user()->getViewer()->getIdentity()));
      if((int)$totalUserCredit < $redeemCreditValue){
          $session->error = "You have only ".$totalUserCredit." credit points";
          return array('status'=>false);
      }
      $creditConfig = Engine_Api::_()->getDbTable('managemodules','sescredit')->getModule($module);
      if($creditConfig){
          if($cartTotalPrice > 0){
                if($creditConfig->min_credit > 0 && $redeemCreditValue < $creditConfig->min_credit){
                    $session->error = "Credit points to be redeemed should be greater than or equal to ".$redeemCreditValue;
                    return array('status'=>false);
                }else if($creditConfig->min_checkout_price > 0 && $cartTotalPrice < $creditConfig->min_checkout_price){
                    $session->error = "Total cart amount should be greater than or equal to ".$creditConfig->min_checkout_price;
                    return array('status'=>false);
                }else{
                    if($creditConfig->limit_use != 100){
                        $settings = Engine_Api::_()->getApi('settings', 'core');
                        $value = $settings->getSetting('sescredit_creditvalue', 0);
                        $allowedCreditUsage = ($creditConfig->limit_use / 100) * $cartTotalPrice * $value ;
                        if($allowedCreditUsage < $redeemCreditValue){
                            $session->error = "You can only redeem a maximum of ".$allowedCreditUsage.' credit points in this order.';
                            return array('status'=>false);
                        }
                        return array('status'=>true,'value'=>$allowedCreditUsage);
                    }else{
                        return array('status'=>true,'value'=>$redeemCreditValue);
                    }
                }
          }
      }
      return array('status'=>false);
  }
  public function getWidgetPageId($widgetId) {
    $db = Engine_Db_Table::getDefaultAdapter();
    $params = $db->select()
            ->from('engine4_core_content', 'page_id')
            ->where('`content_id` = ?', $widgetId)
            ->query()
            ->fetchColumn();
    return json_decode($params, true);
  }

  function multiCurrencyActive() {
    if (!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])) {
      return Engine_Api::_()->sesmultiplecurrency()->multiCurrencyActive();
    } else {
      return false;
    }
  }

  function isMultiCurrencyAvailable() {
    if (!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])) {
      return Engine_Api::_()->sesmultiplecurrency()->isMultiCurrencyAvailable();
    } else {
      return false;
    }
  }

  function getCurrencyPrice($price = 0, $givenSymbol = '', $change_rate = '') {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $precisionValue = $settings->getSetting('sesmultiplecurrency.precision', 2);
    $defaultParams['precision'] = $precisionValue;
    if (!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])) {
      return Engine_Api::_()->sesmultiplecurrency()->getCurrencyPrice($price, $givenSymbol, $change_rate);
    } else {
      $givenSymbol = $settings->getSetting('payment.currency', 'USD');
      return Zend_Registry::get('Zend_View')->locale()->toCurrency($price, $givenSymbol, $defaultParams);
    }
  }

  function getCurrentCurrency() {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if (!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])) {
      return Engine_Api::_()->sesmultiplecurrency()->getCurrentCurrency();
    } else {
      return $settings->getSetting('payment.currency', 'USD');
    }
  }

  function defaultCurrency() {
    if (!empty($_SESSION['ses_multiple_currency']['multipleCurrencyPluginActivated'])) {
      return Engine_Api::_()->sesmultiplecurrency()->defaultCurrency();
    } else {
      return Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    }
  }

  public function getCurrencySymbol($currency = '') {
    if (!$currency)
      $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
    return $curArr[$currency];
  }

  public function setPhoto($photo, $param = null) {
    if ($photo instanceof Zend_Form_Element_File)
      $file = $photo->getFileName();
    else if (is_array($photo) && !empty($photo['tmp_name']))
      $file = $photo['tmp_name'];
    else if (is_string($photo) && file_exists($photo))
      $file = $photo;
    else
      throw new Sescredit_Model_Exception('Invalid argument passed to setPhoto: ' . print_r($photo, 1));
    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'sescredit_badge',
        'parent_id' => $param['badge_id']
    );
    //Save
    $storage = Engine_Api::_()->storage();
    if ($param == 'mainPhoto') {
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(500, 500)
              ->write($path . '/m_' . $name)
              ->destroy();
    } else {
      $image = Engine_Image::factory();
      $image->open($file)
              ->resize(1600, 1600)
              ->write($path . '/m_' . $name)
              ->destroy();
    }
    //Resize image (icon)
    $image = Engine_Image::factory();
    $image->open($file);
    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;
    $image->resample($x, $y, $size, $size, 48, 48)
            ->write($path . '/is_' . $name)
            ->destroy();
    //Store
    $iMain = $storage->create($path . '/m_' . $name, $params);
    $iSquare = $storage->create($path . '/is_' . $name, $params);

    $iMain->bridge($iMain, 'thumb.profile');
    $iMain->bridge($iSquare, 'thumb.icon');

    //Remove temp files
    @unlink($path . '/m_' . $name);
    @unlink($path . '/is_' . $name);

    return $iMain->getIdentity();
  }

}
