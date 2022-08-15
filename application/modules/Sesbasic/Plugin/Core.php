<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Plugin_Core extends Zend_Controller_Plugin_Abstract {

	public function onRenderLayoutDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }

	public function onRenderLayoutMobileDefault($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }

	public function onRenderLayoutMobileDefaultSimple($event) {
    return $this->onRenderLayoutDefault($event,'simple');
  }
  
  public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    $module = $request->getModuleName();
    $controller = $request->getControllerName(); 
    $action = $request->getActionName();
    if(isset($_GET['gateway_id'])) {
      $gateway = Engine_Api::_()->getItem('payment_gateway', $_GET['gateway_id']);
    }   
    if($module == "payment" && $controller == "subscription" && $action == "gateway"){
      $request->setModuleName('sesbasic');
      $request->setControllerName('subscription');
      $request->setActionName('gateway');
    }
    if($module == "payment" && $controller == "subscription" && $action == "process"){
      $request->setModuleName('sesbasic');
      $request->setControllerName('subscription');
      $request->setActionName('process');
    }
    if($module == "payment" && $controller == "subscription" && $action == "return"){
      $request->setModuleName('sesbasic');
      $request->setControllerName('subscription');
      $request->setActionName('return');
    }
  }
	public function onRenderLayoutDefault($event) {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $themeName = $view->layout()->themes[0];
    if ($themeName == 'sesmodern' || $themeName == 'sesclean')
      include APPLICATION_PATH . '/application/modules/Sesbasic/views/scripts/theme_responsive.tpl';

		$request = Zend_Controller_Front::getInstance()->getRequest();
		$moduleName = $request->getModuleName();
		$actionName = $request->getActionName();
		$controllerName = $request->getControllerName();
		if($controllerName == 'error' && $moduleName == 'core' && $actionName == 'requireuser'){
      $headScript = new Zend_View_Helper_HeadScript();
      $headScript->prependFile(Zend_Registry::get('StaticBaseUrl')
									 .'application/modules/Sesbasic/externals/scripts/sesJquery.js');
    }
		$script =
"var videoURLsesbasic;
 var moduleName;
 var itemType;
 var sestweet_text;
 var sesbasicdisabletooltip = ".Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.disable.tooltip',0).";
 var sesbasicShowInformation = '".Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.show.information',1)."';
 ";
$script .=
            "var openVideoInLightBoxsesbasic = " . Engine_Api::_()->getApi('settings', 'core')->getSetting('sesbasic.enable.lightbox', 1) . ";
