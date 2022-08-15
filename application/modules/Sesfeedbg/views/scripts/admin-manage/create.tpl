<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedbg
 * @package    Sesfeedbg
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="text/javascript">
  var isEndDateRequired = '<?php echo $this->enableenddate; ?>';
  var MAX_UPLOAD_SIZE_NAME =  '<?php echo $this->upload_max_size ?>'; 
  var MAX_UPLOAD_SIZE_BYTES =  <?php echo $this->max_file_upload_in_bytes ?>; 
  var cal_starttime_onHideStart = function(){
    // check end date and make it the same date if it's too
    cal_endtime.calendars[0].start = new Date( $('starttime-date').value );
    // redraw calendar
    cal_endtime.navigate(cal_endtime.calendars[0], 'm', 1);
    cal_endtime.navigate(cal_endtime.calendars[0], 'm', -1);
    document.getElementById("endtime-hour").remove(0);
    document.getElementById("endtime-minute").remove(0);
  }
  var cal_endtime_onHideStart = function(){
    // check start date and make it the same date if it's too
    cal_starttime.calendars[0].end = new Date( $('endtime-date').value );
    // redraw calendar
    cal_starttime.navigate(cal_starttime.calendars[0], 'm', 1);
    cal_starttime.navigate(cal_starttime.calendars[0], 'm', -1);
  }
  function validFileSize(file) {
    var fileElement = document.getElementById("file");
    var size = fileElement.files[0].size;
    console.log(size , MAX_UPLOAD_SIZE_BYTES);
    if (size > MAX_UPLOAD_SIZE_BYTES)
    {
      fileElement.value = "";
      alert("File size must under "+MAX_UPLOAD_SIZE_NAME);
      return;
    }
  }

</script>
<div class='settings sesbasic_popup_form'>
  <?php echo $this->form->render($this); ?>
</div>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    $('endtime-hour').hide();
    $('endtime-minute').hide();
    $('endtime-ampm').hide();
    document.getElementById("endtime-hour").remove(0);
    document.getElementById("endtime-minute").remove(0);
    document.getElementById("endtime-ampm").remove(0);
  });
  if(isEndDateRequired=='1'){
    $('endtime-hour').show();
    $('endtime-minute').show();
  }
  
  <?php if(empty($this->enableenddate)): ?>
    window.addEvent('domready',function() {
      $('endtime-wrapper').style.display = 'none';
    });
  <?php endif; ?>

  var enableenddatse = function(value){
    if(value == 1) {
      $('endtime-wrapper').style.display = 'block';
    } else {
      $('endtime-wrapper').style.display = 'none';
    }
  }

  $('starttime-hour').hide();
  $('starttime-minute').hide();
  $('starttime-ampm').hide();
</script>