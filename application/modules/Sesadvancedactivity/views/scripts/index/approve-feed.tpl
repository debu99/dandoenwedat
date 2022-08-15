<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: delete.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class='sesact_delete_popup'>
  <form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>" id="<?php echo $id; ?>">
  <input class="hidden_actn" type="hidden" name="action_id" value="<?php echo (int) $this->action_id ?>"/>
      <div class="sesact_delete_popup_head">
        <?php echo $this->translate("Approve Activity Item?") ?>
      </div>
      <div class="sesact_delete_popup_cont">
        <?php echo $this->translate("Are you sure want to approve this feed? This action cannot be undone.") ?>
      </div>
      <div class="sesact_delete_popup_btm sesbasic_clearfix">
        <input type="hidden" name="action_id" value="<?php echo (int) $this->action_id ?>"/>
        <button type='button' data-url="<?php echo (int) $this->action_id; ?>" class="sesadv_approve_btn"><?php echo $this->translate("Approve") ?></button>
        <?php echo $this->translate(" or ") ?>
        <a href="javascript:void(0);" onclick="sessmoothboxclose();"><?php echo $this->translate("cancel") ?></a>
      </div>
  </form>
</div>

