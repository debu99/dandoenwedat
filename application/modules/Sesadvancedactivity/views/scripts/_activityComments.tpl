<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _activityComments.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
   $actions = $this->actions;
} ?>

<?php $this->headScript()
        ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/core.js')
        ->appendFile($this->layout()->staticBaseUrl . 'externals/flowplayer/flowplayer-3.2.13.min.js') ?>

<?php if( !$this->getUpdate ): ?>
<?php endif ?>
  
<?php
  foreach( $actions as $action ): // (goes to the end of the file)
    try { // prevents a bad feed item from destroying the entire page
      // Moved to controller, but the items are kept in memory, so it shouldn't hurt to double-check
      if( !$action->getTypeInfo()->enabled ) continue;
      if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
      if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
      
      ob_start();
    ?>
  <?php if( !$this->noList ): ?><li id="activity-item-<?php echo $action->action_id ?>" data-activity-feed-item="<?php echo $action->action_id ?>"><?php endif; ?>
    <?php $this->commentForm->setActionIdentity($action->action_id) ?>
    <script type="text/javascript">
      (function(){
        var action_id = '<?php echo $action->action_id ?>';
        en4.core.runonce.add(function(){
          $('activity-comment-body-' + action_id).autogrow();
          en4.activity.attachComment($('activity-comment-form-' + action_id));
        });
      })();
    </script>
      <?php
//        $icon_type = 'activity_icon_'.$action->type;
//        list($attachment) = $action->getAttachments();
//        if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ):
//          $icon_type .= ' item_icon_'.$attachment->item->getType() . ' ';
//        endif;
        $coreVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core')->version;
        if(version_compare($coreVersion, '4.8.5') < 0){
          $canComment = ( $action->getTypeInfo()->commentable && $this->viewer()->getIdentity() && Engine_Api::_()->authorization()->isAllowed($action->getObject(), null, 'comment') && !empty($this->commentForm) );
        } else {
          $canComment = ( $action->getTypeInfo()->commentable && $this->viewer()->getIdentity() && Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') && !empty($this->commentForm) );
        }
      ?>
      
      <div class='feed_item_date feed_item_icon sesbasic_clearfix'>
        <ul class="sesbasic_clearfix">
          <?php if( $canComment ): ?>
            <?php if( $action->likes()->isLike($this->viewer()) ): ?>
              <li class="feed_item_option_unlike">
              	<a href="javascript:void(0);" onclick='javascript:en4.activity.unlike("<?php echo $action->action_id ?>")'>
                	<i></i>
                  <span><?php echo $this->translate('SESADVUNLIKE');?></span>
                </a>
              </li>
            <?php else: ?>
              <li class="feed_item_option_like">
              	<a href="javascript:void(0);" onclick='javascript:en4.activity.like("<?php echo $action->action_id ?>")'>
                	<i></i>
                  <span><?php echo $this->translate('SESADVLIKE');?></span>
                </a>
              </li>
            <?php endif; ?>
            <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes ?>
              <li class="feed_item_option_comment">
              
              	<a href="<?php echo $this->url(array('module'=>'sesadvancedactivity','controller'=>'index','action'=>'viewcomment','action_id'=>$action->getIdentity(),'format'=>'smoothbox'),'default',true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('SESADVCOMMMENT');?></span>
                </a>              
              </li>
            <?php else: ?>
              <li class="feed_item_option_comment">
              	<a href="javascript:void(0);" onclick='document.getElementById("<?php echo $this->commentForm->getAttrib('id').'").style.display = ""; document.getElementById("'.$this->commentForm->submit->getAttrib('id').'").style.display = "block"; document.getElementById("'.$this->commentForm->body->getAttrib('id')?>").focus();'>
                	<i></i>
                  <span><?php echo $this->translate('SESADVCOMMMENT');?></span>
                </a>
              </li>
            <?php endif; ?>
            <?php if( $this->viewAllComments ): ?>
              <script type="text/javascript">
                en4.core.runonce.add(function() {
                  document.getElementById('<?php echo $this->commentForm->getAttrib('id') ?>').style.display = "";
                  document.getElementById('<?php echo $this->commentForm->submit->getAttrib('id') ?>').style.display = "block";
                  document.getElementById('<?php echo $this->commentForm->body->getAttrib('id') ?>').focus();
                });
              </script>
            <?php endif ?>
          <?php endif; ?>
          <?php $eneblelikecommentshare = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.eneblelikecommentshare', 1);
          $viewer_id = $this->viewer()->getIdentity(); ?>
          <?php //Show like, comment and share to non loggined member accorditg to admin settings
            if($eneblelikecommentshare && empty($viewer_id)) { ?>
            <li class="feed_item_option_like">
            
              <a onclick="ajasmoothboxopen('<?php echo $this->escape($this->url(array('module'=> 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'format' => 'smoothbox'), 'default' , true)); ?>');return false;" href="javascript:void(0);" class="smoothbox">
                <i></i>
                <span><?php echo $this->translate('Like');?></span>
              </a>
            </li>
            <li class="feed_item_option_comment">
              <a onclick="ajasmoothboxopen('<?php echo $this->escape($this->url(array('module'=> 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'format' => 'smoothbox'), 'default' , true)); ?>');return false;" href="javascript:void(0);" class="smoothbox">
                <i></i>
                <span><?php echo $this->translate('SESADVCOMMMENT');?></span>
              </a>
            </li>
<!--            <li class="feed_item_option_share">
              <a onclick="ajasmoothboxopen('<?php echo $this->escape($this->url(array('module'=> 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'format' => 'smoothbox'), 'default' , true)); ?>');return false;" href="javascript:void(0);" class="smoothbox">
                <i></i>
                <span><?php echo $this->translate('Share');?></span>
              </a>
            </li>-->
          <?php } ?>
          
          <?php // Share ?>
          <?php if( $action->getTypeInfo()->shareable): ?>
            <?php if( $action->getTypeInfo()->shareable == 1 && $action->attachment_count == 1 && ($attachment = $action->getFirstAttachment()) ): ?>
              <li class="feed_item_option_share">
              	<a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(),'action_id'=>$action->getIdentity(), 'format' => 'smoothbox'), 'default', true); ?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('Share');?></span>
                </a>
                <?php // echo $this->htmlLink(array('route' => 'default', 'module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 2 ): ?>
              <li class="feed_item_option_share">
              	<a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $subject->getType(), 'id' => $subject->getIdentity(), 'format' => 'smoothbox','action_id'=>$action->getIdentity()), 'default', true);?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('Share');?></span>
                </a>
                <?php // echo $this->htmlLink(array('route' => 'default', 'module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $subject->getType(), 'id' => $subject->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 3 ): ?>
              <!--<li class="feed_item_option_share">
              	<a href="<?php // echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $object->getType(), 'id' => $object->getIdentity(), 'format' => 'smoothbox'), 'default', true)?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('Share');?></span>
                </a>
                <?php // echo $this->htmlLink(array('route' => 'default', 'module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $object->getType(), 'id' => $object->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>-->
            <?php elseif( $action->getTypeInfo()->shareable == 4 ): ?>
              <li class="feed_item_option_share">
              	<a href="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'format' => 'smoothbox','action_id'=>$action->getIdentity()), 'default', true);?>" class="smoothbox">
                	<i></i>
                  <span><?php echo $this->translate('Share');?></span>
                </a>
                <?php // echo $this->htmlLink(array('route' => 'default', 'module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share'), array('class' => 'smoothbox', 'title' => 'Share')) ?>
              </li>
            <?php endif; ?>
          <?php endif; ?>
        </ul>
      </div>
      
      <?php if( $action->getTypeInfo()->commentable ): // Comments - likes ?>
        <div class='comments' >
          <ul>
            <?php if( $action->likes()->getLikeCount() > 0 && (count($action->likes()->getAllLikesUsers())>0) ): ?>
              <li>
                <div></div>
                <div class="comments_likes">
                  <?php if( $action->likes()->getLikeCount() <= 3 || $this->viewAllLikes ): ?>
                    <?php echo $this->translate(array('%s likes this.', '%s like this.', $action->likes()->getLikeCount()), $this->fluentList($action->likes()->getAllLikesUsers()) )?>

                  <?php else: ?>
                    <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_likes' => true)),
                      $this->translate(array('%s person likes this', '%s people like this', $action->likes()->getLikeCount()), $this->locale()->toNumber($action->likes()->getLikeCount()) )
                    ) ?>
                  <?php endif; ?>
                </div>
              </li>
            <?php endif; ?>
            <?php if( $action->comments()->getCommentCount() > 0 ): ?>
              <?php if( $action->comments()->getCommentCount() > 5 && !$this->viewAllComments): ?>
                <li>
                  <div></div>
                  <div class="comments_viewall">
                    <?php if( $action->comments()->getCommentCount() > 2): ?>
                      <?php echo $this->htmlLink($action->getSubject()->getHref(array('action_id' => $action->action_id, 'show_comments' => true)),
                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
                          $this->locale()->toNumber($action->comments()->getCommentCount()))) ?>
                    <?php else: ?>
                      <?php echo $this->htmlLink('javascript:void(0);',
                          $this->translate(array('View all %s comment', 'View all %s comments', $action->comments()->getCommentCount()),
                          $this->locale()->toNumber($action->comments()->getCommentCount())),
                          array('onclick'=>'en4.activity.viewComments('.$action->action_id.');')) ?>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endif; ?>
              <?php foreach( $action->getComments($this->viewAllComments,'','') as $comment ): ?>
                <li id="comment-<?php echo $comment->comment_id ?>">
                  <div class="comments_author_photo">
                    <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(),
                      $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $action->getSubject()->getTitle())
                    ) ?>
                  </div>
                  <div class="comments_info">
                  
                   <?php if ( $this->viewer()->getIdentity() &&
                             (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                              ($this->viewer()->getIdentity() == $comment->poster_id) ||
                              $this->activity_moderate ) ): ?>
                   <span class="sesact_comments_delete">
                     <?php echo $this->htmlLink(array('route'=>'default', 'module'    => 'sesadvancedactivity', 'controller'=> 'index', 'action' => 'delete', 'action_id' => $action->action_id, 'comment_id'=> $comment->comment_id,), $this->translate(''), array('title', $this->translate("delete"), 'class' => 'smoothbox fas fa-times')) ?>
                   </span>
                    <?php endif; ?>
                      
                   <span class='comments_author'>
                     <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle()); ?>
                   </span>
                   <span class="comments_body">
                     <?php echo $this->viewMore($comment->body) ?>
                   </span>
                   <ul class="comments_date">
                     <li class="comments_timestamp">
                       <?php echo $this->timestamp($comment->creation_date); ?>
                     </li>
                      <?php if( $canComment ):
                        $isLiked = $comment->likes()->isLike($this->viewer());
                      ?>
                        <li class="sep">-</li>
                        <li class="comments_like">
                          <?php if( !$isLiked ): ?>
                            <a href="javascript:void(0)" onclick="en4.activity.like(<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>)">
                              <?php echo $this->translate('like') ?>
                            </a>
                          <?php else: ?>
                            <a href="javascript:void(0)" onclick="en4.activity.unlike(<?php echo sprintf("'%d', %d", $action->getIdentity(), $comment->getIdentity()) ?>)">
                              <?php echo $this->translate('unlike') ?>
                            </a>
                          <?php endif ?>
                        </li>
                      <?php endif ?>
                      <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
                        <li class="sep">-</li>
                        <li class="comments_likes_total">
                          <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
                            <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
                          </a>
                        </li>
                      <?php endif ?>
                    </ul>
                  </div>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <?php if( $canComment ) echo $this->commentForm->render(); ?>
         
      <?php endif; ?>
</div>
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

<?php if( !$this->getUpdate ): ?>
<?php endif ?>

<script type="text/javascript">
//open ajaxsmothbox
function ajasmoothboxopen(redirectURL) { 
  Smoothbox.open(redirectURL);
  parent.Smoothbox.close;
}
</script>