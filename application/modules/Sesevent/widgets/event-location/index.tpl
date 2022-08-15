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
<?php if (!$this->is_ajax) { ?>
	<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
	<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
	<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/richMarker.js');
}
?>
<?php $randonNumber = "location_widget_sesevent"; ?>
<?php $locationArray = array();
$counter = 0; ?>
<?php foreach ($this->paginator as $event) : ?>
	<?php if ($event->lat) : ?>
		<?php $host = Engine_Api::_()->getItem('sesevent_host', $event->host);
		// Show Label
		$labels = '';
		if (isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive) || isset($this->verifiedLabel)) {
			$labels .= "<p class=\"sesevent_labels\">";
			if (isset($this->featuredLabelActive) && $event->featured == 1) {
				$labels .= "<span class=\"sesevent_label_featured\">FEATURED</span>";
			}
			if (isset($this->sponsoredLabelActive) && $event->sponsored == 1) {
				$labels .= "<span class=\"sesevent_label_sponsored\">SPONSORED</span>";
			}
			$labels .= "</p>";
			if (isset($this->verifiedLabelActive) && $event->verified == 1) {
				$labels .= "<div class=\"sesevent_verified_label\" title=\"VERIFIED\"><i class=\"fa fa-check\"></i></div>";
			}
		}
		$location = ''; ?>
		<?php if (isset($this->locationActive) && $event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) : ?>
			<?php $locationText = $this->translate('Location'); ?>
			<?php $locationvalue = $event->location; ?>
			<?php $location = "<div class=\"sesevent_list_stats sesevent_list_location\">
												<span class=\"widthfull\">
													<i class=\"fas fa-map-marker-alt sesbasic_text_light\" title=\"$locationText\"></i>
													<span title=\"$locationvalue\"><a href='" . $this->url(array('resource_id' => $event->event_id, 'resource_type' => 'sesevent_event', 'action' => 'get-direction'), 'sesbasic_get_direction', true) . "' class=\"openSmoothbox\">$locationvalue</a></span>
												</span>
											</div>";
			?>
		<?php endif; ?>
		<?php
		$canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
		$likeButton = '';
		if (Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($this->likeButtonActive) && $canComment) {
			$LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id, $event->getType());
			$likeClass = ($LikeStatus) ? ' button_active' : '';
			$likeButton = '<a href="javascript:;" data-url="' . $event->getIdentity() . '" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event_' . $event->event_id . ' sesevent_like_sesevent_event ' . $likeClass . '"> <i class="fa fa-thumbs-up"></i><span>' . $event->like_count . '</span></a>';
		}
		$favouriteButton = '';
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)) {
			$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type' => 'sesevent_event', 'resource_id' => $event->event_id));
			$favClass = ($favStatus)  ? 'button_active' : '';
			$favouriteButton = '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn  sesevent_favourite_sesevent_event_' . $event->event_id . ' sesevent_favourite_sesevent_event ' . $favClass . '" data-url="' . $event->getIdentity() . '"><i class="fa fa-heart"></i><span>' . $event->favourite_count . '</span></a>';
		}
		$listButton = '';
		if (isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) {
			$URL = $this->url(array('action' => 'add', 'module' => 'sesevent', 'controller' => 'list', 'event_id' => $event->event_id), 'default', true);
			$listButton = "<a href='javascript:;' onclick='opensmoothboxurl(\"$URL\")' class='sesbasic_icon_btn  sesevent_add_list'  title='" . $this->translate('Add To List') . "' data-url='$event->event_id'><i class='fa fa-plus'></i></a>";
		}

		$eventStartEndDate = '';
		if (isset($this->startenddateActive)) {
			$eventStartEndDate = "<div class='sesevent_list_stats sesevent_list_time'>
                            <span class='widthfull'>
                              <i class='far fa-calendar-alt sesbasic_text_light' title='" . $this->translate('Start & End Time') . "'></i>
                               " . $this->eventStartEndDates($event) . "
                            </span>
                          </div>";
		}

		$user = Engine_Api::_()->getItem('user', $event->user_id);
		$owner = $event->getOwner();
		$ratings = '';
		if (Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating() && $this->ratingActive) {
			if ($event->rating > 0) :
				$ratings .= '
<div class="sesevent_list_grid_rating" title="' . $this->translate(array('%s rating', '%s ratings', $event->rating), $this->locale()->toNumber($event->rating)) . '">';
				for ($x = 1; $x <= $event->rating; $x++) :
					$ratings .= '<span class="sesbasic_rating_star_small fa fa-star"></span>';
				endfor;
				if ((round($event->rating) - $event->rating) > 0) :
					$ratings .= '<span class="sesbasic_rating_star_small fa fa-star-half"></span>';
				endif;
				$ratings .= '</div>';
			endif;
		}

		$urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
		if (isset($this->byActive)) {
			$owner = '<div class="sesevent_grid_date sesevent_list_stats sesbasic_text_light"><span><i class="fa fa-user"></i>' . $this->htmlLink($owner->getHref(), $owner->getTitle()) . '</span></div>';
		} else
			$owner = '';
		if (isset($this->hostActive)) {
			$host = '<div class="sesevent_grid_date sesevent_list_stats sesbasic_text_light"><span><i class="fa fa-user"></i>' . $this->htmlLink($host->getHref(), $host->getTitle()) . '</span></div>';
		} else
			$host = '';
		$stats = $eventStartEndDate . $ratings . '<div class="sesbasic_largemap_stats sesevent_list_stats sesbasic_clearfix">';
		if (isset($this->commentActive)) {
			$stats .= '<span title="' . $this->translate(array('%s comment', '%s comments', $event->comment_count), $this->locale()->toNumber($event->comment_count)) . '"><i class="fa fa-comment"></i>' . $event->comment_count . '</span>';
		}
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive)) {
			$stats .= '<span title="' . $this->translate(array('%s favourite', '%s favourites', $event->favourite_count), $this->locale()->toNumber($event->favourite_count)) . '"><i class="fa fa-heart"></i>' . $event->favourite_count . '</span>';
		}
		if (isset($this->viewActive)) {
			$stats .= '<span title="' . $this->translate(array('%s view', '%s views', $event->view_count), $this->locale()->toNumber($event->view_count)) . '"><i class="fa fa-eye"></i>' . $event->view_count . '</span>';
		}
		if (isset($this->likeActive)) {
			$stats .= '<span title="' . $this->translate(array('%s like', '%s likes', $event->like_count), $this->locale()->toNumber($event->like_count)) . '"><i class="fa fa-thumbs-up"></i>' . $event->like_count . '</span> ';
		}
		$stats .= '</div>';

		if (isset($this->socialSharingActive)) {
			$socialShare = $this->partial('_socialShareIcons.tpl', 'sesbasic', array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit));
			$socialshare = '<div class="sesevent_grid_btns">' . $socialShare . $likeButton . $favouriteButton . $listButton . '</div>';
		} else
			$socialshare = $likeButton . $favouriteButton . $listButton;
		$locationArray[$counter]['id'] = $event->getIdentity();
		$locationArray[$counter]['owner'] = $owner;
		$locationArray[$counter]['location'] = $location;
		$locationArray[$counter]['host'] = $host;
		$locationArray[$counter]['labels'] = $labels;
		$locationArray[$counter]['stats'] = $stats;
		$locationArray[$counter]['socialshare'] = $socialshare;
		$locationArray[$counter]['lat'] = $event->lat;
		$locationArray[$counter]['lng'] = $event->lng;
		$locationArray[$counter]['iframe_url'] = '';
		$locationArray[$counter]['image_url'] = $event->getPhotoUrl();
		$locationArray[$counter]['title'] = '<a href="' . $event->getHref() . '">' . $event->title . '</a>';
		//$locationArray[$counter]['description'] = $event->description;      
		$counter++; ?>
	<?php endif; ?>
