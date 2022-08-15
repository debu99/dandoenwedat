<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<div class="sespwa_startup_screen">
	<div class="sespwa_startup_screen_content">
    <p class="_text"><?php echo $this->title; ?></p>
    <?php if($this->logo){ ?>
    	<p class="_logo"><img src="<?php echo Engine_Api::_()->sespwa()->getFileUrl($this->logo); ?>"></p>
    <?php  } ?>
    <?php if($this->copyright){ ?>
    	<p class="_copyright">Copyright @<?php echo date('Y'); ?></p>
    <?php } ?>
  </div>
  <div class="sespwa_startup_loading"><div></div><div></div><div></div><div></div></div>
</div>

<script type="application/javascript">
	setTimeout(function () {
			sesJqueryObject('.layout_sespwa_startup').remove();
	},2200)
</script>
