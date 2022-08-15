<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _eventBrowseWidget.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $previousSubject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : false; ?>

<?php $buyTicket = ''; ?>
<?php  if(!$this->is_ajax){ ?>
<style>
.displayFN{display:none !important;}
</style>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?> 
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?> 
<?php  }  ?>
<?php if(isset($this->optionsEnable) && in_array('pinboard',$this->optionsEnable) && !$this->is_ajax){ 
 $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/imagesloaded.pkgd.js');
	 $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/pinboard.css'); 
   $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/wookmark.min.js');
   $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/pinboardcomment.js');
} ?>
<?php $masonryViewData = $listViewData = $gridViewData = $pinViewData = $advgridViewData = '';?>
<?php $locationArray = array();?>
<?php $counter = 0;?>
  <?php if(!$this->is_ajax){ ?>
   <div class="sesbasic_view_type sesbasic_view_type_<?php echo $randonNumber ?> sesbasic_clearfix clear">
    <?php if(isset($managepage)){ ?>
        <div class="sesbasic_content_filters sesbasic_filter_tabs sesbasic_clearfix" id="sesevent_manage_event_optn">
        	<ul>
          	<li><a class="changeoptn active" href="javascript:;" data-url="upcomingandongoing"><?php echo $this->translate('Upcoming & Ongoing') ; ?></a></li>
          	<li><a class="changeoptn" href="javascript:;" data-url="upcoming"><?php echo $this->translate('Upcoming') ; ?></a></li>
          	<li><a class="changeoptn" href="javascript:;" data-url="ongoing"><?php echo $this->translate('Ongoings') ; ?></a></li>
          	<li><a class="changeoptn" href="javascript:;" data-url="past"><?php echo $this->translate('Past') ; ?></a></li>
          	<li><a class="changeoptn" href="javascript:;" data-url="week"><?php echo $this->translate('This Week') ; ?></a></li>
          	<li><a class="changeoptn" href="javascript:;" data-url="weekend"><?php echo $this->translate('This Weekend') ; ?></a></li>
          	<li><a class="changeoptn" href="javascript:;" data-url="month"><?php echo $this->translate('This Month') ; ?></a></li>
          </ul>
        </div>
     <?php } ?>
      <div class="sesbasic_view_type_options sesbasic_view_type_options_<?php echo $randonNumber;?>">
  <?php if(is_array($this->optionsEnable) && in_array('list',$this->optionsEnable)){ ?>
	  <a title="List View" class="listicon list_selectView_<?php echo $randonNumber;?> <?php if($this->view_type == 'list') { echo 'active'; } ?>" rel="list" href="javascript:showData_<?php echo $randonNumber; ?>('list');"></a>
	<?php } ?>
  	<?php if(is_array($this->optionsEnable) && in_array('grid',$this->optionsEnable)){ ?>
	  <a title="Grid View" class="gridicon grid_selectView_<?php echo $randonNumber;?> <?php if($this->view_type == 'grid') { echo 'active'; } ?>" rel="grid" href="javascript:showData_<?php echo $randonNumber; ?>('grid');"></a>
	<?php } ?>
    <?php if(is_array($this->optionsEnable) && in_array('advgrid',$this->optionsEnable)){ ?>
	  <a title="Advanced Grid View" class="a-gridicon advgrid_selectView_<?php echo $randonNumber;?> <?php if($this->view_type == 'advgrid') { echo 'active'; } ?>" rel="advgrid" href="javascript:showData_<?php echo $randonNumber; ?>('advgrid');"></a>
	<?php } ?>
  <?php if(is_array($this->optionsEnable) && in_array('masonry',$this->optionsEnable)){ ?>
	  <a title="Masonry View" class="flexicon masonry_selectView_<?php echo $randonNumber;?> <?php if($this->view_type == 'masonry') { echo 'active'; } ?>" rel="masonry" href="javascript:showData_<?php echo $randonNumber; ?>('masonry');"></a>
	<?php } ?>  
	<?php if(is_array($this->optionsEnable) && in_array('pinboard',$this->optionsEnable)){ ?>
	  <a title="Pinboard View" class="boardicon pin_selectView_<?php echo $randonNumber;?> <?php if($this->view_type == 'pinboard') { echo 'active'; } ?>" rel="pinboard" href="javascript:showData_<?php echo $randonNumber; ?>('pinboard');"></a>
  <?php if(is_array($this->optionsEnable) && in_array('map',$this->optionsEnable) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)){ ?>
	  <a title="Map View" class="mapicon map_selectView_<?php echo $randonNumber;?> <?php if($this->view_type == 'map') { echo 'active'; } ?>" rel="map" href="javascript:showData_<?php echo $randonNumber; ?>('map');"></a>
	<?php } ?>
	<?php } ?>
      </div>
    </div>
  <?php } ?>
  <?php if(!isset($this->bothViewEnable) && !$this->is_ajax){ ?>
  	<script type="text/javascript">
        en4.core.runonce.add(function() {
            sesJqueryObject('.sesbasic_view_type_<?php echo $randonNumber ?>').addClass('displayFN');
            sesJqueryObject('.sesbasic_view_type_<?php echo $randonNumber ?>').parent().parent().css('border', '0px');
        });
    </script>
  <?php } ?>
  <?php if(!$this->is_ajax) :?>
  <script type="text/javascript">$$('.sesbasic_view_type_<?php echo $randonNumber ?>').setStyle('display', 'block');</script>
  <?php endif; ?>
 <?php if( count($this->paginator) > 0 ): ?>
  <?php $prevEvent; ?>
  <?php foreach( $this->paginator as $key=>$event ): ?>
    <?php 
      if(strlen($event->getTitle()) > $this->list_title_truncation) {
				$listViewTitle = mb_substr($event->getTitle(),0,($this->list_title_truncation-3)).'...';
      }else{
				$listViewTitle = $event->getTitle();
      };
      if(strlen($event->getTitle()) > $this->grid_title_truncation) {
				$gridViewTitle = mb_substr($event->getTitle(),0,($this->grid_title_truncation-3)).'...';
      }else{
				$gridViewTitle = $event->getTitle();
      }
      if(strlen($event->getTitle()) > $this->advgrid_title_truncation) {
				$advGridViewTitle = mb_substr($event->getTitle(),0,($this->advgrid_title_truncation-3)).'...';
      }else{
				$advGridViewTitle = $event->getTitle();
      }
			if(strlen($event->getTitle()) > $this->pinboard_title_truncation) {
				$pinboardViewTitle = mb_substr($event->getTitle(),0,($this->pinboard_title_truncation-3)).'...';
      }else{
				$pinboardViewTitle = $event->getTitle();
      }
			if(strlen($event->getTitle()) > $this->masonry_title_truncation) {
				$masonryViewTitle = mb_substr($event->getTitle(),0,($this->masonry_title_truncation - 3)).'...';
      }else{
				$masonryViewTitle = $event->getTitle();
      }
    ?> 
    <?php $location = '';?>
    <?php $host = Engine_Api::_()->getItem('sesevent_host', $event->host);
          if($host->type == 'site' || $host == null) {
            $host = Engine_Api::_()->getItem('user', $host->user_id);
          }
    ?>
    <?php if(isset($this->locationActive) && $event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)):?>
		<?php $locationText = $this->translate('Location');?>
    <?php $locationvalue = $event->location;?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
    <?php $location = "<div class=\"sesevent_list_stats sesevent_list_location\">
												<span class=\"widthfull\">
													<i class=\"fas fa-map-marker-alt sesbasic_text_light\" title=\"$locationText\"></i>
													<span title=\"$locationvalue\"><a href='".$this->url(array('resource_id' => $event->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true)."' class=\"openSmoothbox\">$locationvalue</a></span>
												</span>
											</div>"; 
    ?>
    <?php } else { ?>
    <?php $location = "<div class=\"sesevent_list_stats sesevent_list_location\">
												<span class=\"widthfull\">
													<i class=\"fas fa-map-marker-alt sesbasic_text_light\" title=\"$locationText\"></i>
													<span title=\"$locationvalue\">$locationvalue</span>
												</span>
											</div>"; 
    ?>
    <?php } ?>
    <?php endif;?>
    <?php $labels = '';?>
    <?php $statstics = '';?>
    <?php $showCategory = '';?>
    <?php $showDescription = '';?>
    <?php $like_count = $event->like_count;?>
    <?php $favourite_count = $event->favourite_count;?>
    <?php 
      $shareOptionsListView = '';
      $shareOptionsGridView = '';
      $shareOptionsPinView = '';
      $shareOptionsmasonryView = '';
    
    
    ?>
    <?php 
		      $eventTitle = '';
          $eventPinboardTitle = '';
					if(isset($this->titleActive)){
					$eventListTitle =	"<div class=\"sesevent_list_title\">
													".$this->htmlLink($event->getHref(), $listViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid()))."
												</div>";
					$eventGridTitle =	"<div class=\"sesevent_list_title\">
													".$this->htmlLink($event->getHref(), $gridViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid()))."
												</div>";
          $eventMasonryTitleShow =	"<div class=\"sesevent_grid_in_title_show sesevent_animation\">
													".$this->htmlLink($event->getHref(), $masonryViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid()))."
												</div>";              
					$eventMasonryTitle =	"<div class=\"sesevent_list_title\">
													".$this->htmlLink($event->getHref(), $masonryViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid()))."
												</div>";
          $verifiedlabelAdvGrid = '';
          if(isset($this->verifiedLabelActive) && $event->verified == 1) {
            $verifiedlabelAdvGrid = "<i class=\"sesevent_verified_sign fa fa-check-circle\"></i>";
          }
          $eventAdvGridTitle =	"<div class=\"sesbasic_item_grid_title\">
													".$this->htmlLink($event->getHref(), $advGridViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid())).$verifiedlabelAdvGrid."
												</div>";
          $eventPinboardTitle = "<div class=\"sesbasic_pinboard_list_item_title\">
                                  ".$this->htmlLink($event->getHref(), $pinboardViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid()))."
                                </div>";
					}
        $eventStartEndDate = '';
        if(isset($this->startenddateActive)){
          $eventStartEndDate = "<div class='sesevent_list_stats sesevent_list_time'>
                                <span class='widthfull'>
                                  <i class='far fa-calendar-alt sesbasic_text_light' title='".$this->translate('Start & End Time')."'></i>
                                   ".$this->eventStartEndDates($event)."
                                </span>
                              </div>";	
        }
      if(isset($this->joinedcountActive)){
      	 $guestCountStats = $event->joinedmember ? $event->joinedmember : 0;
     		 $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
      	 $eventStartEndDate .=	"<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
      }
			$comment_count = $event->comment_count;
			$view_count = $event->view_count; 
			$hoverComments = $this->translate(array('%s comment', '%s comments', $comment_count), $this->locale()->toNumber($comment_count));
			$hoverLikes = $this->translate(array('%s like', '%s likes', $like_count), $this->locale()->toNumber($like_count));
			$hoverFavourites = $this->translate(array('%s favourite', '%s favourites', $favourite_count), $this->locale()->toNumber($favourite_count));
			$hoverViews = $this->translate(array('%s view', '%s views', $view_count), $this->locale()->toNumber($view_count));
			if(isset($this->likeActive) && isset($event->like_count))
				$statstics .= "<span title=\"$hoverLikes\"><i class=\"fa fa-thumbs-up sesbasic_text_light\"></i>$like_count</span>";
			if(isset($this->commentActive) && isset($comment_count))
				$statstics .= "<span title=\"$hoverComments\"><i class=\"fa fa-comment sesbasic_text_light\"></i>$comment_count</span>";
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($favourite_count))
				$statstics .= "<span title=\"$hoverFavourites\"><i class=\"fa fa-heart sesbasic_text_light\"></i>$favourite_count</span>";
			if(isset($this->viewActive) && isset($view_count))
				$statstics .= "<span title=\"$hoverViews\"><i class=\"fa fa-eye sesbasic_text_light\"></i>$view_count</span>";

      if(Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating()){
				$statstics .= '<span title="'.$this->translate(array('%s rating', '%s ratings', $event->rating), $this->locale()->toNumber($event->rating)).'"><i class="fa fa-star sesbasic_text_light"></i>'.round($event->rating,1).'/5'. '</span>';
      }
    
    //List View
    if((isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) && $event->is_approved) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
      $shareOptionsListView .= "<div class='sesevent_grid_btns sesbasic_pinboard_list_btns'>";
      if(isset($this->socialSharingActive)) {
        $shareOptionsListView .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_listviewplusicon, 'socialshare_icon_limit' => $this->socialshare_icon_listviewlimit));
      }
      $canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
      if(isset($this->likeButtonActive) && $canComment){
        $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
        $likeClass = ($LikeStatus) ? ' button_active' : '' ;
        $shareOptionsListView .= "<a href='javascript:;' data-url=\"$event->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $event->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$event->like_count</span></a>";
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)  ){ 
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
        $favClass = ($favStatus)  ? 'button_active' : '';
        $shareOptionsListView .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $event->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$event->event_id\"><i class='fa fa-heart'></i><span>$event->favourite_count</span></a>";
      }
      if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
        $shareOptionsListView .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
			}
      if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') || $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')) {
        $shareOptionsListView .= '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a><div class="sesbasic_option_box">';
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') ){
          $shareOptionsListView  .='<a href="'.$this->url(array('event_id' => $event->custom_url,'action'=>'edit'), 'sesevent_dashboard', true).'"  title="'.$this->translate("Edit Event").'"><i class="fa fa-edit"></i>'.$this->translate("Edit Event").'</a>';
        }    
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
          $shareOptionsListView .= '<a onclick="opensmoothboxurl('."'".$this->url(array('event_id' => $event->event_id,'action'=>'delete'), 'sesevent_specific', true)."'".');return false;"	 href="javascript:;" title="'.$this->translate("Delete Event").'"><i class="fa fa-trash"></i>'.$this->translate("Delete Event").'</a>';
        }
         $shareOptionsListView .= "</div>";
      }
      $shareOptionsListView .= "</div>";
    }
    
    //Grid View
    if((isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) && $event->is_approved) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
      $shareOptionsGridView .= "<div class='sesevent_grid_btns sesbasic_pinboard_list_btns'>";
      if(isset($this->socialSharingActive)) {
        $shareOptionsGridView .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_gridviewplusicon, 'socialshare_icon_limit' => $this->socialshare_icon_gridviewlimit));
      }
      $canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
      if(isset($this->likeButtonActive) && $canComment){
        $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
        $likeClass = ($LikeStatus) ? ' button_active' : '' ;
        $shareOptionsGridView .= "<a href='javascript:;' data-url=\"$event->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $event->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$event->like_count</span></a>";
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)  ){ 
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
        $favClass = ($favStatus)  ? 'button_active' : '';
        $shareOptionsGridView .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $event->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$event->event_id\"><i class='fa fa-heart'></i><span>$event->favourite_count</span></a>";
      }
      if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
        $shareOptionsGridView .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
			}
      if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') || $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')) {
        $shareOptionsGridView .= '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a><div class="sesbasic_option_box">';
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') ){
          $shareOptionsGridView  .='<a href="'.$this->url(array('event_id' => $event->custom_url,'action'=>'edit'), 'sesevent_dashboard', true).'"  title="'.$this->translate("Edit Event").'"><i class="fa fa-edit"></i>'.$this->translate("Edit Event").'</a>';
        }    
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
          $shareOptionsGridView .= '<a onclick="opensmoothboxurl('."'".$this->url(array('event_id' => $event->event_id,'action'=>'delete'), 'sesevent_specific', true)."'".');return false;"	 href="javascript:;" title="'.$this->translate("Delete Event").'"><i class="fa fa-trash"></i>'.$this->translate("Delete Event").'</a>';
        }
         $shareOptionsGridView .= "</div>";
      }
      $shareOptionsGridView .= "</div>";
    }
    
    //Pinboard View
    if((isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) && $event->is_approved) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
      $shareOptionsPinView .= "<div class='sesevent_grid_btns sesbasic_pinboard_list_btns'>";
      if(isset($this->socialSharingActive)) {
        $shareOptionsPinView .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_pinviewplusicon, 'socialshare_icon_limit' => $this->socialshare_icon_pinviewlimit));
      }
      $canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
      if(isset($this->likeButtonActive) && $canComment){
        $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
        $likeClass = ($LikeStatus) ? ' button_active' : '' ;
        $shareOptionsPinView .= "<a href='javascript:;' data-url=\"$event->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $event->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$event->like_count</span></a>";
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)  ){ 
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
        $favClass = ($favStatus)  ? 'button_active' : '';
        $shareOptionsPinView .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $event->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$event->event_id\"><i class='fa fa-heart'></i><span>$event->favourite_count</span></a>";
      }
      if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
        $shareOptionsPinView .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
			}
      if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') || $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')) {
        $shareOptionsPinView .= '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a><div class="sesbasic_option_box">';
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') ){
          $shareOptionsPinView  .='<a href="'.$this->url(array('event_id' => $event->custom_url,'action'=>'edit'), 'sesevent_dashboard', true).'"  title="'.$this->translate("Edit Event").'"><i class="fa fa-edit"></i>'.$this->translate("Edit Event").'</a>';
        }    
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
          $shareOptionsPinView .= '<a onclick="opensmoothboxurl('."'".$this->url(array('event_id' => $event->event_id,'action'=>'delete'), 'sesevent_specific', true)."'".');return false;"	 href="javascript:;" title="'.$this->translate("Delete Event").'"><i class="fa fa-trash"></i>'.$this->translate("Delete Event").'</a>';
        }
         $shareOptionsPinView .= "</div>";
      }
      $shareOptionsPinView .= "</div>";
    }
    
    //Masonry View
    if((isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) && $event->is_approved) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
      $shareOptionsmasonryView .= "<div class='sesevent_grid_btns sesbasic_pinboard_list_btns'>";
      if(isset($this->socialSharingActive)) {
        $shareOptionsmasonryView .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_pinviewplusicon, 'socialshare_icon_limit' => $this->socialshare_icon_pinviewlimit));
      }
      $canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
      if(isset($this->likeButtonActive) && $canComment){
        $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
        $likeClass = ($LikeStatus) ? ' button_active' : '' ;
        $shareOptionsmasonryView .= "<a href='javascript:;' data-url=\"$event->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $event->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$event->like_count</span></a>";
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)  ){ 
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
        $favClass = ($favStatus)  ? 'button_active' : '';
        $shareOptionsmasonryView .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $event->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$event->event_id\"><i class='fa fa-heart'></i><span>$event->favourite_count</span></a>";
      }
      if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
        $shareOptionsmasonryView .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
			}
      if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') || $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')) {
        $shareOptionsmasonryView .= '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a><div class="sesbasic_option_box">';
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') ){
          $shareOptionsmasonryView  .='<a href="'.$this->url(array('event_id' => $event->custom_url,'action'=>'edit'), 'sesevent_dashboard', true).'"  title="'.$this->translate("Edit Event").'"><i class="fa fa-edit"></i>'.$this->translate("Edit Event").'</a>';
        }    
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
          $shareOptionsmasonryView .= '<a onclick="opensmoothboxurl('."'".$this->url(array('event_id' => $event->event_id,'action'=>'delete'), 'sesevent_specific', true)."'".');return false;"	 href="javascript:;" title="'.$this->translate("Delete Event").'"><i class="fa fa-trash"></i>'.$this->translate("Delete Event").'</a>';
        }
         $shareOptionsmasonryView .= "</div>";
      }
      $shareOptionsmasonryView .= "</div>";
    }
    
    $shareoptionsAdv = '';
		if((isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) && $event->is_approved) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
      $shareoptionsAdv .= "<div class='sesbasic_item_grid_btns sesevent_grid_btns'>";
      if(isset($this->socialSharingActive)) {
        $shareoptionsAdv .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_advgridviewplusicon, 'socialshare_icon_limit' => $this->socialshare_icon_advgridviewlimit));
      }
      $canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
      if(isset($this->likeButtonActive) && $canComment){
        $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
        $likeClass = ($LikeStatus) ? ' button_active' : '' ;
        $shareoptionsAdv .= "<a href='javascript:;' data-url=\"$event->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $event->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$event->like_count</span></a>";
      }
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)  ){ 
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
        $favClass = ($favStatus)  ? 'button_active' : '';
        $shareoptionsAdv .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $event->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$event->event_id\"><i class='fa fa-heart'></i><span>$event->favourite_count</span></a>";
      }
      if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
        $shareoptionsAdv .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
			}
      if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') || $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
        $shareoptionsAdv .= '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a><div class="sesbasic_option_box">';
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'edit') ){
          $shareoptionsAdv  .='<a href="'.$this->url(array('event_id' => $event->custom_url,'action'=>'edit'), 'sesevent_dashboard', true).'"  title="'.$this->translate("Edit Event").'"><i class="fa fa-edit"></i>'.$this->translate("Edit Event").'</a>';
        }    
        if($event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){
          $shareoptionsAdv .= '<a onclick="opensmoothboxurl('."'".$this->url(array('event_id' => $event->event_id,'action'=>'delete'), 'sesevent_specific', true)."'".');return false;"	 href="javascript:;" title="'.$this->translate("Delete Event").'"><i class="fa fa-trash"></i>'.$this->translate("Delete Event").'</a>';
        }
         $shareoptionsAdv .= "</div>";
      }
      $shareoptionsAdv .= "</div>";
    }
    
    ?>
    <?php 
    //ratings
    $ratings ='';
    if(Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating()){
			$ratings .= '
<span class="sesevent_list_grid_rating" title="'.$this->translate(array('%s rating', '%s ratings', $event->rating), $this->locale()->toNumber($event->rating)).'">';?>
                        <?php if( $event->rating > 0 ): 
                          	 for( $x=1; $x<= $event->rating; $x++ ): 
                          $ratings .= '<span class="sesbasic_rating_star_small fa fa-star"></span>';
                           endfor; 
                           if( (round($event->rating) - $event->rating) > 0): 
                           $ratings.= '<span class="sesbasic_rating_star_small fa fa-star-half"></span>';
                           endif; 
                         endif;  
                     $ratings .= '</span>';
    }
    
    // Show Category
    if(isset($this->categoryActive)){
      if($event->category_id != '' && intval($event->category_id) && !is_null($event->category_id)){
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
        $categoryUrl = $categoryItem->getHref();
        $categoryName = $this->translate($categoryItem->category_name);
        if($categoryItem){
          $showCategory .= "<div class=\"sesevent_list_stats\">
            <span class=\"widthfull\">
              <i class=\"fa fa-folder-open sesbasic_text_light\"></i> 
              <span><a href=\"$categoryUrl\">$categoryName</a>";
              $subcategory = Engine_Api::_()->getItem('sesevent_category',$event->subcat_id);
              if($subcategory && $event->subcat_id != 0){
                $subCategoryUrl = $subcategory->getHref();
                $subCategoryName = $subcategory->category_name;
                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
              }
              $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$event->subsubcat_id);
              if($subsubcategory && $event->subsubcat_id != 0){
                $subsubCategoryUrl = $subsubcategory->getHref();
                $subsubCategoryName = $subsubcategory->category_name;
                 $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
              }
            	$showCategory .= "<span></span></div>";
        }
      }
    }
    // Show Label
    if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive) || isset($this->verifiedLabel)) {
      $labels .= "<p class=\"sesevent_labels\">";
      if(isset($this->featuredLabelActive) && $event->featured == 1) {
				$labels .= "<span class=\"sesevent_label_featured\">FEATURED</span>";
      }
      if(isset($this->sponsoredLabelActive) && $event->sponsored == 1) {
				$labels .= "<span class=\"sesevent_label_sponsored\">SPONSORED</span>";
      }
      if(isset($this->verifiedLabelActive) && $event->verified == 1) {
				$labels .= "<div class=\"sesevent_verified_label\" title=\"VERIFIED\"><i class=\"fa fa-check\"></i></div>";
      }
      
      $labels .= "</p>";
    }
    $advLabels = '';
    if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive) || isset($this->verifiedLabel)) {
      $advLabels .= "<p class=\"sesbasic_item_grid_labels\">";
      if(isset($this->featuredLabelActive) && $event->featured == 1) {
				$advLabels .= "<span class=\"sesevent_label_featured\">FEATURED</span>";
      }
      if(isset($this->sponsoredLabelActive) && $event->sponsored == 1) {
				$advLabels .= "<span class=\"sesevent_label_sponsored\">SPONSORED</span>";
      }
      $advLabels .= "</p>";
    }
      $imageURL = $event->getPhotoUrl('thumb.main');
      if(strpos($imageURL,'http://') === FALSE && strpos($imageURL,'https://') === FALSE) {
        if(!empty($event->photo_id))
					$imageGetSizeURL = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . substr($imageURL, 0, strpos($imageURL, "?"));
        else
        	$imageGetSizeURL =$imageURL;
      }
      else
      $imageGetSizeURL =$imageURL;
			
			$imageHeightWidthData = file_exists(imageGetSizeURL) ? getimagesize($imageGetSizeURL) : array(); 
			
      if(strpos($imageURL,'nophoto_event_thumb_profile.png') === FALSE) {
        $width = isset($imageHeightWidthData[0]) ? $imageHeightWidthData[0] : rand(200,300);
        $height = isset($imageHeightWidthData[1]) ? $imageHeightWidthData[1] : rand(300,400);
      }
      else {
        $width = rand(200,300);
        $height = rand(300,400);
      }
      
      if($width >= 500)
      $imageURL = $event->getPhotoUrl('thumb.main');?>
    <?php $srcImgae = $this->layout()->staticBaseUrl.'application/modules/Sesbasic/externals/images/blank-img.gif';?>
    <?php $masonryHeight =  is_numeric($this->masonry_height) ? $this->masonry_height.'px' : $this->masonry_height ?>
    <?php $masonry = "<li class=\"sesevent_list_flex sesevent_grid_in sesbasic_list_photo_grid sesevent_list_grid sesbasic_bxs sesevent_grid_btns_wrap sesae-i-over\" data-w=\"$width\" data-h=\"$height\">
      <a href=".$event->getHref()." class=\"sesevent_list_flex_img\"> 
				<img data-src=\"$imageURL\" src=\"$srcImgae\" />  
      </a>";?>
		<?php if(!$event->is_approved){ 
     $masonry .=	 "<span class='sesevent_unapproved_label'>".$this->translate("Unapproved")."</span>";
     } ?>
   <?php if(!$event->draft){ 
    	$masonry .=	 "<span class='sesevent_unpublished_label'>".$this->translate("Not Published")."</span>";  
    }
	$masonry .=	"
			$shareOptionsmasonryView
      $labels
      $eventMasonryTitleShow
      <div class=\"sesevent_list_info sesbasic_clearfix\">
        ".$eventMasonryTitle."";?>
      <?php
			if(isset($this->byActive)){
      $masonry .=  "<div class=\"sesevent_list_stats\">
        	<span>
          	<i class='fa fa-user sesbasic_text_light' title='".$this->translate('By')."'></i>	
         		".$this->htmlLink($event->getOwner()->getHref(), $event->getOwner()->getTitle(), array('class' => 'thumbs_author'))."
        	</span>
        </div>";
			}
		 ?>
     <?php
			if(isset($this->hostActive) && isset($host)){
      $masonry .=  "<div class=\"sesevent_list_stats\">
        	<span>
          	<i class='fa fa-male sesbasic_text_light' title='".$this->translate('Hosted By')."'></i>	
         		".$this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author'))."
        	</span>
        </div>";
			}
		 ?>
		 <?php
     $masonry .=   $location.$eventStartEndDate
       ."
        $showCategory
        <div class=\"sesevent_list_stats\">
         	".$statstics."
        </div>
      </div>
    </li>"; ?>
    <?php $masonryViewData .= $masonry;?>
    <?php
    	$href = $event->getHref(); 
    	$imageURL = $event->getPhotoUrl('thumb.profile'); 
			$height = is_numeric($this->height) ? $this->height.'px' : $this->height;
			$width = is_numeric($this->width) ? $this->width.'px' : $this->width;
    ?>
    <!-- start list view item -->
    <?php 
        $currentFormattedDate = $this->eventStartDate($event);
        $currentDateString = "{$currentFormattedDate['day']} {$currentFormattedDate['date']} {$currentFormattedDate['month']}";

        $shortLocation = ($event->is_webinar) ? $this->translate('Online Event') : $this->shortLocation($event->location);

        $ageCategory = $event->age_category_from . " - " . $event->age_category_to . " " . $this->translate('jr') . ".";
        $participants = Engine_Api::_()->getDbtable('membership', 'sesevent')->getMembership(array('event_id' => $event->getIdentity(), 'type' => 'attending'))->getTotalItemCount() ." / ". $event->max_participants;
        $prevFormattedDate = $this->eventStartDate($prevEvent);
        $prevDateString = "{$prevFormattedDate['day']} {$prevFormattedDate['date']} {$prevFormattedDate['month']}";
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->getIdentity()));
        if ($favStatus){
            $favoriteIcon = "<i class='fas fa-star'></i>";
        } else {
            $favoriteIcon = "<i class='far fa-star'></i>";
        };
        $favouriteElement = (Engine_Api::_()->user()->getViewer()->getIdentity()) ? "<a class='list-item-favourite' id='list-item-favourite-{$event->getIdentity()}' onclick='favouriteEvent({$event->getIdentity()});'>{$favoriteIcon}</a>" : "";
        switch ($event->nieuwOrLastMinute()){
            case 1:
                $nieuwOrLastMinute = "<span class='nieuw-label'>{$this->translate('Nieuw')}</span>";
                break;
            case 2:
                $nieuwOrLastMinute = "<span class='last_minute-label'>{$this->translate('Last Minute')}</span>";
                break;
            default:
                $nieuwOrLastMinute = "";
        }
      $sameDate = $prevDateString == $currentDateString;
      $listViewData .= 
        ($key > 0 && !$sameDate? '<div class=\'list-item divider\'></div>':"")
        ."<div class='list-item'>
          <div class='list-item-date'>
            <h1>".(!$sameDate? $currentDateString: '')."</h1>
          </div>
            {$favouriteElement}
          <a href='{$event->getHref()}' class='list-item-info'>
              <div class='list-item-info--title--location'>
                  <div class='title-wrapper'>
                      {$nieuwOrLastMinute}
                      <h1>{$event->title}</h1>
                  </div>
                  <div class='list-item-info-location'>
                      {$shortLocation}
                  </div>
                  <div class='list-item-info-description'>
                      <span>{$ageCategory}</span>
                      <span><i class='fas fa-user'></i>{$participants}</span>
                  </div>
              </div>
          </a>
          <div class='list-item--time'>
            <h1>{$currentFormattedDate['time']}</h1>
          </div>
        </div>
        ";
    ?>
  <!-- end end list view item -->

    <?php $pinboardWidth =  is_numeric($this->pinboard_width) ? $this->pinboard_width.'px' : $this->pinboard_width ;
    $hostPinboard = '';
    if(isset($this->hostActive) && $host !== null){
     $hostPinboard .=	 "<div class=\"sesevent_list_stats\">
        	<span>
          	<i class='fa fa-male sesbasic_text_light' title='".$this->translate('Hosted By')."'></i>	
         		".$this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author'))."
        	</span>
        </div>";
		 }

    $pinboard = "<li class=\"sesbasic_bxs sesbasic_pinboard_list_item_wrap new_image_pinboard_".$randonNumber."\" style='width:$pinboardWidth;'>
      <div class=\"sesbasic_pinboard_list_item sesbm\">
			<div class=\"sesbasic_pinboard_list_item_top\">
				<div class=\"sesbasic_pinboard_list_item_thumb\">
					".$this->htmlLink($event->getHref(), $this->itemPhoto($event, 'thumb.profile'))."
				</div>";?>
        <?php if(!$event->is_approved){ 
					 $pinboard .=	 "<span class='sesevent_unapproved_label'>".$this->translate("Unapproved")."</span>";
					 } ?>
       <?php if(!$event->draft){ 
      $pinboard .=	 "<span class='sesevent_unpublished_label'>".$this->translate("Not Published")."</span>";  
      }
			$pinboard .= "
				$shareOptionsPinView
				$labels                   
			</div>
			<div class=\"sesbasic_pinboard_list_item_cont sesbasic_clearfix\">
				$eventPinboardTitle
        $hostPinboard
				$location
				$eventStartEndDate
				$showCategory";
			?>
      <?php
				if(isset($this->pinboarddescriptionActive)){
					$pinboard .= "<div class=\"sesbasic_pinboard_list_item_des\" style=\"display:inline-block;\">";
						$pinboard .=	 $this->string()->truncate($this->string()->stripTags($event->description), $this->pinboard_description_truncation);
						$pinboard  .= "</div>";
				}
			?>
      <?php $pinboard .= "</div>"; ?>
      <?php $pinboard .=	"<div class=\"sesbasic_pinboard_list_item_btm sesbm sesbasic_clearfix\">";?>
	 <?php
   if(isset($this->byActive) || $statstics != ''){
$pinboard .= "<div class=\"sesbasic_pinboard_list_item_poster sesbasic_text_light sesbasic_clearfix \">";
	 if(isset($this->byActive)){
	   $pinboard .= "<div class=\"sesbasic_pinboard_list_item_poster_thumb\">
	      ".$this->htmlLink($event->getOwner()->getParent(), $this->itemPhoto($event->getOwner()->getParent(), 'thumb.icon'))."                     
	    </div>";
    } ?>
    <?php	
	  $pinboard .= "<div class=\"sesbasic_pinboard_list_item_poster_info\"> ";
      if(isset($this->byActive)){
	    $pinboard .= "<span class=\"sesbasic_pinboard_list_item_poster_info_title\">".$this->htmlLink($event->getOwner()->getHref(),$event->getOwner()->getTitle() )."</span>";
        }
      $pinboard .= "<span class=\"sesbasic_pinboard_list_item_poster_info_stats sesbasic_text_light\">
						".$statstics."
				</span>
	    </div>
    </div>";
	 } ?>
  <?php
	if(isset($this->commentpinboardActive) && $event->is_approved){
	$pinboard .=  "<div class=\"sesbasic_pinboard_list_comments sesbasic_clearfix\">
			".(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment') ? $this->action('list', 'comment', 'sesadvancedcomment', array('type' => $event->getType(), 'id' => $event->getIdentity(),'page'=>'')) : $this->action('list', 'comment', 'sesbasic', array('item_type' => $event->getType(), 'item_id' => $event->getIdentity(),'widget_identity'=>$randonNumber)))."
    </div>";
	}	
 ?>
  <?php 
	$pinboard .= "</div>
      </div>
    </li>"; ?>
    <?php $pinViewData .= $pinboard;?>
    <?php 
		$showDescription = '';
    if(isset($this->griddescriptionActive)){
      $showDescription .= "<div class=\"sesevent_list_des\">";
      $showDescription .=	 $this->string()->truncate($this->string()->stripTags($event->description), $this->grid_description_truncation);
      $showDescription .= "</div>";
    }
    
		// Category Only for grid view
		$showgrid1Category ='';
    $colorCategory = '';
    if(isset($this->categoryActive)){
     if($event->category_id != '' && intval($event->category_id) && !is_null($event->category_id)){
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
        $categoryUrl = $categoryItem->getHref();
        $categoryName = $this->translate($categoryItem->category_name);
          if($categoryItem){
            $colorCategory = (!empty($categoryItem->color)) ? '#'.$categoryItem->color : '#990066';
            $showgrid1Category .= "<span> 
                <a href=\"$categoryUrl\">$categoryName</a>";
                $subcategory = Engine_Api::_()->getItem('sesevent_category',$event->subcat_id);
                if($subcategory && $event->subcat_id){
                  $subCategoryUrl = $subcategory->getHref();
                  $subCategoryName = $subcategory->category_name;
                  $showgrid1Category .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
                }
                $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$event->subsubcat_id);
                if($subsubcategory && $event->subsubcat_id){
                  $subsubCategoryUrl = $subsubcategory->getHref();
                  $subsubCategoryName = $subsubcategory->category_name;
                  $showgrid1Category .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
                }
               $showgrid1Category .="<style type='text/css'>.sesevent_grid_bubble_$categoryItem->category_id > span:after{border-top-color:$colorCategory ;}</style>";
              $showgrid1Category .= "</span>";
              
          }
       }
    }
		$href = $event->getHref();
    $imageURL = $event->getPhotoUrl('thumb.profile');
    ?>
    <?php $photoWidth =  is_numeric($this->photo_width) ? $this->photo_width.'px' : $this->photo_width ?>
    <?php $photoHeight =  is_numeric($this->photo_height) ? $this->photo_height.'px' : $this->photo_height ?>
    <?php $infoHeight =  is_numeric($this->info_height) ? $this->info_height.'px' : $this->info_height ?>
    <?php
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
			if(isset($this->buyActive)){
			$params['event_id'] = $event->event_id;
			$params['checkEndDateTime'] = date('Y-m-d H:i:s');
			$ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
			if(count($ticket))
				$buyTicket = '<a class="sesbasic_link_btn" href="'.$this->url(array('event_id' => $event->custom_url), 'sesevent_ticket', true).'">'.$this->translate("Book Now").'</a>';
			 else
				$buyTicket = '';
			}else
				$buyTicket = '';
		}
		?>
    <?php $advgridWidth =  is_numeric($this->advgrid_width) ? $this->advgrid_width.'px' : $this->advgrid_width; ?>
    <?php $advgridHeight =  is_numeric($this->advgrid_height) ? $this->advgrid_height.'px' : $this->advgrid_height  ;
    $stats = '<div class="sesevent_list_stats">';
  
	if(isset($this->commentActive)){
	$stats .= '<span title="'.$this->translate(array('%s comment', '%s comments', $event->comment_count), $this->locale()->toNumber($event->comment_count)).'"><i class="fa fa-comment sesbasic_text_light"></i>'.$event->comment_count.'</span>';
	}
	if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive)){
	$stats .= '<span title="'.$this->translate(array('%s favourite', '%s favourites', $event->favourite_count), $this->locale()->toNumber($event->favourite_count)).'"><i class="fa fa-heart sesbasic_text_light"></i>'. $event->favourite_count.'</span>';
	}
	if(isset($this->viewActive)){
	$stats .= '<span title="'. $this->translate(array('%s view', '%s views', $event->view_count), $this->locale()->toNumber($event->view_count)).'"><i class="fa fa-eye sesbasic_text_light"></i>'.$event->view_count.'</span>';
	}
	if(isset($this->likeActive)){
	 $stats .= '<span title="'.$this->translate(array('%s like', '%s likes', $event->like_count), $this->locale()->toNumber($event->like_count)).'"><i class="fa fa-thumbs-up sesbasic_text_light"></i>'.$event->like_count.'</span> ';
	 }
   if(isset($this->ratingActive)){
    if(Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating()){
			$stats .= '<span title="'.$this->translate(array('%s rating', '%s ratings', $event->rating), $this->locale()->toNumber($event->rating)).'"><i class="fa fa-star sesbasic_text_light"></i>'.round($event->rating,1).'/5'. '</span>';
    }
   }
	
   $stats .= '</div>';
   
    $advGrid = "<li class='sesbasic_item_grid sesbasic_clearfix sesbasic_bxs sesbasic_item_grid_btns_wrap sesbm' style='width:$advgridWidth;height:$advgridHeight'>
    <div class='sesbasic_item_grid_thumb floatL'>";?>    
    <?php
		$advGrid .=
      '<a href="'.$event->getHref().'" class="sesbasic_item_grid_thumb_img floatL">
        <span class="floatL" style="background-image:url('.$event->getPhotoUrl().');"></span>
        <div class="sesbasic_item_grid_thumb_overlay"></div>
      </a>';
			?>
      
     <?php if(!$event->draft){ 
      $advGrid .=	 "<span class='sesevent_unpublished_label'>".$this->translate("Not Published")."</span>";
      }
    	$advGrid .=  $advLabels.
      $shareoptionsAdv.
			$eventAdvGridTitle.'
      <div class="sesbasic_item_grid_date">
        <span class="day">'.date('d',strtotime($event->starttime)).'</span>
        <span class="month">'.date('M',strtotime($event->starttime)).'</span>
        <span class="year">'. date('Y',strtotime($event->starttime)).'</span>
      </div>
      <div class="sesbasic_item_grid_owner">
        <a href="'.$host->getHref().'">
          <img src="'.$host->getPhotoUrl("thumb.icon").'"  class="thumb_icon item_photo_user thumb_icon"></a>
      </div>
    </div>
    <div class="sesbasic_item_grid_info  sesbasic_clearfix">';?>    
      <?php if(isset($this->byActive)){ ?>
        <?php $owner = $event->getOwner(); ?>
   			<?php $advGrid .=' <div class="sesevent_list_stats">
          <span>
          <i class="fa fa-user sesbasic_text_light" title="'.$this->translate('By:').'"></i>
          '.$this->htmlLink($owner->getHref(),$owner->getTitle()).'</span>
        </div>';
       } ?>
      <?php if(isset($this->hostActive)){ ?>
	     <?php
			 	$advGrid .= 
				'<div class="sesevent_list_stats">
	        <span><i class="fa fa-male sesbasic_text_light" title="'.$this->translate('Hosted By').'"></i>'.$this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author')).'</span>
        </div>';
       }
				$advGrid .= $location.$eventStartEndDate.$showCategory;
        $advGrid .= $stats
			  ?>
      <?php if(isset($buyTicket) && $buyTicket != ''){ 
      $advGrid .=  '<div class="sesbasic_item_grid_info_btns clear">
           '.$buyTicket.'
        </div>';?>
      <?php } ?>
   <?php
	 $advGrid .='
	  </div>
  </li>'; 
  $advgridViewData .= $advGrid;
  $category_id =  !empty($categoryItem->category_id) ? $categoryItem->category_id : 0;
	?>    
    <?php $grid = "<li style='width:$photoWidth;' class='sesevent_grid1 sesbasic_bxs sesbm'>
			<div class='sesevent_list_thumb sesevent_grid_btns_wrap' style='height:$photoHeight;'>
				<a href='".$href."' class='sesevent_list_thumb_img'>
					<span style='background-image:url(".$imageURL.");'></span>
        </a>"; ?>
        <?php if(!$event->is_approved){ 
					 $grid .=	 "<span class='sesevent_unapproved_label'>".$this->translate("Unapproved")."</span>";
					 } ?>
       <?php if(!$event->draft){ 
       	$grid .=	 "<span class='sesevent_unpublished_label'>".$this->translate("Not Published")."</span>"; 
       }
			$grid .= "
        $labels
        $shareOptionsGridView
      </div>
      <div class='sesevent_list_info' style='height:$infoHeight;'>
      	<div class='sesevent_grid_bubble sesevent_grid_bubble_$category_id sesbasic_clearfix' style='background-color:$colorCategory ;'>
					$showgrid1Category
          $buyTicket  
				</div>
        ".$eventGridTitle.""; ?>
       <?php
			 if(isset($this->byActive)){
       $grid .=  "<div class='sesevent_list_host sesbasic_clearfix'>
										<div class='sesevent_list_host_img'>
											".$this->htmlLink($event->getOwner()->getParent(), $this->itemPhoto($event->getOwner()->getParent(), 'thumb.icon'))."               
										</div>
										<div class='sesevent_list_host_info'>
											<span class='sesevent_list_host_title'>".$this->htmlLink($event->getOwner()->getHref(),$event->getOwner()->getTitle() )."</span>
										</div>
									</div>";
			 }
			?>
       <?php
			if(isset($this->hostActive)){
      $grid .= "<div class='sesevent_list_host sesbasic_clearfix'>
										<div class='sesevent_list_host_img'>
											".$this->htmlLink($host, $this->itemPhoto($host, 'thumb.icon'))."               
										</div>
										<div class='sesevent_list_host_info'>
											<span class='sesevent_list_host_title'>".$this->htmlLink($host->getHref(),$host->getTitle() )."</span>
										</div>
									</div>";
			}
		 ?>
       <?php
       $grid .= $location.$eventStartEndDate
        ."
      	<div class='sesevent_list_stats'>$statstics </div>
        $showDescription          
    	</div>
      <div class='sesevent_list_footer'><a href='".$href."' class='sesevent_animation' style='background-color:$colorCategory'>".$this->translate("View Details")."</a></div>
    </li>";?>
    <?php $gridViewData .= $grid;?>
    <?php if($event->lat):?>
      <?php 
			$canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
      $likeButton = '';
			if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($this->likeButtonActive) && $canComment){
     	  $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
        $likeClass = ($LikeStatus) ? ' button_active' : '' ;
				$likeButton = '<a href="javascript:;" data-url="'.$event->getIdentity().'" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event_'. $event->event_id.' sesevent_like_sesevent_event '.$likeClass .'"> <i class="fa fa-thumbs-up"></i><span>'.$event->like_count.'</span></a>';
			}
      $favouriteButton = '';
      if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive)  && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && isset($event->favourite_count)){
        $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
        $favClass = ($favStatus)  ? 'button_active' : '';
      	$favouriteButton = '<a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn  sesevent_favourite_sesevent_event_'. $event->event_id.' sesevent_favourite_sesevent_event '.$favClass .'" data-url="'.$event->getIdentity().'"><i class="fa fa-heart"></i><span>'.$event->favourite_count.'</span></a>';
      }
      $listButton = '';
       if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
				$listButton = '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
			}
		$user = Engine_Api::_()->getItem('user',$event->user_id);
		$owner = $event->getOwner();
		$urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
		if(isset($this->byActive)){
			$owner ='<div class="sesevent_list_stats"><span><i class="fa fa-user sesbasic_text_light"></i>'.$this->translate("by ").$this->htmlLink($owner->getHref(),$owner->getTitle()).'</span></div>';
		}else
			$owner = '';
			if(isset($this->hostActive)){
      	$host ='<div class="sesevent_list_stats"><span><i class="fa fa-male sesbasic_text_light"></i>'.$this->translate("Hosted By ").$this->htmlLink($host->getHref(),$host->getTitle()).'</span></div>';
			}else
      	$host = '';	
	if(isset($this->socialSharingActive)){
	$shareOptions = $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_mapviewplusicon, 'socialshare_icon_limit' => $this->socialshare_icon_mapviewlimit));
	$socialshare = '<div class="sesevent_grid_btns">'.$shareOptions.$likeButton.$favouriteButton.$listButton.'</div>';
	}else
		$socialshare = $likeButton.$favouriteButton.$listButton;
    // Show Label
    $labels = '';
    if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive) || isset($this->verifiedLabel)) {
      $labels .= "<p class=\"sesevent_labels\">";
      if(isset($this->featuredLabelActive) && $event->featured == 1) {
				$labels .= "<span class=\"sesevent_label_featured\">FEATURED</span>";
      }
      if(isset($this->sponsoredLabelActive) && $event->sponsored == 1) {
				$labels .= "<span class=\"sesevent_label_sponsored\">SPONSORED</span>";
      }
      if(isset($this->verifiedLabelActive) && $event->verified == 1) {
				$labels .= "<div class=\"sesevent_verified_label\" title=\"VERIFIED\"><i class=\"fa fa-check\"></i></div>";
      }
      $labels .= "</p>";
    }
     $joinedmember = '';
     if(isset($this->joinedcountActive)){
      	 $guestCountStats = $event->joinedmember ? $event->joinedmember : 0;
     		 $guestCount = $this->translate(array('%s guest', '%s guests', $joinedmember), $this->locale()->toNumber($guestCountStats));
      	 $joinedmember =	"<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
      }
        $locationArray[$counter]['id'] = $event->getIdentity();
				$locationArray[$counter]['owner'] = $owner;
        $locationArray[$counter]['host'] = $host;
        $locationArray[$counter]['location'] = $location;
				$locationArray[$counter]['stats'] = $stats;
        $locationArray[$counter]['joinedmember'] = $joinedmember;
				$locationArray[$counter]['socialshare'] = $socialshare.$labels;
        $locationArray[$counter]['lat'] = $event->lat;
        $locationArray[$counter]['lng'] = $event->lng;
        $locationArray[$counter]['iframe_url'] = '';
        $locationArray[$counter]['image_url'] = $event->getPhotoUrl();
        $locationArray[$counter]['title'] = '<a href="'.$event->getHref().'">'.$event->title.'</a>';
        //$locationArray[$counter]['description'] = $event->description;      
      $counter++;?>
    <?php endif;?>
    <?php $prevEvent = $event ?>
  <?php endforeach; 
  ?>
  <div id="browse-widget_<?php echo $randonNumber;?>" class="sesevent_event_all_events sesevent_browse_listing">
  <?php if(isset($this->show_item_count) && $this->show_item_count){ ?>
   <div class="sesbasic_clearfix sesbm sesevent_search_result" style="display:<?php !$this->is_ajax ? 'block' : 'none'; ?>" id="<?php echo !$this->is_ajax ? 'paginator_count_sesevent' : 'paginator_count_ajax_sesevent' ?>"><span id="total_item_count_sesevent" style="display:inline-block;"></span> <?php echo $this->translate(array('%s event found', '%s events found', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())); ?></div>
   <?php } ?>
    <ul id="sesevent_masonry_view_<?php echo $randonNumber;?>" class="sesevent_list_flex_wrapper clear sesbasic_clearfix" <?php if($this->view_type != 'masonry'):?> style="display:none;"<?php endif;?>>
      <?php echo $masonryViewData;?>
    </ul>
    <ul id="sesevent_list_view_<?php echo $randonNumber;?>" class='sesevents_list_view_wrapper sesbasic_clearfix clear' <?php if($this->view_type != 'list'):?> style="display:none;"<?php endif;?> >
      <?php echo $listViewData;?>
    </ul>
    <ul id="sesevent_pinboard_view_<?php echo $randonNumber;?>" class="sesbasic_pinboard sesbasic_clearfix clear sesbasic_pinboard_<?php echo $randonNumber;?>" style="<?php if($this->view_type != 'pinboard'):?> display:none;<?php endif;?>;">
      <?php echo $pinViewData;?>
    </ul>
    <ul id="sesevent_grid_view_<?php echo $randonNumber;?>" class="sesbasic_clearfix clear" <?php if($this->view_type != 'grid'):?> style="display:none;" <?php endif;?> >
      <?php echo $gridViewData;?>
    </ul>
    <div id="map-data_<?php echo $randonNumber;?>" style="display:none;"><?php echo json_encode($locationArray,JSON_HEX_QUOT | JSON_HEX_TAG); ?></div>
    <ul id="sesevent_map_view_<?php echo $randonNumber;?>" <?php if($this->view_type != 'map'):?> style="display:none;" <?php endif;?> >
      <div id="map-canvas-<?php echo $randonNumber;?>" class="map sesbasic_large_map sesbm sesbasic_bxs"></div>
    </ul>
    <ul id="sesevent_advgrid_view_<?php echo $randonNumber;?>" class="sesbasic_clearfix clear" <?php if($this->view_type != 'advgrid'):?> style="display:none;" <?php endif;?> >
      <?php echo $advgridViewData;?>
    </ul>
    <?php if($this->loadOptionData == 'pagging' && (empty($this->show_limited_data) || $this->show_limited_data  == 'no')){ ?>
      <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>$randonNumber)); ?>
    <?php } ?>
  </div>
