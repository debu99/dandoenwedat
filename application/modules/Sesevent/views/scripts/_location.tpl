<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _location.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
$enableglocation = Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1);
$optionsenableglotion = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('optionsenableglotion','a:6:{i:0;s:7:"country";i:1;s:5:"state";i:2;s:4:"city";i:3;s:3:"zip";i:4;s:3:"lat";i:5;s:3:"lng";}'));
?>
<?php $is_webinar = isset($_POST['is_webinar']) ? $_POST['is_webinar'] : (isset($this->event->is_webinar) ? $this->event->is_webinar : false) ; ?>
<?php $location = isset($_POST['location']) ? $_POST['location'] : (isset($this->event) && !empty($this->event->location) ? $this->event->location : '') ; ?>
<?php $venue_name = isset($_POST['venue_name']) ? $_POST['venue_name'] : (isset($this->event) && !empty($this->event->venue_name) ? $this->event->venue_name : '') ; ?>
<?php $lat = isset($_POST['lat']) ? $_POST['lat'] : (isset($this->itemlocation) && !empty($this->itemlocation->lat) ? $this->itemlocation->lat : '') ; ?>
<?php $lng = isset($_POST['lng']) ? $_POST['lng'] : (isset($this->itemlocation) && !empty($this->itemlocation->lng) ? $this->itemlocation->lng : '') ; ?>
<?php $address = isset($_POST['address']) ? $_POST['address'] : (isset($this->itemlocation) && !empty($this->itemlocation->address) ? $this->itemlocation->address : '') ; ?>
<?php $address2 = isset($_POST['address2']) ? $_POST['address2'] : (isset($this->itemlocation) && !empty($this->itemlocation->address2) ? $this->itemlocation->address2 : '') ; ?>
<?php $city = isset($_POST['city']) ? $_POST['city'] : (isset($this->itemlocation) && !empty($this->itemlocation->city) ? $this->itemlocation->city : '') ; ?>
<?php $state = isset($_POST['state']) ? $_POST['state'] : (isset($this->itemlocation) && !empty($this->itemlocation->state) ? $this->itemlocation->state : '') ; ?>
<?php $country = isset($_POST['country']) ? $_POST['country'] : (isset($this->itemlocation) && !empty($this->itemlocation->country) ? $this->itemlocation->country : '') ; ?>
<?php $zip = isset($_POST['zip']) ? $_POST['zip'] : (isset($this->itemlocation) && !empty($this->itemlocation->zip) ? $this->itemlocation->zip : '') ; ?>
<?php $regionOptions = isset($_POST['regionOptions']) ? $_POST['regionOptions'] : (isset($this->regionOptions) && !empty($this->regionOptions) ? $this->regionOptions : '') ; ?>
<?php $regionValue = isset($_POST['regionValue']) ? $_POST['regionValue'] : (isset($this->regionValue) && !empty($this->regionValue) ? $this->regionValue : '') ; ?>
<script>var checkinD = false;</script>
<div id="seslocation-wrapper" class="form-wrapper" id="who-wrapper">
	<fieldset id="fieldset-where">

		<legend><?php echo $this->translate("Where"); ?></legend>
		<div id="location-label" class="form-label">
			<label class="required"><?php echo $this->translate("Location"); ?></label>
		</div>
        <!-- Add Location -->
		<div id="location-add_location" class="form-wrapper">
			<div id="location-element" class="form-element">
				<input type="text" name="location" id="locationSes" value="<?php echo $location; ?>" placeholder="<?php echo $this->translate("Enter a location"); ?>"/>
			</div>
		</div>
        <!-- Online Event -->
		<div id="online_event-wrapper" class="form-wrapper" style="display:none;">
			<div id="online_event-element" class="form-element">
				<div class="tip"><span><?php echo $this->translate("This is an online event"); ?></span></div>
			</div>
		</div>
        <!-- Enter Address -->
		<div id="sesevent_location_data-wrapper" style="display:none;">
			<div class="sesevent_create_location_details">
				<div id="venue_name-wrapper" class="sesevent_create_location_field _full">
					<input type="text" name="venue_name" class="location_value" id="venue_name" value="<?php echo $venue_name; ?>" placeholder="<?php echo $this->translate("Venue Name"); ?>" />
				</div>
			</div>
			<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
			<div id="sesevent_location_map_data" class="sesevent_create_map_container sesbm" style="display:none;">
				<div id="sesevent_default_map" class="sesevent_create_blank_map centerT" style="display:none">
				<i class="fas fa-map-marker-alt sesbasic_text_light"></i>
				<span class="sesbasic_text_light">No Map</span>
				</div>
				<div id="sesevent_location_map" class="sesevent_create_map" style="display:none"></div>
			</div>
			<?php } ?>
		</div>
		<!-- Region -->
		<div id="sesevent_location_region" class="form-wrapper" style="display:none">
			<div id="region-label" class="form-label">
				<label class="required" for="region"><?php echo $this->translate("Region"); ?></label>
			</div>
			<select class="region required" id="region" name="region">
				<?php foreach($regionOptions as $key => $region): ?>
					<option value="<?php echo $key ?>"><?php echo $region ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="clear:both"></div>
		<div id="location_options">
			<a id="sesevent_online_event" href="javascript:;" class="form-link"><i class="fa fa-globe"></i><?php echo $this->translate("Online Event"); ?></a>
			<a id="sesevent_enter_address" href="javascript:;" class="form-link"><i class="fas fa-map-marker-alt"></i><?php echo $this->translate("Enter Address"); ?></a>
			<a id="sesevent_add_location" style="display:none" href="javascript:;"  class="form-link"><i class="fa fa-plus"></i><?php echo $this->translate("Regular Event"); ?></a>
		<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
			<a id="sesevent_reset_location" style="display:none" href="javascript:;" class="form-link"><i class="fa fa-sync"></i><?php echo $this->translate("Reset Location"); ?></a>
		<?php } ?>
		</div>


			<!-- Lat lng wrapper -->
		<div id="seseventlat-wrapper" class="form-wrapper" style="display:none">
			<div id="lat-label" class="form-label">&nbsp;</div>
			<div id="lat-element" class="form-element">
				<input type="text" name="lat" id="latSes" value="<?php echo $lat; ?>" style="display:none" />
			</div>
		</div>
		<div id="seseventlat-wrapper" class="form-wrapper" style="display:none">
			<div id="lng-label" class="form-label">&nbsp;</div>
			<div id="lng-element" class="form-element">
				<input type="text" name="lng" id="lngSes" value="<?php echo $lng; ?>" style="display:none" />
			</div>
		</div>
	</fieldset>
