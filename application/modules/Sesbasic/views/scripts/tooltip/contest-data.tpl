<?php $contest = $this->subject;?>
<?php if (!empty($contest->category_id)):?>
  <?php $category = Engine_Api::_ ()->getDbtable('categories', 'sescontest')->find($contest->category_id)->current();?>
<?php endif;?>
<div class="sescontest_info_tip sesbasic_bxs">
	<div class="sescontest_info_tip_cover">
      <span class="sescontest_info_tip_cover_img" <?php if(in_array('coverphoto',$this->globalEnableTip) && in_array('coverphoto',$this->moduleEnableTip)):?>style="background-image:url('<?php echo $contest->getCoverPhotoUrl() ?>');"<?php endif;?>></span>
    <div class="sescontest_info_tip_cover_cont">
      <?php if(in_array('media',$this->moduleEnableTip)): ?>
        <?php if($contest->contest_type == 3):?>
          <a href="<?php echo $this->url(array('action' => 'video'),'sescontest_media',true);?>" class="sescontest_info_tip_mediatype"><i class="fa fa-video-camera" title="<?php echo $this->translate('Video Contest');?>"></i></a>
        <?php elseif($contest->contest_type == 4):?>
          <a href="<?php echo $this->url(array('action' => 'audio'),'sescontest_media',true);?>"  class="sescontest_info_tip_mediatype"><i class="fa fa-music" title="<?php echo $this->translate('Audio Contest');?>"></i></a>
        <?php elseif($contest->contest_type == 2):?>
          <a href="<?php echo $this->url(array('action' => 'photo'),'sescontest_media',true);?>" class="sescontest_info_tip_mediatype"><i class="far fa-image" title="<?php echo $this->translate('Photo Contest');?>"></i></a>
        <?php else:?>
          <a href="<?php echo $this->url(array('action' => 'text'),'sescontest_media',true);?>" class="sescontest_info_tip_mediatype"><i class="far fa-file-alt" title="<?php echo $this->translate('Writing Contest');?>"></i></a>
        <?php endif;?>
      <?php endif;?>
      <?php if(in_array('mainphoto',$this->globalEnableTip) && in_array('mainphoto',$this->moduleEnableTip)): ?>
        <div class="sescontest_info_tip_photo">
          <a href="<?php echo $contest->getHref();?>">
            <img src="<?php echo $contest->getPhotoUrl('thumb.profile'); ?>" />
          </a>	
        </div>
      <?php endif;?>
      <div class="sescontest_info_tip_cover_info">
        <?php if(in_array('title',$this->globalEnableTip) && in_array('title',$this->moduleEnableTip)): ?>
          <div class="sescontest_info_tip_title"><a href="<?php echo $contest->getHref();?>"><?php echo $contest->getTitle();?></a></div>
        <?php endif;?>
        <div class="sescontest_info_tip_date">by <a href="<?php echo $contest->getOwner()->getHref();?>"><?php echo $contest->getOwner()->getTitle();?></a></div>
        <?php if(in_array('category',$this->globalEnableTip) && in_array('category',$this->moduleEnableTip) && $category):?>
          <div class="sescontest_info_tip_date"><a href="<?php echo $category->getHref();?>"><?php echo $category->category_name;?></a> &middot;<?php endif;?><?php if(in_array('entries',$this->moduleEnableTip)): ?><?php echo $contest->join_count.' ';?><?php echo $this->translate(array('Entry', 'Entries', $contest->join_count), $this->locale()->toNumber($contest->join_count)) ?><?php endif;?></div>
      </div>
    </div>
  </div>
	<div class="sescontest_info_tip_content sesbasic_clearfix">
      <?php if(in_array('description',$this->moduleEnableTip)): ?>
        <div class="_des"><?php echo $this->string()->truncate($this->string()->stripTags($contest->description), '130') ?></div>
      <?php endif;?>
      <div class="_contactinfo">
        <?php if($contest->contest_contact_website):?>
      <p class="sesbasic_clearfix">
        <i class="sesbasic_text_light fa fa-globe"></i>
        <span><a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($contest->contact_contact_website, PHP_URL_SCHEME) === null ? 'http://' . $contest->contact_contact_website : $contest->contact_contact_website; ?>' title='<?php echo $this->translate("Website URL"); ?>'><?php echo parse_url($contest->contact_contact_website, PHP_URL_SCHEME) === null ? '' . $contest->contact_contact_website : $contest->contact_contact_website; ?></a></span>
        </p>
      <?php endif;?>
      <p class="sesbasic_clearfix">
        <?php if($contest->contest_contact_phone):?>
      	  <i class="sesbasic_text_light fa fa-phone-square "></i>
        <?php endif;?>
        <span>
          <?php if($contest->contest_contact_email):?>
            <span><a href='mailto:<?php echo $contest->contest_contact_email ?>' target="_blank" class="sesbasic_linkinherit"><?php echo $contest->contest_contact_email ?></a></span>
          <?php endif;?>
          <?php if($contest->contest_contact_phone):?>
            <span><?php echo $contest->contest_contact_phone; ?></span>
          <?php endif;?>
          <?php if($contest->contest_contact_facebook || $contest->contest_contact_linkedin || $contest->contest_contact_twitter):?>
            <span class="_sociallinks">
              <?php if($contest->contest_contact_facebook):?>
                <a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($contest->contest_contact_facebook, PHP_URL_SCHEME) === null ? 'https://' . $contest->contest_contact_facebook : $contest->contest_contact_facebook; ?>'><i class="fab fa-facebook-f-square"><?php echo parse_url($contest->contest_contact_facebook, PHP_URL_SCHEME) === null ? '' . $contest->contest_contact_facebook : $contest->contest_contact_facebook; ?></i></a>
              <?php endif;?>
               <?php if($contest->contest_contact_linkedin):?>
                <a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($contest->contest_contact_linkedin, PHP_URL_SCHEME) === null ? 'https://' . $contest->contest_contact_linkedin : $contest->contest_contact_linkedin; ?>'><i class="fa fa-linkedin-square"><?php echo parse_url($contest->contest_contact_linkedin, PHP_URL_SCHEME) === null ? '' . $contest->contest_contact_linkedin : $contest->contest_contact_linkedin; ?></i></a>
              <?php endif;?>
              <?php if($contest->contest_contact_twitter):?>
                <a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($contest->contest_contact_twitter, PHP_URL_SCHEME) === null ? 'https://' . $contest->contest_contact_twitter : $contest->contest_contact_twitter; ?>'><i class="fab fa-twitter-square"><?php echo parse_url($contest->contest_contact_twitter, PHP_URL_SCHEME) === null ? '' . $contest->contest_contact_twitter : $contest->contest_contact_twitter; ?></i></a>
              <?php endif;?>
            </span>
          <?php endif;?>
       	</span>
      </p>	
    </div>
    <?php if(count($this->friends) > 0):?>
      <div class="_friends sesbasic_clearfix">
        <div class="sescontest_info_tip_head"><?php echo $this->translate("Friends Participating");?></div>
        <div class="_friendslist sesbasic_clearfix">
          <?php foreach($this->friends as $user):?>
          <div class="_item"><a href="<?php echo $user->getHref();?>"><img src="<?php echo $user->getPhotoUrl('thumb.profile');?>" alt="<?php echo $user->getTitle();?>" class="thumb_icon item_photo_user " /></a></div>
          <?php endforeach;?>
        </div>
      </div>
    <?php endif;?>
    <?php if(count($this->entries) > 0):?>
      <div class="_entries sesbasic_clearfix">
        <div class="sescontest_info_tip_head"><?php echo $this->translate("Recent Entries");?></div>
        <div class="_entrieslist sesbasic_clearfix">
          <?php foreach($this->entries as $entry):?>
            <div class="_item">
              <a href="<?php echo $entry->getHref();?>"><span style="background-image:url(<?php echo $entry->getPhotoUrl('thumb.main');?>);"></span></a>
            </div>
          <?php endforeach;?>
        </div>
      </div>
    <?php endif;?>
    <?php if(in_array('joinNow',$this->moduleEnableTip)): ?>
      <?php $participate = Engine_Api::_()->getDbTable('participants', 'sescontest')->hasParticipate($this->viewer()->getIdentity(), $contest->contest_id);?>
      <div class="_btn">
        <?php if(isset($participate['can_join']) && isset($participate['show_button'])){?>
          <a href="<?php echo $this->url(array('action' => 'create', 'contest_id' => $contest->contest_id),'sescontest_join_contest','true');?>" class="sescontest_list_join_btn join sesbasic_animation"><?php echo $this->translate('JOIN NOW');?></a>
        <?php } elseif(isset($participate['show_button'])) {?>
          <a href="javascript:;" class="sescontest_list_join_btn joined sesbasic_animation"><?php echo $this->translate('JOINED');?></a>
        <?php };?>
      </div>
    <?php endif;?>
  </div>
  <?php if(in_array('socialshare',$this->moduleEnableTip)): ?>
    <?php $this->socialSharingActive = 1;?>
    <?php $this->favouriteButtonActive = 1;?>
    <?php $this->likeButtonActive = 1;?>
    <?php $this->followButtonActive = 1;?>
    <div class="sesbasic_tooltip_footer sescontest_info_tip_footer sesbasic_clearfix">
      <?php include APPLICATION_PATH .  '/application/modules/Sescontest/views/scripts/_dataSharing.tpl';?>
    </div>
  <?php endif;?>
</div>
<?php die; ?>