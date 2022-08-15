<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';
$this->headScript()->prependFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
?>
<div class="settings sesbasic_admin_form sesact_global_setting">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script>
window.addEvent('domready',function() {
//enablesessocialshare(<?php //echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.enablesessocialshare', 0); ?>);
});
function enableShare(value) {

  if(value == 1) {
    var enableShareval = sesJqueryObject('input[name=sesadvancedactivity_enablesocialshare]:checked').val();
    var enablesessocialshareval = sesJqueryObject('input[name=sesadvancedactivity_enablesessocialshare]:checked').val();
    sesJqueryObject('input[name="sesadvancedactivity_enablesessocialshare"]').prop('checked',true);
  }
}

function enablesessocialshare(value) {

if(value == 1) {
  var enableShareval = sesJqueryObject('input[name=sesadvancedactivity_enablesocialshare]:checked').val();
  var enablesessocialshareval = sesJqueryObject('input[name=sesadvancedactivity_enablesessocialshare]:checked').val();
  sesJqueryObject('input[name="sesadvancedactivity_enablesocialshare"]').prop('checked',true);
  $('sesadvancedactivity_enableplusicon-wrapper').style.display = 'block';
  $('sesadvancedactivity_iconlimit-wrapper').style.display = 'block';
} else {
  $('sesadvancedactivity_enableplusicon-wrapper').style.display = 'none';
  $('sesadvancedactivity_iconlimit-wrapper').style.display = 'none';
}

}

</script>
