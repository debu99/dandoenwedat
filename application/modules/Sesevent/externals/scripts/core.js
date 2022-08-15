/* $Id: core.js  2016-03-17 00:00:000 SocialEngineSolutions $ */
//initialize default values
var locationcreatedata;
var formObj;
var map_event;
var infowindow_event;
var marker_event;
var mapLoad_event = true;
//list page map 
function initializeSesEventMapList() {
  if (mapLoad_event) {
    var mapOptions = {
      center: new google.maps.LatLng(-33.8688, 151.2195),
      zoom: 17
    };
    map_event = new google.maps.Map(document.getElementById('map-canvas-list'),
            mapOptions);
  }
  if (sesJqueryObject('#locationSes').length)
    var input = document.getElementById('locationSes');
  else
    var input = document.getElementById('locationSesList');

  var autocomplete = new google.maps.places.Autocomplete(input);
  if (mapLoad_event)
    autocomplete.bindTo('bounds', map);

  if (mapLoad_event) {
    infowindow_event = new google.maps.InfoWindow();
    marker_event = new google.maps.Marker({
      map: map_event,
      anchorPoint: new google.maps.Point(0, -29)
    });
  }
  google.maps.event.addListener(autocomplete, 'place_changed', function () {

    if (mapLoad_event) {
      infowindow_event.close();
      marker_event.setVisible(false);
    }
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      return;
    }
    if (mapLoad_event) {
      // If the place has a geometry, then present it on a map.
      if (place.geometry.viewport) {
        map_event.fitBounds(place.geometry.viewport);
      } else {
        map_event.setCenter(place.geometry.location);
        map_event.setZoom(17);  // Why 17? Because it looks good.
      }
      marker_event.setIcon(/** @type {google.maps.Icon} */({
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(35, 35)
      }));
    }
    if (sesJqueryObject('#locationSes').length) {
      document.getElementById('lngSes').value = place.geometry.location.lng();
      document.getElementById('latSes').value = place.geometry.location.lat();
    } else {
      document.getElementById('lngSesList').value = place.geometry.location.lng();
      document.getElementById('latSesList').value = place.geometry.location.lat();
    }
    if (mapLoad_event) {
      marker_event.setPosition(place.geometry.location);
      marker_event.setVisible(true);
    }
    var address = '';
    if (place.address_components) {
      address = [
        (place.address_components[0] && place.address_components[0].short_name || ''),
        (place.address_components[1] && place.address_components[1].short_name || ''),
        (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }
    if (mapLoad_event) {
      infowindow_event.setContent('<div><strong>' + place.name + '</strong><br>' + address);
      infowindow_event.open(map_event, marker_event);
      return false;
    }
  });
  if (mapLoad_event) {
    google.maps.event.addDomListener(window, 'load', initializeSesEventMapList);
  }
}
//list page map 
function initializeSesEventMapList() {
if(sesJqueryObject('#locationSes').length)
	var input = document.getElementById('locationSes');
else
  var input =document.getElementById('locationSesList');

  var autocomplete = new google.maps.places.Autocomplete(input);


  google.maps.event.addListener(autocomplete, 'place_changed', function() {
	
	
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      return;
    }
	if(sesJqueryObject('#locationSes').length){
		document.getElementById('lngSes').value = place.geometry.location.lng();
		document.getElementById('latSes').value = place.geometry.location.lat();
	}else{
		document.getElementById('lngSesList').value = place.geometry.location.lng();
		document.getElementById('latSesList').value = place.geometry.location.lat();
	}
		if(typeof createEventLoadMap == 'function')
			createEventLoadMap();
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
//open popup in smoothbox
function openURLinSmoothBox(openURLsmoothbox) {
  Smoothbox.open(openURLsmoothbox);
  parent.Smoothbox.close;
  return false;
}

// profile tab resize
sesJqueryObject(document).on('click','.tab_layout_sesevent_profile_events',function (event) {
    var pinboardVar = sesJqueryObject('.sesbasic_view_type_options').find('.boardicon').attr('class');
    if(typeof pinboardVar != 'undefined'){
      pinboardVar = pinboardVar.replace('boardicon pin_selectView_','');
      if(pinboardVar.indexOf('active') > 0) {
				pinboardVar = pinboardVar.replace(' active','');
				eval("pinboardLayout_"+pinboardVar+"('true',true)");
      }
    }
    sesJqueryObject(window).trigger('resize');
});
//common function for like comment ajax
function like_data_sesevent(element, functionName, itemType) {
  if (!sesJqueryObject (element).attr('data-url'))
    return;
  var id = sesJqueryObject (element).attr('data-url');
  if (sesJqueryObject (element).hasClass('button_active')) {
    sesJqueryObject (element).removeClass('button_active');
  } else
    sesJqueryObject (element).addClass('button_active');
  (new Request.HTML({
    method: 'post',
    'url': en4.core.baseUrl + 'sesevent/index/' + functionName,
    'data': {
      format: 'html',
      id: sesJqueryObject (element).attr('data-url'),
      type: itemType,
    },
    onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
      var response = jQuery.parseJSON(responseHTML);
      if (response.error)
        alert(en4.core.language.translate('Something went wrong,please try again later'));
      else {
				if(sesJqueryObject(element).hasClass('sesevent_albumlike')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_photolike')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_list')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_like_seseventspeaker_speaker')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_like_sesevent_host')){
					var elementCount = 	element;
				}else {
					var elementCount = '.sesevent_like_sesevent_event_'+id;
				}
				 sesJqueryObject (elementCount).find('span').html(response.count);
        if (response.condition == 'reduced') {
					if(sesJqueryObject(element).hasClass('sesevent_cover_btn')){
						sesJqueryObject (element).find('i').removeClass('fa-thumbs-up');
						sesJqueryObject (element).find('i').addClass('fa-thumbs-up');
					}else
						sesJqueryObject (elementCount).removeClass('button_active');
        } else {
					if(sesJqueryObject(element).hasClass('sesevent_cover_btn')){
						sesJqueryObject (element).find('i').addClass('fa-thumbs-up');
						sesJqueryObject (element).find('i').removeClass('fa-thumbs-up');
					}else
						sesJqueryObject (elementCount).addClass('button_active');
        }
      }
      return true;
    }
  })).send();
}
//common function for favourite item ajax
function favourite_data_sesevent(element, functionName, itemType) {
  if (!sesJqueryObject (element).attr('data-url'))
    return;
   var id = sesJqueryObject (element).attr('data-url');
  if (sesJqueryObject (element).hasClass('button_active')) {
    sesJqueryObject (element).removeClass('button_active');
  } else
    sesJqueryObject (element).addClass('button_active');
  (new Request.HTML({
    method: 'post',
    'url': en4.core.baseUrl + 'sesevent/index/' + functionName,
    'data': {
      format: 'html',
      id: sesJqueryObject (element).attr('data-url'),
      type: itemType,
    },
    onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {      
      var response = jQuery.parseJSON(responseHTML);
      if (response.error)
        alert(en4.core.language.translate('Something went wrong,please try again later'));
      else {
				if(sesJqueryObject(element).hasClass('sesevent_favourite_seseventspeaker_speaker')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_favourite_sesevent_list')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_favourite_sesevent_host')){
					var elementCount = 	element;
				} else if(sesJqueryObject(element).hasClass('sesevent_favourite_seseventspeaker_speaker')){
					var elementCount = 	element;
				}
				sesJqueryObject (elementCount).find('span').html(response.count);
				 if (response.condition == 'reduced') {
					if(sesJqueryObject(element).hasClass('sesevent_cover_btn')){
						sesJqueryObject (element).find('i').removeClass('fa-heart');
						sesJqueryObject (element).find('i').addClass('fa-heart');
					}else
						sesJqueryObject (elementCount).removeClass('button_active');
        } else {
					if(sesJqueryObject(element).hasClass('sesevent_cover_btn')){
						sesJqueryObject (element).find('i').addClass('fa-heart');
						sesJqueryObject (element).find('i').removeClass('fa-heart');
					}else
						sesJqueryObject (elementCount).addClass('button_active');
        }
 				sesJqueryObject ('.sesevent_favourite_sesevent_event_'+id).find('span').html(response.count);
        if (response.condition == 'reduced') {
					sesJqueryObject ('.sesevent_favourite_sesevent_event_'+id).removeClass('button_active');
        } else {
					sesJqueryObject ('.sesevent_favourite_sesevent_event_'+id).addClass('button_active');
        }				
      }
      return true;
    }
  })).send();
}