<?php endforeach; ?>
<?php if (!$this->is_ajax) { ?>
	<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
	<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/richMarker.js'); ?>
	<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/marker.js'); ?>
	<script type="text/javascript">
		var markers_<?php echo $randonNumber; ?> = [];
		var map_<?php echo $randonNumber; ?>;
		if ('<?php echo $this->lat; ?>' == '') {
			var latitude_<?php echo $randonNumber; ?> = '26.9110600';
			var longitude_<?php echo $randonNumber; ?> = '75.7373560';
		} else {
			var latitude_<?php echo $randonNumber; ?> = '<?php echo $this->lat; ?>';
			var longitude_<?php echo $randonNumber; ?> = '<?php echo $this->lng; ?>';
		}

		function initialize_<?php echo $randonNumber ?>() {
			var bounds_<?php echo $randonNumber; ?> = new google.maps.LatLngBounds();
			map_<?php echo $randonNumber; ?> = new google.maps.Map(document.getElementById('map-canvas-<?php echo $randonNumber; ?>'), {
				zoom: 17,
				scrollwheel: true,
				center: new google.maps.LatLng(latitude_<?php echo $randonNumber; ?>, longitude_<?php echo $randonNumber; ?>),
			});
			oms_<?php echo $randonNumber; ?> = new OverlappingMarkerSpiderfier(map_<?php echo $randonNumber; ?>, {
				nearbyDistance: 40,
				circleSpiralSwitchover: 0
			});
		}
		var countMarker_<?php echo $randonNumber; ?> = 0;

		function DeleteMarkers_<?php echo $randonNumber ?>() {
			//Loop through all the markers and remove
			for (var i = 0; i < markers_<?php echo $randonNumber; ?>.length; i++) {
				markers_<?php echo $randonNumber; ?>[i].setMap(null);
			}
			markers_<?php echo $randonNumber; ?> = [];
			markerData_<?php echo $randonNumber ?> = [];
			markerArrayData_<?php echo $randonNumber ?> = [];
		};
		var markerArrayData_<?php echo $randonNumber ?>;
		var markerData_<?php echo $randonNumber ?> = [];
		var bounds_<?php echo $randonNumber; ?> = new google.maps.LatLngBounds();

		function newMarkerLayout_<?php echo $randonNumber ?>(dataLenth) {
			var bounds = new google.maps.LatLngBounds();
			for (i = 0; i < markerArrayData_<?php echo $randonNumber ?>.length; i++) {
				var images = '<div class="image sesevent_map_thumb_img"><img src="' + markerArrayData_<?php echo $randonNumber ?>[i]['image_url'] + '"  /></div>';
				var owner = markerArrayData_<?php echo $randonNumber ?>[i]['owner'];
				var host = markerArrayData_<?php echo $randonNumber ?>[i]['host'];
				var labels = markerArrayData_<?php echo $randonNumber ?>[i]['labels'];
				var location = markerArrayData_<?php echo $randonNumber ?>[i]['location'];
				var stats = markerArrayData_<?php echo $randonNumber ?>[i]['stats'];
				var socialshare = markerArrayData_<?php echo $randonNumber ?>[i]['socialshare'];
				var marker_html = '<div class="pin public marker_' + countMarker_<?php echo $randonNumber; ?> + '" data-lat="' + markerArrayData_<?php echo $randonNumber ?>[i]['lat'] + '" data-lng="' + markerArrayData_<?php echo $randonNumber ?>[i]['lng'] + '">' +
					'<div class="wrapper">' +
					'<div class="small">' +
					'<img src="' + markerArrayData_<?php echo $randonNumber ?>[i]['image_url'] + '" style="height:48px;width:48px;" alt="" />' +
					'</div>' +
					'<div class="large"><div class="sesevent_map_thumb sesevent_grid_btns_wrap">' +
					images + labels + socialshare +
					'</div><div class="sesbasic_large_map_content sesevent_large_map_content sesbasic_clearfix">' +
					'<div class="sesbasic_large_map_content_title">' + markerArrayData_<?php echo $randonNumber ?>[i]['title'] + '</div>' + owner + host + location + stats +
					'</div>' +
					'<a class="icn close" href="javascript:;" title="Close"><i class="fa fa-times"></i></a>' +
					'</div>' +
					'</div>' +
					'<span class="sesbasic_largemap_pointer"></span>' +
					'</div>';
				markerData = new RichMarker({
					position: new google.maps.LatLng(markerArrayData_<?php echo $randonNumber ?>[i]['lat'], markerArrayData_<?php echo $randonNumber ?>[i]['lng']),
					map: map_<?php echo $randonNumber; ?>,
					flat: true,
					draggable: false,
					scrollwheel: false,
					id: countMarker_<?php echo $randonNumber; ?>,
					anchor: RichMarkerPosition.BOTTOM,
					content: marker_html
				});
				oms_<?php echo $randonNumber; ?>.addListener('click', function(marker) {
					var id = marker.markerid;
					previousIndex = sesJqueryObject('.marker_' + id).parent().parent().css('z-index');
					sesJqueryObject('.marker_' + id).parent().parent().css('z-index', '9999');
					sesJqueryObject('.pin').removeClass('active').css('z-index', 10);
					sesJqueryObject('.marker_' + id).addClass('active').css('z-index', 200);
					sesJqueryObject('.marker_' + id + ' .large .close').click(function() {
						sesJqueryObject(this).parent().parent().parent().parent().parent().css('z-index', previousIndex);
						sesJqueryObject('.pin').removeClass('active');
						return false;
					});
				});
				markers_<?php echo $randonNumber; ?>.push(markerData);
				markerData.setMap(map_<?php echo $randonNumber; ?>);
				bounds.extend(markerData.getPosition());
				markerData.markerid = countMarker_<?php echo $randonNumber; ?>;
				oms_<?php echo $randonNumber; ?>.addMarker(markerData);
				countMarker_<?php echo $randonNumber; ?>++;
			}
			map_<?php echo $randonNumber; ?>.fitBounds(bounds);
		}


		var searchParams;
		var markerArrayData;

		function callNewMarkersAjax() {
			(new Request.HTML({
				method: 'post',
				'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
				'data': {
					format: 'html',
					is_ajax: 1,
					searchParams: searchParams,
					show_criterias: '<?php echo json_encode($this->show_criterias); ?>'
				},
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
					if ($('loadingimgsesevent-wrapper'))
						sesJqueryObject('#loadingimgsesevent-wrapper').hide();
					DeleteMarkers_<?php echo $randonNumber ?>();
					if (responseHTML) {
						var mapData = sesJqueryObject.parseJSON(responseHTML);
						if (sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
							newMapData_<?php echo $randonNumber ?> = mapData;
							mapFunction_<?php echo $randonNumber ?>();
						}
					}
					sesJqueryObject('#loadingimgsesevent-wrapper').hide();
				}
			})).send();
		}
		var newMapData_<?php echo $randonNumber ?> = [];

		function mapFunction_<?php echo $randonNumber ?>() {
			if (!map_<?php echo $randonNumber; ?> || loadMap_<?php echo $randonNumber; ?>) {
				initialize_<?php echo $randonNumber ?>();
				loadMap_<?php echo $randonNumber; ?> = false;
			}
			if (!newMapData_<?php echo $randonNumber ?>) {
				return false;
			}
			google.maps.event.trigger(map_<?php echo $randonNumber; ?>, "resize");
			markerArrayData_<?php echo $randonNumber ?> = newMapData_<?php echo $randonNumber ?>;
			if (markerArrayData_<?php echo $randonNumber ?>.length)
				newMarkerLayout_<?php echo $randonNumber ?>();
			newMapData_<?php echo $randonNumber ?> = '';
			sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').addClass('checked');
		}
		window.addEvent('domready', function() {
			var mapData = sesJqueryObject.parseJSON(document.getElementById('map-data_<?php echo $randonNumber; ?>').innerHTML);
			if (sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
				newMapData_<?php echo $randonNumber ?> = mapData;
				mapFunction_<?php echo $randonNumber ?>();
			}
			sesJqueryObject('#locationSesList').val('<?php echo $this->location; ?>');
			sesJqueryObject('#latSesList').val('<?php echo $this->lat; ?>');
			sesJqueryObject('#lngSesList').val('<?php echo $this->lng; ?>');
		});
	</script>
<?php } ?>
<?php if (!$this->is_ajax) { ?>
	<script type="application/javascript">
		var tabId_loc = <?php echo $this->identity; ?>;
		window.addEvent('domready', function() {
			tabContainerHrefSesbasic(tabId_loc);
		});
	</script>
	<div id="map-data_location_widget_sesevent" style="display:none;"><?php echo json_encode($locationArray, JSON_HEX_QUOT | JSON_HEX_TAG); ?></div>
	<div id="map-canvas-location_widget_sesevent" class="map sesbasic_large_map sesbm sesbasic_bxs"></div>
<?php } else {
	echo json_encode($locationArray, JSON_HEX_QUOT | JSON_HEX_TAG);;
	die;
} ?>