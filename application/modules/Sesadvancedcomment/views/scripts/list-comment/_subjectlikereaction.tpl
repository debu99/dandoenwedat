<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _subjectlikereaction.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $subject = !empty($this->subject) ? $this->subject : $this->action; ?>
<?php 
   $likesGroup = Engine_Api::_()->sesadvancedcomment()->likesGroup($subject,'subject'); 
   $commentCount = Engine_Api::_()->sesadvancedcomment()->commentCount($subject,'subject');
   if($commentCount || count($likesGroup['data'])){
?>
<li class="sesadvcmt_comments_stats">
<?php if(count($likesGroup['data'])){ ?>
  <div class="comments_stats_likes">
    <span class="comments_likes_reactions">
     <?php foreach($likesGroup['data'] as $type){ ?>
      <a title="<?php echo $this->translate('%s (%s)',$type['counts'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type'])) ?>" href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedcomment', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $subject->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>"><i style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']);?>);"></i></a>
      <?php } ?>
    </span>
      <a href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedcomment', 'controller' => 'ajax', 'action' => 'likes', 'type' => '', 'id' => $subject->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>"> <?php echo $this->FluentListUsers($subject->likes()->getAllLikesUsers(),'',$subject->likes()->getLike($this->viewer()),$this->viewer()); ?></a>
  </div>
<?php } ?>
  <div class="comments_stats_comments  comment_stats_<?php echo $subject->getIdentity(); ?>"">
    <?php if($commentCount > 0){ ?>
      <?php echo $this->partial('list-comment/_commentstats.tpl','sesadvancedcomment',array('subject'=>$subject,'commentCount'=>$commentCount));  ?>
  </div>
<?php } ?>                
</li>
<?php } ?>