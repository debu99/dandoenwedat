<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _activityComments.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>

<?php if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
   $actions = $this->actions;
}
  $isOnThisDayPage = !empty($this->isOnThisDayPage) ? true : false;
  $isPageSubject = empty($this->isPageSubject) ? $this->viewer() : $this->isPageSubject;
  $params = !empty($this->params) ? $this->params : '';
  
 ?>

<?php if( !$this->getUpdate && $this->onlyComment): ?>
<ul class='comment-feed'>
<?php endif ?>
<?php
  foreach( $actions as $action ): // (goes to the end of the file)

    $detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
    if($detail_id) {
      $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
    }

    try { // prevents a bad feed item from destroying the entire page
      // Moved to controller, but the items are kept in memory, so it shouldn't 'hurt to double-check
      if( !$action->getTypeInfo()->enabled ) continue;
      if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
      if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
      ob_start();
    ?>
  <?php if( !$this->noList && $this->onlyComment): ?>
  <li id="activity-item-<?php echo $action->action_id ?>" data-activity-feed-item="<?php echo $action->action_id ?>"><?php endif; ?>
      <?php
        $canComment = ( $action->getTypeInfo()->commentable &&
            $this->viewer()->getIdentity() &&
            Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') &&
            !empty($this->commentForm) );
      ?>
     <?php if($this->onlyComment){ ?>
      <div class='feed_item_date feed_item_icon <?php // echo $icon_type ?>'>
        <ul>

        <?php if($detailAction && $detailAction->commentable && !$isOnThisDayPage){ ?>
          <?php if( $canComment ): ?>
            <?php 
             if($likeRow =  $action->likes()->getLike($isPageSubject) ){ 
                if($likeRow->getType() == 'activity_like') {
                  $item_activity_like = Engine_Api::_()->getDbTable('activitylikes', 'sesadvancedactivity')->rowExists($likeRow->like_id); 
                } else {
                  $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($likeRow->like_id); 
                }
                $like = true;
                if($item_activity_like)
                  $type = $item_activity_like->type;
                else 
                  $type = 1;
                $imageLike = Engine_Api::_()->sesadvancedcomment()->likeImage($type);
                $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
             }else{
                $like = false;
                $type = '';
                $imageLike = '';
                $text = 'SESADVLIKEC';
             }
             ?>
              <li class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.reactionenable', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
                <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
                <?php if(count($getReactions) > 0): ?>
                  <div class="sesadvcmt_hoverbox">
                    <?php foreach($getReactions as $getReaction): ?>
                      <span>
                        <span data-text="<?php echo $this->translate($getReaction->title);?>" data-actionid = "<?php echo  $action->action_id; ?>" data-type="<?php echo $getReaction->reaction_id; ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>" class="sesadvancedcommentlike reaction_btn sesadvcmt_hoverbox_btn"><div class="reaction sesadvcmt_hoverbox_btn_icon"> <i class="react"  style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($getReaction->reaction_id);?>)"></i> </div></span>
                        <div class="text">
                          <div><?php echo $this->translate($getReaction->title); ?></div>
                        </div>
                      </span> 
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <a href="javascript:void(0);" data-guid="<?php echo $isPageSubject->getGuid(); ?>" data-like="<?php echo $this->translate('SESADVLIKEC') ?>" data-unlike="<?php echo $this->translate('SESADVUNLIKEC') ?>" data-actionid = "<?php echo  $action->action_id; ?>" data-type="1" class="sesadvancedcomment<?php echo $like ? 'unlike _reaction' : 'like' ;  ?>">
                  <i <?php if($imageLike){ ?> style="background-image:url(<?php echo $imageLike; ?>)" <?php } ?>></i>
                  <span><?php echo $this->translate($text);?></span>
                </a> 
              </li>
            <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes ?>
              <li class="feed_item_option_comment">
              	<a id="adv_comment_btn_<?php echo $action->getIdentity(); ?>" href="<?php echo $this->url(array('module'=>'sesadvancedactivity','controller'=>'index','action'=>'viewcomment','action_id'=>$action->getIdentity(),'format'=>'smoothbox'),'default',true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVCOMMENT');?></span>
                </a>              
              </li>
            <?php else: ?>
              <li class="feed_item_option_comment">
              	<a href="javascript:void(0);" id="adv_comment_btn_<?php echo $action->getIdentity(); ?>" class="sesadvanced_comment_btn">
                	<i></i>
                  <span><?php echo $this->translate('SESADVCOMMENT');?></span>
                </a>
              </li>
            <?php endif; ?>
          <?php endif; ?>
        <?php } ?>  
          <?php $eneblelikecommentshare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.eneblelikecommentshare', 1);
          $viewer_id = $this->viewer()->getIdentity(); ?>
          <?php //Show like, comment and share to non loggined member accorditg to admin settings
            if($eneblelikecommentshare && empty($viewer_id)) { ?>
            <li class="feed_item_option_like">
              <a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'format' => 'smoothbox'), 'default', true); ?>" class="smoothbox">
                <i></i>
                <span><?php echo $this->translate('SESADVLIKEC');?></span>
              </a>
            </li>
            <li class="feed_item_option_comment">
              <a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'format' => 'smoothbox'), 'default', true); ?>" class="smoothbox">
                <i></i>
                <span><?php echo $this->translate('SESADVCOMMENT');?></span>
              </a>
            </li>
