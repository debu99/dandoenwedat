<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js'); ?>

<script type="text/javascript">
		var map,
				currentPosition,
				directionsDisplay, 
				directionsService,
				editmap = false,
				destinationLatitude = <?php echo $this->lat; ?>,
        destinationLongitude = <?php echo $this->lng; ?>;
		function initializeMapAndCalculateRoute(lat, lon)
		{
		 if(directionsDisplay != null){
			 directionsDisplay.setMap(null);
			 directionsDisplay = null;
			}
			directionsDisplay = new google.maps.DirectionsRenderer(); 
			directionsService = new google.maps.DirectionsService();
			if(!editmap){
				currentPosition = new google.maps.LatLng(lat, lon);
			}
			if(!map){
				map = new google.maps.Map(document.getElementById('map_canvas'), {
					 zoom: 17,
					 center: currentPosition,
					 mapTypeId: google.maps.MapTypeId.ROADMAP
				 });		
			}
				directionsDisplay.setMap(map);
				// calculate Route
				calculateRoute();
		}
		function locSuccess(position) {
				// initialize map with current position and calculate the route
				getAddressMap(position.coords.latitude, position.coords.longitude);
				initializeMapAndCalculateRoute(position.coords.latitude, position.coords.longitude);
		}
		function locError(){
				jqueryObjectOfSes("#results").show();
				jqueryObjectOfSes("#directions").html('Unable to calculate the direction.');
				jqueryObjectOfSes('#loading-image').hide();
				//initializeMapAndCalculateRoute(position.coords.latitude, position.coords.longitude);
		}
		function calculateRoute() {
				jqueryObjectOfSes('#loading-image').show();
				jqueryObjectOfSes('#directions').html('');
				var targetDestination =  new google.maps.LatLng(destinationLatitude, destinationLongitude);
				if (currentPosition != '' && targetDestination != '') {
						var request = {
								origin: currentPosition, 
								destination: targetDestination,
								travelMode: google.maps.DirectionsTravelMode[jqueryObjectOfSes('.sesbasic_getdirection_popup_left_tbs').find('.selected').attr('title')]
						};
						directionsService.route(request, function(response, status) {
								if (status == google.maps.DirectionsStatus.OK) {
										directionsDisplay.setPanel(document.getElementById("directions"));
										directionsDisplay.setDirections(response);
										jqueryObjectOfSes("#results").show();
										jqueryObjectOfSes('#loading-image').hide();
								}
								else {
										jqueryObjectOfSes("#results").show();
										jqueryObjectOfSes("#directions").html('Unable to calculate the direction.');
										jqueryObjectOfSes('#loading-image').hide();
								}
						});
				}
				else {
						jqueryObjectOfSes("#results").show();
						jqueryObjectOfSes("#directions").html('Unable to calculate the direction.');
						jqueryObjectOfSes('#loading-image').hide();
				}
		}
 var geocoder = new google.maps.Geocoder();
  function codeAddress() {
		editmap = true;
		initializeMapAndCalculateRoute();
  }
function getAddressMap(lat,lng){
	var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};
    geocoder.geocode( { 'location': latlng}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
				if (results[1]) {
					jqueryObjectOfSes('#source_direction').val(results[1].formatted_address);
			}
      }
    });	
}
jqueryObjectOfSes(document).ready(function(e) {
map = new google.maps.Map(document.getElementById('map_canvas'), {
 zoom: 17,
  center: new google.maps.LatLng(0.00, 0.00),
 mapTypeId: google.maps.MapTypeId.ROADMAP
});			
var input = document.getElementById('source_direction');
var autocomplete = new google.maps.places.Autocomplete(input);
google.maps.event.addListener(autocomplete, 'place_changed', function () {
var place = autocomplete.getPlace();
	if (!place.geometry) {
		return;
	}
var lng = place.geometry.location.lng();
var lat = place.geometry.location.lat();
currentPosition = new google.maps.LatLng(lat, lng);
});
navigator.geolocation.getCurrentPosition(locSuccess, locError);
});
jqueryObjectOfSes(document).on('click','#get-direction',function(){
	if(!jqueryObjectOfSes('#source_direction').val()){
		jqueryObjectOfSes('#source_direction').css('border','1px solid red');
		return false;
	}
	else
			jqueryObjectOfSes('#source_direction').css('border','');
	codeAddress();
	if(!location)
		return false;
	
});
</script>
<div class="sesbasic_getdirection_popup sesbasic_bxs">
	<div class="sesbasic_getdirection_popup_heading">
  	<?php echo (!empty($this->item->location) ? $this->item->location : ($this->location->venue  ? $this->location->venue : '' )); ?>
    <i onclick='javascript:parent.Smoothbox.close()' class="fa fa-times sesbasic_getdirection_popup_close sesbasic_text_light" title="Close"></i>
  </div>
  <div id="basic-map" class="sesbasic_getdirection_popup_cont sesbd sesbasic_clearfix">
    <div class="sesbasic_getdirection_popup_left sesbd">
    	<div class="sesbasic_getdirection_popup_left_form sesbasic_clearfix sesbd">
        <div class="sesbasic_clearfix sesbasic_getdirection_popup_left_form_field">
          <i class="floatL point_a centerT">A</i>
          <span><input type="text" id="source_direction" value="" placeholder="from" /></span>
        </div>
        <div class="sesbasic_clearfix sesbasic_getdirection_popup_left_form_field">
          <i class="floatL point_b centerT">B</i>
          <span><?php echo (!empty($this->item->location) ? $this->item->location : ($this->location->venue  ? $this->location->venue : '' )); ?></span>
        </div>
        <div class="sesbasic_getdirection_popup_left_tbs sesbm">
          <a href="javascript:void(0);" title="DRIVING" class="centerT selected modeC"><i class="fa fa-car"></i></a>
          <a href="javascript:void(0);" title="WALKING" class="sesbm centerT modeC"><i class="fa fa-male"></i></a>
          <a href="javascript:void(0);" title="BICYCLING" class="centerT modeC"><i class="fa fa-bicycle"></i></a>
        </div>
        <div class="sesbasic_getdirection_popup_left_btn sesbasic_clearfix">
          <button type="button" id="get-direction">Get Direction</button>
        </div>
      </div>
      <div id="loading-image" class="sesbasic_loading_container"></div>
      <div id="results" class="sesbasic_getdirection_popup_result sesbasic_custom_scroll" style="display:none">
      	<div id="directions"></div>
    	</div>
    </div>
    <div id="map_canvas" class="sesbasic_getdirection_popup_map"></div>
  </div>
</div>
<script type="application/javascript">

sesJqueryObject(document).on('click','.modeC',function(){
	var len = sesJqueryObject('.sesbasic_getdirection_popup_left_tbs > a');
	for(var i=0;i<len.length;i++){
			len[i].removeClass('selected');
	}
	sesJqueryObject(this).addClass('selected');
});
</script>
<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>
