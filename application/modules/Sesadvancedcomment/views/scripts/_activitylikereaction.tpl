<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _activitylikereaction.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $action = $this->action; 
      $isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
?>
<?php 
   $likesGroup = Engine_Api::_()->sesadvancedcomment()->likesGroup($action);   
   $commentCount = Engine_Api::_()->sesadvancedcomment()->commentCount($action);
   if($commentCount || count($likesGroup['data'])){
?>
<li class="sesadvcmt_comments_stats">
<?php if(count($likesGroup['data'])){ ?>
  <div class="comments_stats_likes">
    <span class="comments_likes_reactions">
     <?php foreach($likesGroup['data'] as $type){ ?>
      <a title="<?php echo $this->translate('%s (%s)',$type['counts'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type'])) ?>" href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'ajax', 'action' => 'likes', 'type' => $type['type'], 'id' => $action->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>"><i style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']);?>);"></i></a>
      <?php } ?>
    </span>
      <a href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'ajax', 'action' => 'likes', 'type' => '', 'id' => $action->getIdentity(),'resource_type'=>$likesGroup['resource_type'],'item_id'=>$likesGroup['resource_id'], 'format' => 'smoothbox'), 'default', true); ?>"> <?php echo $this->FluentListUsers($action->likes()->getAllLikes(),'',$action->likes()->getLike($this->viewer()),$this->viewer()); ?></a>
    
  </div>
<?php } ?>
<?php if(!$this->isOnThisDayPage){ ?>
  <div class="comments_stats_comments comment_stats_<?php echo $action->getIdentity(); ?>">
    <?php if($commentCount > 0){ ?>
      <?php echo $this->partial('_commentstats.tpl','sesadvancedcomment',array('action'=>$action,'commentCount'=>$commentCount,'isPageSubject'=>$this->isPageSubject));  ?>
    <?php } ?>
  </div>
<?php } ?>                
</li>
<?php } ?>