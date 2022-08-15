<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _subjectcommentbodyoptions.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php 
  $canComment = $this->canComment;
  $comment = $this->comment;
  $actionBody = $this->actionBody;
  $isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
  
?>
<ul class="comments_date" id="comments_reply_<?php echo $comment->comment_id; ?>_<?php echo $actionBody->getIdentity(); ?>" style="display:block;">
  
    <?php if( $canComment ): ?>
    <?php $isLiked = $comment->likes()->isLike($isPageSubject); ?>
    <?php if( $this->viewer()->getIdentity() && $this->canComment ):
      if($likeRow =  $comment->likes()->getLike($isPageSubject)){
      
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
          $text = 'SESADVLIKE';
       }
       ?>
        <li class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.reactionenable', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
          <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
          <?php if(count($getReactions) > 0): ?>
            <div class="sesadvcmt_hoverbox">
              <?php foreach($getReactions as $getReaction): ?>
                <span>
                  <span  data-text="<?php echo $this->translate($getReaction->title);?>" data-sbjecttype="<?php echo $actionBody->getType(); ?>" data-subjectid="<?php echo $actionBody->getIdentity(); ?>" data-actionid="<?php echo  $actionBody->getIdentity(); ?>" data-commentid = "<?php echo  $comment->getIdentity(); ?>"  data-type="<?php echo $getReaction->reaction_id; ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>"  class="sesadvancedcommentcommentlike reaction_btn sesadvcmt_hoverbox_btn"><div class="reaction sesadvcmt_hoverbox_btn_icon"> <i class="react"  style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($getReaction->reaction_id);?>)"></i> </div></span>
                  <div class="text">
                    <div><?php echo $this->translate($getReaction->title); ?></div>
                  </div>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <a href="javascript:void(0);" <?php if(!empty($_SESSION["sesfromLightbox"])){ ?> id="sesadvancedcomment_like_action_<?php echo $comment->getIdentity(); ?>" <?php $_SESSION["sesfromLightbox"] = ''; }else{ ?> id="sesadvancedcomment_like_actionrec_<?php echo $comment->getIdentity(); ?>" <?php } ?> data-like="<?php echo $this->translate('SESADVLIKEC') ?>" data-unlike="<?php echo $this->translate('SESADVUNLIKEC') ?>" data-actionid="<?php echo  $actionBody->getIdentity(); ?>" data-commentid = "<?php echo  $comment->getIdentity(); ?>" data-sbjecttype="<?php echo $actionBody->getType(); ?>" data-subjectid="<?php echo $actionBody->getIdentity(); ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>" data-type="1" class="sesadvancedcommentcomment<?php echo $like ? 'unlike _reaction' : 'like' ;  ?>">
            <i style="background-image:url(<?php echo $imageLike; ?>)"></i>
            <span><?php echo $this->translate($text);?></span>
          </a> 
        </li>
    <?php endif; ?>
    <li class="sep">&middot;</li> 
  <?php endif ?>
                             
     <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
    <?php $likesGroup = Engine_Api::_()->sesadvancedcomment()->commentLikesGroup($comment,false); 
      if(count($likesGroup['data'])){ 
    ?>
    <li class="comments_likes_total">
       <span class="comments_likes_reactions">
       <?php foreach($likesGroup['data'] as $type){ ?>
        <a title="<?php echo $this->translate('%s (%s)',$type['counts'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type'])) ?>" href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedcomment', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $comment->getIdentity(), 'id' => $actionBody->getIdentity(),'resource_type'=>$actionBody->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><i style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']);?>);"></i></a>
        <?php } ?>
      </span>
        <a href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedcomment', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $comment->getIdentity(), 'id' => $actionBody->getIdentity(),'resource_type'=>$actionBody->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><?php echo $comment->likes()->getLikeCount(); ?></a>
    </li>
    <li class="sep">&middot;</li>
    <?php } ?>
  <?php endif ?>
  	<?php if(empty($_SESSION['fromActivityFeed']) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablencommentupdownvote', 1)){ ?>
      <?php echo $this->partial('_updownvote.tpl', 'sesadvancedcomment', array('item' => $comment,'isPageSubject'=>$isPageSubject)); ?>
    	<li class="sep">&middot;</li>
    <?php } ?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablenestedcomments', 1)): ?>
      <li class="comments_reply">
        <?php echo $this->htmlLink('javascript:;', $this->translate('SESADVREPLY'), array('class' => 'sesadvancedcommentreply')) ?>
      </li>
      <li class="sep">&middot;</li>
    <?php endif; ?>  
    
    <li class="comments_timestamp">
    	<?php echo $this->timestamp($comment->creation_date); ?>
   	</li>
  </ul>
