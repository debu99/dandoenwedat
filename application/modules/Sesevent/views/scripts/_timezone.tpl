<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _timezone.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $timezone = isset($_POST['timezone']) ? $_POST['timezone'] : (isset($this->event) && !empty($this->event->timezone) ? $this->event->timezone : $this->viewer->timezone) ; ?>
<div id="timezone_setn" class="form-wrapper">
  <div id="timezone_setn-label" class="form-element">
    <a href="javascript:;" id="timezone_setting_event" class="form-link">
      <i class="far fa-clock"></i><?php echo $this->translate("Timezone Setting"); ?> (<span id="selected_timezone_val"><?php echo $timezone; ?></span>)</a>
  </div>
</div>
<div class="sesevent_timezone_popup_overlay" style="display:none;"></div>
<div class="sesevent_timezone_popup" style="display:none;">
	<div class="sesevent_timezone_popup_content">
  	<div class="sesevent_timezone_popup_content_inner">
      <div class="sesevent_timezone_popup_heading">
        <h2><?php echo $this->translate("Select Your Time Zone"); ?></h2>
      </div>
      <div class="sesevent_timezone_popup_elements">  
        <select id="event_timezone_jq" name="timezone">
        <?php if(count($this->timezone) && count($this->timezone)){ ?>
    			<?php foreach($this->timezone as $key=>$valTimezone){ ?>
          <option value="<?php echo $key ?>" <?php if($key == $timezone){ ?> selected="selected" <?php } ?>><?php echo $valTimezone ?></option>
          <?php } ?>
        <?php } ?>
				</select>
   			<h4 class="sesevent_timezone_popup_subheading" style="display:none;"><?php echo $this->translate("Event Page Settings"); ?></h4>
        <br />
				<div class="sesevent_timezone_popup_buttons">
          <button id="saveDateTimezoneSetting" onclick=""><?php echo $this->translate("Save"); ?></button>
          <button id="cancelTimeZone" onclick="" class="secondary_button"><?php echo $this->translate("Cancel"); ?></button>
      	</div>
      </div>
		</div>
	</div>
</div>
<script type="application/javascript">
 en4.core.runonce.add(function() {
sesJqueryObject('#saveDateTimezoneSetting').click(function(e){
	var valueArray = '';
  var timeValues = sesJqueryObject('.sesevent_choose_date').find('input');
  for(i=0;i<timeValues.length;i++){
    valueArray =  sesJqueryObject(timeValues[i]).attr('id')+'='+sesJqueryObject(timeValues[i]).val()+"&"+valueArray; 
  }
  
  var request = new Request.JSON({
      method: 'post',
      'url': en4.core.baseUrl + "sesevent/index/set-date-data",
      'data': {
        format: 'json',
        timezone : sesJqueryObject('#event_timezone_jq').val(),
        values:valueArray,
      },
      onSuccess: function(responseJSON) {
        for(i=0;i<responseJSON.length;i++){
          sesJqueryObject('#'+responseJSON[i]['key']).val(responseJSON[i]['value']);
        }
        sesJqueryObject('.sesevent_timezone_popup_overlay').hide();
        sesJqueryObject('.sesevent_timezone_popup').hide();
        sesJqueryObject('#selected_timezone_val').html(sesJqueryObject('#event_timezone_jq').val());
        checkDateTime();
      }
    });
    request.send();
    return false;  
});

sesJqueryObject('#timezone_setting_event').click(function(e){
		sesJqueryObject('.sesevent_timezone_popup_overlay').show();
		sesJqueryObject('.sesevent_timezone_popup').show();
		return false;
});

sesJqueryObject('#cancelTimeZone').click(function(e){
		sesJqueryObject('.sesevent_timezone_popup_overlay').hide();
		sesJqueryObject('.sesevent_timezone_popup').hide();
		return false;
});
});
</script>