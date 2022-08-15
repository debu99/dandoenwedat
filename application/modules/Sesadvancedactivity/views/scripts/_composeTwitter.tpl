<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _composeTwitter.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php
  $twitterTable = Engine_Api::_()->getDbtable('twitter', 'sesadvancedactivity');
  $twitter = $twitterTable->getApi();
  
  if(!$twitterTable->isEnabled())
    return;
  
  $status = true;
  // Not connected
  if( !$twitter || !$twitterTable->isConnected() ) {
    $status = false;
  }

  // Disabled
  if( 'publish' != Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable ) {
    $status = false;
  }

  // Add script
  $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/composer_twitter.js');
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.Twitter({
      status:'<?php echo $status ?>',
      lang : {
        'Publish this on Twitter' : '<?php echo $this->string()->escapeJavascript($this->translate('Publish this on Twitter')); ?>'
      }
    }));
  });
  sesJqueryObject(document).on('click','.openWindowTwitter',function(e){
  authSesactmyWindow =  window.open('<?php echo Engine_Api::_()->getDbTable("twitter","sesadvancedactivity")->loginButton(); ?>','Twitter', "width=780,height=410,toolbar=0,scrollbars=0,status=0,resizable=0,location=0,menuBar=0");  
 });
</script>
