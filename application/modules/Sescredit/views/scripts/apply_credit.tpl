<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: apply_credit.tpl  2019-11-07 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php 
$creditCode = 'credit-'.$this->moduleName.'-'.$this->id.'-'.$this->item_id;
$creditCheckout = new Zend_Session_Namespace($creditCode);
if($this->moduleName == "sesevent") 
  $creditCheckout->unsetAll();
?>
<?php  if(Engine_Api::_()->getDbTable('managemodules','sescredit')->getModule($this->moduleName)) { ?>
<form method="post">
  <input type="text" value="<?php echo isset($creditCheckout->value) ? $creditCheckout->value : 0; ?>" name="credit_value" id="credit_value_<?php echo $this->id; ?>" onkeypress="return isNumberKey(event)">
  <button class="btn_left" id="sescredit_apply_credit_<?php echo $this->id; ?>" onclick ="applyCredit<?php echo $this->id; ?>('<?php echo $this->id; ?>',event)"><?php echo $this->translate("Apply Credit"); ?></button>
</form>
<p class="credit_redeem_error" id ="credit_redeem_error_<?php echo $this->id; ?>" ><?php echo isset($creditCheckout->error) ? $creditCheckout->error : ''; ?></p>
<div class="sescredit_coupon_total" style ="display:<?php echo isset($creditCheckout->item_amount) ? 'block' : 'none'; ?>">
  <div class="sescredit_cart_total">
    <h5><?php echo $this->translate("Price Details"); ?></h5>
    <table>
      <tbody>
      <tr style="display: none;">
        <td><?php echo $this->translate("Coupon Discount"); ?></td>
        <td><?php echo $this->translate("- Rs. 1000"); ?></td>
      </tr>
      </tbody>
      <tfoot>
        <tr>
          <td><?php echo $this->translate("Net Amount"); ?></td>
          <td id="item_price_<?php echo $this->id; ?>"><?php echo Engine_Api::_()->sesbasic()->getCurrencyPrice(round((isset($creditCheckout->item_amount) ? $creditCheckout->item_amount : 0),2)); ?></td>
        </tr>
        <tr style="display:<?php echo isset($creditCheckout->purchaseValue) ? 'block': 'none'; ?>">
          <td><?php echo $this->translate("Credit Points Redeemed (%s)",(isset($creditCheckout->value) ? $creditCheckout->value : 0)); ?></td>
          <td id="purchaseValue_<?php echo $this->id; ?>"><?php echo Engine_Api::_()->sesbasic()->getCurrencyPrice(round((isset($creditCheckout->purchaseValue) ? $creditCheckout->purchaseValue : 0),2)); ?></td>
        </tr>
        <tr>
          <td><?php echo $this->translate("Order Total"); ?></td>
          <td id="total_amount_<?php echo $this->id; ?>"><?php echo Engine_Api::_()->sesbasic()->getCurrencyPrice(round((isset($creditCheckout->total_amount) ? $creditCheckout->total_amount : 0),2)); ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>	
<script type="application/javascript">
      function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        return !(charCode > 31 && (charCode < 48 || charCode > 57));
    }
    function applyCredit<?php echo $this->id; ?>(key,event) {
        event.preventDefault();
        if(!sesJqueryObject("#credit_value_" +key).val())
          return false;
        var that = sesJqueryObject(this);
        new Request.HTML({
        method: 'post',
        format: 'html',
        'url': en4.core.baseUrl + 'sescredit/index/apply-credit',
        'data': {
            credit_value : sesJqueryObject("#credit_value_" +key).val(),
            id: '<?php echo $this->id; ?>',
            item_id: '<?php echo $this->item_id; ?>',
            moduleName: '<?php echo $this->moduleName; ?>',
            item_amount: itemPrice<?php echo $this->id; ?>,
        },
        onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
            var obj = jQuery.parseJSON(responseHTML);
            if(obj.status){
              sesJqueryObject('#total_amount_'+key).html(obj.total_amount);
              sesJqueryObject('#purchaseValue_'+key).html(obj.purchaseValue);
              sesJqueryObject('#item_price_'+key).html(obj.item_amount);
              sesJqueryObject('#credit_redeem_error_'+key).html(''); 
              sesJqueryObject('.sescredit_coupon_total').show();
              sesJqueryObject('#purchaseValue_'+key).parent().show();
            } else {
              sesJqueryObject('#credit_redeem_error_'+key).html(obj.message);
              sesJqueryObject('#purchaseValue_'+key).parent().hide();
            }
            if(typeof creditApplied<?php echo $this->id; ?> == 'function'){
                creditApplied<?php echo $this->id; ?>(obj);
            }
        }
      }).send();
    }
</script>
<?php } ?>
