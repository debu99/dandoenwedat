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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_cover.css');?>
<script type="text/javascript">
sesJqueryObject(document).click(function(event){
	if(event.target.id != 'sesevent_dropdown_btn' && event.target.id != 'a_btn' && event.target.id != 'i_btn'){
		sesJqueryObject('#sesevent_dropdown_btn').find('.sesevent_option_box1').css('display','none');
		sesJqueryObject('#a_btn').removeClass('active');
	}
	if(event.target.id == 'change_cover_txt' || event.target.id == 'cover_change_btn_i' || event.target.id == 'cover_change_btn'){
		if(sesJqueryObject('#sesevent_change_cover_op').hasClass('active'))
			sesJqueryObject('#sesevent_change_cover_op').removeClass('active')
		else
			sesJqueryObject('#sesevent_change_cover_op').addClass('active');

		sesJqueryObject('#sesevent_cover_option_main_id').removeClass('active');

	}else if(event.target.id == 'change_main_txt' || event.target.id == 'change_main_btn' || event.target.id == 'change_main_i'){
		if(sesJqueryObject('#sesevent_cover_option_main_id').hasClass('active'))
			sesJqueryObject('#sesevent_cover_option_main_id').removeClass('active')
		else
			sesJqueryObject('#sesevent_cover_option_main_id').addClass('active');

		sesJqueryObject('#sesevent_change_cover_op').removeClass('active');
	}else{
			sesJqueryObject('#sesevent_change_cover_op').removeClass('active');
			sesJqueryObject('#sesevent_cover_option_main_id').removeClass('active')
	}
	if(event.target.id == 'a_btn'){
			if(sesJqueryObject('#a_btn').hasClass('active')){
				sesJqueryObject('#a_btn').removeClass('active');
				sesJqueryObject('.sesevent_option_box1').css('display','none');
			}
			else{
				sesJqueryObject('#a_btn').addClass('active');
				sesJqueryObject('.sesevent_option_box1').css('display','block');
			}
		}else if(event.target.id == 'i_btn'){
			if(sesJqueryObject('#a_btn').hasClass('active')){
				sesJqueryObject('#a_btn').removeClass('active');
				sesJqueryObject('.sesevent_option_box1').css('display','none');
			}
			else{
				sesJqueryObject('#a_btn').addClass('active');
				sesJqueryObject('.sesevent_option_box1').css('display','block');
			}
	}
});
</script>
<?php
if (isset($this->can_edit)) {
// First, include the Webcam.js JavaScript Library
    $base_url = $this->layout()->staticBaseUrl;
    $this->headScript()->appendFile($base_url . 'application/modules/Sesbasic/externals/scripts/webcam.js');
}
?>
<div class="sesevent_cover_wrapper sesbasic_bxs sesbasic_clearfix <?php echo $this->fullwidth ? 'sesevent_cover_full' : 'sesevent_cover_middle'; ?> <?php if ($this->tab) {?>sesevent_cover_tabs_wrap<?php }?>"  style="height:<?php echo $this->height; ?>px;<?php if ($this->padding) {?>margin-top:<?php echo $this->padding . 'px;';} ?>">
  <div class="sesevent_cover_container" style="height:<?php echo $this->height; ?>px">
    <!--Event Cover Photo-->
    <?php $eventCover = $this->subject->getCoverPhotoUrl();?>
    <span class="sesevent_cover_image" id="cover_art_work_image" style="background-image:url(<?php echo $eventCover; ?>);height:<?php echo $this->height; ?>px"></span>
   <div style="display:none;" id="sesevent-pos-btn" class="sesevent_cover_positions_btns">
      <a id="saveCoverPosition" href="javascript:;" class="sesbasic_button"><?php echo $this->translate("Save"); ?></a>
      <a href="javascript:;" id="cancelCoverPosition" class="sesbasic_button"><?php echo $this->translate("Cancel"); ?></a>
    </div>
    <span class="sesevent_cover_fade"></span>
    <?php if ($this->can_edit): ?>
      <div class="sesevent_cover_change_cover" id="sesevent_change_cover_op">
        <a href="javascript:;" id="cover_change_btn"><i class="fa fa-camera" id="cover_change_btn_i"></i><span id="change_cover_txt"><?php echo $this->translate("Upload Cover Photo"); ?></span></a>
        <div class="sesevent_cover_change_cover_options sesbasic_option_box">
          <i class="sesevent_cover_change_cover_options_arrow"></i>
          <?php if ($this->can_edit) {?>
            <input type="file" id="uploadFilesesevent" name="art_cover" onchange="uploadCoverArt(this);"  style="display:none" />
            <a id="uploadWebCamPhoto" href="javascript:;"><i class="fa fa-camera"></i><?php echo $this->translate("Take Photo"); ?></a>
            <a id="coverChangesesevent" data-src="<?php echo $this->subject->cover_photo; ?>" href="javascript:;"><i class="fa fa-plus"></i>
            <?php echo (isset($this->subject->cover_photo) && $this->subject->cover_photo != 0 && $this->subject->cover_photo != '') ? $this->translate('Change Cover Photo') : $this->translate('Add Cover Photo'); ?></a>
             <a id="coverRemovesesevent" style="display:<?php echo (isset($this->subject->cover_photo) && $this->subject->cover_photo != 0 && $this->subject->cover_photo != '') ? 'block' : 'none'; ?>;" data-src="<?php echo $this->subject->cover_photo; ?>" href="javascript:;"><i class="fa fa-trash"></i><?php echo $this->translate('Remove Cover Photo'); ?></a>
          <?php }?>
        </div>
      </div>
    <?php endif;?>
    <div class="sesevent_cover_inner">
      <div class="sesevent_cover_cont sesbasic_clearfix">
        <div class="sesevent_cover_cont_inner">
          <!--Main Photo-->
        <?php if (in_array('mainPhoto', $this->show_criterias)) {?>
          <div class="sesevent_cover_main_photo">
           <?php
if ($this->photo == 'oPhoto') {
    $user = Engine_Api::_()->getItem('user', $this->subject->user_id);
    echo $this->itemPhoto($user, 'thumb.profile');
} else if ($this->Photo == 'hPhoto') {
    echo $this->host['image'];
} else {?>
                  <img src="<?php echo $this->subject->getPhotoUrl('thumb.normal'); ?>" alt="" class="thumb_profile item_photo_user sesevent_cover_image_main">
        <?php }?>
        <?php if ($this->can_edit && $this->photo == 'mPhoto') {?>
            <div class="sesevent_cover_change_cover" id="sesevent_cover_option_main_id">
            <input type="file" id="uploadFileMainsesevent" name="main_photo_cvr" onchange="uploadFileMainsesevent(this);"  style="display:none" />
              <a href="javascript:;" id="change_main_btn">
                <i class="fa fa-camera" id="change_main_i"></i>
                <span id="change_main_txt"><?php echo $this->translate("Upload Main Photo"); ?></span>
              </a>
              <div class="sesevent_cover_change_cover_options sesbasic_option_box">
                <i class="sesevent_cover_change_cover_options_arrow"></i>
                <a href="javascript:;" id="change_main_cvr_pht"><i class="fa fa-plus"></i><?php echo $this->subject->photo_id ? $this->translate("Change Main Photo") : $this->translate("Add Main Photo"); ?></a>
                <a style="display:<?php echo $this->subject->photo_id ? 'block !important' : 'none !important'; ?>;" href="javascript:;" id="sesevent_main_photo_i"><i class="fa fa-trash"></i><?php echo $this->translate("Remove Main Photo"); ?></a>
              </div>
            </div>
        <?php }?>
          </div>
        <?php }?>
          <div class="sesevent_cover_info">
           <?php if (in_array('title', $this->show_criterias)) {?>
           <?php if ($this->actionA != 'buy') {?>
            <h2 class="sesevent_cover_title"><?php echo $this->subject->getTitle(); ?></h2>
          <?php } else {?>
          	<h2 class="sesevent_cover_title"><?php echo $this->htmlLink($this->subject->getHref(), $this->subject->getTitle(), array('class' => '')); ?></h2>
          <?php }?>
          <?php }?>
           <?php if (in_array('createdon', $this->show_criterias)) {?>
            <div class="sesevent_cover_date clear sesbasic_clearfix">
              <i title='<?php echo $this->translate("Created On"); ?>' class="far fa-clock"></i>
              <span>
                <?php echo $this->translate("Created On"); ?>
                  <?php echo $this->timestamp($this->subject->creation_date) ?>
              </span>
            </div>
           <?php }?>
          <?php if (in_array('createdby', $this->show_criterias)) {?>
            <div class="sesevent_cover_date clear sesbasic_clearfix">
              <i title="<?php echo $this->translate("Created By"); ?>" class="fa fa-user"></i>
              <span>
                <?php echo $this->translate("Created By"); ?>
                <a href="<?php echo $this->subject->getOwner()->getTitle(); ?>">
                  <?php echo $this->htmlLink($this->subject->getOwner()->getHref(), $this->subject->getOwner()->getTitle(), array('class' => 'thumbs_author')); ?>
                </a>
              </span>
            </div>
           <?php }?>
           <?php if (in_array('hostedby', $this->show_criterias)) {?>
            <div class="sesevent_cover_date clear sesbasic_clearfix">
              <i title="<?php echo $this->translate("Hosted By"); ?>" class="fa fa-male"></i>
              <span>
                <?php echo $this->translate("Hosted By"); ?>
                <a href="<?php echo $this->host['href']; ?>">
                  <?php echo $this->host['title']; ?>
                </a>
              </span>
            </div>
           <?php }?>

           <?php if (in_array('startEndDate', $this->show_criterias)) {?>
            <div class="sesevent_cover_date clear sesbasic_clearfix sesevent_cover_time">
              <i title="<?php echo $this->translate("Start & End Date"); ?>" class="far fa-calendar-alt"></i>
              <?php $dateinfoParams['starttime'] = true;?>
              <?php $dateinfoParams['endtime'] = true;?>
              <?php $dateinfoParams['timezone'] = true;?>
              <?php echo $this->eventStartEndDates($this->subject, $dateinfoParams); ?>
            </div>
          <?php }?>
			  <?php
				  $timezone = $this->subject->timezone;
				  $timeStart = new DateTime($this->subject->starttime, new DateTimeZone('UTC'));
				  $timeEnd = new DateTime($this->subject->endtime, new DateTimeZone('UTC'));
				  $timeStart->setTimeZone(new DateTimeZone($timezone));
				  $timeEnd->setTimeZone(new DateTimeZone($timezone));
				  $timeInfo = date_format($timeStart,"H:i") . " - " . date_format($timeEnd,"H:i");
			  ?>
          <?php if (in_array('minimalisticCover', $this->show_criterias)) {?>
            <div class="sesevent_minimalistic_cover">
            <?php $formattedDate = $this->eventStartDate($this->subject, $dateinfoParams);?>
                <div class='seevent-cover-date'>
                      <h1 class='seevent-cover-date--day'><?php echo $formattedDate["day"] ?></h1>
                      <h1 class='seevent-cover-date--date'><?php echo $formattedDate["date"] ?></h1>
                      <h1 class='seevent-cover-date--month'><?php echo $formattedDate["month"] ?></h1>
                </div>
                <div class="divider"></div>
                <div class="seevent-cover-title">
                  <h1><?php echo $this->subject->getTitle(); ?></h1>
                    <div class="description">
                        <?php echo $timeInfo ?>
                    </div>
                    <?php if (!$this->subject->is_webinar):?>
                        <div class="description" style="margin-top: 0;">
                            <?php 
                                $locations = explode(',', $this->shortLocation($this->subject->location) );
                                echo $locations[0];
                            ?>
                        </div>
                    <?php endif?>
                </div>
            </div>
          <?php }?>
            <?php if (in_array('location', $this->show_criterias) && $this->subject->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)) {?>
            <div class="sesevent_cover_date clear sesbasic_clearfix sesevent_cover_location">
              <i title='<?php echo $this->translate("Location"); ?>' class="fas fa-map-marker-alt"></i>
              <span>
                <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) {?>
                  <a href="<?php echo $this->url(array('resource_id' => $this->subject->event_id, 'resource_type' => 'sesevent_event', 'action' => 'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $this->subject->location ?></a>
                <?php } else {?>
                  <?php echo $this->subject->location; ?>
                <?php }?>
              </span>
            </div>
            <?php }?>
          <?php
