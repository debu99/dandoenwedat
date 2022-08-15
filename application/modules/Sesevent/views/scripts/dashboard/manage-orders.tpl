<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage-orders.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_ajax){ 
  echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array(
 	 'event' => $this->event,
  ));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
	echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
 echo $this->content()->renderWidget('sesevent.manage-orders', array('event_id' => $this->event->event_id));
?>
<?php if(!$this->is_ajax){ ?>
	</div>
  </div>
</div>
<?php  } ?>
<style>
#date-date_from,
#date-date_to{ display:block !important;}
</style>
<script type="application/javascript">
function executeAfterLoad(){
	if(!sesBasicAutoScroll('#date-date_to').length )
		return;
	var FromEndDateOrder;
	var selectedDateOrder =  new Date(sesBasicAutoScroll('#date-date_to').val());
	sesBasicAutoScroll('#date-date_to').datepicker({
			format: 'yyyy-m-d',
			weekStart: 1,
			autoclose: true,
			endDate: FromEndDateOrder, 
	}).on('changeDate', function(ev){
		selectedDateOrder = ev.date;	
		sesBasicAutoScroll('#date-date_from').datepicker('setStartDate', selectedDateOrder);
	});
	sesBasicAutoScroll('#date-date_from').datepicker({
			format: 'yyyy-m-d',
			weekStart: 1,
			autoclose: true,
			startDate: selectedDateOrder,
	}).on('changeDate', function(ev){
		FromEndDateOrder	= ev.date;	
		 sesBasicAutoScroll('#date-date_to').datepicker('setEndDate', FromEndDateOrder);
	});	
}
</script>
<?php if($this->is_ajax) die; ?>
