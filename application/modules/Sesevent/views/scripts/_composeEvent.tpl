<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _composeEvent.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php 
$request = Zend_Controller_Front::getInstance()->getRequest();
$requestParams = $request->getParams();

if(($requestParams['action'] == 'home' || $requestParams['action'] == 'index') && $requestParams['module'] == 'user' && ($requestParams['controller'] == 'index' || $requestParams['controller'] == 'profile')) {
?>
<style>
#compose-sesevent-activator{display:inline-block !important;}
</style>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?> 
<?php $this->headScript()->appendFile('externals/tinymce/tinymce.min.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    if (Composer.Plugin.Sesevent)
      return;
    Asset.javascript('<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sesevent/externals/scripts/composer_event.js', {
      onLoad:  function() {
        var type = 'wall';
        if (composeInstance.options.type) type = composeInstance.options.type;
        composeInstance.addPlugin(new Composer.Plugin.Sesevent({
          title : '<?php echo $this->string()->escapeJavascript($this->translate("Create Event")) ?>',
          lang : {
            'Create Event' : '<?php echo $this->string()->escapeJavascript($this->translate("Create Event")) ?>'
          },
          requestOptions : {
						url:"<?php echo $this->url(array('action'=>'create'), 'sesevent_general'); ?>",	
					},
        }));
      }});
  });
  sesJqueryObject(document).on('click','.tool_i_sesevent #compose-sesevent-activator:not(.sessmoothbox)',function () {
      sesJqueryObject(this).addClass('sessmoothbox').attr('href','javascript:;');
      sesJqueryObject(this).attr('data-url',"<?php echo $this->url(array('action'=>'create'), 'sesevent_general'); ?>");
      sesJqueryObject(this).trigger('click');
  })
</script>
<?php } ?>