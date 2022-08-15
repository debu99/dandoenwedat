<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: global.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<h2><?php echo $this->translate('SocialEngineSolutions Basic Required Plugin'); ?></h2>
<?php include APPLICATION_PATH .  '/application/modules/Sesbasic/views/scripts/_mapKeyTip.tpl';?>
<?php if (count($this->navigation)): ?>
  <div class='tabs'>
    <?php
    // Render the menu
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<div class='clear'>
  <div class='settings sesbasic_admin_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="text/javascript">
 
  window.addEvent('domready',function() {
    enablelocation('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', '1') ;?>');
  });
  
  function enablelocation(value) {
    if(value == 1) {
			document.getElementById('ses_mapApiKey-wrapper').style.display = 'block';
			document.getElementById('optionsenableglotion-wrapper').style.display = 'none';
		} else {
			document.getElementById('ses_mapApiKey-wrapper').style.display = 'none';
			document.getElementById('optionsenableglotion-wrapper').style.display = 'block';
    }
  }
</script>