<script type="text/javascript">$$('.sesbasic_view_type_<?php echo $randonNumber ?>').setStyle('display', 'block');</script>
<?php elseif( preg_match("/category_id=/", $_SERVER['REQUEST_URI'] )): ?>
  <script type="text/javascript">$$('.sesbasic_view_type_<?php echo $randonNumber ?>').setStyle('display', 'none');</script>
  <div id="browse-widget_<?php echo $randonNumber;?>" class="sesevent_event_all_events sesevent_browse_listing_<?php echo $randonNumber;?>">
  	 <div id="error-message_<?php echo $randonNumber;?>">
  <div class="sesevent_noevent_tip clearfix">
    <img src="<?php echo Engine_Api::_()->sesevent()->getFileUrl(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_event_no_photo', 'application/modules/Sesevent/externals/images/event-icon.png')); ?>" alt="" />
    <span class="sesbasic_text_light">
      <?php echo $this->translate('Nobody has created an event with that criteria.');?>
      <?php if( $this->canCreate ): ?>
	<?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action'=>'create'), 'sesevent_general').'">', '</a>'); ?>
      <?php endif; ?>
    </span>
  </div>   
  </div>
  </div>
 <?php else: ?>
<div id="browse-widget_<?php echo $randonNumber;?>" class="sesevent_event_all_events sesevent_browse_listing sesevent_browse_listing_<?php echo $randonNumber;?>">
	<div id="error-message_<?php echo $randonNumber;?>">
  <div class="sesevent_noevent_tip clearfix">
    <img src="<?php echo Engine_Api::_()->sesevent()->getFileUrl(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_event_no_photo', 'application/modules/Sesevent/externals/images/event-icon.png')); ?>" alt="" />
    <span class="sesbasic_text_light">
    <?php if( $this->filter != "past" ): ?>
      <?php echo $this->translate('Nobody has created an event yet.') ?>
      <?php if( $this->canCreate ): ?>
        <?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action'=>'create'), 'sesevent_general').'">', '</a>'); ?>
      <?php endif; ?>
    <?php else: ?>
      <?php echo $this->translate('There are no past events yet.') ?>
    <?php endif; ?>
    </span>
  </div>
