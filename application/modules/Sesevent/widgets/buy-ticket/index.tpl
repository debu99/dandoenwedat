<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php if($this->type != 'button'){ ?>
<?php if(!isset($this->noTicketAvailable)){ ?>
<div class="sesevent_tickets_listing_form sesbasic_bxs sesbasic_clearfix sesbm">
    <form name="ticket_purchase" id="bookNowSesevent" >
      <table>
        <thead>
          <tr class="sesbm">
            <th><?php echo $this->translate("Ticket Type"); ?></th>
            <th><?php echo $this->translate("Ticket Stats"); ?></th>
            <th ><?php echo $this->translate("Price"); ?></th>
            <th><?php echo $this->translate("Quantity"); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($this->ticket as $item): ?>
            <?php 
                $ticketsBoughtByUser = Engine_Api::_()->sesevent()->purchaseTicketByUserCount($this->viewer,$item->ticket_id); 
                $minQuatity = (int) $item->min_quantity == 0 ? 0 : (int) $item->min_quantity; 
                $maxQuatity = (int) $item->max_quantity == 0 ? 10 : (int) $item->max_quantity;
                $ticketsLeftForUser =  $maxQuatity - $ticketsBoughtByUser;
                $maxQuatity = $ticketsLeftForUser;
             ?>
            <?php 
              if($item->total > 0){
                $availableTicketSold =  Engine_Api::_()->sesevent()->purchaseTicketCount($this->event->event_id,$item->ticket_id); 
                $availableTicket = $item->total - $availableTicketSold;
              }else{
                $availableTicketSold = Engine_Api::_()->sesevent()->purchaseTicketCount($this->event->event_id,$item->ticket_id);
                $availableTicket  = 0;
                }
             ?>
            <tr class="ticketlist sesbm">
              <td style="width:70%" class="ticket_info">
                <span class="ticket_name" id="sesevent_ticket_title_<?php echo $item->ticket_id; ?>"><?php echo $item->name; ?></span>
                <span class="ticket_expiry sesbasic_text_light"><?php echo $this->translate("Last Date:"); ?> <?php echo Engine_Api::_()->sesevent()->dateFormat($item->endtime,'changetimezone',$this->event); ?> </span>
                <?php if($item->service_tax > 0){ ?>
                  <span class="ticket_tax"><?php echo $this->translate("* Exclusive of Service Tax"); ?> <?php echo @round($item->service_tax,2); ?>% </span>
                <?php } ?>
                <?php if($item->entertainment_tax > 0){ ?>
                  <span class="ticket_tax"><?php echo $this->translate("* Exclusive of Entertainment Tax"); ?> <?php echo @round($item->entertainment_tax,2); ?>% </span>
                <?php } ?>
                <p class="ticket_des"><?php echo $this->viewMore($item->description); ?></p></td>
              <td><?php if($item->total > 0){
                  echo $this->translate("%s out of %s tickets sold.",(int)$availableTicketSold,$item->total);
            }else{
              echo $this->translate("%s ticket sold.",(int)$availableTicketSold);
            } ?></td>
              <td><?php echo $item->price <= 0 ? $this->translate("FREE") : Engine_Api::_()->sesevent()->getCurrencyPrice($item->price); ?></td>
              <td><?php if((int)$availableTicketSold == 0 || (int)$item->total == 0 || (int)$availableTicketSold != (int)$item->total){ ?>
                <select data-available="<?php echo (int)$availableTicket; ?>" data-rel="<?php echo $item->ticket_id; ?>" id="ticker_id_<?php echo $item->ticket_id; ?>" class="sesevent_ticket_purchase_qty"  name="ticket_<?php echo $item->ticket_id; ?>"  style="margin:0px;">
                  <?php $counter = 0; ?>
                  <?php for($i = $minQuatity;$i <= $maxQuatity;$i++){ ?>
                  <?php if($counter == 0 && $i != 0){ ?>
                  <option value="0">0</option>
                  <?php  } ?>
                  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                  <?php $counter++; ?>
                  <?php } ?>
                </select>
                <?php }else{
              echo $this->translate("Sold Out");
            } ?></td>
            </tr>
            <?php $currency = Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>
            <!-- Total Ticket Purchase Amount -->
            <tr class="sesbm">
              <td colspan="4">
                <div class="sesevent_ticket_price_box sesbm">
                  <div class ="totAmtDiv">
                    <span><?php echo $this->translate("Total Amount"); ?> (<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>)</span>
                    <span class="totAmt"><?php echo $currency; ?>0.00</span>
                  </div>
                  <div class ="entertaimentTaxDiv" style="display:none;">
                    <span colspan="3"><?php echo $this->translate("Entertainment Tax"); ?> (<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>)</span>
                    <span class="entertaimentTaxAmt"><?php echo $currency; ?>0.00</span>
                  </div>
                  <div class="serviceTaxDiv" style="display:none;">
                    <span><?php echo $this->translate("Service Tax"); ?> (<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>)</span>
                    <span class="serviceTaxAmt"><?php echo $currency; ?>0.00</span>
                  </div>
            <?php endforeach; ?>
                <div id="couponAppliedDiv" style="display:none;">
                  <span><?php echo $this->translate("Coupon Applied"); ?> (<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>)</span>
                  <span id="couponAppliedAmt"><?php echo '-'.$currency; ?>0.00</span>
                </div>
                <div id="creditAppliedDiv" style="display:none;">
                  <span><?php echo $this->translate("Credit Points Redeemed"); ?> (<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>)</span>
                  <span id="creditAppliedAmt"><?php echo $currency; ?>0.00</span>
                </div>
                <div id="purchaseDiv" class="sesevent_ticket_price_box_total">
                  <span><?php echo $this->translate("Purchase Total"); ?> (<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>)</span>
                  <span id="purchaseAmt"><?php echo $currency; ?>0.00</span>
                </div>
            	</div>
            </td>
          </tr>
        <?php if($this->event->is_custom_term_condition){ ?>
          <tr id="eventTNC" class="sesbm">
            <td width="100%" colspan="3">
                <label for="eventTC">
                  <input id="eventTC" name="eventTC" type="checkbox" value="1">
                  <span><?php echo $this->translate("I confirm that I have read and agree to the"); ?> </span>
                </label>
              <a href="javascript:void();" onclick="window.open('<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'event-termcondition'), 'sesevent_dashboard', true); ?>','mywindow','width=710,height=600,scrollbars=yes,resizable=yes')"><?php echo $this->translate("Terms and Conditions"); ?></a></td>
          </tr>
         <?php } ?>
         <tr id="eventBUYBtn" class="sesbm">
            <td colspan="4" style="text-align:right; padding:10px;">
              <button type="submit" name="eventTKSbt" value="bookNow" ><?php echo $this->translate("Book Now"); ?></button>
            </td>
         </tr>
        </tbody>
      </table>
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('ecoupon')): ?>
          <?php  echo $this->partial('have_coupon.tpl','ecoupon',array('id'=>$this->event->event_id,'params'=>json_encode(array('resource_type'=>$this->event->getType(),'resource_id'=>$this->event->event_id,'is_package'=>0)))); ?> 
      <?php endif; ?>
      
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescredit')) { ?>
        <?php  echo $this->partial('apply_credit.tpl','sescredit',array('id'=>$this->event->event_id,'moduleName'=>'sesevent','item_id'=>$this->event->event_id)); ?> 
      <?php } ?>
    </form>
    <div class="sesbasic_loading_cont_overlay" style="display:none;"></div>
	</div>
