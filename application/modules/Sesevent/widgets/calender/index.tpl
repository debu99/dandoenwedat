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
<?php $class = "sessmoothbox";?>
<?php $randonNumber = $this->identity; ?>
<?php if(!empty($_SESSION["removeSiteHeaderFooter"])){ ?>
<style>
  .layout_sesevent_browse_menu{display:none;}
</style>
<?php $class = "";?>
<?php } ?>
<?php if(!$this->is_ajax){?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/calendar.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sessmoothbox/sessmoothbox.js'); ?>
<?php $this->headScript()->appendFile( 'externals/tinymce/tinymce.min.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>
<?php } ?>
<?php 
  $events = $this->events;
  $month = $this->month;
  $year = $this->year;
?>
<?php if(!$this->is_ajax){?>
 <?php if($this->loadData != 'nextprev'){ ?>
 <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $this->identity; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div> 
<?php }else{ ?>
	<div class="sesbasic_loading_cont_overlay" id="sesbasic_loading_cont_overlay_<?php echo $this->identity ?>" style="display:none"></div>
<?php } ?>
<ul id="calander_data_<?php echo $this->identity ?>">
<?php } ?>
<li class="sesevent_calendar_container sesbasic_bxs sesbasic_clearfix" id="sescalander_<?php echo $month; ?>">
    <div class="sesevent_calendar_header sesbasic_clearfix">
     <?php if($this->loadData != 'nextprev'){ ?>
      <div class="sesevent_calendar_header_left_btns floatL"> 
      	<a href="javascript:;" class="previousCal" onclick="previousCal('<?php echo $this->year ?>','<?php echo $this->month ?>')"><i class="fa fa-chevron-up"></i></a> 
        <a href="javascript:;" class="nextCal" onclick="nextCal('<?php echo $this->year ?>','<?php echo $this->month ?>');"><i class="fa fa-chevron-down"></i></a> 
      </div>
 <?php } ?>
    <div class="sesevent_calendar_header_name floatL"><?php echo $this->translate(date('F',strtotime($year.'-'.$month))).' '.$year; ?></div>
    <?php if($this->loadData == 'nextprev'){ ?>
    	<div class="sesevent_calendar_header_right_btns floatR"> 
      <a href="javascript:;" class="sesbasic_button floatL" onclick="previousNextFixCal('<?php echo $this->year ?>','<?php echo $this->month ?>','prev');"><i class="fa fa-angle-left"></i><?php echo $this->translate("Previous"); ?></a> 
      <a href="javascript:;" class="sesbasic_button floatL" onclick="previousNextFixCal('<?php echo $this->year ?>','<?php echo $this->month ?>','next');"><?php echo $this->translate("Next"); ?><i class="fa fa-angle-right right"></i></a> 
     </div>
    <?php } ?>
  </div>
  <div class="sesevent_calendar_main"> 
    <?php
  /* draw table */
	$calendar = '<table cellpadding="0" cellspacing="0">';
	/* table headings */
	$headings = array($this->translate('Sunday'),$this->translate('Monday'),$this->translate('Tuesday'),$this->translate('Wednesday'),$this->translate('Thursday'),$this->translate('Friday'),$this->translate('Saturday'));
	$calendar.= '<thead><tr><th><div class="day">'.implode('</th><th><div class="day">',$headings).'</th></tr><thead>';
	/* days and weeks vars now ... */
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	/* row for week one */
	$calendar.= '<tbody><tr class="calendar-row">';
	$lastDayOfPreviousMonth = date('Y-m-d',strtotime('last day of previous month',strtotime(date($year.'-'.$month.'-10'))));
 	$firstDayOfPreviousMonth = date('Y-m-d',strtotime('first day of next month',strtotime(date($year.'-'.$month.'-10'))));
 	$running_day_d = $running_day -1;
	/* print "blank" days until the first of the current week */
	for($x = 0; $x < $running_day; $x++):
  	$daysTxt = ($running_day_d) > 1 ? '-'.($running_day_d).' days' : '-'.($running_day_d).' day';
		$calendar.= '<td><div class="date_inactive"><span class="date">'.ltrim(date('d',strtotime($daysTxt,strtotime($lastDayOfPreviousMonth))),'0').'</span></div></td>';
		$days_in_this_week++;
    $running_day_d--;
	endfor;

	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td><div class="day">';
    if(strlen($list_day) == 1)
      	$list_day_checklist_day = '0'.$list_day;
      else
      	$list_day_checklist_day = $list_day;
		$event_day = $year.'-'.$month.'-'.$list_day_checklist_day;
    $url = $this->url(array('module' => 'sesevent', 'controller' => 'ajax', 'action' => 'get-event'), 'default', true);
    $calendar .='<a href="'.$url.'" rel="'.$event_day.'" class="sesevent_calendar_date_link sessmoothbox"></a>';
			/* add in the day number */
			$calendar.= '<a href="'.$url.'" rel="'.$event_day.'" class="date sessmoothbox">'.$list_day.'</a>';
      
			if(strtotime($event_day) >= strtotime(date('Y-m-d')) && $this->can_create)
				$calendar .='<a href="'.$this->url(array('action'=>'create'), 'sesevent_general').'" class="'.$class.' sesevent_calendar_create_link fa fa-plus" title="Create Event"></a>';
    if(isset($events[$event_day])) {
      $calendar .='<ul class="sesevent_calendar_event_list">';
      $counter = 1;
      foreach($events[$event_day] as $key=>$event) {
        if($counter > $this->viewMoreAfter){
						$calendar .= '<li class="more"><a href="'.$url.'" rel="'.date('Y-m-d',strtotime($event->starttime)).'" class="sessmoothbox">+ '.(count($events[$event_day]) - $counter + 1).'</a></li>';
            break;
         }
        $calendar.= '<li><a href="'.$event->getHref().'" class="ses_tooltip" data-src="'.$event->getGuid().'" title="'.$event->getTitle().'"><img src="'.$event->getPhotoUrl().'" class="thumb_icon item_photo_user thumb_icon"></a></li>';
        $counter++;
        //more setting
      }      
      $calendar .= '</ul>';
    }
		$calendar.= '</div></td>';
		if($running_day == 6):
			$calendar.= '</tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr>';
			endif;
			$running_day = -1;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;
	/* finish the rest of the days in the week */
	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$daysTxt = ($x - 1) > 1 ? '+'.($x - 1).' days' : '+'.($x - 1).' day';
			$calendar.= '<td><div class="date_inactive"><span class="date">'.ltrim(date('d',strtotime($daysTxt,strtotime($firstDayOfPreviousMonth))),'0').'</span></div></td>';
		endfor;
	endif;
	/* final row */
	$calendar.= '</tr></tbody>';
	/* end the table */
	$calendar.= '</table>';
	/** DEBUG **/
	$calendar = str_replace('</td>','</td>'."\n",$calendar);
	$calendar = str_replace('</tr>','</tr>'."\n",$calendar);
  echo $calendar;
  ?>
  </div>
</div>
</li>
<?php if($this->is_ajax){ ?>
<?php  die;
} ?>
</ul>
 <?php if($this->loadData != 'nextprev'){ ?>
  <div class="sesbasic_load_btn" id="view_more_cal<?php echo $this->identity; ?>" onclick="viewMore_cal<?php echo $this->identity; ?>();" > <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$this->identity", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $this->identity;?>" id="loading_image_next_<?php echo $this->identity; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div> 
<?php } ?>
<script type="application/javascript">
 <?php if($this->loadData != 'nextprev'){ ?>
function viewMore_cal<?php echo $this->identity;?>(){
	var totalElem = sesJqueryObject('#calander_data_<?php echo $this->identity; ?>').children().length;
	var parentId =  sesJqueryObject('#calander_data_<?php echo $this->identity; ?> > li').eq(index-1).attr('id');
	var parentObj = sesJqueryObject('#'+parentId);
	parentObj.find('.sesevent_calendar_header').find('.sesevent_calendar_header_left_btns').find('a.nextCal').trigger('click');	
}
<?php if($this->loadData != 'nextprev'){ ?>
var noAnimation = false;
   window.addEvent('load', function() {
			sesJqueryObject(window).scroll( function() {
			var	containerId = '#calander_data_<?php echo $this->identity;?>';
					if(typeof sesJqueryObject(containerId).offset() != 'undefined') {
							if((sesJqueryObject(window).scrollTop() + sesJqueryObject(window).height()) >=  (sesJqueryObject(containerId).offset().top + sesJqueryObject(containerId).height()) && sesJqueryObject('#loading_image_next_<?php echo $this->identity; ?>').css('display') == 'none'){
								var totalElem = sesJqueryObject('#calander_data_<?php echo $this->identity; ?>').children().length;
								var parentId =  sesJqueryObject('#calander_data_<?php echo $this->identity; ?> > li').eq(totalElem-1).attr('id');
								var parentObj = sesJqueryObject('#'+parentId);
								noAnimation = true;
								parentObj.find('.sesevent_calendar_header').find('.sesevent_calendar_header_left_btns').find('a.nextCal').trigger('click');
							}
					}
			});
  });
<?php } ?>
function previousCal(year,month){
	var index = sesJqueryObject('#sescalander_'+month).index();
	var parentId =  sesJqueryObject('#calander_data_<?php echo $this->identity; ?> > li').eq(index-1).attr('id');
	var parentObj = sesJqueryObject('#'+parentId);
	if(index != 0){
		sesJqueryObject('html, body').animate({
			scrollTop: parentObj.offset().top
		 }, 2000);
			return false;
		}	
	if(typeof requestViewMoreCal != 'undefined')
		requestViewMoreCal.cancel();
	sesJqueryObject('#loading_image_<?php echo $this->identity; ?>').show();
	var scrollObj = sesJqueryObject('#loading_image_<?php echo $this->identity; ?>');
	var amt = scrollObj.offset().top;
	sesJqueryObject('html, body').animate({
			scrollTop: amt
	}, 2000);
	requestViewMoreCal = new Request.HTML({
			method: 'post',
			'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
			'data': {
				format: 'html',
				is_ajax : 1,
				identity : '<?php echo $this->identity; ?>',
				year:year,
				type:'prev',
				month:month,
				viewMoreAfter:'<?php echo $this->viewMoreAfter; ?>',
				loadData:'<?php echo $this->loadData; ?>',
			},
			onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject('#loading_image_<?php echo $this->identity; ?>').hide();
				sesJqueryObject('#calander_data_<?php echo $this->identity ?> li:first').before(responseHTML);
			}
		});
	requestViewMoreCal.send();
	return false;
}
function nextCal(year,month){
	var index = sesJqueryObject('#sescalander_'+month).index();
	var totalElem = sesJqueryObject('#calander_data_<?php echo $this->identity; ?>').children().length;
	var parentId =  sesJqueryObject('#calander_data_<?php echo $this->identity; ?> > li').eq(index+1).attr('id');
	var parentObj = sesJqueryObject('#'+parentId);
	if(index+1 != totalElem){
		sesJqueryObject('html, body').animate({
			scrollTop: parentObj.offset().top
		 }, 2000);
			return false;
		}	
	sesJqueryObject('#loading_image_next_<?php echo $this->identity; ?>').show();
	var scrollObj = sesJqueryObject('#loading_image_next_<?php echo $this->identity; ?>');
	var amt = scrollObj.offset().top - 300;
	sesJqueryObject('html, body').animate({
			scrollTop: amt
	}, 2000);
	if(typeof requestViewMoreCalNext != 'undefined')
		requestViewMoreCalNext.cancel();
	sesJqueryObject('#view_more_cal<?php echo $this->identity;?>').hide();
	requestViewMoreCalNext = new Request.HTML({
			method: 'post',
			'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
			'data': {
				format: 'html',
				is_ajax : 1,
				identity : '<?php echo $this->identity; ?>',
				year:year,
				type:'next',
				month:month,
				viewMoreAfter:'<?php echo $this->viewMoreAfter; ?>',
				loadData:'<?php echo $this->loadData; ?>',
			},
			onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject('#loading_image_next_<?php echo $this->identity; ?>').hide();
				sesJqueryObject('#view_more_cal<?php echo $this->identity;?>').show();
				sesJqueryObject('#calander_data_<?php echo $this->identity ?>').append(responseHTML);
				if(!noAnimation){
					var parentId =  sesJqueryObject('#calander_data_<?php echo $this->identity; ?> > li').eq(totalElem).attr('id');
					var parentObj = sesJqueryObject('#'+parentId);
					sesJqueryObject('html, body').animate({
						scrollTop: parentObj.offset().top
					 }, 2000);
				}
				noAnimation = true;
				return false;
			}
		});
	requestViewMoreCalNext.send();
	return false;
}
<?php }else{ ?>
function previousNextFixCal(year,month,type){
		sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $this->identity ?>').show();
		requestViewMoreCalNext = new Request.HTML({
			method: 'post',
			'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
			'data': {
				format: 'html',
				is_ajax : 1,
				identity : '<?php echo $this->identity; ?>',
				year:year,
				type:type,
				month:month,
				viewmore:'<?php echo $this->viewMoreAfter; ?>',
				loadData:'<?php echo $this->loadData; ?>',
			},
			onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $this->identity ?>').hide();
				sesJqueryObject('#calander_data_<?php echo $this->identity ?>').html(responseHTML);
				return false;
			}
		});
	requestViewMoreCalNext.send();
	return false;
}
<?php } ?>
</script>