</div>

<script type="application/javascript">
 en4.core.runonce.add(function() {
	 var region_value = '<?php echo $regionValue ?>';
	 if (region_value){
		 sesJqueryObject('#sesevent_location_region').show();
		 sesJqueryObject('#region').addClass('required');
		 sesJqueryObject('#region').val(region_value);
	 }
	 if (typeof locationcreatedata == 'undefined') {
		 locationcreatedata = true;
		 sesJqueryObject(document).on('click', '#sesevent_online_event', function () {
			 sesJqueryObject('#sesevent_location_data-wrapper').hide();
			 sesJqueryObject('#location-add_location').hide();
			 sesJqueryObject('#online_event-wrapper').show();

			 sesJqueryObject('#sesevent_online_event').hide();
			 sesJqueryObject('#sesevent_add_location').show();
			 sesJqueryObject('#sesevent_enter_address').hide();
			 sesJqueryObject('#sesevent_location_region').hide();
			 sesJqueryObject('#region').removeClass('required');

			 sesJqueryObject('#meeting_point-wrapper').hide();
			 sesJqueryObject('#meeting_url-wrapper').show();

			 sesJqueryObject('.location_value').val('');
			 sesJqueryObject('#locationSesList').val('');
			 sesJqueryObject('#lngSesList').val('');
			 sesJqueryObject('#latSesList').val('');
		 });
		 sesJqueryObject(document).on('click', '#sesevent_add_location', function () {
			 sesJqueryObject('#sesevent_location_data-wrapper').hide();
			 sesJqueryObject('#location-add_location').show();
			 sesJqueryObject('#online_event-wrapper').hide();

			 sesJqueryObject('#sesevent_online_event').show();
			 sesJqueryObject('#sesevent_add_location').hide();
			 sesJqueryObject('#sesevent_enter_address').show();
			 sesJqueryObject('#sesevent_location_region').hide();
			 sesJqueryObject('#region').removeClass('required');

			 sesJqueryObject('#meeting_point-wrapper').show();
			 sesJqueryObject('#meeting_url-wrapper').hide();

			 sesJqueryObject('.location_value').val('');
			 sesJqueryObject('#locationSesList').val('');
			 sesJqueryObject('#lngSesList').val('');
			 sesJqueryObject('#latSesList').val('');
		 });
		 sesJqueryObject(document).on('click', '#sesevent_enter_address', function () {
			 sesJqueryObject('#online_event-wrapper').hide();
			 sesJqueryObject('#location-add_location').hide();
			 sesJqueryObject('#sesevent_location_data-wrapper').show();
			 if(region_value){
				 sesJqueryObject('#sesevent_location_region').show();
				 sesJqueryObject('#region').addClass('required');
			 } else {
				 sesJqueryObject('#sesevent_location_region').hide();
				 sesJqueryObject('#region').removeClass('required');
			 }

			 sesJqueryObject('#meeting_point-wrapper').show();
			 sesJqueryObject('#meeting_url-wrapper').hide();

			 sesJqueryObject('#sesevent_online_event').hide();
			 sesJqueryObject('#sesevent_add_location').hide();
			 sesJqueryObject('#sesevent_enter_address').hide();
			 sesJqueryObject('#sesevent_reset_location').show();

			 var lat = sesJqueryObject('#latSes').val();
			 var lng = sesJqueryObject('#lngSes').val();
			 if (lat && lng) {
				 sesJqueryObject('#sesevent_location_map_data').show();
				 sesJqueryObject('#sesevent_location_map').show();
				 sesJqueryObject('#sesevent_default_map').hide();
				 createEventLoadMap();
			 } else {
				 sesJqueryObject('#sesevent_location_map_data').show();
				 sesJqueryObject('#sesevent_location_map').hide();
				 sesJqueryObject('#sesevent_default_map').show();
			 }
		 });
		 sesJqueryObject(document).on('click', '#sesevent_reset_location', function () {
			 const confirmAc = confirm('Are you sure that you want to reset this location? It will not be recoverable after being deleted.');
			 if (confirmAc === true) {
				 sesJqueryObject('#online_event-wrapper').hide();
				 sesJqueryObject('#location-add_location').show();
				 sesJqueryObject('#sesevent_location_data-wrapper').hide();

				 sesJqueryObject('#sesevent_online_event').show();
				 sesJqueryObject('#sesevent_add_location').hide();
				 sesJqueryObject('#sesevent_enter_address').show();
				 sesJqueryObject('#sesevent_reset_location').hide();
				 sesJqueryObject('#sesevent_location_region').hide();
				 sesJqueryObject('#region').removeClass('required');

				 sesJqueryObject('.location_value').val('');
				 sesJqueryObject('#locationSes').val('');
				 sesJqueryObject('#lngSes').val('');
				 sesJqueryObject('#latSes').val('');
			 }
			 return false;
		 });
	 }
<?php if($is_webinar){ ?>
	sesJqueryObject('#sesevent_online_event').trigger('click');
	 sesJqueryObject('#meeting_point-wrapper').hide();
	 sesJqueryObject('#meeting_url-wrapper').show();
<?php }else if($location != '' && ($venue_name != '' || $city != '' || $state != '' || $country != '' || $zip != '' || $address != '' || $address2 != '')){ ?>
	checkinD = true;
	sesJqueryObject('#sesevent_enter_address').trigger('click');
	sesJqueryObject('#meeting_point-wrapper').show();
	sesJqueryObject('#meeting_url-wrapper').hide();
<?php }else{ ?>
	//sesJqueryObject('#sesevent_enter_address').trigger('click');
	sesJqueryObject('#meeting_point-wrapper').show();
	sesJqueryObject('#meeting_url-wrapper').hide();
<?php } ?>
	mapLoad_event = false;
	initializeSesEventMapList();
 });
  function createEventLoadMap() {
	 var lat = sesJqueryObject('#latSes').val();
	 var lng = sesJqueryObject('#lngSes').val();
	 if (lat && lng && sesJqueryObject('#sesevent_location_map_data').css('display') == 'none') {
		 sesJqueryObject('#sesevent_enter_address').trigger('click');
	 }
	 if (lat && lng && sesJqueryObject('#sesevent_location_map_data').css('display') == 'block') {
		 sesJqueryObject('#sesevent_location_map').show();
		 sesJqueryObject('#sesevent_default_map').hide();
		 var myLatlng = new google.maps.LatLng(lat, lng);
		 var myOptions = {
			 zoom: 17,
			 center: myLatlng,
			 mapTypeId: google.maps.MapTypeId.ROADMAP
		 }
		 var mapLocationCreate = new google.maps.Map(document.getElementById("sesevent_location_map"), myOptions);
		 var marker = new google.maps.Marker({
			 position: myLatlng,
			 map: mapLocationCreate,
		 });
		 google.maps.event.addListener(mapLocationCreate, 'click', function () {
			 google.maps.event.trigger(mapLocationCreate, 'resize');
			 mapLocationCreate.setZoom(17);
			 mapLocationCreate.setCenter(myLatlng);
		 });
		 if (checkinD) {
			 checkinD = false;
			 return;
		 }
		 var geocoder = new google.maps.Geocoder();
		 geocoder.geocode({'latLng': new google.maps.LatLng(lat, lng)}, function (results, status) {
			 var country_code;
			 var country;
			 var state;
			 var city;
			 if (status == google.maps.GeocoderStatus.OK && results.length) {
				 if (results[0]) {
					 for (var i = 0; i < results[0].address_components.length; i++) {
						 var postalCode = results[0].address_components[i].long_name;
					 }
				 }
				 if (results[1]) {
					 var indice = 0;
					 for (var j = 0; j < results.length; j++) {
						 if (results[j].types[0] == 'locality') {
							 indice = j;
							 break;
						 }
					 }
					 for (var i = 0; i < results[j].address_components.length; i++) {
						 if (results[j].address_components[i].types[0] == "locality") {
							 //this is the object you are looking for
							 city = results[j].address_components[i].long_name;
						 }
						 if (results[j].address_components[i].types[0] == "administrative_area_level_1") {
							 //this is the object you are looking for
							 state = results[j].address_components[i].long_name;
						 }
						 if (results[j].address_components[i].types[0] == "country") {
							 //this is the object you are looking for
							 country = results[j].address_components[i].long_name;
							 country_code = results[j].address_components[i].short_name;
						 }
					 }
					 if (postalCode)
						 sesJqueryObject('#zip').val(postalCode);
					 if (city)
						 sesJqueryObject('#city').val(city);
					 if (state)
						 sesJqueryObject('#state').val(state);
					 if (country)
						 sesJqueryObject('#country').val(country);
					 if (country_code === 'NL') {
						 sesJqueryObject('#sesevent_location_region').show();
						 sesJqueryObject('#region').addClass('required');
					 } else {
						 sesJqueryObject('#sesevent_location_region').hide();
						 sesJqueryObject('#region').removeClass('required');
						 sesJqueryObject('#region').val('');
					 }
				 }
			 }
		 });
	 }
 }
</script>
