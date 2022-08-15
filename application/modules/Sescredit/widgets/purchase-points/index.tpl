<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $givenSymbol = Engine_Api::_()->sescredit()->getCurrentCurrency(); $random = rand(20000,100000); ?>
<div id="sescredit_show_purchase_form" class="sescredit_purchase_points_form sesbasic_bxs">
  <?php echo $this->form->render($this); ?>
</div>
<div id="sescrdit_payment_options" class="sescrdit_purchase_payment_options sesbasic_bxs" style="display:none;">
  <a href="javascript:void(0);" id="sescredit_back_point_page" class="_back">&larr; <?php echo $this->translate("Return");?></a>
  <div class="sescredit_purchase_total">
    <div id="sescredit_receive_point" class="sesbasic_clearfix"></div>
    <div id="sescredit_pay_amount" class="sesbasic_clearfix"></div>
  </div>
  <form method="get" id='sescredit_gateway_url' action="<?php echo $this->escape($this->url(array('module'=> 'sescredit','controller' => 'payment', 'action' => 'process'),'default',true)) ?>" enctype="application/x-www-form-urlencoded">
  <div class="form-elements">
    <div id="buttons-wrapper" class="form-wrapper">
      <?php foreach( $this->gateways as $gatewayInfo ):
        $gateway = $gatewayInfo['gateway'];
        $plugin = $gatewayInfo['plugin'];
        $first = ( !isset($first) ? true : false );
        $gatewayObject = $gateway->getGateway();
        $supportedCurrencies = $gatewayObject->getSupportedCurrencies();
        if(!in_array($givenSymbol,$supportedCurrencies))
          continue;
      ?>
        <?php if( !$first ): ?>
          <?php echo $this->translate('or') ?>
        <?php endif; ?>
        <button class="sesbasic_animation" type="button" name="execute" onclick="checkGatewayId(<?php echo $gateway->gateway_id ?>);">
          <i class="fab <?php echo $gateway->title == 'Stripe' ? 'fa-cc-stripe' : 'fa-paypal'; ?>"></i><?php echo $this->translate('Pay with %1$s', $this->translate($gateway->title)) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
  </form>
  <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')): ?>
    <?php  echo $this->partial('have_coupon.tpl','ecoupon',array('id'=>$random,'params'=>json_encode(array('resource_type'=>'sescredit','is_package'=>0,'currency'=>$givenSymbol)))); ?> 
  <?php endif; ?>
</div>
<script type="text/javascript">
  <?php $currencySymbol = Engine_Api::_()->sescredit()->getCurrencySymbol(Engine_Api::_()->sescredit()->getCurrentCurrency());?>
  function checkGatewayId(gatewayId) {
    $('gateway_id').set('value', gatewayId);
    sesJqueryObject('#sescredit_purchase_point').attr('action',sesJqueryObject('#sescredit_gateway_url').attr('action'))
    sesJqueryObject('#gatewayButton').trigger('click');
  }
  var finalValue = 0;
  sesJqueryObject('#sescredit_number_point').keyup(function() {
    sesJqueryObject('.sescredit_point_error_message').remove();
    var value = parseInt(this.value);
    if(value) {
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmultiplecurrency')):?>
        <?php $currencyValue =  round(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmultiplecurrency.' . Engine_Api::_()->sescredit()->getCurrentCurrency(),1),2);?>
      <?php else:?>
        <?php $currencyValue = 1;?>
      <?php endif;?>
      finalValue = value*parseInt('<?php echo $currencyValue;?>')/parseInt('<?php echo Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.creditvalue', '100'); ?>');
      sesJqueryObject('#sescredit_number_point_value-element').html("<?php echo $currencySymbol;?>"+round2Fixed(finalValue));
    }
    else { 
      sesJqueryObject('#sescredit_number_point_value-element').html("<?php echo $currencySymbol;?>"+"0");
    }
  });
  sesJqueryObject(document).on('change','input[type=radio][name=sescredit_purchase_type]',function(){
    if (this.value == 1) {
      sesJqueryObject('#sescredit_site_offers-wrapper').show();
      sesJqueryObject('#sescredit_number_point-wrapper').hide();
      sesJqueryObject('#sescredit_number_point_value-wrapper').hide();
    }else{
      sesJqueryObject('#sescredit_site_offers-wrapper').hide();
      sesJqueryObject('#sescredit_number_point-wrapper').show();
      sesJqueryObject('#sescredit_number_point_value-wrapper').show();
    }
  });
  window.addEvent('domready', function() {
    sesJqueryObject('#gatewayButton-wrapper').hide();
    var valueStyle = sesJqueryObject('input[name=sescredit_purchase_type]:checked').val();
    if(valueStyle == 1) {
      sesJqueryObject('#sescredit_site_offers-wrapper').show();
      sesJqueryObject('#sescredit_number_point-wrapper').hide();
      sesJqueryObject('#sescredit_number_point_value-wrapper').hide();
    }
    else {
      sesJqueryObject('#sescredit_site_offers-wrapper').hide();
      sesJqueryObject('#sescredit_number_point-wrapper').show();
      sesJqueryObject('#sescredit_number_point_value-wrapper').show();
    }
  });
  var optionsArray = <?php echo $this->optionArray;?>;
  function showPaymentOption() {
    sesJqueryObject('.sescredit_point_error_message').remove();
    if(sesJqueryObject('input[name=sescredit_purchase_type]:checked').val() == 0 && sesJqueryObject('#sescredit_number_point').val() == '') {
      sesJqueryObject('#sescredit_number_point').parent().append('<span class="sescredit_point_error_message sescredit_error_message"><span>'+en4.core.language.translate("Please enter number of points.")+'</span></span>');
      return false;
    }
    sesJqueryObject('#sescredit_show_purchase_form').hide();
    sesJqueryObject('#coupon_code_value_<?php echo $random; ?>').attr('data-amount',finalValue);
    if(sesJqueryObject('input[name=sescredit_purchase_type]:checked').val() == 0) {
      sesJqueryObject('#sescredit_receive_point').html('<span>Point</span><span>'+ sesJqueryObject('#sescredit_number_point').val() + '</span>');
      sesJqueryObject('#sescredit_pay_amount').html('<span>Amount</span><span>'+ sesJqueryObject('#sescredit_number_point_value-element').html() + '</span>');
    }
    else {
      var selectedOffer = sesJqueryObject('input[name=sescredit_site_offers]:checked').val();
      sesJqueryObject('#sescredit_receive_point').html('<span>Point</span><span>'+ optionsArray[selectedOffer].point + '</span>');
      sesJqueryObject('#sescredit_pay_amount').html('<span>Amount</span><span>'+ optionsArray[selectedOffer].value + '</span>');
    }
    sesJqueryObject('#sescrdit_payment_options').show();
  }
  sesJqueryObject('#sescredit_back_point_page').on('click',function() {
    sesJqueryObject('#sescrdit_payment_options').hide();
    sesJqueryObject('#sescredit_show_purchase_form').show();
  });
  function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
  }
  function round2Fixed(value) {
    value = +value;
    if (isNaN(value))
      return NaN;
    // Shift
    value = value.toString().split('e');
    value = Math.round(+(value[0] + 'e' + (value[1] ? (+value[1] + 2) : 2)));
    // Shift back
    value = value.toString().split('e');
    return (+(value[0] + 'e' + (value[1] ? (+value[1] - 2) : -2))).toFixed(2);
  }
</script>
