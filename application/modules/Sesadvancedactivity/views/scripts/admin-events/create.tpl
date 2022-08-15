<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="text/javascript">
  var cal_starttime_onHideStart = function(){
    // check end date and make it the same date if it's too
    cal_endtime.calendars[0].start = new Date( $('starttime-date').value );
    // redraw calendar
    cal_endtime.navigate(cal_endtime.calendars[0], 'm', 1);
    cal_endtime.navigate(cal_endtime.calendars[0], 'm', -1);
  }
  var cal_endtime_onHideStart = function(){
    // check start date and make it the same date if it's too
    cal_starttime.calendars[0].end = new Date( $('endtime-date').value );
    // redraw calendar
    cal_starttime.navigate(cal_starttime.calendars[0], 'm', 1);
    cal_starttime.navigate(cal_starttime.calendars[0], 'm', -1);
  }
</script>
<div class='sesbasic_popup_form settings'>
  <?php echo $this->form->render($this); ?>
</div>
<style>
  #date-hour, #date-minute, #date-ampm{display:none;}
  #starttime-hour, #starttime-minute, #starttime-ampm{display:none;}
  #endtime-hour, #endtime-minute, #endtime-ampm{display:none;}
</style>

<script>

  en4.core.runonce.add(function() {
    document.getElementById("endtime-hour").remove(0);
    document.getElementById("endtime-minute").remove(0);
    document.getElementById("starttime-hour").remove(0);
    document.getElementById("starttime-minute").remove(0);
    choosedate(<?php echo $this->visibility; ?>);
  });
function choosedate(value) {
  if(value != 4) {
    if($('starttime-wrapper'))
      $('starttime-wrapper').style.display = 'none';
    if($('endtime-wrapper'))
      $('endtime-wrapper').style.display = 'none';
  } else {
    if($('starttime-wrapper'))
      $('starttime-wrapper').style.display = 'block';
    if($('endtime-wrapper'))
      $('endtime-wrapper').style.display = 'block';
  }
}
</script>
