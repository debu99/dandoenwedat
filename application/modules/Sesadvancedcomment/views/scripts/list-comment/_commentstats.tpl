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
<?php $subject = $this->subject;
  $commentCount = $this->commentCount;
  $enableordering = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enableordering', 'a:4:{i:0;s:6:"newest";i:1;s:6:"oldest";i:2;s:5:"liked";i:3;s:7:"replied";}'));
?>
<a class="comment_btn_open select_action_<?php echo $subject->getIdentity(); ?>" data-subjectid = "<?php echo $subject->getIdentity(); ?>" data-subjecttype = "<?php echo $subject->getType(); ?>" href="javascript:void(0);"><?php echo $this->translate(array('%s comment', '%s comments',  $commentCount), $this->locale()->toNumber( $commentCount))?></a>

<?php if(!empty($enableordering)) { ?>
&nbsp;&middot;&nbsp;
<?php  $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false); ?>
<div class="sesadvcmt_pulldown_wrapper sesact_pulldown_wrapper" data-actionid = "<?php echo $subject->getIdentity(); ?>">
  <a href="javascript:void(0);" class="search_advcomment_txt"><span> <?php echo $this->translate($reverseOrder) ? $this->translate('Newest') : $this->translate('Oldest') ?></span> <i class="fa fa-angle-down"></i></a>
  <div class="sesadvcmt_pulldown">
    <div class="sesadvcmt_pulldown_cont">
      <ul class="search_adv_comment">
        <?php if(in_array('newest', $enableordering)): ?>
          <li><a href="javascript:;" data-subjectype="<?php echo $subject->getType(); ?>" data-type="newest" class="subject search_adv_comment_a <?php echo $reverseOrder ? 'active' : '' ?>"><?php echo $this->translate("Newest"); ?></a></li>
        <?php endif; ?>
        <?php if(in_array('oldest', $enableordering)): ?>
          <li><a href="javascript:;"  data-subjectype="<?php echo $subject->getType(); ?>" data-type="oldest" class="subject search_adv_comment_a <?php echo !$reverseOrder ? 'active' : '' ?> "><?php echo $this->translate("Oldest"); ?></a></li>
        <?php endif; ?>
        <?php if(in_array('liked', $enableordering)): ?>
          <li><a href="javascript:;" data-subjectype="<?php echo $subject->getType(); ?>" data-type="liked" class="subject search_adv_comment_a"><?php echo $this->translate("Liked"); ?></a></li>
        <?php endif; ?>
        <?php if(in_array('replied', $enableordering)): ?>
          <li><a href="javascript:;" data-subjectype="<?php echo $subject->getType(); ?>" data-type="replied" class="subject search_adv_comment_a"><?php echo $this->translate("Replied"); ?></a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php } ?>