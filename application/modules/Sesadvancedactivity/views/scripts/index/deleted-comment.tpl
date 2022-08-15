<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: delete-comment.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
if(!empty($this->commentCount)){
  $commentcount =  $this->translate(array('%s comment', '%s comments',  $this->commentCount), $this->locale()->toNumber( $this->commentCount));
}
?>
<script type="text/javascript">
  parent.$('comment-<?php echo $this->comment_id ?>').destroy();
  <?php if(!empty($commentcount)){ ?>
    parent.sesJqueryObject('.comment_stats_<?php echo $this->action->getIdentity(); ?>').find('.comment_btn_open').html('<?php echo $commentcount; ?>');
  <?php }else{ ?>
    parent.sesJqueryObject('.comment_stats_<?php echo $this->action->getIdentity(); ?>').remove();
  <?php } ?>
  setTimeout(function()
  {
    parent.Smoothbox.close();
  }, 1000 );
</script>

  <div class="global_form_popup_message">
    <?php echo $this->message ?>
  </div>