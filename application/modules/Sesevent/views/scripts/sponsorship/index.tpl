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
<div class="layout_middle">
  <div class="sesevent_ticket_order_page sesbasic_bxs generic_layout_container layout_core_content"> 
    <div class="sesevent_ticket_order_page_right">
    	<div class="sesevent_ticket_order_info_box sesbm sesbasic_bxs">
        <span class="sesevent_ticket_order_info_box_title">Order Information
          <a href="<?php echo $this->url(array('event_id' => $this->event->custom_url), 'sesevent_ticket', true); ?>" class="fa fa-times" title="<?php echo $this->translate('Cancel Order');?>"></a>
        </span>
        <div class="sesevent_ticket_order_info">
          <div class="sesbasic_clearfix">
          <div class="sesevent_ticket_order_info_photo">
            <?php echo $this->htmlLink($this->event->getHref(), $this->itemPhoto($this->event, 'thumb.icon')) ?>
          </div>
          <div class="sesevent_ticket_order_info_name"><?php echo $this->htmlLink($this->event->getHref(),$this->event->getTitle()); ?></div>
          </div>
          <div class="sesevent_ticket_order_info_stats sesbasic_clearfix">
          <?php if($this->event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)){ ?>
            <span>
              <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->event->location; ?>"></i>
              <span>	
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                  <a href="<?php echo $this->url(array('resource_id' => $this->event->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $this->event->location ?></a>
                <?php } else { ?>
                  <?php echo $this->event->location;?>
                <?php } ?>
              </span>
            </span>
            <?php } ?>
            <span>
              <i class="far fa-calendar-alt sesbasic_text_light" title=""></i>
              <span>
              	<?php echo Engine_Api::_()->sesevent()->dateFormat($this->event->starttime); ?> to <?php echo Engine_Api::_()->sesevent()->dateFormat($this->event->endtime); ?>
               </span>
            </span>
          </div>
          <div class="sesevent_ticket_order_info_summary sesbm">
            <span class="sesevent_ticket_order_info_box_title">Order Summary</span>
          <?php foreach($this->ticketDetail->toArray() as $valTicket){ ?>
            <p class="sesbasic_clearfix">
              <span><?php echo $valTicket['title']; ?></span>
              <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($valTicket['price']); ?></span>
            </p>
          <?php } ?>
            <p class="sesbasic_clearfix">
              <span>Total Tax</span>
              <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice(($this->order->total_service_tax+$this->order->total_entertainment_tax)); ?></span>
            </p>
            <p class="sesbasic_clearfix">
              <span>Grand Total</span>
              <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice(($this->order->total_service_tax+$this->order->total_entertainment_tax+$this->order->total_amount)); ?></span>
            </p>
          </div>
        </div>
    	</div>
    </div>  
    <div class="sesevent_ticket_order_page_left">
      <h3>Tickets Owner Details</h3>  
      <div class="sesevent_ticket_order_details">
        <div class="sesevent_ticket_order_form_hint_txt">Enter Attendee Information for each Tickets. Fields that are marked (*) are mandatory.</div>
       <!-- OWNER INFO -->
        <div class="sesevent_ticket_order_details_box">
         <div class="sesevent_ticket_order_details_box_title sesbm">Owner Information</div>
          <div class="sesevent_ticket_order_details_box_fields sesbm sesbasic_clearfix">
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="ownerFname">First Name <span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="ownerFname" type="text" name="ownerFname" value="<?php echo isset($this->fnamelname['first_name']) ? $this->fnamelname['first_name'] : '' ?>" />
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="ownerLname">Last Name <span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="ownerLname" type="text" name="ownerLname" value="<?php echo isset($this->fnamelname['last_name']) ? $this->fnamelname['last_name'] : '' ?>" />
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="ownerEmail">Email <span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="ownerEmail" type="text" name="ownerEmail" value="<?php echo $this->viewer->email; ?>" />
              </div>
            </div>
          </div>
       </div>
       <!-- fill detail as per owner -->
       <div class="sesevent_ticket_order_page_checkbox">
        <label for="fillDt">
          <input id="fillDt" name="chOn" type="checkbox" value="0">
          <span> Fill Ticket detail same as owner</span>
        </label>
      </div>
      <form name="ticket" id="ticketDtEvn" method="post" action="<?php echo $this->url(array('event_id' => $this->event->custom_url,'controller'=>'order','order_id'=>$this->order->order_id,'action'=>'checkout'), 'sesevent_order', true); ?>">
       <?php foreach($this->ticketDetail->toArray() as $valTicket){ ?>
        <div class="sesevent_ticket_order_details_box">
          <div class="sesevent_ticket_order_details_box_title sesbm"><?php echo $valTicket['title']; ?></div>		
         <?php for($i=0;$i<$valTicket['quantity'];$i++){ ?>
          <div class="sesevent_ticket_order_details_box_fields sesbm sesbasic_clearfix">
            <div class="sesevent_ticket_order_ticket_no"><?php echo $this->translate("Ticket ").($i+1); ?></div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="<?php echo 'fName'.($i+1+$valTicket['ticket_id']); ?>"><?php echo $this->translate("First Name");?> <span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="<?php echo 'fName'.($i+1+$valTicket['ticket_id']); ?>" class="ticket_owner_fname" type="text" name="firstName[<?php echo $valTicket['ticket_id']; ?>][<?php echo $i+1; ?>]" value="" /><br />
                <span class="required noDisp"><?php echo $this->translate("Please enter first name.");?></span>
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="<?php echo 'lName'.($i+1+$valTicket['ticket_id']); ?>"><?php echo $this->translate("Last Name";?><span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="<?php echo 'lName'.($i+1+$valTicket['ticket_id']); ?>" type="text" class="ticket_owner_lname" name="lastName[<?php echo $valTicket['ticket_id']; ?>][<?php echo $i+1; ?>]"  value="" /><br />
                <span class="required noDisp errormsg"><?php echo $this->translate("Please enter last name.");?></span>
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="<?php echo 'email'.($i+1+$valTicket['ticket_id']); ?>">Email <span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="<?php echo 'email'.($i+1+$valTicket['ticket_id']); ?>" type="text" class="ticket_owner_email" name="email[<?php echo $valTicket['ticket_id']; ?>][<?php echo $i+1; ?>]" value="" /><br />
                <span class="required noDisp"><?php echo $this->translate("Please enter valid email.");?></span>
              </div>
            </div>
          </div>
         <?php } ?>
        </div>
        <?php } ?>
        <button type="submit" name="submit" value="submit" id="sbtBtn">Continue</button>
      </form>
      </div>
    </div>
  </div> 
</div>
<script type="application/javascript">
function validateForm(){
	valid = true;
	sesJqueryObject('#ticketDtEvn').find(':input[type=text]').each(function(){
			if(!sesJqueryObject(this).val() || (sesJqueryObject(this).hasClass('ticket_owner_email') && !validateEmail(sesJqueryObject(this).val()))){
				valid = false;
				sesJqueryObject(this).parent().find('span').show();
			}else{
				sesJqueryObject(this).parent().find('span').hide();
			}
	});
	return valid;
}
sesJqueryObject('#ticketDtEvn').submit(function(e){
	valid = validateForm();
	if(!valid){
			e.preventDefault();
			return false;
	}
		return true;
});
function validateEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}
sesJqueryObject('#fillDt').bind('change', function () {
	if(sesJqueryObject("#fillDt").is(':checked')){
		sesJqueryObject('#ticketDtEvn').find(':input').each(function(){
			if(sesJqueryObject(this).hasClass('ticket_owner_fname'))
				sesJqueryObject(this).val(sesJqueryObject('#ownerFname').val());
			if(sesJqueryObject(this).hasClass('ticket_owner_lname'))
				sesJqueryObject(this).val(sesJqueryObject('#ownerLname').val());
			if(sesJqueryObject(this).hasClass('ticket_owner_email'))
				sesJqueryObject(this).val(sesJqueryObject('#ownerEmail').val());
		})
	}else{
		sesJqueryObject('#ticketDtEvn').find(':input').each(function(){
			sesJqueryObject(this).val('');
		});
	}
}).trigger('change');
</script>