//Like
sesJqueryObject (document).on('click', '.sesevent_like_sesevent_event', function () {
  like_data_sesevent(this, 'like', 'sesevent_event');
});

sesJqueryObject (document).on('click', '.sesevent_albumlike', function () {
  like_data_sesevent(this, 'like', 'sesevent_album');
});

sesJqueryObject (document).on('click', '.sesevent_photolike', function () {
  like_data_sesevent(this, 'like', 'sesevent_photo');
});

sesJqueryObject (document).on('click', '.sesevent_like_sesevent_list', function () {
	like_data_sesevent(this, 'like', 'sesevent_list');
});

sesJqueryObject (document).on('click', '.sesevent_like_seseventspeaker_speaker', function () {
	like_data_sesevent(this, 'like', 'seseventspeaker_speaker');
});

sesJqueryObject (document).on('click', '.sesevent_like_sesevent_host', function () {
	like_data_sesevent(this, 'like', 'sesevent_host');
});

//Favourite
sesJqueryObject (document).on('click', '.sesevent_favourite_sesevent_event', function () {
  favourite_data_sesevent(this, 'favourite', 'sesevent_event');
});

sesJqueryObject (document).on('click', '.sesevent_favourite_sesevent_list', function () {
	favourite_data_sesevent(this, 'favourite', 'sesevent_list');
});

sesJqueryObject (document).on('click', '.sesevent_favourite_seseventspeaker_speaker', function () {
	favourite_data_sesevent(this, 'favourite', 'seseventspeaker_speaker');
});

sesJqueryObject (document).on('click', '.sesevent_favourite_sesevent_host', function () {
	favourite_data_sesevent(this, 'favourite', 'sesevent_host');
});
function chnageManifestUrl(type) {
  window.location.href = en4.core.staticBaseUrl + eventURLsesevent + '/' + type;
}

function favouriteEvent(object_id) {
    sesJqueryObject.ajax({
        type: 'POST',
        url: en4.core.baseUrl + 'events/favourite/type/sesevent_event/id/' + object_id,
        success: function () {
            const elementId = "list-item-favourite-" + object_id;
            const element = document.getElementById(elementId);
            if (element.getElementsByTagName("i")[0].className === "far fa-star") {
                element.innerHTML = "<i class='fas fa-star'></i>";
            } else {
                element.innerHTML = "<i class='far fa-star'></i>";
            }
        }
    });
}