<script type="application/javascript">
 var itemPrice<?php echo $this->event->event_id; ?> = 0;
//validate function
function validateBookPayment(){
	var valid = false;
	sesJqueryObject('select[id^="ticker_id_"]').each(function(){
		if(sesJqueryObject(this).val() > 0){
			valid = true;
			return false;
		}
	});
	if(!valid){
		 alert("<?php echo $this->translate('Please select Ticket Quantity'); ?>");
		 return false;
	}else if(sesJqueryObject('#eventTC').length && !sesJqueryObject('#eventTC').is(':checked')){
			alert("<?php echo $this->translate('Please agree Term & Condition'); ?>");
		 return false
	}else
		return true;
}
sesJqueryObject(document).ready(function(){
	sesJqueryObject('#bookNowSesevent')[0].reset();	
	if(!sesJqueryObject('select[id^="ticker_id_"]').length){
			sesJqueryObject('#eventTNC').hide();
			sesJqueryObject('#eventBUYBtn').hide();
	}
})
sesJqueryObject('#bookNowSesevent').submit(function(e){
	var validation = validateBookPayment();
	if(!validation){
		e.preventDefault();
		return false;	
	}else{
		var tickets = [];
	sesJqueryObject('select[id^="ticker_id_"]').each(function () {
	 oldTicketVal[sesJqueryObject(this).attr('data-rel')] = sesJqueryObject(this).val();
   tickets.push({
			value:sesJqueryObject(this).val(),
			id : sesJqueryObject(this).attr('data-rel'),
		});
	});

	var currency = "<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>";
	new Request.JSON({
      method: 'post',
      url : "<?php echo $this->url(array('event_id' => $this->event->event_id,'action'=>'save-checkout','controller'=>'ajax','module'=>'sesevent'), 'default', true); ?>",
      data : {
        format : 'json',
				data:tickets,
				currency:currency,
      },
      onComplete: function(response) {
				if(!response){
					alert("<?php echo $this->translate('Something went wrong,try again later'); ?>");	
					return false;
				}else if(sesJqueryObject.isArray(response) &&  response.length && !response.redirect){
						var string = '';
						for(i=0;i<response.length;i++){
							sesJqueryObject('#ticker_id_'+response[i].id).val('0');
							if(response[i].availability == 0){
								sesJqueryObject('#ticker_id_'+response[i].id).hide();
								sesJqueryObject('Sold Out').insertAfter(sesJqueryObject('#ticker_id_'+response[i].id));
							}
							string += 'There are only '+response[i].availability+' tickets left in '+sesJqueryObject('#sesevent_ticket_title_'+response[i].id).html()+'\n';
						}
						alert(string);
						getAjaxTicketChangeData();
						return false;
				}else{
						window.location.href = response.redirect;
				}
			}
    }).send();
		return false;
	}
});
//intialize values
<?php foreach($this->ticket as $key=>$value){ ?>
var ticketVal<?php echo $value->ticket_id; ?>;
<?php } ?>
var oldTicketVal = [];
sesJqueryObject('.sesevent_ticket_purchase_qty').on('change',function(e){
	var dataAvail = sesJqueryObject(this).attr('data-available') ;
	var val = sesJqueryObject(this).val();
	if(dataAvail != 0 && parseFloat(val) > parseFloat(dataAvail)){
			sesJqueryObject(this).val(oldTicketVal[sesJqueryObject(this).attr('data-rel')]);
			alert("<?php echo $this->translate('Only '); ?>"+dataAvail+"<?php echo $this->translate(' tickets left'); ?>");
			return false;
	}
	var ticketId = sesJqueryObject(this).attr('data-rel');
	var value = sesJqueryObject(this).val();
	getAjaxTicketChangeData();
});
/*sesJqueryObject(document).ready(function(){
	if(sesJqueryObject('.sesevent_ticket_purchase_qty').length == 1){
	var objectValue = sesJqueryObject('.sesevent_ticket_purchase_qty').eq(0).children().eq(0).attr('value');
	if(objectValue == 0)
		objectValue = sesJqueryObject('.sesevent_ticket_purchase_qty').eq(0).children().eq(1).attr('value');
	sesJqueryObject('.sesevent_ticket_purchase_qty').eq(0).val(objectValue).trigger('change');
	}
});*/
var currency = "<?php echo Engine_Api::_()->sesevent()->getCurrentCurrency(); ?>";
var purchaseTotal = 0;
function getAjaxTicketChangeData(){
	var tickets = [];
	sesJqueryObject('select[id^="ticker_id_"]').each(function () {
	 oldTicketVal[sesJqueryObject(this).attr('data-rel')] = sesJqueryObject(this).val();
   tickets.push({
		value:sesJqueryObject(this).val(),
		id : sesJqueryObject(this).attr('data-rel'),
	});
});
	new Request.JSON({
      method: 'post',
      url : "<?php echo $this->url(array('event_id' => $this->event->event_id,'action'=>'checkout','controller'=>'ajax','module'=>'sesevent'), 'default', true); ?>",
      data : {
        format : 'json',
				data:tickets,
				currency:currency,
      },
      onComplete: function(response) {
				if(response){
					var service_tax = response.service_tax;
					var entertainment_tax = response.entertainment_tax;
					purchaseTotal = response.purchaseTotal;
					if(parseFloat(service_tax) <= 0){
						sesJqueryObject('.serviceTaxDiv').hide();
						sesJqueryObject('.serviceTaxAmt').html(currency+'0.00');
					}else{
						sesJqueryObject('.serviceTaxDiv').show();
						sesJqueryObject('.serviceTaxAmt').html(service_tax);
					}
					if(parseFloat(entertainment_tax) <= 0){
						sesJqueryObject('.entertaimentTaxDiv').hide();
						sesJqueryObject('.entertaimentTaxAmt').html(currency+'0.00');
					}else{
						sesJqueryObject('.entertaimentTaxDiv').show();
						sesJqueryObject('.entertaimentTaxAmt').html(entertainment_tax);
					}
					itemPrice<?php echo $this->event->event_id; ?> = response.price.substr(1);
					if(parseFloat(purchaseTotal.substr(1)) != 0)
						sesJqueryObject('#purchaseAmt').html(purchaseTotal);
					else
						sesJqueryObject('#purchaseAmt').html(currency+'0.00');
					if(parseFloat(response.price) != 0) {
						sesJqueryObject('.totAmt').html(response.price);
						if(typeof sesJqueryObject('#coupon_code_value_<?php echo $this->event->event_id; ?>') != 'undefined') {
              sesJqueryObject('#coupon_code_value_<?php echo $this->event->event_id; ?>').attr('data-amount',response.price.substr(1));
              sesJqueryObject('#apply_coupon_code_<?php echo $this->event->event_id; ?>').trigger('click');
						}
						if(typeof sesJqueryObject('#credit_value_<?php echo $this->event->event_id; ?>') != 'undefined') {
              sesJqueryObject('#credit_value_<?php echo $this->event->event_id; ?>').attr('data-amount',response.price.substr(1));
              sesJqueryObject('#sescredit_apply_credit_<?php echo $this->event->event_id; ?>').trigger('click');
						}
					} else {
						sesJqueryObject('.totAmt').html(currency+'0.00');
						if(typeof sesJqueryObject('#coupon_code_value_<?php echo $this->event->event_id; ?>') != 'undefined') {
              sesJqueryObject('#coupon_code_value_<?php echo $this->event->event_id; ?>').attr('data-amount','0');
              sesJqueryObject('#apply_coupon_code_<?php echo $this->event->event_id; ?>').trigger('click');
						}
						if(typeof sesJqueryObject('#credit_value_<?php echo $this->event->event_id; ?>') != 'undefined') {
              sesJqueryObject('#credit_value_<?php echo $this->event->event_id; ?>').attr('data-amount','0');
              sesJqueryObject('#sescredit_apply_credit_<?php echo $this->event->event_id; ?>').trigger('click');
						}
          }
				}
			}
    }).send();
}
function couponApplied<?php echo $this->event->event_id; ?>(obj) {
    if(obj.status){
      if(parseFloat(obj.discount_amount) <= 0){
        sesJqueryObject('#couponAppliedDiv').hide();
        sesJqueryObject('#couponAppliedAmt').html(currency+'0.00');
      }else{
        sesJqueryObject('#couponAppliedDiv').show();
        sesJqueryObject('#couponAppliedAmt').html('-'+purchaseTotal[0]+obj.discount_amount);
      }
      if(parseFloat(purchaseTotal) != 0) {
        sesJqueryObject('#purchaseAmt').html(purchaseTotal[0]+(parseFloat(purchaseTotal.substr(1))-parseFloat(obj.discount_amount)).toFixed(2));
      }else {
        sesJqueryObject('#purchaseAmt').html(currency+'0.00');
      }
    } else {
      sesJqueryObject('#couponAppliedDiv').hide();
      sesJqueryObject('#couponAppliedAmt').html(currency+'0.00');
    }
}
function creditApplied<?php echo $this->event->event_id; ?>(obj) {
    if(obj.status){
      if(parseFloat(obj.purchaseValue.substr(1)) <= 0){
        sesJqueryObject('#creditAppliedDiv').hide();
        sesJqueryObject('#creditAppliedAmt').html(currency+'0.00');
      }else{
        sesJqueryObject('#creditAppliedDiv').show();
        sesJqueryObject('#creditAppliedAmt').html('-'+purchaseTotal[0]+obj.purchaseValue.substr(1));
      }
      if(parseFloat(purchaseTotal) != 0) {
        sesJqueryObject('#purchaseAmt').html(purchaseTotal[0]+(parseFloat(purchaseTotal.substr(1).replace(',',''))-parseFloat(obj.purchaseValue.substr(1).replace(',',''))).toFixed(2));
      }else {
        sesJqueryObject('#purchaseAmt').html(currency+'0.00');
      }
    } else {
      sesJqueryObject('#creditAppliedDiv').hide();
      sesJqueryObject('#creditAppliedAmt').html(currency+'0.00');
    }
}
</script>
<?php }else{ ?>
	<div class="tip">
    <span>
      <?php echo $this->translate('No ticket available.');?>
    </span>
  </div>   
<?php } ?>
<?php }else{ ?>
<div class="sesevent_tickets_listing sesbasic_bxs sesbasic_clearfix">
  <ul class="sesbasic_clearfix">
   <?php $ticketsAvailable = false ?>
	 <?php foreach($this->ticket as $item): ?>
      <?php 
        $ticketsBoughtByUser = Engine_Api::_()->sesevent()->purchaseTicketByUserCount($this->viewer,$item->ticket_id); 
        $minQuatity = (int) $item->min_quantity == 0 ? 0 : (int) $item->min_quantity; 
        $maxQuatity = (int) $item->max_quantity == 0 ? 10 : (int) $item->max_quantity;
        $ticketsLeftForUser =  $maxQuatity - $ticketsBoughtByUser;
      ?>
      <?php 
       if($item->total > 0){
        $availableTicketSold =  Engine_Api::_()->sesevent()->purchaseTicketCount($this->event->event_id,$item->ticket_id); 
        $availableTicket = $item->total - $availableTicketSold;
        if($availableTicket > 0 && $ticketsLeftForUser > 0) $ticketsAvailable = true;
       }else{
        $availableTicketSold = 0;
        $availableTicket  = 0;
        }
      ?>
    	<li class="sesbm sesbasic_clearfix sesbm">
        <div class="ticket_price">
        	<?php if($item->price <= 0){ ?>
          		<?php echo $this->translate("FREE"); ?>
          <?php }else{ ?>
          	<?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->price); ?>
          <?php } ?>
        </div>
        <div class="ticket_info">
          <span class="ticket_name"><?php echo $item->name; ?></span>
          <span class="ticket_expiry sesbasic_text_light"><i class="far fa-clock sesbasic_text_light"></i>Last Date: <?php echo Engine_Api::_()->sesevent()->dateFormat($item->endtime,'changetimezone',$this->event,'M d, Y h:m A'); ?> </span>
          <?php if($item->service_tax > 0){ ?>
          	<span class="ticket_tax"><?php echo $this->translate("* Exclusive of Service Tax"); ?> : <?php echo @round($item->service_tax,2); ?>% </span>
          <?php } ?>
          <?php if($item->entertainment_tax > 0){ ?>
          	<span class="ticket_tax"><?php echo $this->translate("* Exclusive of Entertainment Tax"); ?> : <?php echo @round($item->entertainment_tax,2); ?>% </span>
          <?php } ?>
          <p class="ticket_des"><?php echo $this->viewMore($item->description); ?></p>
      	</div>
      </li>    
   <?php endforeach; ?>
  </ul>
  <div class="sesbasic_clearfix sesevent_tickets_booking_btn">
  	<?php if($ticketsAvailable){ ?>
      <a class="sesbasic_link_btn" href="<?php echo $this->url(array('event_id' => $this->event->custom_url), 'sesevent_ticket', true); ?>"><?php echo $this->translate("Book Now"); ?></a>
    <?php } else if($availableTicket <= 0) { ?>
      <a class="sesbasic_link_btn"><?php echo $this->translate("Sold Out"); ?></a>
    <?php } else { ?>   
      <a class="sesbasic_link_btn"><?php echo $this->translate("Ticket Limit Reached")." ({$maxQuatity})"; ?></a>
      <?php } ?>  
  </div>
</div>
<?php } ?>
