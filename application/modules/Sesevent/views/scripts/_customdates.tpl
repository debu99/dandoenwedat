<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _customdates.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="sesevent_choose_date">
  <div id="event_start_time-wrapper" class="form-wrapper">
    <div id="event_start_time-label" class="form-label">
      <label for="event_start_time" class="optional"><?php echo $this->translate('Start Time') ?></label>
    </div>
    <div id="event_start_time-element" class="form-element">
      <span class="sesevent-date-field"><input type="text" class="displayF" name="start_date" id="sesevent_start_date" value="<?php echo isset($this->start_date) ? ($this->start_date) : ''  ?>"></span>
      <span class="sesevent-time-field"><input required class="ui-timepicker-input" type="time" step="60" name="start_time" id="sesevent_start_time" value="<?php echo isset($this->start_time) ? ($this->start_time) : ''  ?>" autocomplete="off"></span>
    </div>
  </div>
  <div id="event_end_time-wrapper" class="form-wrapper">
    <div id="event_end_time-label" class="form-label">
      <label for="event_end_time" class="optional"><?php echo $this->translate('End Time') ?></label>
    </div>
    <div id="event_end_time-element" class="form-element">
		<?php 
			$viewer = Engine_Api::_()->user()->getViewer();
			$isAdmin = $viewer->isAdmin();
			if($isAdmin) {
		?>
      		<span class="sesevent-date-field"><input class="displayF" type="text" name="end_date" id="sesevent_end_date" value="<?php echo isset($this->end_date) ? ($this->end_date) : ''  ?>"></span>
		<?php } ?>

      <span class="sesevent-time-field"><input required class="ui-timepicker-input" type="time" step="60" name="end_time"  value="<?php echo isset($this->end_time) ? ($this->end_time) : ''  ?>"></span>
    </div>
  </div>
</div>
<div id="event_error_time-wrapper" class="form-wrapper" style="display:none;">
  <div class="form-element tip"><span id="event_error_time-element"></span></div>
</div>



<script type="application/javascript">

 en4.core.runonce.add(function() {
	 <?php if(isset($this->subject) && $this->subject != ''){ ?>
		var sesstartCalanderDate = new Date('<?php echo date("m/d/Y",strtotime($this->subject->creation_date));  ?>');
	<?php }else{ ?>
		var sesstartCalanderDate = new Date('<?php echo date("m/d/Y");  ?>');
	<?php } ?>
	var sesselectedDate =  new Date(sesJqueryObject('#sesevent_start_date').val());
	var sesFromEndDate;
	
	sesBasicAutoScroll('#sesevent_start_date').datepicker({
			format: 'm/d/yyyy',
			weekStart: 1,
			autoclose: true,
			startDate: sesstartCalanderDate,
			endDate: sesFromEndDate, 
	}).on('changeDate', function(ev){
		sesselectedDate = ev.date;
		var lastTwoDigit = sesBasicAutoScroll('#sesevent_end_time').val().slice('-2');
		var endDate = new Date(sesBasicAutoScroll('#sesevent_end_date').val()+' '+sesBasicAutoScroll('#sesevent_end_time').val().replace(lastTwoDigit,'')+':00 '+lastTwoDigit);
		var lastTwoDigitStart = sesBasicAutoScroll('#sesevent_start_time').val().slice('-2');
		var startDate = new Date(sesBasicAutoScroll('#sesevent_start_date').val()+' '+sesBasicAutoScroll('#sesevent_start_time').val().replace(lastTwoDigitStart,'')+':00 '+lastTwoDigitStart);
		var error = checkDateTime(startDate,endDate);
		if(error != ''){
			sesBasicAutoScroll('#event_error_time-wrapper').show();
			sesBasicAutoScroll('#event_error_time-element').text(error);
		}else{
			sesBasicAutoScroll('#event_error_time-wrapper').hide();
			sesFromEndDate = new Date(sesBasicAutoScroll('#sesevent_end_date').val());
			sesBasicAutoScroll('#sesevent_end_date').datepicker('setStartDate', sesselectedDate);
		}
	});
	sesBasicAutoScroll('#sesevent_end_date').datepicker({
			format: 'm/d/yyyy',
			weekStart: 1,
			autoclose: true,
			startDate: sesselectedDate,
	}).on('changeDate', function(ev){
		sesFromEndDate = new Date(ev.date.valueOf());
		sesFromEndDate.setDate(sesFromEndDate.getDate(new Date(ev.date.valueOf())));
		var lastTwoDigit = sesBasicAutoScroll('#sesevent_end_time').val().slice('-2');
		var endDate = new Date(sesBasicAutoScroll('#sesevent_end_date').val()+' '+sesBasicAutoScroll('#sesevent_end_time').val().replace(lastTwoDigit,'')+':00 '+lastTwoDigit);
		var lastTwoDigitStart = sesBasicAutoScroll('#sesevent_start_time').val().slice('-2');
		var startDate = new Date(sesBasicAutoScroll('#sesevent_start_date').val()+' '+sesBasicAutoScroll('#sesevent_start_time').val().replace(lastTwoDigitStart,'')+':00 '+lastTwoDigitStart);
		var error = checkDateTime(startDate,endDate);
		if(error != ''){
			sesBasicAutoScroll('#event_error_time-wrapper').show();
			sesBasicAutoScroll('#event_error_time-element').text(error);
		}else{
			sesBasicAutoScroll('#event_error_time-wrapper').hide();
			 sesBasicAutoScroll('#sesevent_start_date').datepicker('setEndDate', sesFromEndDate);
		}
	});
});
function checkDateTime(startdate,enddate){
	var errorMessage = '';
	var checkdate = true;
	var currentTime =  new Date();
  	var format = 'YYYY/MM/DD HH:mm:ss';
  	currentTime = moment(currentTime, format).tz(sesJqueryObject('#event_timezone_jq').val()).format(format);
  	currentTime =  new Date(currentTime);    
	if(<?php echo $this->subject ? 1 : 0 ?> == 0 && currentTime.valueOf() > startdate.valueOf() && sesBasicAutoScroll('#sesevent_start_date').val() && 1 == '<?php echo $this->start_time_check; ?>'){
		errorMessage = "<?php echo $this->translate('Event date is in the past. Please enter an event date greater than or equal to today\'s date.')?>";	
	}else if(startdate.valueOf() >= enddate.valueOf() && sesBasicAutoScroll('#sesevent_start_date').val() && sesBasicAutoScroll('#sesevent_end_date').val()){
			errorMessage = "<?php echo $this->translate('Event cannot end before or same date as it starts. Please choose an event end date and time that is later than the start date and time.')?>";
	}
	return errorMessage;
	}
</script>


<style>

input[type="time"]::-webkit-calendar-picker-indicator {
    background: none;
}

.ui-timepicker-input {
	text-align: center;
}
</style>