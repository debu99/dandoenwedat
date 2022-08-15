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
<script type="text/javascript">
    var latLngSesSidebarSidebar;
    function initializeMapSesSidebar() {
        var latLngSesSidebar = new google.maps.LatLng(<?php echo $this->locationLatLng->lat; ?>,<?php echo $this->locationLatLng->lng; ?>);
        var myOptions = {
            zoom: 13,
            center: latLngSesSidebar,
            navigationControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
				var map = new google.maps.Map(document.getElementById("sesevent_map_container_sidebar"), myOptions);
				var marker = new google.maps.Marker({
            position: latLngSesSidebar,
            map: map,
        });       
    }
</script>
<ul class="sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block sesevent_sidebar_map_block">
  <li>
    <div class="sesevent_sidebar_map sesbd" id="sesevent_map_container_sidebar"></div>
  </li>
  <li class="sesbasic_clearfix sesevent_list_stats sesevent_list_location">
  	<span class="widthfull"> 
    	<i title="Location" class="fas fa-map-marker-alt sesbasic_text_light"></i>
  		<span>
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
        <a href="<?php echo $this->url(array('resource_id' => $this->subject->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $this->subject->location ?></a>
      <?php } else { ?>
        <?php echo $this->subject->location;?>
      <?php } ?>
      </span> 
    </span>
  </li>
  <li class="sesbasic_clearfix sesevent_list_stats sesevent_list_time"> 
  	<span class="widthfull"> 
    	<i title="Start & End Date" class="far fa-calendar-alt sesbasic_text_light"></i>
      <?php $dateinfoParams['starttime'] = true; ?>
      <?php $dateinfoParams['endtime']  =  true; ?>
      <?php $dateinfoParams['timezone']  =  true; ?>
      <?php echo $this->eventStartEndDates($this->subject,$dateinfoParams); ?>
    </span>
  </li>
</ul>
<script type="text/javascript">
    window.addEvent('domready', function() {
        initializeMapSesSidebar();
    });
</script>
