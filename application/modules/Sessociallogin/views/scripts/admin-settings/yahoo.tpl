<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: yahoo.tpl 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sessociallogin/views/scripts/dismiss_message.tpl';
$this->headScript()->prependFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
?>
<div class="settings sesbasic_admin_form sesact_global_setting">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.pluginactivated',0)){ ?>
  <script type="application/javascript">
    sesJqueryObject('.global_form').submit(function(e){
      sesJqueryObject('.sesbasic_waiting_msg_box').show();
    });
  </script>
<?php } ?>