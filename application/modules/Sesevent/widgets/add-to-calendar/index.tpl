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
<?php $randomNum = 'sesevent_calander_add_'.rand(110,8764359450); ?>
<div class="sesevent_addtocalendar_btn sesevent_button" style="display:block;">
  <a href="javascript:void(0);" class="sesbasic_link_btn" id="sesevent_addtocalendar_btn_<?php echo $randomNum; ?>">
    <i class="far fa-calendar-plus" id="sesevent_addtocalendar_i_<?php echo $randomNum; ?>"></i>
    <span id="sesevent_calander_title_<?php echo $randomNum; ?>"><?php echo $this->translate("Add to Calendar"); ?></span>
  </a>
  <div class="sesevent_addtocalendar_options sesevent_add_to_calander_btn_<?php echo $randomNum; ?> sesbd" style="display:none;">
    <?php if(isset($this->google)){ ?>
     <?php echo $this->google; ?>
    <?php } ?>
    <?php if(isset($this->yahoo)){ ?>
     <?php echo $this->yahoo; ?>
    <?php } ?>
    <?php if(isset($this->msn)){ ?>
     <?php echo $this->msn; ?>
    <?php } ?>
    <?php
    if (in_array('ical', $this->options)) { ?>
      <a href="<?php echo $this->url(array('event_id' => $this->event->event_id,'action'=>'add-calander','controller'=>'index','module'=>'sesevent'), 'default', true); ?>"><i class="sesevent_icon_iCal"></i>iCal</a>
    <?php } ?>
    <?php
    if (in_array('outlook',$this->options)) { ?>
      <a href="<?php echo $this->url(array('event_id' => $this->event->event_id,'action'=>'add-calander','controller'=>'index','module'=>'sesevent'), 'default', true); ?>"><i class="sesevent_icon_outlook"></i><?php echo $this->translate('Outlook');?></a>
    <?php } ?>
  </div>
</div>
<script type="text/javascript">
	sesJqueryObject(document).click(function(event){
		if(event.target.id == 'sesevent_addtocalendar_btn_<?php echo $randomNum; ?>'){
			if(sesJqueryObject('#sesevent_addtocalendar_btn_<?php echo $randomNum; ?>').hasClass('active')){
				sesJqueryObject('#sesevent_addtocalendar_btn_<?php echo $randomNum; ?>').removeClass('active');
				sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').hide();	
			}else{
				sesJqueryObject('#sesevent_addtocalendar_btn_<?php echo $randomNum; ?>').addClass('active');
				sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').show();	
		  }
		}else if(event.target.id == 'sesevent_addtocalendar_i_<?php echo $randomNum; ?>'){
			if(sesJqueryObject('#sesevent_addtocalendar_i_<?php echo $randomNum; ?>').parent().hasClass('active')){
				sesJqueryObject('#sesevent_addtocalendar_i_<?php echo $randomNum; ?>').parent().removeClass('active');
				sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').hide();	
			}else{
				sesJqueryObject('#sesevent_addtocalendar_i_<?php echo $randomNum; ?>').parent().addClass('active');
				sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').show();	
		  }
		}else if(event.target.id == 'sesevent_calander_title_<?php echo $randomNum; ?>'){
			if(sesJqueryObject('#sesevent_addtocalendar_i_<?php echo $randomNum; ?>').parent().hasClass('active')){
				sesJqueryObject('#sesevent_addtocalendar_i_<?php echo $randomNum; ?>').parent().removeClass('active');
				sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').hide();	
			}else{
				sesJqueryObject('#sesevent_addtocalendar_i_<?php echo $randomNum; ?>').parent().addClass('active');
				sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').show();	
		  }
		}else{
			sesJqueryObject('#sesevent_addtocalendar_btn_<?php echo $randomNum; ?>').removeClass('active');
			sesJqueryObject('.sesevent_add_to_calander_btn_<?php echo $randomNum; ?>').hide();	
		}
		
	});
</script>