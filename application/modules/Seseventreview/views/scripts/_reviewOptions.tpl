<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _ratingOptions.tpl 2016-07-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $item = $this->subject; $viewer = $this->viewer;?>
<div class="sesevent_review_listing_footer clear sesbasic_clearfix">
	<?php if(in_array('socialSharing', $this->stats)):?>
		<?php $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); ?>
		<div class="sesevent_review_blog_social_btn floatL"> 
			<?php if(in_array('socialSharing', $this->stats)  && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.sharing', 1)):?>
				<a href="<?php echo 'http://www.facebook.com/sharer/sharer.php?u=' . $urlencode . '&t=' . $item->getTitle(); ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Facebook'); ?>')" class="sesbasic_icon_btn sesbasic_icon_facebook_btn"><i class="fa fa-facebook"></i></a>
				<a href="<?php echo 'http://twitthis.com/twit?url=' . $urlencode . '&title=' . $item->getTitle(); ?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Twitter')?>')" class="sesbasic_icon_btn sesbasic_icon_twitter_btn"><i class="fa fa-twitter"></i></a>
				<a href="<?php echo 'http://pinterest.com/pin/create/button/?url='.$urlencode; ?>&media=<?php echo urlencode((strpos($item->getPhotoUrl(),'http') === FALSE ? (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://") . $_SERVER['HTTP_HOST'].$item->getPhotoUrl() ) : $item->getPhotoUrl())); ?>&description=<?php echo $item->getTitle();?>" onclick="return socialSharingPopUp(this.href,'<?php echo $this->translate('Pinterest'); ?>')" class="sesbasic_icon_btn sesbasic_icon_pintrest_btn"><i class="fa fa-pinterest"></i></a>
			<?php endif;?>
		</div>  
	<?php endif;?>
  <?php $ownerSelf = $viewer->getIdentity() == $item->owner_id ? true : false; ?>
	<div class="sesevent_review_listing_btn_right floatR">
		<?php if($item->authorization()->isAllowed($viewer, 'edit')) { ?>     
			<a class="fa fa-pencil sesbasic_button sesbasic_button_icon <?php if($ownerSelf) { echo 'sesevent_own_update_review'; } ?>" href="<?php echo $this->url(array('route' => 'seseventreview_view', 'action' => 'edit', 'review_id' => $item->review_id,'format' => 'smoothbox'),'seseventreview_view',true); ?>" <?php if(!$ownerSelf) { ?> onclick='return opensmoothboxurl(this.href);' <?php  } ?> ><span><i class="fa fa-caret-down"></i><?php echo $this->translate('Edit Review'); ?></span></a>
		<?php } ?>
		<?php if($item->authorization()->isAllowed($viewer, 'delete')) { ?>     
		<a class="fa fa-trash sesbasic_button sesbasic_button_icon" href="<?php echo $this->url(array('route' => 'seseventreview_extended', 'action' => 'delete', 'review_id' => $item->review_id,'format' => 'smoothbox'),'seseventreview_extended',true); ?>" onclick='return opensmoothboxurl(this.href);'><span><i class="fa fa-caret-down"></i><?php echo $this->translate('Delete Review'); ?></span></a>
		<?php } ?>
		<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.show.report', 1) && $viewer->getIdentity() && in_array('report', $this->stats) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.report', 1)): ?>
		<a class="fa fa-flag sesbasic_button sesbasic_button_icon" href="<?php echo $this->url(array('route' => 'default', 'module' => 'core', 'controller' => 'report', 'action' => 'create', 'subject' => $item->getGuid(), 'format' => 'smoothbox',),'default',true); ?>" onclick='return opensmoothboxurl(this.href);'><span><i class="fa fa-caret-down"></i><?php echo $this->translate('Report');?></span></a>
		<?php endif; ?>
		<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allow.share', 1) && $viewer->getIdentity() && in_array('share', $this->stats)  && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.sharing', 1)): ?>
		<a class="fa fa-share sesbasic_button sesbasic_button_icon" href="<?php echo $this->url(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $item->getType(), 'id' => $item->getIdentity(), 'format' => 'smoothbox'),'default',true); ?>" onclick='return opensmoothboxurl(this.href);'><span><i class="fa fa-caret-down"></i><?php echo $this->translate('Share Review');?></span></a> 
		<?php endif; ?>
		</div>
</div>