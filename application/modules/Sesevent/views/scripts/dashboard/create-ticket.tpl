<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create-ticket.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_ajax){ 
	echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array('event' => $this->event));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
	<div class="sesbasic_dashboard_form">
  	<?php if(isset($this->error)){ ?>
  		<ul class="form-errors">
        <li>
          <?php echo $this->error ?>
        </li>
      </ul>
    <?php }else{ ?>
    <!--<div class="clear sesevent_order_view_top">
      <a href="javascript:;" class="buttonlink sesbasic_icon_back seseventticket_back_manage"><?php echo $this->translate("Back To Manage Tickets");?></a>
    </div>-->
 		<?php echo $this->form->render() ?>  
    <?php } ?>
  </div>  
<?php if(!$this->is_ajax){ ?>
	</div>
  </div>
</div>
<?php  } ?>
<style>
.displayF{
	display:block !important;
}
</style>
<script type="application/javascript">
sesJqueryObject(document).on('click','.seseventticket_back_manage',function(e){
	sesJqueryObject('#manage-ticket').trigger('click');	
});
sesJqueryObject('#currency').hide();
sesJqueryObject('#currency-wrapper').hide();
<?php if(Engine_Api::_()->sesevent()->isMultiCurrencyAvailable()){ ?>
	sesJqueryObject('#price-element').append('<span class="fa fa-retweet sesevent_convert_icon sesbasic_link_btn" id="sesevent_currency_coverter" title="<?php echo $this->translate("Convert to %s",Engine_Api::_()->sesevent()->defaultCurrency());?>"></span>');
	sesJqueryObject('#price-label').append('<span> (<?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?>)</span>');
<?php }else{ ?>
	sesJqueryObject('#price-label').append('<span> (<?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?>)</span>');
<?php } ?>

sesJqueryObject('#timezone').hide();
sesJqueryObject('<span><?php echo $this->event_timezone[$this->event->timezone]; ?></span>').insertAfter('#timezone');
function eventType(value){
 if(value == 'paid'){
		sesJqueryObject('#price').show();
		sesJqueryObject('#free_ticket_label').remove();
		sesJqueryObject('#price-element').find('.sesevent_convert_icon').show();
		sesJqueryObject('#price-label').find('span').show();
		//sesJqueryObject('#currency-wrapper').show();
		sesJqueryObject("#tax-wrapper").show();
		sesJqueryObject('#price-label').find('label').addClass('required');
	}else{
		sesJqueryObject('#price-label').find('label').removeClass('required');
		sesJqueryObject('#price').hide();
		sesJqueryObject("#tax").prop('checked', false); 
		sesJqueryObject("#tax-wrapper").hide();
		sesJqueryObject('#price').val('');		
		sesJqueryObject('#price-element').find('.sesevent_convert_icon').hide();
		sesJqueryObject('#price-label').find('span').hide();
		sesJqueryObject('<span id="free_ticket_label"><?php echo $this->translate("Free"); ?></span>').insertAfter('#price');
		//sesJqueryObject('#currency-wrapper').hide();	
	}	
}
//call ticket type function on page initialize
sesJqueryObject('input[type=radio][name=type]').on('change',function() {
	eventType(this.value);
});
 var typeVal = sesJqueryObject("input[name='type']:checked").val();
  eventType(typeVal);
//change tax checkbox code
sesJqueryObject('#tax').on('change', function() {
	var sesevent_tax = sesJqueryObject('.sesevent_tax');
    if(sesJqueryObject(this).is(':checked')){
			for(i=0;i<sesevent_tax.length;i++)
				sesJqueryObject(sesevent_tax[i]).parent().parent().show();
		}else{
			for(i=0;i<sesevent_tax.length;i++)
				sesJqueryObject(sesevent_tax[i]).parent().parent().hide();
		}
 if(sesJqueryObject(this).is(':checked')){
	if(sesJqueryObject('#service_tax_checkbox').is(':checked'))
			sesJqueryObject('#service_tax-wrapper').show();
	else
		sesJqueryObject('#service_tax-wrapper').hide();
	if(sesJqueryObject('#entertainment_tax_checkbox').is(':checked'))
			sesJqueryObject('#entertainment_tax-wrapper').show();
	else
		sesJqueryObject('#entertainment_tax-wrapper').hide();
 }
}).trigger('change');
sesJqueryObject('#service_tax_checkbox').change(function() {
		if(sesJqueryObject(this).is(':checked')){
			sesJqueryObject('#service_tax-wrapper').show();
		}else
			sesJqueryObject('#service_tax-wrapper').hide();
});
sesJqueryObject('#entertainment_tax_checkbox').change(function() {
		if(sesJqueryObject(this).is(':checked')){
			sesJqueryObject('#entertainment_tax-wrapper').show();
		}else
			sesJqueryObject('#entertainment_tax-wrapper').hide();
});
sesJqueryObject(document).on('submit','#sesevent_ticket_submit_form',function(e){
	var validation = validateForm();
	//if error comes show alert message and exit.
		if(validation)
		{
			e.preventDefault();
			if(!customAlert){
				if(sesJqueryObject(objectError).hasClass('event_calendar')){
					alert('<?php echo $this->translate("Start date must be less than end date."); ?>');
				}else{
					alert('<?php echo $this->translate("Please complete the red mark fields"); ?>');
				}
			}
			if(typeof objectError != 'undefined'){
			 var errorFirstObject = sesJqueryObject(objectError).parent().parent();
			 sesJqueryObject('html, body').animate({
        scrollTop: errorFirstObject.offset().top
    	 }, 2000);
			}
			return false;	
		}else{
			return true;
		}
});
</script>