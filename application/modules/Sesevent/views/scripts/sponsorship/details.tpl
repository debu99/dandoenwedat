<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: details.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="layout_middle">
  <div class="sesevent_ticket_order_page sesbasic_bxs generic_layout_container layout_core_content"> 
    <div class="sesevent_ticket_order_page_right">
    	<div class="sesevent_ticket_order_info_box sesbm sesbasic_bxs">
        <span class="sesevent_ticket_order_info_box_title"><?php echo $this->translate("Sponsorship Information"); ?>
          <a href="<?php echo $this->sponsorship->getHref(); ?>" class="fa fa-edit" title="Cancel"></a>
        </span>
        <div class="sesevent_ticket_order_info">
          <div class="sesbasic_clearfix">
          <div class="sesevent_ticket_order_info_photo">
           <a href="<?php echo $this->sponsorship->getHref(); ?>">
           		<img src="<?php echo $this->sponsorship->getPhotoUrl(); ?>" />
           </a>
          </div>
          <div class="sesevent_ticket_order_info_name"><?php echo $this->htmlLink($this->sponsorship->getHref(),$this->sponsorship->getTitle()); ?></div>
          </div>
          <div class="sesevent_ticket_order_info_summary sesbm">
            <span class="sesevent_ticket_order_info_box_title"><?php echo $this->translate("Order Summary"); ?></span>
            <p class="sesbasic_clearfix">
              <span><?php echo $this->translate("Grand Total"); ?></span>
              <span><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice(($this->sponsorship->price)); ?></span>
            </p>
          </div>
        </div>
    	</div>
    </div>  
    <div class="sesevent_ticket_order_page_left">
      <h3><?php echo $this->translate("Sponsorship Owner Details"); ?></h3>  
      <div class="sesevent_ticket_order_details">
        <div class="sesevent_ticket_order_form_hint_txt"><?php echo $this->translate("Fields that are marked (*) are mandatory.");?></div>
         <form name="ticket" id="sponsorship" method="post" action="<?php echo $this->url(array('event_id' => $this->event->custom_url,'id'=>$this->sponsorship->sponsorship_id,'action'=>'details'), 'sesevent_sponsorship', true); ?>" enctype="multipart/form-data">
       <!-- OWNER INFO -->
        <div class="sesevent_ticket_order_details_box">
         <div class="sesevent_ticket_order_details_box_title sesbm"><?php echo $this->translate("Owner Information"); ?></div>
          <div class="sesevent_ticket_order_details_box_fields sesbm sesbasic_clearfix">
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="title"><?php echo $this->translate("Title");?> <span class="required">*</span></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="title" type="text" name="title" value="" />
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="description"><?php echo $this->translate("Short Description");?></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <textarea id="description" name="description" type="text"></textarea>
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="website"><?php echo $this->translate("Website URL");?></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                 <input id="website" type="text" name="website" value="" />
              </div>
            </div>
            <div class="sesevent_ticket_order_ticket_info">
              <div class="sesevent_ticket_order_ticket_info_label">
                <label for="logo"><?php echo $this->translate("Logo");?></label>
              </div>
              <div class="sesevent_ticket_order_ticket_info_element">
                <input id="logo" type="file" name="logo" value="" />
              </div>
            </div>
          </div>
       </div>
        <button type="submit" name="submit" value="submit" id="sbtBtn"><?php echo $this->translate("Continue");?></button>
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