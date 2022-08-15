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
<div class="sesbasic_sidebar_block sesevent_user_location sesbasic_clearfix sesbasic_bxs">
  <i class="fas fa-map-marker-alt floatL"></i>
	<a class="location_data_seevent" <?php if(!empty($this->cookiedata['location'])){ ?> style="display:block" <?php }else{ ?> style="display:none"  <?php } ?> id="sesbasic_location_data_f" href="javascript:;"><?php echo $this->cookiedata['location']; ?></a>
  <a class="location_data_seevent" href="javascript:;" id="sesbasic_location_data_e" <?php if(empty($this->cookiedata['location'])){ ?> style="display:block" <?php }else{ ?>style="display:none"  <?php } ?>><?php echo $this->translate('Select Your Location'); ?></a>
</div>
<div class="sesevent_timezone_popup_overlay" id="sesbasic_location_cookie_overlay" style="display:none;"></div>
<div class="sesevent_timezone_popup" style="display:none;" id="sesbasic_location_cookie_container">
	<div class="sesevent_timezone_popup_content">
  	<div class="sesevent_timezone_popup_content_inner">
      <div class="sesevent_timezone_popup_heading">
        <h2><?php echo $this->translate("Your Current Location"); ?></h2>
      </div>
      <div class="sesevent_timezone_popup_elements">  
        <input style="width:100%" type="text" id="sesbasic_cookie_value" autocomplete="off" placeholder="Location" value="<?php echo !empty($this->cookiedata['location']) ? $this->cookiedata['location'] : '' ; ?>"/>
        <br />
        <div id="sesbasic_remove_location_ctn">
        <input type="checkbox" id="sesbasic_remove_location" />Remove Selected Location 
        </div>
        <input type="hidden" id="sesbasic_cookie_lat" value="<?php echo !empty($this->cookiedata['lat']) ? $this->cookiedata['lat'] : '' ; ?>" />
        <input type="hidden" id="sesbasic_cookie_lng"  value="<?php echo !empty($this->cookiedata['lng']) ? $this->cookiedata['lng'] : '' ; ?>"/>
        <br />
				<div class="sesevent_timezone_popup_buttons">
          <button id="saveLocationData" onclick=""><?php echo $this->translate("Save"); ?></button>
          <button id="cancelLocationData" onclick="" class="secondary_button"><?php echo $this->translate("Cancel"); ?></button>
      	</div>
      </div>
		</div>
	</div>
</div>
<?php $request = Zend_Controller_Front::getInstance()->getRequest(); ?>
<?php $controllerName = $request->getControllerName(); ?>
<?php $actionName = $request->getActionName(); ?>

<script type="application/javascript">
<?php if(empty($this->cookiedata['location'])){ ?>
window.addEvent('domready', function() {
		getLocation();
	});	

function getLocation() {
		if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showPosition);
		}
}
function showPosition(position) {
		var latMap = position.coords.latitude;
		var lngMap = position.coords.longitude;
		codeLatLng(latMap,lngMap);
}
function codeLatLng(lat, lng) {
    var latlng = new google.maps.LatLng(lat, lng);
		var 	geocoder = new google.maps.Geocoder();
		var mylocation;
    geocoder
            .geocode(
                    {
                        'latLng' : latlng
                    },
                    function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                var arrAddress = results;
                                //iterate through address_component array
                                sesJqueryObject
																	.each(
																		arrAddress,
																		function(i, address_component) {
																		if(i == 0){
																			mylocation = address_component.formatted_address;
																		}
																			
																	});
                            } 
														if(mylocation){
																sesJqueryObject('#sesbasic_cookie_value').val(mylocation);
																sesJqueryObject('#sesbasic_cookie_lat').val(lat);
																sesJqueryObject('#sesbasic_cookie_lng').val(lng);
																sesJqueryObject('#saveLocationData').trigger('click');
														}
                        } 
                    });
	}