</div>
</div>
  <script type="text/javascript">$$('.sesbasic_view_type_<?php echo $randonNumber ?>').setStyle('display', 'none');</script>
<?php endif; ?>
<?php if($this->loadOptionData != 'pagging' && !$this->is_ajax && (empty($this->show_limited_data) || $this->show_limited_data  == 'no')):?>
  <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
<?php endif;?>
<?php if (empty($this->is_ajax)) : ?>
  <div id="temporary-data-<?php echo $randonNumber?>" style="display:none"></div>
<?php endif;?>
<script type="text/javascript">
  <?php if(!$this->is_ajax):?>
  if("<?php echo $this->view_type ; ?>" == 'masonry'){
    sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
  }
  <?php if($this->loadOptionData == 'auto_load' && (empty($this->show_limited_data) || $this->show_limited_data  == 'no')){ ?>
    window.addEvent('load', function() {
      sesJqueryObject(window).scroll( function() {
				var containerId = '#browse-widget_<?php echo $randonNumber;?>';
				if(typeof sesJqueryObject(containerId).offset() != 'undefined') {
					var hT = sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').offset().top,
					hH = sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').outerHeight(),
					wH = sesJqueryObject(window).height(),
					wS = sesJqueryObject(this).scrollTop();
					if ((wS + 30) > (hT + hH - wH) && sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').css('display') == 'block') {
						document.getElementById('feed_viewmore_link_<?php echo $randonNumber; ?>').click();
					}
				}      
      });
    });
  <?php } ?>
