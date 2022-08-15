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
<div id='profile_options' <?php if ($this->viewType == 'horizontal'): ?>class="seseventreview_profile_options_horrizontal"<?php endif; ?>>
  <?php echo $this->navigation()->menu()->setContainer($this->navigation)->setPartial(array('_navIcons.tpl', 'core'))->render(); ?>
</div>