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
<div class="clear sesbasic-form">
	<div>
		<?php if( count($this->subnavigation) ): ?>
			<div class='sesbasic-admin-sub-tabs'>
				<?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render();?>
			</div>
		<?php endif; ?>
		<div class='sesbasic-form-cont'>
			<div class='clear'>
				<div class='settings sesbasic_admin_form'>
					<?php echo $this->form->render($this); ?>
				</div>
			</div>
		</div>
	</div>
</div>
