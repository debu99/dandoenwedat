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
<ul class="sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block sesevent_host_block">
  <li class="sesevent_host_block_top sesbm sesbasic_clearfix">
    <div class="sesevent_host_photo floatL">
      <a href="<?php echo $this->host['href']; ?>">
        <?php echo $this->host['image']; ?>
      </a>
    </div>
    <div class="sesevent_host_block_top_info">
      <div class="sesevent_host_title">
        <a href="<?php echo $this->host['href']; ?>">
          <?php echo $this->host['title'];  ?>
        </a>
      </div>   
      <?php if (!empty($this->viewer_id) && $this->allowFollow): ?>
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
      <?php endif; ?>
   	</div>
  </li>
  <li class="sesevent_host_block_stats">
    <div><b class="bold"><?php echo $this->totalEventOfHost; ?></b>&nbsp;<?php echo $this->translate("Events"); ?></div>
    <?php if($this->followCount): ?>
    <div><b class="bold"><?php echo $this->followCount ?></b>&nbsp;<?php echo $this->translate("Followed"); ?></div>
    <?php endif; ?>
   
	<li class="sesevent_host_btn sesbm">
    <a href="<?php echo $this->url(array('event_id' => $this->subject->event_id,'item_type'=>'sesevent_event','action'=>'message'), 'sesevent_specific', true); ?>" title="Message Host" class="openSmoothbox"><i class="fa fa-envelope sesbasic_text_light"></i><?php echo $this->translate('Message Host');?></a>
  </li>
  <li class="sesevent_host_btn sesbm">
    <a href="<?php echo $this->host['href']; ?>" title="View Host Profile" class=""><i class="fa fa-user sesbasic_text_light"></i><?php echo $this->translate('View Host Profile');?></a>
  </li>
</ul>
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