<?php endif; ?>
  <?php if(!$this->is_ajax):?>
	var loadMap_<?php echo $randonNumber;?> = false;
  var activeType_<?php echo $randonNumber ?>;
  function showData_<?php echo $randonNumber; ?>(type) {
    activeType_<?php echo $randonNumber ?> = '';
    if(type == 'grid') {
      sesJqueryObject('#sesevent_grid_view_<?php echo $randonNumber;?>').show();
      sesJqueryObject('#sesevent_list_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_masonry_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_map_view_<?php echo $randonNumber;?>').hide();
			sesJqueryObject('#sesevent_advgrid_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('.list_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.grid_selectView_<?php echo $randonNumber; ?>').addClass('active');
			 sesJqueryObject('.advgrid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.pin_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.masonry_selectView_<?php echo $randonNumber; ?>').removeClass('active');
			sesJqueryObject('.map_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      activeType_<?php echo $randonNumber ?> = 'grid';
    }else if(type == 'advgrid') {
			sesJqueryObject('#sesevent_advgrid_view_<?php echo $randonNumber;?>').show();
			sesJqueryObject('.advgrid_selectView_<?php echo $randonNumber; ?>').addClass('active');
      sesJqueryObject('#sesevent_grid_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_list_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_masonry_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_map_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('.list_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.grid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.pin_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.masonry_selectView_<?php echo $randonNumber; ?>').removeClass('active');
			sesJqueryObject('.map_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      activeType_<?php echo $randonNumber ?> = 'advgrid';
    }else if(type == 'list') {
			sesJqueryObject('#sesevent_advgrid_view_<?php echo $randonNumber;?>').hide();
			sesJqueryObject('.advgrid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('#sesevent_grid_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_list_view_<?php echo $randonNumber;?>').show();
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_masonry_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_map_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('.list_selectView_<?php echo $randonNumber; ?>').addClass('active');
      sesJqueryObject('.grid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.pin_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.masonry_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.map_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      activeType_<?php echo $randonNumber ?> = 'list';
    }else if(type == 'pinboard') {
			sesJqueryObject('#sesevent_advgrid_view_<?php echo $randonNumber;?>').hide();
			sesJqueryObject('.advgrid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('#sesevent_grid_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_list_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber;?>').show();
      sesJqueryObject('#sesevent_masonry_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_map_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('.pin_selectView_<?php echo $randonNumber; ?>').addClass('active');
      sesJqueryObject('.list_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.grid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.masonry_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.map_selectView_<?php echo $randonNumber; ?>').removeClass('active');
			pinboardLayout_<?php echo $randonNumber ?>('',true);
      activeType_<?php echo $randonNumber ?> = 'pinboard';
    }else if(type == 'masonry') {
			sesJqueryObject('#sesevent_advgrid_view_<?php echo $randonNumber;?>').hide();
			sesJqueryObject('.advgrid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('#sesevent_grid_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_list_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_masonry_view_<?php echo $randonNumber;?>').show();
      sesJqueryObject('#sesevent_map_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('.masonry_selectView_<?php echo $randonNumber; ?>').addClass('active');
      sesJqueryObject('.list_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.grid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.pin_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.map_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
      activeType_<?php echo $randonNumber ?> = 'masonry';
    }else if(type == 'map') {
			if(sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber;?>').find('.active').attr('rel') == 'map')
				return;
			sesJqueryObject('#sesevent_advgrid_view_<?php echo $randonNumber;?>').hide();
			sesJqueryObject('.advgrid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('#sesevent_grid_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_list_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_masonry_view_<?php echo $randonNumber;?>').hide();
      sesJqueryObject('#sesevent_map_view_<?php echo $randonNumber;?>').show();
      sesJqueryObject('.pin_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.list_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.grid_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.masonry_selectView_<?php echo $randonNumber; ?>').removeClass('active');
      sesJqueryObject('.map_selectView_<?php echo $randonNumber; ?>').addClass('active');
      var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#map-data_<?php echo $randonNumber;?>').html());
			if(sesJqueryObject('#map-data_<?php echo $randonNumber;?>').hasClass('checked'))
				return;
      if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
				newMapData_<?php echo $randonNumber ?> = mapData;
				for(var i=0; i < mapData.length; i++)
				{
					var isInsert = 1;
					for(var j= 0;j < oldMapData_<?php echo $randonNumber; ?>.length; j++){
							if(oldMapData_<?php echo $randonNumber; ?>[j]['id'] == mapData[i]['id']){
								isInsert = 0;
								break;
							}
					}
					if(isInsert){
						oldMapData_<?php echo $randonNumber; ?>.push(mapData[i]);
					}
				}
				mapFunction_<?php echo $randonNumber?>();
				sesJqueryObject('#map-data_<?php echo $randonNumber;?>').addClass('checked');
      }else{
				if(typeof  map_<?php echo $randonNumber;?> == 'undefined')	{
							sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
							initialize_<?php echo $randonNumber?>();	
				}
			}
      activeType_<?php echo $randonNumber; ?> = 'map';
    }
  }
  //Code for Pinboard View
  var wookmark<?php echo $randonNumber ?>;;
	function pinboardLayout_<?php echo $randonNumber ?>(force,checkEnablePinboard){
     if(!sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
       return;
    }
		if(typeof checkEnablePinboard == 'undefined' && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel') == 'pinboard'){
		 sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber; ?>').removeClass('sesbasic_pinboard_<?php echo $randonNumber; ?>');
		 sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber; ?>').css('height','');
	 		return;
	  }
		sesJqueryObject('.new_image_pinboard_<?php echo $randonNumber; ?>').css('display','none');
		var imgLoad = imagesLoaded('#sesevent_pinboard_view_<?php echo $randonNumber; ?>');
		imgLoad.on('progress',function(instance,image){
			sesJqueryObject(image.img).parent().parent().parent().parent().parent().show();
			sesJqueryObject(image.img).parent().parent().parent().parent().parent().removeClass('new_image_pinboard_<?php echo $randonNumber; ?>');
			imageLoadedAll<?php echo $randonNumber ?>(force,checkEnablePinboard);
		});
  }
  sesJqueryObject('.sesbasic_pinboard_list_comments').bind('DOMSubtreeModified', function(e) {
    // do something after the div content has changed
   pinboardLayout_<?php echo $randonNumber ?>('',true);
  });
  function imageLoadedAll<?php echo $randonNumber ?>(force,checkEnablePinboard){
      sesJqueryObject('#sesevent_pinboard_view_<?php echo $randonNumber; ?>').addClass('sesbasic_pinboard_<?php echo $randonNumber; ?>');
      if (typeof wookmark<?php echo $randonNumber ?> == 'undefined' || typeof force != 'undefined') {

          function getWindowWidth_<?php echo $randonNumber; ?>() {
              return Math.max(document.documentElement.clientWidth, window.innerWidth || 0)
          }
          wookmark<?php echo $randonNumber ?> = new Wookmark(
              '#sesevent_pinboard_view_<?php echo $randonNumber;?>',
              {
                  itemWidth:  <?php echo isset($this->pinboard_width) ? str_replace(array('px','%'),array(''),$this->pinboard_width) : '300'; ?>, // Optional min width of a grid item
      outerOffset: 0, // Optional the distance from grid to parent
      align: 'left',
      flexibleWidth: function () {
              // Return a maximum width depending on the viewport
              return getWindowWidth_<?php echo $randonNumber; ?>() < 1024 ? '100%' : '40%';
          }
      });

      } else {
              wookmark<?php echo $randonNumber ?>.initItems();
              wookmark<?php echo $randonNumber ?>.layout(true);
          }
      }
   sesJqueryObject(window).resize(function(e){
    pinboardLayout_<?php echo $randonNumber ?>('',true);
   });
  <?php if($this->view_type == 'pinboard'):?>
     sesJqueryObject(document).ready(function(e){
      pinboardLayout_<?php echo $randonNumber ?>('force',true);
		});
  <?php endif;?>
	var searchParams<?php echo $randonNumber; ?> ;
  var identity<?php echo $randonNumber; ?>  = '<?php echo $randonNumber; ?>';
  <?php endif;?> 
  var params<?php echo $randonNumber; ?> = '<?php echo json_encode($this->params); ?>';
  var page<?php echo $randonNumber; ?> = '<?php echo $this->page + 1; ?>';
  <?php if(!$this->is_ajax):?>
    var isSearch = false;
    var oldMapData_<?php echo $randonNumber; ?> = [];
  <?php endif;?>
  <?php if($this->loadOptionData != 'pagging') { ?>
      en4.core.runonce.add(function() {
    viewMoreHide_<?php echo $randonNumber; ?>();
    });
    function viewMoreHide_<?php echo $randonNumber; ?>() {
      if ($('view_more_<?php echo $randonNumber; ?>'))
	$('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
    }
    function viewMore_<?php echo $randonNumber; ?> () {
      sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
      sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
			var searchCriteriaSesevent = '';
			if(sesJqueryObject('#sesevent_manage_event_optn').length){
				searchCriteriaSesevent = sesJqueryObject('#sesevent_manage_event_optn').find('.active').attr('data-url');
			}else{
				searchCriteriaSesevent = '';	
			}
			if(typeof requestViewMore_<?php echo $randonNumber; ?>  != "undefined"){
              requestViewMore_<?php echo $randonNumber; ?>.cancel();
          }
              sesJqueryObject.ajax({
					method: 'post',
					url: en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
					data: {
						format: 'html',
						page: page<?php echo $randonNumber; ?>,    
						params : params<?php echo $randonNumber; ?>, 
						is_ajax : 1,
						searchCtr : searchCriteriaSesevent,
						searchParams:searchParams<?php echo $randonNumber; ?> ,
						identity : '<?php echo $randonNumber; ?>',
						height:'<?php echo $this->masonry_height;?>',
						type:activeType_<?php echo $randonNumber ?>,
						identityObject:'<?php echo isset($this->identityObject) ? $this->identityObject : "" ?>'
					},
                success: function(responseTree, responseElements, responseHTML, responseJavaScript) {
								sesJqueryObject('#map-data_<?php echo $randonNumber;?>').removeClass('checked');
								sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').html(responseTree);
								if(sesJqueryObject('#error-message_<?php echo $randonNumber;?>').length > 0) {
									var optionEnable = sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel');
									var optionEnableList = sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?> > a');
									for(i=0;i<optionEnableList.length;i++)
										sesJqueryObject('#sesevent_'+optionEnable+'_view_<?php echo $randonNumber; ?>').hide();
									sesJqueryObject('#tabbed-widget_<?php echo $randonNumber;?>').append('<div id="error-message_<?php echo $randonNumber;?>">'+sesJqueryObject('#error-message_<?php echo $randonNumber;?>').html()+'</div>')
								}
								if(!isSearch){
									if($('loadingimgsesevent-wrapper'))
										sesJqueryObject('#loadingimgsesevent-wrapper').hide();
									if(document.getElementById('sesevent_list_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_list_view_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('sesevent_list_view_<?php echo $randonNumber; ?>').innerHTML + sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_list_view_<?php echo $randonNumber; ?>').html();
									if(	document.getElementById('sesevent_grid_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_grid_view_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('sesevent_grid_view_<?php echo $randonNumber; ?>').innerHTML + sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_grid_view_<?php echo $randonNumber; ?>').html();
									if(	document.getElementById('sesevent_advgrid_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_advgrid_view_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('sesevent_advgrid_view_<?php echo $randonNumber; ?>').innerHTML + sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_advgrid_view_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber; ?>').innerHTML + sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_pinboard_view_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('sesevent_masonry_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_masonry_view_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('sesevent_masonry_view_<?php echo $randonNumber; ?>').innerHTML + sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_masonry_view_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('map-data_<?php echo $randonNumber;?>'))
										document.getElementById('map-data_<?php echo $randonNumber;?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html();
								}
								else{
									if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
										document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').html() ;	
									oldMapData_<?php echo $randonNumber; ?> = [];
									isSearch = false;
								}
								
								if(document.getElementById('map-data_<?php echo $randonNumber;?>') && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel') == 'map') {
								if(document.getElementById('sesevent_map_view_<?php echo $randonNumber;?>'))	
									document.getElementById('sesevent_map_view_<?php echo $randonNumber;?>').style.display = 'block';
									var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
									if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
										newMapData_<?php echo $randonNumber ?> = mapData;
										sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
										mapFunction_<?php echo $randonNumber?>();
									}else{
											if(typeof  map_<?php echo $randonNumber;?> == 'undefined')	{
														sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
														initialize_<?php echo $randonNumber?>();	
											}	
									}
								}else if(document.getElementById('map-data_<?php echo $randonNumber;?>')){
									var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
									sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, mapData);
									sesJqueryObject('#map-data_<?php echo $randonNumber;?>').addClass('read');
								}
								if(sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>'))
										document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>').style.display = 'block';
									pinboardLayout_<?php echo $randonNumber ?>('force','true');
								}
								else if(sesJqueryObject('.masonry_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
								}
								if(document.getElementById('temporary-data-<?php echo $randonNumber?>'))
									document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML = '';
								sesJqueryObject('.sesbasic_view_more_loading_<?php echo $randonNumber;?>').hide();
								sesJqueryObject('#loadingimgsesevent-wrapper').hide();
								viewMoreHide_<?php echo $randonNumber; ?>();
							}
				});
    }
    <?php }else{ ?>
    function paggingNumber<?php echo $randonNumber; ?>(pageNum){
      sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').css('display','block');
			var searchCriteriaSesevent = '';
			if(sesJqueryObject('#sesevent_manage_event_optn').length){
				searchCriteriaSesevent = sesJqueryObject('#sesevent_manage_event_optn').find('.active').attr('data-url');
			}else{
				searchCriteriaSesevent = '';	
			}
          if(typeof requestViewMore_<?php echo $randonNumber; ?>  != "undefined"){
              requestViewMore_<?php echo $randonNumber; ?>.cancel();
          }
              sesJqueryObject.ajax({
				method: 'post',
				url: en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
				data: {
					format: 'html',
					page: pageNum,
					params :params<?php echo $randonNumber; ?> , 
					is_ajax : 1,
					searchCtr : searchCriteriaSesevent,
					searchParams:searchParams<?php echo $randonNumber; ?>,
					identity : <?php echo $randonNumber; ?>,
					type:sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel'),
					height:'<?php echo $this->masonry_height;?>',
					identityObject:'<?php echo isset($this->identityObject) ? $this->identityObject : "" ?>'
				},
				success: function(responseTree, responseElements, responseHTML, responseJavaScript) {
					sesJqueryObject('#map-data_<?php echo $randonNumber;?>').removeClass('checked');
					sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').html(responseTree);
						if(!isSearch){
									if($('loadingimgsesevent-wrapper'))
										sesJqueryObject('#loadingimgsesevent-wrapper').hide();
									if(document.getElementById('sesevent_list_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_list_view_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_list_view_<?php echo $randonNumber; ?>').html();
									if(	document.getElementById('sesevent_grid_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_grid_view_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_grid_view_<?php echo $randonNumber; ?>').html();
									if(	document.getElementById('sesevent_advgrid_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_advgrid_view_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_advgrid_view_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_pinboard_view_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('sesevent_masonry_view_<?php echo $randonNumber; ?>'))
										document.getElementById('sesevent_masonry_view_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#sesevent_masonry_view_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('map-data_<?php echo $randonNumber;?>'))
										document.getElementById('map-data_<?php echo $randonNumber;?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html();
									if(document.getElementById('map-data_<?php echo $randonNumber;?>'))
							if(document.getElementById('ses_pagging_<?php echo $randonNumber;?>'))
							document.getElementById('ses_pagging_<?php echo $randonNumber;?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#ses_pagging_<?php echo $randonNumber; ?>').html();
								}
								else{
									if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
										document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').html() ;	
									oldMapData_<?php echo $randonNumber; ?> = [];
									isSearch = false;
								}
					if(document.getElementById('map-data_<?php echo $randonNumber;?>') && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber;?>').find('.active').attr('rel') == 'map'){
						var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
						if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
							oldMapData_<?php echo $randonNumber; ?> = [];
							newMapData_<?php echo $randonNumber ?> = mapData;
							loadMap_<?php echo $randonNumber ?> = true;
							sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
							mapFunction_<?php echo $randonNumber?>();
						}else{
							sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
							initialize_<?php echo $randonNumber?>();	
						}
					}else{
						oldMapData_<?php echo $randonNumber; ?> = [];	
					}
					if(sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
						if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>'))
							document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>').style.display = 'block';
						pinboardLayout_<?php echo $randonNumber ?>('force','true');
					}else if(sesJqueryObject('.masonry_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
						sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
					}
					if(document.getElementById('temporary-data-<?php echo $randonNumber?>'))
						document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML = '';
					sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').css('display', 'none');
					if(document.getElementById('map-data_<?php echo $randonNumber;?>') && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber;?>').find('.active').attr('rel') == 'map'){
						var mapData = sesJqueryObject.parseJSON(document.getElementById('temporary-data-<?php echo $randonNumber?>').getElementById('map-data_<?php echo $randonNumber;?>').innerHTML);
						if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
							oldMapData_<?php echo $randonNumber; ?> = [];
							newMapData_<?php echo $randonNumber ?> = mapData;
							loadMap_<?php echo $randonNumber ?> = true;
							sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
							mapFunction_<?php echo $randonNumber?>();
						}else{
							sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
							initialize_<?php echo $randonNumber?>();	
						}
					}else{
						oldMapData_<?php echo $randonNumber; ?> = [];	
					}
					if(sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
						if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>'))
							document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>').style.display = 'block';
						pinboardLayout_<?php echo $randonNumber ?>('force','true');
					}else if(sesJqueryObject('.masonry_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
						sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
					}
					if(document.getElementById('temporary-data-<?php echo $randonNumber?>'))
						document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML = '';
					sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').css('display', 'none');
					sesJqueryObject('#loadingimgsesevent-wrapper').hide();
					}
      });
    }
  <?php } ?>
  <?php if(!$this->is_ajax):?>
  var newMapData_<?php echo $randonNumber ?> = [];		 
  function mapFunction_<?php echo $randonNumber?>(){
    if(!map_<?php echo $randonNumber;?> || loadMap_<?php echo $randonNumber;?>){
      initialize_<?php echo $randonNumber?>();
			loadMap_<?php echo $randonNumber;?> = false;
		}
    if(sesJqueryObject('.map_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
      if(!newMapData_<?php echo $randonNumber ?>){
      return false;
			}
      <?php if($this->loadOptionData == 'pagging'){ ?>DeleteMarkers_<?php echo $randonNumber ?>();<?php }?>
      google.maps.event.trigger(map_<?php echo $randonNumber;?>, "resize");
      markerArrayData_<?php echo $randonNumber?> = newMapData_<?php echo $randonNumber ?>;
      if(markerArrayData_<?php echo $randonNumber?>.length)
      	newMarkerLayout_<?php echo $randonNumber?>();
      newMapData_<?php echo $randonNumber ?> = '';
			sesJqueryObject('#map-data_<?php echo $randonNumber;?>').addClass('checked');
    }
  }
	<?php endif;?>
</script>
  <?php if(!$this->is_ajax && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)):?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/richMarker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/marker.js'); ?>
<script type="text/javascript">
  var markers_<?php echo $randonNumber;?>  = [];
  var map_<?php echo $randonNumber;?>;
  if('<?php echo $this->lat; ?>' == '') {
    var latitude_<?php echo $randonNumber;?> = '26.9110600';
    var longitude_<?php echo $randonNumber;?> = '75.7373560';
  }else{
    var latitude_<?php echo $randonNumber;?> = '<?php echo $this->lat; ?>';
    var longitude_<?php echo $randonNumber;?> = '<?php echo $this->lng; ?>';
  }
  function initialize_<?php echo $randonNumber?>() {
    var bounds_<?php echo $randonNumber;?> = new google.maps.LatLngBounds();
    map_<?php echo $randonNumber;?> = new google.maps.Map(document.getElementById('map-canvas-<?php echo $randonNumber;?>'), {
    zoom: 17,
    scrollwheel: true,
    center: new google.maps.LatLng(latitude_<?php echo $randonNumber;?>, longitude_<?php echo $randonNumber;?>),
    });
		oms_<?php echo $randonNumber;?> = new OverlappingMarkerSpiderfier(map_<?php echo $randonNumber;?>,
        {nearbyDistance:40,circleSpiralSwitchover:0 }
				);
  }
  var countMarker_<?php echo $randonNumber;?> = 0;
  function DeleteMarkers_<?php echo $randonNumber ?>(){
    //Loop through all the markers and remove
    for (var i = 0; i < markers_<?php echo $randonNumber;?>.length; i++) {
    markers_<?php echo $randonNumber;?>[i].setMap(null);
    }
    markers_<?php echo $randonNumber;?> = [];
    markerData_<?php echo $randonNumber ?> = [];
    markerArrayData_<?php echo $randonNumber?> = [];
  };
  var markerArrayData_<?php echo $randonNumber?> ;
  var markerData_<?php echo $randonNumber ?> =[];
  var bounds_<?php echo $randonNumber;?> = new google.maps.LatLngBounds();
  function newMarkerLayout_<?php echo $randonNumber?>(dataLenth){
    if(typeof dataLenth != 'undefined') {
      initialize_<?php echo $randonNumber?>();
      markerArrayData_<?php echo $randonNumber?> = sesJqueryObject.parseJSON(dataLenth);
    }
    if(!markerArrayData_<?php echo $randonNumber?>.length){
   	 return;
		}
    DeleteMarkers_<?php echo $randonNumber ?>();
    markerArrayData_<?php echo $randonNumber?> = oldMapData_<?php echo $randonNumber; ?>;
		var bounds = new google.maps.LatLngBounds();
    for(i=0;i<markerArrayData_<?php echo $randonNumber?>.length;i++){
		var images = '<div class="image sesevent_map_thumb_img"><img src="'+markerArrayData_<?php echo $randonNumber?>[i]['image_url']+'"  /></div>';		
		var owner = markerArrayData_<?php echo $randonNumber?>[i]['owner'];
		var host = markerArrayData_<?php echo $randonNumber?>[i]['host'];
		var stats = markerArrayData_<?php echo $randonNumber?>[i]['stats'];
		var location = markerArrayData_<?php echo $randonNumber?>[i]['location'];
		var joinedmember = markerArrayData_<?php echo $randonNumber?>[i]['joinedmember'];
		var socialshare = markerArrayData_<?php echo $randonNumber?>[i]['socialshare'];
		 var marker_html = '<div class="pin public marker_'+countMarker_<?php echo $randonNumber;?>+'" data-lat="'+ markerArrayData_<?php echo $randonNumber?>[i]['lat']+'" data-lng="'+ markerArrayData_<?php echo $randonNumber?>[i]['lng']+'">' +
				'<div class="wrapper">' +
					'<div class="small">' +
						'<img src="'+markerArrayData_<?php echo $randonNumber?>[i]['image_url']+'" style="height:48px;width:48px;" alt="" />' +
					'</div>' +
					'<div class="large"><div class="sesevent_map_thumb sesevent_grid_btns_wrap">' +
						images + socialshare+
						'</div><div class="sesbasic_large_map_content sesevent_large_map_content sesbasic_clearfix">' +
							'<div class="sesbasic_large_map_content_title">'+markerArrayData_<?php echo $randonNumber?>[i]['title']+'</div>' +owner+host+joinedmember+location+stats +
						'</div>' +
						'<a class="icn close" href="javascript:;" title="Close"><i class="fa fa-times"></i></a>' + 
					'</div>' +
				'</div>' +
				'<span class="sesbasic_largemap_pointer"></span>' +
				'</div>';
			  markerData = new RichMarker({
						position: new google.maps.LatLng(markerArrayData_<?php echo $randonNumber?>[i]['lat'], markerArrayData_<?php echo $randonNumber?>[i]['lng']),
						map: map_<?php echo $randonNumber;?>,
						flat: true,
						draggable: false,
						scrollwheel: false,
						id:countMarker_<?php echo $randonNumber;?>,
						anchor: RichMarkerPosition.BOTTOM,
						content: marker_html
				});
				oms_<?php echo $randonNumber;?>.addListener('click', function(marker) {
					var id = marker.markerid;
					previousIndex = sesJqueryObject('.marker_'+ id).parent().parent().css('z-index');
					sesJqueryObject('.marker_'+ id).parent().parent().css('z-index','9999');
						sesJqueryObject('.pin').removeClass('active').css('z-index', 10);
						sesJqueryObject('.marker_'+ id).addClass('active').css('z-index', 200);
						sesJqueryObject('.marker_'+ id+' .large .close').click(function(){
							sesJqueryObject(this).parent().parent().parent().parent().parent().css('z-index',previousIndex);
							sesJqueryObject('.pin').removeClass('active');
							return false;
						});
				});
				markers_<?php echo $randonNumber;?> .push( markerData);
				markerData.setMap(map_<?php echo $randonNumber;?>);
				bounds.extend(markerData.getPosition());
				markerData.markerid = countMarker_<?php echo $randonNumber;?>;
				oms_<?php echo $randonNumber;?>.addMarker(markerData);
				countMarker_<?php echo $randonNumber;?>++;
  }
    map_<?php echo $randonNumber;?>.fitBounds(bounds);

  }
  <?php if($this->view_type == 'map'){?>
          en4.core.runonce.add(function() {
			var mapData = sesJqueryObject.parseJSON(document.getElementById('map-data_<?php echo $randonNumber;?>').innerHTML);
			if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
				newMapData_<?php echo $randonNumber ?> = mapData;
				sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
				mapFunction_<?php echo $randonNumber?>();
				sesJqueryObject('#map-data_<?php echo $randonNumber;?>').addClass('checked')
			}else{
				if(typeof  map_<?php echo $randonNumber;?> == 'undefined')	{
							sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
							initialize_<?php echo $randonNumber?>();	
				}
			}
		});
  <?php }else{ ?>
      en4.core.runonce.add(function() {
	if(document.getElementById('map-data_<?php echo $randonNumber;?>')){
			var mapData = sesJqueryObject.parseJSON(document.getElementById('map-data_<?php echo $randonNumber;?>').innerHTML);
			sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, mapData);
		}
		});
 <?php } ?>
</script> 
  <?php endif;?>
<?php
    if($previousSubject){
       Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->clearSubject(null) : false;
       Engine_Api::_()->core()->setSubject($previousSubject);
    }else{
        Engine_Api::_()->core()->clearSubject(null);
    }
?>