<?php } ?>
sesJqueryObject('.location_data_seevent').click(function(){
		sesJqueryObject('#sesbasic_location_cookie_container').show();
		sesJqueryObject('#sesbasic_location_cookie_overlay').show();
		var htmlF = sesJqueryObject('#sesbasic_location_data_f').html();
		if(!htmlF){
			sesJqueryObject('#sesbasic_remove_location_ctn').hide();
		}else{
			sesJqueryObject('#sesbasic_remove_location_ctn').show();
		}
		sesCookieChangedLocation();
});
	sesJqueryObject('#saveLocationData').click(function(e){
		//get remove location value
		var removeLocation = sesJqueryObject('#sesbasic_remove_location').is(':checked');
		if(removeLocation){
			sesJqueryObject('#sesbasic_cookie_value').val('');
			sesJqueryObject('#sesbasic_cookie_lat').val('');
			sesJqueryObject('#sesbasic_cookie_lng').val('');
		}
		//set location data in cookie
		var location = sesJqueryObject('#sesbasic_cookie_value').val();
		var lat = sesJqueryObject('#sesbasic_cookie_lat').val();
		var lng = sesJqueryObject('#sesbasic_cookie_lng').val();
		sesJqueryObject('#sesbasic_cookie_value').css('border','');
		sesJqueryObject("#sesbasic_remove_location").prop('checked', false); 
		if(lat && lng && location){
			setCookie('sesbasic_location_data',location,30);
			//set lat in cookie
			setCookie('sesbasic_location_lat',lat,30);
			//set lng in cookie		
			setCookie('sesbasic_location_lng',lng,30);
			sesJqueryObject('#sesbasic_location_data_f').show();
			sesJqueryObject('#sesbasic_location_data_e').hide();
			sesJqueryObject('#sesbasic_location_cookie_container').hide();
			sesJqueryObject('#sesbasic_location_cookie_overlay').hide();
		}else{
			setCookie('sesbasic_location_data',location,30,'Thu, 01 Jan 1970 00:00:01 GMT');
			//set lat in cookie
			setCookie('sesbasic_location_lat',lat,30,'Thu, 01 Jan 1970 00:00:01 GMT');
			//set lng in cookie		
			setCookie('sesbasic_location_lng',lng,30,'Thu, 01 Jan 1970 00:00:01 GMT');
			sesJqueryObject('#sesbasic_location_data_f').hide();
			sesJqueryObject('#sesbasic_location_data_e').show();
			sesJqueryObject('#sesbasic_location_cookie_container').hide();
			sesJqueryObject('#sesbasic_location_cookie_overlay').hide();
		}
		if(sesJqueryObject('#locationSesList').length){
			sesJqueryObject('#locationSesList').val(location);	
			sesJqueryObject('#latSesList').val(lat);	
			sesJqueryObject('#lngSesList').val(lng);	
		}
		sesJqueryObject('#sesbasic_location_data_f').html(location);
		if(!sesJqueryObject('#locationSesList').length)
			return false;
		<?php if($controllerName == 'index' && ($actionName == 'browse')){?>
				<?php $pageName = 'sesevent_index_browse';?>
				<?php $identity = Engine_Api::_()->sesevent()->getIdentityWidget('sesevent.browse-events','widget',$pageName); ?>
				<?php if($identity):?>
						sesJqueryObject(document).ready(function(){
								if(sesJqueryObject('.sesevent_event_all_events').length > 0){
									e.preventDefault();
									sesJqueryObject('#loadingimgsesevent-wrapper').show();
									loadMap_<?php echo $identity;?> = true;
									if(typeof paggingNumber<?php echo $identity; ?> == 'function'){
										sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $identity?>').css('display', 'block');
										isSearch = true;
										e.preventDefault();
										searchParams<?php echo $identity; ?> = 'location='+location+'&lat='+lat+'&lng='+lng+'&miles=1000';
										paggingNumber<?php echo $identity; ?>(1);
									}else if(typeof viewMore_<?php echo $identity; ?> == 'function'){
										sesJqueryObject('#browse-widget_<?php echo $identity; ?>').html('');
										sesJqueryObject('#loading_image_<?php echo $identity; ?>').show();
										isSearch = true;
										e.preventDefault();
										searchParams<?php echo $identity; ?> = 'location='+location+'&lat='+lat+'&lng='+lng+'&miles=1000';
										page<?php echo $identity; ?> = 1;
										viewMore_<?php echo $identity; ?>();
									}
								}
								return true;
     			 });
				<?php endif; ?>
		<?php } ?>
});
sesJqueryObject('#cancelLocationData').click(function(){
		sesJqueryObject('#sesbasic_location_cookie_container').hide();
		sesJqueryObject('#sesbasic_location_cookie_overlay').hide();
		var htmlF = sesJqueryObject('#sesbasic_location_data_f').html();
		if(!htmlF){
			sesJqueryObject('#sesbasic_location_data_e').show();
			sesJqueryObject('#sesbasic_location_data_f').hide();
		}else{
			sesJqueryObject('#sesbasic_location_data_e').hide();
			sesJqueryObject('#sesbasic_location_data_f').show();
		}
		sesJqueryObject('#sesbasic_cookie_value').val(htmlF);
			sesJqueryObject("#sesbasic_remove_location").prop('checked', false);
});
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/"; 
} 

//list page map 
function sesCookieChangedLocation() {
  var input =document.getElementById('sesbasic_cookie_value');
  var autocomplete = new google.maps.places.Autocomplete(input);
  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      return;
    }
		document.getElementById('sesbasic_cookie_lng').value = place.geometry.location.lng();
		document.getElementById('sesbasic_cookie_lat').value = place.geometry.location.lat();
    var address = '';
    if (place.address_components) {
      address = [
        (place.address_components[0] && place.address_components[0].short_name || ''),
        (place.address_components[1] && place.address_components[1].short_name || ''),
        (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }
	}); 
}
</script>