";
    $view->headScript()->appendScript($script);
    $singlecart = Engine_Api::_()->getApi('settings', 'core')->getSetting('site.enble.singlecart', 0); 
    $sesproduct_enable_module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesproduct');
    $courses_enable_module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('courses');
    if($singlecart && (!$sesproduct_enable_module || !$courses_enable_module)){
      Engine_Api::_()->sesbasic()->updateCart(0);
      Engine_Api::_()->getApi('settings', 'core')->setSetting('site_enble_singlecart', 0);
    }
    // Common photo lightbox work
    $viewer = Engine_Api::_()->user()->getViewer();
		if($viewer->getIdentity() == 0)
			$level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		else
			$level = $viewer;
    $type = Engine_Api::_()->authorization()->getPermission($level,'album','imageviewer');
    $headScript = new Zend_View_Helper_HeadScript();
    if($type == 1) {
        $headScript->appendFile(Zend_Registry::get('StaticBaseUrl')
        . 'application/modules/Sesbasic/externals/scripts/sesimagevieweradvance/photoswipe.min.js')
        ->appendFile(Zend_Registry::get('StaticBaseUrl')
        . 'application/modules/Sesbasic/externals/scripts/sesimagevieweradvance/photoswipe-ui-default.min.js')
        ->appendFile(Zend_Registry::get('StaticBaseUrl')
        . 'application/modules/Sesbasic/externals/scripts/sesimagevieweradvance/sesalbumimagevieweradvance.js')
        ->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/flexcroll.js');
        $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl
        . 'application/modules/Sesbasic/externals/styles/photoswipe.css');
    } else {
      $headScript->appendFile(Zend_Registry::get('StaticBaseUrl').'application/modules/Sesbasic/externals/scripts/sesimagevieweradvance/sesalbumimageviewerbasic.js')
      ->appendFile(Zend_Registry::get('StaticBaseUrl') . 'application/modules/Sesbasic/externals/scripts/zoom-image/wheelzoom.js');
      $view->headLink()->appendStylesheet($view->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/medialightbox.css');
    }

    $script .=
    "var openPhotoInLightBoxSesalbum = ".Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.enable.lightbox',1).";
    var sesshowShowInfomation = ".Engine_Api::_()->getApi('settings', 'core')->getSetting('sesalbum.show.information', 1).";
    ";
    $view->headScript()->appendScript($script);
    // Common photo lightbox work
    $sesproduct_enable_module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesproduct');
    $courses_enable_module = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('courses');
    if($singlecart){
			$script .= "sesJqueryObject(document).on('click','.site_add_cart_dropdown',function(e){
				e.preventDefault();
				var totalcartItems = '';
				if(sesJqueryObject(this).hasClass('active')){
						sesJqueryObject('.site_single_cart_dropdown').hide();
						sesJqueryObject('.site_add_cart_dropdown').removeClass('active');
						return;
				}
				sesJqueryObject('.site_add_cart_dropdown').addClass('active');
				if(!sesJqueryObject(this).parent().find('.site_single_cart_dropdown').length){
						sesJqueryObject(this).parent().append('<div class=\"site_single_cart_dropdown sesbasic_cart_pulldown sesbasic_header_pulldown sesbasic_clearfix sesbasic_bxs\"><div class=\"sesbasic_header_pulldown_inner\"><div class=\"sesbasic_header_pulldown_loading\"><img src=\"application/modules/Core/externals/images/loading.gif\" alt=\"Loading\" /></div></div></div>');
				}
        sesJqueryObject('.site_single_cart_dropdown').show();";
        if ($courses_enable_module) {
                    $script .= "sesJqueryObject.post('courses/cart/view',{cart_page:cartviewPage},function(res){
                          totalcartItems = totalcartItems + res;
                          sesJqueryObject('.site_single_cart_dropdown').html(totalcartItems); 
                          sesJqueryObject('.site_single_cart_dropdown').find('.sesbasic_header_pulldown_inner').each(function(){
                               if(sesJqueryObject(this).find('.sesbasic_header_pulldown_tip').length)
                                 sesJqueryObject(this).remove();
                          });
                    });";
        }
        if ($sesproduct_enable_module) {
         $script .= "sesJqueryObject.post('sesproduct/cart/view',{},function(res){
                        totalcartItems = totalcartItems + res;
                        sesJqueryObject('.site_single_cart_dropdown').html(totalcartItems); 
                        sesJqueryObject('.site_single_cart_dropdown').find('.sesbasic_header_pulldown_inner').each(function(){
                            if(sesJqueryObject(this).find('.sesbasic_header_pulldown_tip').length)
                              sesJqueryObject(this).remove();
                        });
                    });";
        }
        $script .= "
        });";
        $script .= "
          sesJqueryObject(document).click(function(e){
          totalcartItems = '';
          var elem = sesJqueryObject('.site_single_cart_dropdown').parent();
          if(!elem.has(e.target).length){
            sesJqueryObject('.site_single_cart_dropdown').hide();
            sesJqueryObject('.site_add_cart_dropdown').removeClass('active');
          }
        });";
      $view->headScript()->appendScript($script);
		}
    //Currency function moved in sesmultiplecurrency plugin
    /*$checkPaymentExtentionsEnable = Engine_Api::_()->sesbasic()->checkSesPaymentExtentionsEnable();
		$getCurrentCurrency = Engine_Api::_()->sesbasic()->getCurrentCurrency();
    if($checkPaymentExtentionsEnable && Engine_Api::_()->sesbasic()->multiCurrencyActive()) {
      $fullySupportedCurrencies = Engine_Api::_()->sesbasic()->getSupportedCurrency();
      $currencyData = '<li class="sesbasic_mini_menu_currency_chooser"><a href="javascript:;" id="sesbasic_btn_currency"><span>'.Engine_Api::_()->sesbasic()->getCurrentCurrency().'</span><i class="fa fa-caret-down"></i></a><div class="sesbasic_mini_menu_currency_chooser_dropdown" id="sesbasic_currency_change"><ul id="sesbasic_currency_change_data">';
      foreach ($fullySupportedCurrencies as $key => $values) {
				if($getCurrentCurrency == $key)
					$active ='selected';
				else
					$active ='';
        $currencyData .= '<li class="'.$active.'"><a href="javascript:;" data-rel="'.$key.'">'.$key.'</a></li>';
      }
      $currencyData .= '</ul></div></li>';
      $script = 'sesJqueryObject(document).ready(function(e){
          if(!sesJqueryObject(".sesariana_currencydropdown").length)
          sesJqueryObject("#core_menu_mini_menu").find("ul").first().append(\''.$currencyData.'\');
          else{
          sesJqueryObject(".sesariana_currencydropdown").html(\''.$currencyData.'\');
          if(!sesJqueryObject(".sesariana_currencydropdown").children().length)
            sesJqueryObject(".sesariana_currencydropdown").parent().remove();
          }
      })';
      $view->headScript()->appendScript($script);
    } else{
      $script = 'sesJqueryObject(document).ready(function(e){
            sesJqueryObject(".sesariana_currencydropdown").parent().remove();
      })';
      $view->headScript()->appendScript($script);
    }*/
    $sesalbum_enable_module = Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesalbum'));
    $sesvideo_enable_module = Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesvideo'));
    if($actionName == 'index' && $controllerName == 'index' && $moduleName == 'core'){
     } else {
      if(($sesalbum_enable_module || $sesvideo_enable_module) && Engine_Api::_()->getApi('settings', 'core')->getSetting('ses.allow.adult.filtering',0)){
        $getvalue =  Engine_Api::_()->getApi('core', 'sesbasic')->checkAdultContent();
        if($getvalue)
          $attr = 'checked=""';
        else
          $attr = '';
        $contentAdultFiltering = '<li class="onoffswitch-wrapper"><div class="onoffswitch"><input id="myonoffswitch" name="onoffswitch"  class="onoffswitch-checkbox onoffswitch-checkbox-round" type="checkbox" '.$attr.'><label for="myonoffswitch"></label></div><span>Allow 18+ Content</span></li>';
        $script = 'sesJqueryObject(document).ready(function(e){
        sesJqueryObject("#core_menu_mini_menu").find("ul").first().append(\''.$contentAdultFiltering.'\');
        })';
        $view->headScript()->appendScript($script);
      }
    }
  }

  public function onUserFormSignupAccountInitAfter($event) {
    $form = $event->getPayload();
    if($form->getElement('username') !== null) {
        $bannedUsernameValidator = new Engine_Validate_Callback(array(new Sesbasic_Plugin_Core(), 'checkBannedUsername'), $form->getElement('username')->getvalue());
        $bannedUsernameValidator->setMessage("This profile address is not available, please use another one.");
        $form->username->addValidator($bannedUsernameValidator);
    }
  }

  public function checkBannedUsername($value) {
    return (Engine_Api::_()->sesbasic()->checkBannedWord($value,"")) ? false : true;
  }
  public function checkBannedUsernameEditProfile($value) {
    return (Engine_Api::_()->sesbasic()->checkBannedWord($value,Engine_Api::_()->user()->getViewer()->username)) ? false : true;
  }
  public function onUserFormSettingsGeneralInitAfter($event) {
    $form = $event->getPayload();
    if($form->getElement('username') !== null) {
	$bannedUsernameValidator = new Engine_Validate_Callback(array(new Sesbasic_Plugin_Core(), 'checkBannedUsernameEditProfile'), $form->getElement('username')->getvalue());
    $bannedUsernameValidator->setMessage("This profile address is not available, please use another one.");
    $form->username->addValidator($bannedUsernameValidator);
    }
  }
}
