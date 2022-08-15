<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _commentstats.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php 
  $action = $this->action;
  $commentCount = $this->commentCount;
  $isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
  $enableordering = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enableordering', 'a:4:{i:0;s:6:"newest";i:1;s:6:"oldest";i:2;s:5:"liked";i:3;s:7:"replied";}'));
?>
<a class="comment_btn_open select_action_<?php echo $action->getIdentity(); ?>" data-actionid = "<?php echo $action->getIdentity(); ?>" href="javascript:void(0);"><?php echo $this->translate(array('%s comment', '%s comments',  $commentCount), $this->locale()->toNumber( $commentCount))?></a>
<?php  $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false); ?>

<?php if(!empty($enableordering)) { ?>
&nbsp;&middot;&nbsp;
<div class="sesadvcmt_pulldown_wrapper sesact_pulldown_wrapper" data-actionid = "<?php echo $action->getIdentity(); ?>">
  <a href="javascript:void(0);" class="search_advcomment_txt"><span> <?php echo $this->translate($reverseOrder) ? $this->translate('Newest') : $this->translate('Oldest') ?> </span> <i class="fa fa-angle-down"></i></a>
  <div class="sesadvcmt_pulldown">
    <div class="sesadvcmt_pulldown_cont">
      <ul class="search_adv_comment">
        <?php if(in_array('newest', $enableordering)): ?>
        <li><a href="javascript:;" <?php echo $reverseOrder ? 'active' : '' ?> data-type="newest" class="search_adv_comment_a"><?php echo $this->translate("Newest"); ?></a></li>
        <?php endif; ?>
        <?php if(in_array('oldest', $enableordering)): ?>
        <li><a href="javascript:;"  <?php echo !$reverseOrder ? 'active' : '' ?>  data-type="oldest" class="search_adv_comment_a"><?php echo $this->translate("Oldest"); ?></a></li>
        <?php endif; ?>
        <?php if(in_array('liked', $enableordering)): ?>
        <li><a href="javascript:;" data-type="liked" class="search_adv_comment_a"><?php echo $this->translate("Liked"); ?></a></li>
        <?php endif; ?>
        <?php if(in_array('replied', $enableordering)): ?>
        <li><a href="javascript:;" data-type="replied" class="search_adv_comment_a"><?php echo $this->translate("Replied"); ?></a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php } ?>