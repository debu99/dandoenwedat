<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: show-member-level.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div class="sescredit_level_popup">
	<article>
		<?php if(count($this->levelInfo) > 0):?>
			<form method="post" id="sescreditupgrademember" action="<?php echo $this->escape($this->url(array('action' => 'show-member-level'), 'sescredit_general', true)) ?>">
				<ul class="sescredit_level_popup_content">
					<?php foreach($this->levelInfo as $level):?>
						<li>
							<input type="radio" name="level" id="level_id_<?php echo $level->level_id ?>" class="selectupgrademember" value="<?php echo $level->level_id ?>" />
							<span><?php echo $level->title;?></span>
							<span><?php echo $level->point;?></span>
						</li>
					<?php endforeach;?>
				</ul>
				<div class="_btn">
					<button type="submit" name="submit"><?php echo $this->translate("Upgrade Membership");?></button>
				</div>
		</form>
		<?php else:?>
			<div class="_tip">
				<img src="application/modules/Sescredit/externals/images/no-credit.png" />
				<span><?php echo $this->translate("You don't have sufficient points for upgrading your membership.");?></span>
			</div>
		<?php endif;?>
	</article>
</div>