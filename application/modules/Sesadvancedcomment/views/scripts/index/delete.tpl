<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: delete.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<div class='sesact_delete_popup'>
  <?php if (empty($this->comment_id)): ?>
    <?php $id = 'sesact_adv_delete'; ?>
  <?php else: ?>
    <?php $id = 'sesact_adv_comment_delete'; ?>
  <?php endif; ?>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>" id="<?php echo $id; ?>">
  <input class="hidden_actn" type="hidden" name="action_id" value="<?php echo (int) $this->action_id ?>"/>
      <div class="sesact_delete_popup_head">
        <?php if (!empty($this->comment_id)): ?>
        <?php echo $this->translate("Delete Comment?") ?>
        <?php else: ?>
        <?php echo $this->translate("Delete Feed?") ?>
        <?php endif; ?>
      </div>
      <div class="sesact_delete_popup_cont">
        <?php if (!empty($this->comment_id)): ?>
        <?php echo $this->translate("Are you sure that you want to delete this comment? This action cannot be undone.") ?>
        <?php else: ?>
        <?php echo $this->translate("This feed will be deleted and you won't be able to find it anymore. You can also edit this feed, if you just want to change something.") ?>
        <?php endif; ?>
      </div>
      <div class="sesact_delete_popup_btm sesbasic_clearfix">
        
        <?php if (!empty($this->comment_id)): ?>
        <input type="hidden" name="comment_id" value="<?php echo (int) $this->comment_id ?>" class="hidden_cmnt"/>
        <?php endif; ?>
       <?php if (!empty($this->comment_id)): ?> 
        <button type='submit'><?php echo $this->translate("Delete") ?></button>
        <?php echo $this->translate(" or ") ?>
        <a href="javascript:void(0);" onclick="parent.Smoothbox.close();"><?php echo $this->translate("Cancel") ?></a>
      <?php else: ?>
      	<div class="floatL">
        	<button type='submit' onClick="sessmoothboxclose();return false;"><?php echo $this->translate("Cancel") ?></button>
        </div>
        <div class="floatR">
          <button type='submit' class="edit_feed_edit"><?php echo $this->translate("Edit Feed") ?></button>
          <button type='submit'><?php echo $this->translate("Delete Feed") ?></button>
				</div>        
      <?php endif; ?>
      </div>
  </form>
</div>

<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>
