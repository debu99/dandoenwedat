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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $host = $this->host; ?>
<?php
  $sitehostredirect = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1); 
	if($sitehostredirect && $host->user_id) {
	  $user = Engine_Api::_()->getItem('user', $host->user_id);
	  $href = $user->getHref();
	} else {
	  $href = $host->getHref();
	}
?>
<div class="sesevent_host_profile sesbm clear sesbasic_clearfix sesbasic_bxs">
  <?php if(!empty($this->infoshow) && in_array('profilePhoto', $this->infoshow)): ?>
    <div class="sesevent_host_profile_photo sesevent_grid_btns_wrap">
    	<img src="<?php echo $host->getPhotoUrl(); ?>" alt="<?php echo $host->host_name; ?>" class="thumb_profile item_photo_sesevent_host">
      <?php if($this->infoshow):   ?>
        <?php if(in_array('featuredLabel', $this->infoshow) || in_array('sponsoredLabel', $this->infoshow)):   ?>
          <p class="sesevent_labels">
            <?php if($host->featured && in_array('featuredLabel', $this->infoshow)): ?>
              <span class="sesevent_label_featured"><?php echo $this->translate("FEATURED"); ?></span>
            <?php endif; ?>
            <?php if($host->sponsored && in_array('sponsoredLabel', $this->infoshow)): ?>
              <span class="sesevent_label_sponsored"><?php echo $this->translate("SPONSORED"); ?></span>
            <?php endif; ?>
          </p>
          <?php if($host->verified && in_array('verifiedLabel', $this->infoshow)): ?>
            <span class="sesevent_verified_label" title="<?php echo $this->translate("VERIFIED"); ?>"><i class="fa fa-check"></i></span>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
  		<div class="sesevent_grid_btns"> 
        <?php if(in_array('socialSharing', $this->infoshow)){ 
         $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $href); ?>
         
        <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $host, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

        <?php } ?>
        <?php
          if(in_array('favouriteButton', $this->infoshow) && isset($host->favourite_count)) {
          $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_host','resource_id'=>$host->host_id));
          $favClass = ($favStatus)  ? 'button_active' : '';
          $shareOptions = "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_host_". $host->host_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_host ".$favClass ."' data-url=\"$host->host_id\"><i class='fa fa-heart'></i><span>$host->favourite_count</span></a>";
          echo $shareOptions;
          }
        ?>
      </div>
    </div>
  <?php endif; ?>
  <div class="sesevent_host_profile_info">
    <?php if(!empty($this->infoshow)): ?>
      <?php if(in_array('displayname', $this->infoshow)): ?>
        <div class='sesevent_host_profile_title'>
          <?php echo $host->host_name; ?>
        </div>
      <?php endif; ?>	
      <div class="sesevent_list_stats sesevent_host_profile_stats sesbasic_clearfix">
			  <?php if($this->hostEventCount && in_array('hostEventCount', $this->infoshow)): ?>
			    <span>
          	<i class="far fa-calendar-alt sesbasic_text_light"></i>
						<b class="bold"><?php echo $this->hostEventCount ?></b> <?php echo $this->translate("Event Hosted"); ?>
				  </span>
			  <?php endif; ?>
				<?php if($this->followCount && in_array('follow', $this->infoshow)): ?>
			    <span>
          	<i class="fas fa-users sesbasic_text_light"></i>
						<b class="bold"><?php echo $this->followCount ?></b> <?php echo $this->translate("Followed"); ?>
				  </span>
			  <?php endif; ?>
        <?php if(in_array('view', $this->infoshow) && isset($host->view_count)) { ?>
	        <span><i class="fa fa-eye sesbasic_text_light"></i><?php echo $this->translate(array('%s view', '%s views', $host->view_count), $this->locale()->toNumber($host->view_count))?></span>
        <?php } ?>
        <?php if(in_array('favourite', $this->infoshow) && isset($host->favourite_count)) { ?>
	        <span><i class="fa fa-heart sesbasic_text_light"></i><?php echo $this->translate(array('%s favourite', '%s favourites', $host->favourite_count), $this->locale()->toNumber($host->favourite_count))?></span>
        <?php } ?>
      </div>	
      <?php if($host->host_description && in_array('detaildescription', $this->infoshow)): ?>
        <div class="sesevent_host_profile_des sesbasic_clearfix rich_content_body">         
          <?php echo $host->host_description; ?>
        </div>
      <?php endif; ?>
      <div class="sesevent_host_profile_contact_info">
        <?php if($host->host_email && in_array('email', $this->infoshow)): ?>
          <span><i class="fa fa-envelope sesbasic_text_light"></i><a class="sesevent_host_profile_social_icon_website" href="mailto:<?php echo $host->host_email ?>"><?php echo $host->host_email ?></a></span>
        <?php endif; ?>
        <?php if($host->host_phone && in_array('phone', $this->infoshow)): ?>
          <span><i class="fa fa-phone sesbasic_text_light"></i><?php echo $host->host_phone; ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="sesevent_host_profile_info_btm">
    	<div class="floatL sesevent_host_profile_info_btm_btns">
      <?php if (!empty($this->viewer_id) && in_array('followButton', $this->infoshow) && $this->allowFollow): ?>
      	<div>
          <div class="" id="<?php echo $this->type ?>_follow_<?php echo $this->id; ?>" style ='display:<?php echo $this->isFollow ? "none" : "inline-block" ?>' >
            <a  class="sesbasic_button" href = "javascript:void(0);" onclick = "followButton('<?php echo $this->id; ?>', '<?php echo $this->type ?>');">
              <i class="fa fa-check"></i>
              <span><?php echo $this->translate("Follow") ?></span>
            </a>
          </div>
          <div id="<?php echo $this->type ?>_unfollow_<?php echo $this->id; ?>" style ='display:<?php echo $this->isFollow ? "inline-block" : "none" ?>' >
            <a  class="sesbasic_button" href = "javascript:void(0);" onclick = "followButton('<?php echo $this->id; ?>', '<?php echo $this->type ?>');">
              <i class="fa fa-check"></i>
              <span><?php echo $this->translate("Unfollow") ?></span>
            </a>
          </div>
          <input type ="hidden" id = "<?php echo $this->type ?>_hiddenfollowunfollow_<?php echo $this->id; ?>" value = '<?php echo $this->isFollow ? $this->isFollow : 0; ?>' />
      	</div>  
      <?php endif; ?>      
      <?php if($this->viewer->getIdentity() == $host->owner_id || $this->viewer->level_id == 1 ): ?>
      	<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1) || $host->type == 'offsite'){ ?>
        <div>
          <a href="<?php echo $this->url(array('action'=>'edit', 'host_id'=>$host->getIdentity()),'sesevent_host',true) ?>" class="sesbasic_button" title="<?php echo $this->translate("Edit Host") ?>">
            <i class="fa fa-edit"></i>
            <span><?php echo $this->translate("Edit Host") ?></span>
          </a>
        </div>
        <?php } ?>
        <?php if($host->type == 'offsite'){ ?>
          <div>
            <a href="<?php echo $this->url(array('action'=>'delete', 'host_id'=>$host->getIdentity(),'format' => 'smoothbox'),'sesevent_host',true) ?>" class="sesbasic_button smoothbox" title="<?php echo $this->translate("Delete Host") ?>">
              <i class="fa fa-trash"></i>
              <span><?php echo $this->translate("Delete Host") ?></span>
            </a>
          </div>
       <?php } ?>
     <?php endif; ?>
     </div>
      <div class="sesevent_host_profile_social_icons floatR sesevent_host_social_links centerT">
        <?php if($host->website_url && in_array('website', $this->infoshow)): ?>
          <?php $website_url = (preg_match("#https?://#", $host->website_url) === 0) ? 'http://'.$host->website_url : $host->website_url; ?>
          <a class="sesevent_host_profile_social_icon_website" href="<?php echo $website_url ?>" target="_blank" title="<?php echo $website_url ?>">
            <i class="fa fa-globe"></i>
          </a> 
        <?php endif; ?>
        <?php if($host->facebook_url && in_array('facebook', $this->infoshow)): ?>
          <?php $facebook_url = (preg_match("#https?://#", $host->facebook_url) === 0) ? 'http://'.$host->facebook_url : $host->facebook_url; ?>
          <a class="sesevent_icon_facebook" href="<?php echo $facebook_url ?>" target="_blank" title="<?php echo $facebook_url ?>">
            <i class="fab fa-facebook-f"></i>
          </a> 
        <?php endif; ?>
        <?php if($host->twitter_url && in_array('twitter', $this->infoshow)): ?>
          <?php $twitter_url = (preg_match("#https?://#", $host->twitter_url) === 0) ? 'http://'.$host->twitter_url : $host->twitter_url; ?>
          <a class="sesevent_icon_twitter" href="<?php echo $twitter_url ?>" target="_blank" title="<?php echo $twitter_url ?>">
            <i class="fab fa-twitter"></i>
          </a>
        <?php endif; ?>
        <?php if($host->linkdin_url && in_array('linkdin', $this->infoshow)): ?>
          <?php $linkdin_url = (preg_match("#https?://#", $host->linkdin_url) === 0) ? 'http://'.$host->linkdin_url : $host->linkdin_url; ?>
          <a class="sesevent_icon_linkdin" href="<?php echo $linkdin_url ?>" target="_blank" title="<?php echo $linkdin_url ?>">
            <i class="fab fa-linkedin"></i>
          </a>
        <?php endif; ?>
        <?php if($host->googleplus_url && in_array('googleplus', $this->infoshow)): ?>
          <?php $googleplus_url = (preg_match("#https?://#", $host->googleplus_url) === 0) ? 'http://'.$host->googleplus_url : $host->googleplus_url; ?>
          <a class="sesevent_icon_googleplus" href="<?php echo $googleplus_url ?>" target="_blank" title="<?php echo $googleplus_url ?>">
            <i class="fab fa-google-plus-g"></i>
          </a>
        <?php endif; ?>
      </div>
	  </div>
  </div>
</div>
<script>
function followButton(id, type) {
	if ($(type + '_hiddenfollowunfollow_' + id))
	var contentId = $(type + '_hiddenfollowunfollow_' + id).value
	en4.core.request.send(new Request.JSON({
	url: en4.core.baseUrl + 'sesevent/index/follow',
	data: {
	format: 'json',
		'id': id,
		'type': type,
		'contentId': contentId
	},
	onSuccess: function(responseJSON) {
		if (responseJSON.follow_id) {
			if ($(type + '_hiddenfollowunfollow_' + id))
				$(type + '_hiddenfollowunfollow_' + id).value = responseJSON.follow_id;
			if ($(type + '_follow_' + id))
				$(type + '_follow_' + id).style.display = 'none';
			if ($(type + '_unfollow_' + id))
				$(type + '_unfollow_' + id).style.display = 'inline-block';
		} else {
			if ($(type + '_hiddenfollowunfollow_' + id))
				$(type + '_hiddenfollowunfollow_' + id).value = 0;
			if ($(type + '_follow_' + id))
				$(type + '_follow_' + id).style.display = 'inline-block';
			if ($(type + '_unfollow_' + id))
				$(type + '_unfollow_' + id).style.display = 'none';
		}
	}
	}));
}
</script>