<!--            <li class="feed_item_option_share">
              <a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'format' => 'smoothbox'), 'default', true); ?>" class="smoothbox">
                <i></i>
                <span><?php echo $this->translate('SESADVSHARE');?></span>
              </a>
            </li>-->
          <?php } ?>
          
          <?php // Share ?>
          <?php if(empty($_SESSION['fromActivityFeed'])){ ?>
          <?php if( $action->getTypeInfo()->shareable): ?>
            <?php if( $action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment('comment')) ): ?>
              <li class="feed_item_option_share <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesocialshare', 1) || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
                <?php 
                $AdvShare = $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(), 'format' => 'smoothbox'), 'default', true);
                
                echo $this->partial('_share.tpl', 'sesadvancedcomment', array('href' => $attachment->item->getHref(),'action' => $action,'AdvShare'=> $AdvShare)); ?>
              	<a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(), 'format' => 'smoothbox','action_id'=>$action->getIdentity()), 'default', true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVSHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 2 ): ?>
              <li class="feed_item_option_share <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesocialshare', 1) || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
                 <?php echo $this->partial('_share.tpl', 'sesadvancedcomment', array('href' => $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getSubject()->getType(), 'id' => $action->getSubject()->getIdentity(), 'format' => 'smoothbox'), 'default', true),'action' => $action)); ?>
                <a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getSubject()->getType(), 'id' => $action->getSubject()->getIdentity(), 'format' => 'smoothbox'), 'default', true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVSHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 3 ): ?>
              <li class="feed_item_option_share <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesocialshare', 1) || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
                 <?php echo $this->partial('_share.tpl', 'sesadvancedcomment', array('href' => $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getObject()->getType(), 'id' => $action->getObject()->getIdentity(), 'format' => 'smoothbox'), 'default', true), 'action' => $action)); ?>
                <a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getObject()->getType(), 'id' => $action->getObject()->getIdentity(), 'format' => 'smoothbox','action_id'=>$action->getIdentity()), 'default', true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVSHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 4 ): ?>
              <li class="feed_item_option_share <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesocialshare', 1) || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
								<?php //echo $this->partial('_share.tpl', 'sesadvancedcomment', array('href' => $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity()), 'default', true),'action' => $action));
                  echo $this->partial('_share.tpl', 'sesadvancedcomment', array('href' => $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'format' => 'smoothbox'), 'default', true),'action' => $action));
                 ?>
              	<a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(),'action_id'=>$action->getIdentity()), 'default', true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVSHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 5 ):
                  $attachment = $action->getBuySellItem();
             ?>
              <li class="feed_item_option_share <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesocialshare', 1) || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
                <?php echo $this->partial('_share.tpl', 'sesadvancedcomment', array('href' => $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->getType(), 'id' => $attachment->getIdentity(), 'format' => 'smoothbox'), 'default', true),'action' => $action)); ?>
                <a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->getType(), 'id' => $attachment->getIdentity(), 'format' => 'smoothbox','action_id'=>$action->getIdentity()), 'default', true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVSHARE');?></span>
                </a>
              </li>
            <?php endif; ?>
          <?php endif; ?>
          <?php } ?>
          <?php if( @$icon_type == 'activity_icon_signup'){ ?>
            <?php if($this->viewer()->getIdentity() != 0):?>
            <?php echo '<span>'.$this->partial('_addfriend.tpl', 'sesbasic', array('subject' => $action->getSubject())).'</span>'; ?>
          <?php endif;?>
          <?php } ?> 
          
          
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablenactivityupdownvote', 1)){ ?>
            <?php echo $this->partial('_updownvote.tpl', 'sesadvancedcomment', array('item' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
          <?php } ?>
          
          <?php echo $this->partial('_sespage_content.tpl', 'sesadvancedcomment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
          <?php echo $this->partial('_sesgroup_content.tpl', 'sesadvancedcomment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
          <?php echo $this->partial('_sesbusiness_content.tpl', 'sesadvancedcomment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
            <?php echo $this->partial('_estore_content.tpl', 'sesadvancedcomment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
        </ul>
      </div>
     <?php } ?>
    <?php if($detailAction && $detailAction->commentable){ ?>
      <?php if( $action->getTypeInfo()->commentable ): // Comments - likes ?>
      <?php if($this->onlyComment){ ?>
       <div class='comments sesadvcmt_comments' >
          <?php if( $canComment && !$isOnThisDayPage ){ ?>
            <form name="myForm"  class="sesadvancedactivity-comment-form advcomment_form" method="post" style="display:<?php echo ( $action->comments()->getCommentCount() > 0 || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.opencommentbox', 1)) ? 'block' : 'none';  ?>">
              <div class="comments_author_photo comment_usr_img">
              <?php
                echo $this->itemPhoto($isPageSubject, 'thumb.icon', $isPageSubject->getTitle());
                ?>
              </div>
          <?php
          $session = new Zend_Session_Namespace('sesadvcomment');
           $albumenable = $session->albumenable;
           $videoenable = $session->videoenable;
           //$enableattachement = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enableattachement', ''));
           $viewer = Engine_Api::_()->user()->getViewer();
           $enableattachement = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'cmtattachement');
        ?>
              <div class="_form_container sesbasic_clearfix">
                <div class="comment_form sesbasic_clearfix">
                  <textarea  class="body" name="body" cols="45" rows="1" placeholder="<?php echo $this->translate('Write a comment...'); ?>"  id="comment<?php echo $action->getIdentity();?>"></textarea><span><?php $commentTextarea_id = $action->getIdentity(); ?></span>
                 
                  <div class="_sesadvcmt_post_icons sesbasic_clearfix">
                    <span>
                      <?php if($albumenable && Engine_Api::_()->authorization()->isAllowed('album', null, 'create')){ ?>
                        <a href="javascript:;" class="sesadv_tooltip file_comment_select"  title="<?php echo $this->translate('Attach 1 or more Photos'); ?>"></a>
                      <?php } ?>
                      <input type="file" name="Filedata" class="select_file" multiple value="0" style="display:none;">
                      <input type="hidden" name="emoji_id" class="select_emoji_id" value="0" style="display:none;">
                      <input type="hidden" name="gif_id" class="select_gif_id" value="0" style="display:none;">
                      <input type="hidden" name="file_id" class="file_id" value="0">
                      <input type="hidden" class="file" name="action_id" value="<?php echo $action->getIdentity(); ?>">
                      </span>
                   <?php if($videoenable && Engine_Api::_()->authorization()->isAllowed('video', $viewer, 'create')){ ?>
                      <span><a href="javascript:;" class="sesadv_tooltip video_comment_select" title="<?php echo $this->translate('Attach 1 or more Videos'); ?>"></a></span>
                    <?php } ?>
                    <?php if(in_array('emotions', $enableattachement) || in_array('stickers', $enableattachement)) { ?>
                      <span class="sesact_post_tool_i tool_i_emoji">
                        <a  href="javascript:;" class="sesadv_tooltip emoji_comment_select"  title="<?php if(in_array('stickers', $enableattachement)) { ?><?php echo $this->translate('Post a Sticker'); ?><?php } ?>"  onclick="setCommentFocus(<?php echo $action->getIdentity(); ?>);">&nbsp;</a>
                      </span>
                    <?php } ?>
                    
                    <?php //GIF Work ?>
                    <?php if(defined('SESFEEDGIFENABLED') && in_array('gif', $enableattachement)) {  ?>
                      <?php $enable = Engine_Api::_()->authorization()->isAllowed('sesfeedgif', null, 'enablecommentgif'); ?>
                     
                      <?php if($enable) { ?>
                        <span class="sesact_post_tool_i tool_i_gif">
                          <a  href="javascript:;" class="sesadv_tooltip gif_comment_select" title="<?php echo $this->translate('Post GIF'); ?>" onclick="setCommentFocus(<?php echo $action->getIdentity(); ?>);">&nbsp;</a>
                        </span>
                      <?php } ?>
                    <?php } //GIF Work ?>
                    
                    <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji') && in_array('emojis', $enableattachement)) {
                      $enableemojis = Engine_Api::_()->authorization()->isAllowed('sesemoji', null, 'enableemojis');
                      $getEmojis = Engine_Api::_()->getDbTable('emojis', 'sesemoji')->getEmojis(array('fetchAll' => 1)); 
                      if(count($getEmojis) > 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesemoji.enableemoji', 1) && $enableemojis) { ?>
                      <span class="sesact_post_tool_i tool_i_feelings">
                        <a href="javascript:;" class="sesadv_tooltip feeling_emoji_comment_select" title="<?php echo $this->translate('Post Emojis'); ?>">&nbsp;</a>
                      </span>
                    <?php } ?>
                    <?php } ?>
                  </div>

                </div>
                <div class="uploaded_file" style="display:none;"></div>
                <button type="submit"><?php echo $this->translate("Post Comment"); ?></button>
              </div>
              </form>
          <?php } ?>
          <ul class="comments_cnt_ul">
              <?php
                   echo $this->partial(
                      '_activitylikereaction.tpl',
                      'sesadvancedcomment',
                      array('comment'=>@$comment,'action'=>$action,'isOnThisDayPage'=>$isOnThisDayPage,'isPageSubject'=>$this->isPageSubject)
                    );                    
                  ?>
          <?php  } ?>  
            <li class="sesadvcmt_comments_share" style="display:none;"><div class="sesadvcmt_comments_share_count"><a href="">124 Shares</a></div></li>
            <?php $commentCount = Engine_Api::_()->sesadvancedcomment()->commentCount($action); ?>
            <?php if( $commentCount > 0 && !$isOnThisDayPage):   
              ?>
              <?php foreach($this->comments as $comment):?>
                <?php
              
                   echo $this->partial(
                      '_activitycommentbody.tpl',
                      'sesadvancedcomment',
                      array('comment'=>$comment,'action'=>$action,'isPageSubject'=>$this->isPageSubject)
                    );                    
                  ?>
              <?php endforeach; ?>
              <?php if($this->comments->count() != 0 && $this->comments->getCurrentPageNumber() < $this->comments->count() ): ?>
              <li class="comment_view_more">
                <div> </div>
                <div class="comments_viewall">
                  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View later comments'), array(
                    'onclick' => 'sesadvancedcommentactivitycomment("'.$action->getIdentity().'", "'.($this->comments->getCurrentPageNumber() + 1).'",this)'
                  )) ?>
                </div>
              </li>
            <?php endif; ?>
            <?php endif; ?>
            
           <?php if($this->onlyComment){ ?> 
          </ul>
        </div> 
        <?php } ?>
      <?php endif; ?>
    <?php } ?>
   <!--  </div> -->
  <?php if( !$this->noList ): ?></li><?php endif; ?>
<?php
      ob_end_flush();
    } catch (Exception $e) {
      ob_end_clean();
      if( APPLICATION_ENV === 'development' ) {
        echo $e->__toString();
      }
    };
  endforeach;
?>
<?php if( !$this->getUpdate && $this->onlyComment):  ?>
</ul>
<?php endif ?>
