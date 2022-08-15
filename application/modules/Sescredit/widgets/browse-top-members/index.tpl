<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $randonNumber = $this->widgetId; ?>
<?php if(!$this->is_ajax){ ?>
<div class="sescredit_browse_members sesbasic_bxs">
  <ul id="sescredit_top_members">
<?php } ?>
    <?php $i=$this->rank;?>
    <?php foreach($this->paginator as $member):?>
      <li class="sescredit_browse_members_item sesbasic_clearfix">
        <div class="_position sesbasic_text_hl"><?php echo $i++; ?></div>    
        <div class="_thumb">
          <a href="<?php echo $member->getHref();?>"><?php echo $this->itemPhoto($member, 'thumb.icon', $member->getTitle());?></a>
        </div>
        <div class="_cont">
          <div class="_title"><a href="<?php echo $member->getHref();?>"><?php echo $member->displayname;?></a></div>
          <div class="_points">
            <span><i class="sescredit_icon16 sescredit_icon_points"></i><span><?php echo $member->total_credit;?></span></span>
            <span><i class="sescredit_icon16 sescredit_icon_badge"></i><span><?php echo !empty($member>badgeCount) ? $member->badgeCount : 0;?></span></span>
          </div>
        </div>
        <div class="_buttons">
          <?php if($this->friendButton):?>
            <span><?php echo $this->userFriendship($member) ?></span>
          <?php endif;?>
          <?php if ($this->followButton && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')&& !empty(Engine_Api::_()->user()->getViewer()->getIdentity())): ?>
						<span>
							<?php $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($member->user_id);?>
							<?php $followClass = (!$FollowUser) ? 'fa-check' : 'fa-times' ;?>
							<?php $followText = ($FollowUser) ?  $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow')) : $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow'));?>
							<a href="javascript:void(0);" data-url='<?php echo $member->user_id; ?>' class="sesbasic_animation sesbasic_link_btn sesmember_follow_user sesmember_follow_user_<?php echo $member->user_id; ?>"><i class="fa <?php echo $followClass;?>"></i><span><?php echo $followText; ?></span></a>
						</span>
          <?php endif;?>
        </div>
      </li>
    <?php endforeach;?>
<?php if(!$this->is_ajax){ ?>
  </ul>
</div>
<?php } ?>
<?php if(!$this->is_ajax):?>
  <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" ><a href="javascript:void(0);" class="sesbasic_animation sesbasic_link_btn" id="feed_viewmore_link_<?php echo $randonNumber; ?>"><i class="fa fa-sync"></i><span><?php echo $this->translate('View More');?></span></a></div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"> <span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span></div>
<?php endif;?>
<script type='text/javascript'>
  //Start Pagination Work
  var requestViewMore_<?php echo $randonNumber; ?>;
  var identity<?php echo $randonNumber; ?>  = '<?php echo $randonNumber; ?>';
  var page<?php echo $randonNumber; ?> = '<?php echo $this->page + 1; ?>';
  viewMoreHide_<?php echo $randonNumber; ?>();	
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    if ($('view_more_<?php echo $randonNumber; ?>'))
    $('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
  }
  function viewMore_<?php echo $randonNumber; ?> (){
    sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
    sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
    if(typeof requestViewMore_<?php echo $randonNumber; ?> != 'undefined')
    requestViewMore_<?php echo $randonNumber; ?>.cancel();
    requestViewMore_<?php echo $randonNumber; ?> = new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/mod/sescredit/id/<?php echo $this->widgetId; ?>/name/<?php echo $this->widgetName; ?>",
      'data': {
        format: 'html',
        page: page<?php echo $randonNumber; ?>,     
        is_ajax : 1,
        view_more:1,
        widget_id: '<?php echo $this->widgetId;?>',
        limit: '<?php echo $this->limit;?>',
        followButton: '<?php echo $this->followButton;?>',
        friendButton: '<?php echo $this->friendButton;?>',
        rank:'<?php echo $i;?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject('#sescredit_top_members').append(responseHTML);console.log(sesJqueryObject('#sescredit_top_members'));
        sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').hide(); 
        viewMoreHide_<?php echo $randonNumber; ?>();	
      }
    });
    requestViewMore_<?php echo $randonNumber; ?>.send();
    return false;
  }
  //End Pagination Work
</script>
