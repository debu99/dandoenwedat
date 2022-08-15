<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
// from fields module
echo $this->render('_jsAdmin.tpl'); ?>

<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<div class='sesbasic-form sesbasic-categories-form'>
  <div>
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
      <?php if( count($this->subsubNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs sesbasic-admin-sub-inner-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subsubNavigation)->render();?>
      </div>
    <?php endif; ?>
    <div class='sesbasic-form-cont'>
    <div class='clear'>
			<h3><?php echo $this->translate('Manage Profile Types'); ?></h3>
			<p>
			  <?php echo $this->translate('You might want your users to provide some more information about their reviews. Here, you can create some custom fields of your choice and requirement.<br><br>To reorder the custom fields, click on their names and drag them up or down. If you want to show different sets of fields to different types of categories, you can create multiple "Profile Types". From the "Map Profile Type" field, you can map and associate Profile Types with Categories from "Map Profile Type" section.'); ?>
			</p>
			<br />
			<div class="admin_fields_type">
			  <h3><?php echo $this->translate("Editing Profile Type:") ?></h3>
			  <?php echo $this->formSelect('profileType', $this->topLevelOption->option_id, array(), $this->topLevelOptions) ?>
			</div>
			<br />
			<div class="admin_fields_options">
			  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addquestion"><?php echo $this->translate('Add Question'); ?></a>
			  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_renametype"><?php echo $this->translate('Rename Profile Type'); ?></a>
			  <?php if (count($this->topLevelOptions) > 1): ?>
			  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_deletetype"><?php echo $this->translate('Delete Profile Type'); ?></a>
			  <?php endif; ?>
			  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_addtype"><?php echo $this->translate('Create New Profile Type'); ?></a>
			  <a href="javascript:void(0);" onclick="void(0);" class="buttonlink admin_fields_options_saveorder" style="display:none;"><?php echo $this->translate('Save Order'); ?></a>
			</div>
			<br />
			<ul class="admin_fields clear">
			  <?php foreach ($this->secondLevelMaps as $map): ?>
			  <?php echo $this->adminFieldMeta($map) ?>
			  <?php endforeach; ?>
			</ul>
		</div>
		</div>
  </div>
</div>
