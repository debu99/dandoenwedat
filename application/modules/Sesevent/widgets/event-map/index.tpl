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
<?php
  $href = $this->subject->getHref();
  $imageURL = $this->subject->getPhotoUrl('thumb.profile');
?>
<script type="text/javascript">
    var latLngSes;
    function initializeMapSes() {
        var latLngSes = new google.maps.LatLng(<?php echo $this->locationLatLng->lat; ?>,<?php echo $this->locationLatLng->lng; ?>);
        var myOptions = {
            zoom: 13,
            center: latLngSes,
            navigationControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
				
        var map = new google.maps.Map(document.getElementById("sesevent_map_container"), myOptions);
			

        var marker = new google.maps.Marker({
            position: latLngSes,
            map: map,
        });

				//trigger map resize on every call
       sesJqueryObject(document).on('click','ul#main_tabs li.tab_layout_sesevent_event_map',function (event) {
            google.maps.event.trigger(map, 'resize');
            map.setZoom(13);
            map.setCenter(latLngSes);
        });

        google.maps.event.addListener(map, 'click', function() {
            google.maps.event.trigger(map, 'resize');
            map.setZoom(13);
            map.setCenter(latLngSes);
        });
    }
</script>
<div class="sesevent_profile_map_container sesbasic_clearfix">
	<div class="sesevent_profile_map sesbasic_clearfix sesbd" id="sesevent_map_container"></div>
	<div class="sesevent_profile_map_address_box sesbasic_bxs">
		<b><a href="<?php echo $this->url(array('resource_id' => $this->subject->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $this->subject->location ?></a></b>
		<?php if ($this->subject->venue_name){?><p><?php echo $this->subject->venue_name ?></p><?php } ?>
	</div>
</div>	
<script type="text/javascript">
var tabId_map = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_map);	
});
    window.addEvent('domready', function() {
        initializeMapSes();
    });
</script>