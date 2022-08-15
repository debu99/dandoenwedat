<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<h3><?php echo $this->translate("Manage Membership Upgrades") ?></h3>
<p><?php echo $this->translate('Here, you can configure the membership upgradation requests and points on your website. Below in the "Manage Points for Upgrades" section, enter the points which will be required to upgrade to that respective member level on your website.'); ?></p>
<br />
<div class="clear sesbasic-form">
  <div>  
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <div class="sesbasic-form-cont">
      <div class="sescredit_level_upgrade_points_form">
        <h3></h3>
        <p></p>
        <form name="" id="" method="post">
          <ul>
            <?php foreach($this->levels as $level):?>
              <?php if($level->level_id == 1):?><?php continue;?><?php endif;?>
              <li class="sesbasic_clearfix">
                <div class="_label"><?php echo $level->title;?></div>
                <div class="_element"><input type="text" name="level[<?php echo $level->level_id;?>]" onkeypress="return isNumberKey(event)" value="<?php echo $level->point;?>" /></div>
              </li>
            <?php endforeach;?>
          </ul>
          <div class="_btn">
            <button type="submit" name="submit">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
  }
</script>