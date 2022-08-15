<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: eventvideo.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<div class='sesbasic-form sesbasic-categories-form'>
  <div>
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventvideo')): ?>
    <div class='sesbasic-form-cont'>
    <div class='clear'>
		  <div class='settings sesbasic_admin_form'>
		    <?php echo $this->form->render($this); ?>
		  </div>
		</div>
		</div>
    <?php else: ?>
			<?php $eventsponsorshipInstalled = Engine_Api::_()->sesbasic()->pluginInstalled('seseventvideo'); ?>
			<?php if(empty($eventsponsorshipInstalled)): ?>
				<div id="" class="ses_tip_red tip">
				  <span>
				    <?php echo 'At you site Advanced Event Videos Extension is not installed. So, please purchase Advanced Event Videos Extension from here.'; ?>
				  </span>
				</div>
			<?php elseif(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventvideo')): ?>
				<div id="" class="ses_tip_red tip">
				  <span>
				    <?php echo 'At you site Advanced Event Videos Extension is installed but not enable. So, you can enable this extension from "<a href="admin/packages">Manage Packages</a>" section.'; ?>
				  </span>
				</div>
			<?php endif; ?>
    <?php endif; ?>
  </div>
</div>