if (isset($this->show_criterias) && in_array('venue', $this->show_criterias) && $this->subject->venue_name) {?>
           	 <div class="sesevent_cover_date clear sesbasic_clearfix">
           		<i title='<?php echo $this->translate("Venue"); ?>' class="fa fa-home"></i>
              <span>
              <?php	echo $this->subject->venue_name; ?>
              </span>
             </div>
          <?php
}
if (isset($this->show_criterias) && in_array('tag', $this->show_criterias) && count($this->eventTags)) {?>
           <div class="sesevent_cover_date clear sesbasic_clearfix">
           	<i title='<?php echo $this->translate("Tags"); ?>' class="fa fa-tag"></i>
            <span>
           	<?php
$counter = 1;
    foreach ($this->eventTags as $tag):
        if ($tag->getTag()->text != '') {?>
	                  <a href='javascript:void(0);' onclick='javascript:tagAAction(<?php echo $tag->getTag()->tag_id; ?>,"<?php echo $tag->getTag()->text; ?>");'>#<?php echo $tag->getTag()->text ?></a>
	                  <?php if (count($this->eventTags) != $counter) {
            echo ",";
        }?>
	          <?php	}
        $counter++;
    endforeach;?>
              </span>
              </div>
          <?php
}
?>
       </div>
          <div class="sesevent_cover_right">
            <?php if (isset($this->show_criterias) && (in_array('commentCount', $this->show_criterias) || in_array('likeCount', $this->show_criterias) || in_array('favouriteCount', $this->show_criterias) || in_array('viewCount', $this->show_criterias) || in_array('guestCount', $this->show_criterias))) {?>
              <div class="sesevent_cover_right_stats">
              <?php if (in_array('likeCount', $this->show_criterias)) {?>
                <div>
                  <span class="sesevent_cover_stat_count"><?php echo $this->subject->like_count; ?></span>
                  <span class="sesevent_cover_stat_txt"> <?php echo $this->subject->like_count == 1 ? $this->translate("Like") : $this->translate("Likes"); ?></span>
                </div>
              <?php }?>
              <?php if (in_array('commentCount', $this->show_criterias)) {?>
                <div>
                  <span class="sesevent_cover_stat_count"><?php echo $this->subject->comment_count; ?></span>
                  <span class="sesevent_cover_stat_txt"> <?php echo $this->subject->comment_count == 1 ? $this->translate("CommentSESADV") : $this->translate("CommentsSESADV"); ?></span>
                </div>
              <?php }?>
              <?php if (in_array('viewCount', $this->show_criterias)) {?>
                <div>
                  <span class="sesevent_cover_stat_count"><?php echo $this->subject->view_count; ?></span>
                  <span class="sesevent_cover_stat_txt"> <?php echo $this->subject->view_count == 1 ? $this->translate("View") : $this->translate("Views"); ?></span>
                </div>
              <?php }?>
              <?php if (in_array('favouriteCount', $this->show_criterias)) {?>
                <div>
                  <span class="sesevent_cover_stat_count"><?php echo $this->subject->favourite_count; ?></span>
                  <span class="sesevent_cover_stat_txt"> <?php echo $this->subject->favourite_count == 1 ? $this->translate("Favourite") : $this->translate("Favourites"); ?></span>
                </div>
              <?php }?>
              <?php if (in_array('guestCount', $this->show_criterias)) {?>
                <div>
                  <span class="sesevent_cover_stat_count"><?php echo $this->subject->getAttendingCount(); ?></span>
                  <span class="sesevent_cover_stat_txt"> <?php echo $this->subject->getAttendingCount() == 1 ? $this->translate("Guest") : $this->translate("Guests"); ?></span>
                </div>
              <?php }?>
              </div>
            <?php }?>
            <div class="sesevent_cover_buttons floatR clear">
              <?php if (in_array('bookNow', $this->show_criterias) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')): ?>
                <?php if (empty($this->noTicketAvailable)) {?>
                  <div class="sesevent_button">
                    <a class="sesbasic_link_btn" href="<?php echo $this->url(array('event_id' => $this->subject->custom_url), 'sesevent_ticket', true); ?>">
                      <i class="far fa-calendar-alt"></i>
                      <?php echo $this->translate("Book Now"); ?>
                    </a>
                  </div>
                <?php }?>
              <?php endif;?>
            <?php if (in_array('addtocalender', $this->show_criterias)) {?>
              <div><?php echo $this->content()->renderWidget('sesevent.add-to-calendar', array('options' => $this->show_calander)); ?></div>
            <?php }?>
           <?php
$allowed = true;
if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
    $eventHasTicket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $this->subject->getIdentity()));
    if (count($eventHasTicket)) {
        $allowed = false;
    }

} else {
    //check event expire
    if (strtotime($this->subject->endtime) <= time()) {
        $noRsvp = true;
    }

}
?>
           <?php if (empty($noRsvp) && $allowed && in_array('join', $this->show_criterias) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0) {?>
             <?php $viewer = Engine_Api::_()->user()->getViewer();?>
             <?php $row = $this->subject->membership()->getRow($viewer);
    if ((null === $row)) {
        if ($this->subject->membership()->isResourceApprovalRequired() && !in_array('minimalisticCover', $this->show_criterias)) {
            ?>
              <div>
                <a href="<?php echo $this->url(array('event_id' => $this->subject->event_id, 'controller' => 'member', 'action' => 'request'), 'sesevent_extended', true); ?>" class="openSmoothbox sesbasic_link_btn">
                  <i class="fa fa-check"></i>
                   <?php echo $this->translate("Request Invite"); ?>
                </a>
              </div>
             <?php } else if (!in_array('minimalisticCover', $this->show_criterias)) {?>
               <div>
                  <a href="<?php echo $this->url(array('event_id' => $this->subject->event_id, 'controller' => 'member', 'action' => 'join'), 'sesevent_extended', true); ?>" class="openSmoothbox sesbasic_link_btn">
                    <i class="fa fa-check"></i>
                     <?php echo $this->translate("Join Event"); ?>
                  </a>
                </div>
             <?php }?>
            <?php }?>
           <?php }?>
               <?php if (in_array('advShare', $this->show_criterias)) {?>
                 <div><?php echo $this->content()->renderWidget('sesevent.advance-share', array('options' => $this->show_calander)); ?></div>
               <?php }?>
				<?php if($this->subject->is_webinar): ?>
				<div class="btn-icon online-event">
					<div class="text-online-event">
						<a href="<?php echo $this->isAttending ? $this->subject->meeting_url : 'javascript:;';?>" <?php if ($this->isAttending):?>target="_blank"<?php endif?>><?php echo $this->translate('Online'); ?></a>
					</div>
					<div class="_icon-online-event">
						<i class="fas fa-video" ></i>
					</div>
				</div>
				<?php endif;?>
  </div>
          </div>
         <?php if ($this->actionA != 'buy') {?>
          <div class="sesevent_cover_footer">
            <div class="sesevent_cover_footer_inner sesbasic_clearfix">
              <div class="sesevent_cover_footer_buttons">
                <?php if (in_array('likeBtn', $this->show_criterias) && $this->subject->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment')) {
    $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($this->subject->event_id, $this->subject->getType());
    $likeClassAct = ($LikeStatus) ? ' button_active' : '';
    ?>
                <div>
                  <a href="javascript:;" title="<?php echo $this->translate('Like'); ?>" data-url="<?php echo $this->subject->event_id ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event sesevent_like_sesevent_event_<?php echo $this->subject->event_id, ' ' . $likeClassAct; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $this->subject->like_count; ?></span></a>
                </div>
                <?php }?>

              <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && in_array('favouriteBtn', $this->show_criterias) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0) {
    $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type' => 'sesevent_event', 'resource_id' => $this->subject->event_id));
    $favClass = ($favStatus) ? 'fa-heart' : 'fa-heart';
    ?>
                <div>
                  <a href="javascript:;" title="<?php echo $this->translate('Add to Favourite'); ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn sesevent_favourite_sesevent_event sesevent_favourite_sesevent_event_<?php echo $this->subject->event_id; ?>" data-url="<?php echo $this->subject->event_id ?>"><i class="fa fa-heart"></i><span><?php echo $this->subject->favourite_count; ?></span></a>
                </div>
                <?php }?>
                <?php if (in_array('socialShare', $this->show_criterias)) {?>

                <?php echo $this->partial('_socialShareIcons.tpl', 'sesbasic', array('resource' => $this->subject, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

                <?php }?>

                <?php if (in_array('listBtn', $this->show_criterias) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0) {?>
                <div class="sesevent_cover_btn">
                  <a href="#" class="sesbasic_icon_btn" onclick="opensmoothboxurl('<?php echo $this->url(array('action' => 'add', 'module' => 'sesevent', 'controller' => 'list', 'event_id' => $this->subject->event_id), 'default', true); ?>');return false;"	 title="<?php echo $this->translate('Add to List'); ?>">
                    <i class="fa fa-list"></i>
                  </a>
                </div>
                <?php }?>
                <?php if (Engine_Api::_()->user()->getViewer()->getIdentity() != 0) {?>
                <div>
                  <a href="javascript:;" class="sesbasic_icon_btn sesevent_view_option_btn" id="parent_container_option">
                    <i class="fa fa-ellipsis-v" id="fa-ellipsis-v"></i>
                  </a>
                </div>
                <?php }?>
              </div>
             <?php if ($this->tab) {?>
              <div class="sesevent_cover_tabs_container sesevent_cover_tabs"></div>
             <?php }?>
            </div>
          </div>
         <?php }?>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="sesevent_option_data_div"><?php echo $this->content()->renderWidget("sesevent.profile-options", array("optionsG" => true)); ?></div>
<script type="application/javascript">
sesJqueryObject('<div id="sesevent_profile_options" class="sesevent_cover_options_btn sesbasic_option_box sesbasic_bxs" style="display:none;">'+sesJqueryObject('#sesevent_option_data_div').html()+'</div>').appendTo('body');
sesJqueryObject('#sesevent_option_data_div').remove();
function doResizeForButton(){
  let isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

	var topPositionOfParentDiv =  sesJqueryObject(".sesevent_view_option_btn").offset().top + 40;
	topPositionOfParentDiv = topPositionOfParentDiv+'px';
	var leftPositionOfParentDiv =  sesJqueryObject(".sesevent_view_option_btn").offset().left - 115;
	leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
	sesJqueryObject('.sesevent_cover_options_btn').css('top',topPositionOfParentDiv);
	if(!isMobile) sesJqueryObject('.sesevent_cover_options_btn').css('left',leftPositionOfParentDiv);
}
window.addEvent('load',function(){
	doResizeForButton();
});
sesJqueryObject(document).click(function(event){
	if(event.target.id == 'parent_container_option' || event.target.id == 'fa-ellipsis-v'){
		if(sesJqueryObject('#parent_container_option').hasClass('active')){
			sesJqueryObject('#parent_container_option').removeClass('active');
			sesJqueryObject('.sesevent_cover_options_btn').hide();
		}else{
			sesJqueryObject('#parent_container_option').addClass('active');
			sesJqueryObject('.sesevent_cover_options_btn').show();
		}
	}else{
		sesJqueryObject('#parent_container_option').removeClass('active');
		sesJqueryObject('.sesevent_cover_options_btn').hide();
	}
});
<?php if ($this->can_edit) {?>
sesJqueryObject('<div class="sesevent_photo_update_popup sesbasic_bxs" id="sesevent_popup_cam_upload" style="display:none"><div class="sesevent_photo_update_popup_overlay"></div><div class="sesevent_photo_update_popup_container sesevent_photo_update_webcam_container"><div class="sesevent_photo_update_popup_header"><?php echo $this->translate("Click to Take Cover Photo") ?><a class="fa fa-times" href="javascript:;" onclick="hideProfilePhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="sesevent_photo_update_popup_webcam_options"><div id="sesevent_camera" style="background-color:#ccc;"></div><div class="centerT sesevent_photo_update_popup_btns">   <button onclick="take_snapshot()" style="margin-right:3px;" ><?php echo $this->translate("Take Cover Photo") ?></button><button onclick="hideProfilePhotoUpload()" ><?php echo $this->translate("Cancel") ?></button></div></div></div></div><div class="sesevent_photo_update_popup sesbasic_bxs" id="sesevent_popup_existing_upload" style="display:none"><div class="sesevent_photo_update_popup_overlay"></div><div class="sesevent_photo_update_popup_container" id="sesevent_popup_container_existing"><div class="sesevent_photo_update_popup_header"><?php echo $this->translate("Select a cover photo") ?><a class="fa fa-times" href="javascript:;" onclick="hideProfilePhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="sesevent_photo_update_popup_content"><div id="sesevent_existing_data"></div><div id="sesevent_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Sesbasic/externals/images/loading.gif" alt="<?php echo $this->translate("Loading"); ?>" style="margin-top:10px;"  /></div></div></div></div>').appendTo('body');
sesJqueryObject(document).on('click','#uploadWebCamPhoto',function(){
	sesJqueryObject('#sesevent_popup_cam_upload').show();
	<!-- Configure a few settings and attach camera -->
	Webcam.set({
		width: 320,
		height: 240,
		image_format:'jpeg',
		jpeg_quality: 90
	});
	Webcam.attach('#sesevent_camera');
});
<!-- Code to handle taking the snapshot and displaying it locally -->
function take_snapshot() {
	// take snapshot and get image data
	Webcam.snap(function(data_uri) {
		Webcam.reset();
		sesJqueryObject('#sesevent_popup_cam_upload').hide();
		// upload results
		sesJqueryObject('.sesevent_cover_container').append('<div id="sesevent_cover_loading" class="sesbasic_loading_cont_overlay"></div>');
		 Webcam.upload( data_uri, en4.core.baseUrl+'sesevent/index/upload-cover/event_id/<?php echo $this->subject->event_id ?>' , function(code, text) {
				response = sesJqueryObject.parseJSON(text);
				sesJqueryObject('#sesevent_cover_loading').remove();
				sesJqueryObject('.sesevent_cover_image').css('background-image', 'url(' + response.file + ')');
				sesJqueryObject('#sesevent_cover_default').hide();
				sesJqueryObject('#coverChangesesevent').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Change Cover Photo'));
				sesJqueryObject('#coverRemovesesevent').css('display','block');
			} );
	});
}
function hideProfilePhotoUpload(){
	if(typeof Webcam != 'undefined')
	 Webcam.reset();
	canPaginatePageNumber = 1;
	sesJqueryObject('#sesevent_popup_cam_upload').hide();
	sesJqueryObject('#sesevent_popup_existing_upload').hide();
	if(typeof Webcam != 'undefined'){
		sesJqueryObject('.slimScrollDiv').remove();
		sesJqueryObject('.sesevent_photo_update_popup_content').html('<div id="sesevent_existing_data"></div><div id="sesevent_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Sesbasic/externals/images/loading.gif" alt="Loading" style="margin-top:10px;"  /></div>');
	}
}

sesJqueryObject(document).on('click','#coverChangesesevent',function(){
	document.getElementById('uploadFilesesevent').click();
});

sesJqueryObject(document).on('click','#change_main_cvr_pht',function(){
	document.getElementById('uploadFileMainsesevent').click();
});

function uploadFileMainsesevent(input){
	 var url = input.value;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
				uploadFileToServerMain(input.files[0]);
    }else{
				//Silence
		}

}
function uploadCoverArt(input){
	 var url = input.value;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
				uploadFileToServer(input.files[0]);
    }else{
				//Silence
		}
}
function uploadFileToServerMain(files){
	<?php if ($this->fullwidth) {?>
	sesJqueryObject('.sesevent_cover_main_photo').append('<div id="sesevent_cover_loading_main" class="sesbasic_loading_cont_overlay" style="display:block;border-radius:50%;"></div>');
	<?php } else {?>
		sesJqueryObject('.sesevent_cover_main_photo').append('<div id="sesevent_cover_loading_main" class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
	<?php }?>
	var formData = new FormData();
	formData.append('Filedata', files);
	uploadURL = en4.core.baseUrl+'sesevent/index/upload-main/event_id/<?php echo $this->subject->event_id ?>';
	var jqXHR=sesJqueryObject.ajax({
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
		cache: false,
		data: formData,
		success: function(response){
			response = sesJqueryObject.parseJSON(response);
			sesJqueryObject('#uploadFileMainsesevent').val('');
			sesJqueryObject('#sesevent_cover_loading_main').remove();
			sesJqueryObject('.sesevent_cover_image_main').attr('src', response.file);
			sesJqueryObject('#change_main_cvr_pht').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Change Cover Photo'));
			sesJqueryObject('#sesevent_main_photo_i').css('display','block !important');
     }
    });
}
sesJqueryObject('#sesevent_main_photo_i').click(function(){
		<?php if ($this->fullwidth) {?>
	sesJqueryObject('.sesevent_cover_main_photo').append('<div id="sesevent_cover_loading_main" class="sesbasic_loading_cont_overlay" style="display:block;border-radius:50%;"></div>');
	<?php } else {?>
		sesJqueryObject('.sesevent_cover_main_photo').append('<div id="sesevent_cover_loading_main" class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
	<?php }?>
		var event_id = '<?php echo $this->subject->event_id; ?>';
		uploadURL = en4.core.baseUrl+'sesevent/index/remove-main/event_id/'+event_id;
		var jqXHR=sesJqueryObject.ajax({
			url: uploadURL,
			type: "POST",
			contentType:false,
			processData: false,
			cache: false,
			success: function(response){
				sesJqueryObject('#change_main_cvr_pht').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Add Main Photo'));
				response = sesJqueryObject.parseJSON(response);
				sesJqueryObject('.sesevent_cover_image_main').attr('src', response.file);
				sesJqueryObject('#sesevent_cover_loading_main').remove();
				//silence
			 }
			});
});
sesJqueryObject('#coverRemovesesevent').click(function(){
		sesJqueryObject(this).css('display','none');
		sesJqueryObject('.sesevent_cover_container').append('<div id="sesevent_cover_loading" class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
		var event_id = '<?php echo $this->subject->event_id; ?>';
		uploadURL = en4.core.baseUrl+'sesevent/index/remove-cover/event_id/'+event_id;
		var jqXHR=sesJqueryObject.ajax({
			url: uploadURL,
			type: "POST",
			contentType:false,
			processData: false,
			cache: false,
			success: function(response){
				sesJqueryObject('#coverChangesesevent').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Add Cover Photo'));
				response = sesJqueryObject.parseJSON(response);
				sesJqueryObject('.sesevent_cover_image').css('background-image', 'url(' + response.file + ')');
				sesJqueryObject('#sesevent_cover_loading').remove();
				//silence
			 }
			});
});
function uploadFileToServer(files){
	sesJqueryObject('.sesevent_cover_container').append('<div id="sesevent_cover_loading" class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
	var formData = new FormData();
	formData.append('Filedata', files);
	uploadURL = en4.core.baseUrl+'sesevent/index/upload-cover/event_id/<?php echo $this->subject->event_id ?>';
	var jqXHR=sesJqueryObject.ajax({
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
		cache: false,
		data: formData,
		success: function(response){
			response = sesJqueryObject.parseJSON(response);
			sesJqueryObject('#uploadFilesesevent').val('');
			sesJqueryObject('#sesevent_cover_loading').remove();
			sesJqueryObject('.sesevent_cover_image').css('background-image', 'url(' + response.file + ')');
				sesJqueryObject('#sesevent_cover_default').hide();
			sesJqueryObject('#coverChangesesevent').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Change Cover Photo'));
			sesJqueryObject('#coverRemovesesevent').css('display','block');
     }
    });
}
<?php }?>
var tagAAction = window.tagAAction = function(tag,value){
	var url = "<?php echo $this->url(array('module' => 'sesevent', 'action' => 'browse'), 'sesevent_general', true) ?>?tag_id="+tag+'&tag_name='+value;
 window.location.href = url;
}
</script>
<?php if ($this->fullwidth) {?>
<script type="application/javascript">
sesJqueryObject(document).ready(function(){
	var htmlElement = document.getElementsByTagName("body")[0];
  htmlElement.addClass('sesevent_coverfull');
});
</script>
<?php }?>
<?php if ($this->tab) {?>
<style type="text/css">
@media only screen and (min-width:767px){
.layout_core_container_tabs .tabs_alt{ display:none;}
}
</style>
<script type="application/javascript">
if (matchMedia('only screen and (min-width: 767px)').matches) {
sesJqueryObject(document).ready(function(){
var tabs = sesJqueryObject('.layout_core_container_tabs').find('.tabs_alt').get(0).outerHTML;
sesJqueryObject('.layout_core_container_tabs').find('.tabs_alt').remove();
sesJqueryObject('.sesevent_cover_tabs_container').html(tabs);
});
sesJqueryObject(document).on('click','ul#main_tabs li > a',function(){
	if(sesJqueryObject(this).parent().hasClass('more_tab'))
	  return;
	var index = sesJqueryObject(this).parent().index() + 1;
	var divLength = sesJqueryObject('.layout_core_container_tabs > div');
	for(i=0;i<divLength.length;i++){
		sesJqueryObject(divLength[i]).hide();
	}
	sesJqueryObject('.layout_core_container_tabs').children().eq(index).show();
});
sesJqueryObject(document).on('click','.tab_pulldown_contents ul li',function(){
 var totalLi = sesJqueryObject('ul#main_tabs > li').length;
 var index = sesJqueryObject(this).index();
 var divLength = sesJqueryObject('.layout_core_container_tabs > div');
	for(i=0;i<divLength.length;i++){
		sesJqueryObject(divLength[i]).hide();
	}
 sesJqueryObject('.layout_core_container_tabs').children().eq(index+totalLi).show();
});
}
</script>
<?php }?>
