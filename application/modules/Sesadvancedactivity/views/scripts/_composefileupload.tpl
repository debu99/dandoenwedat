<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _composefileupload.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
$request = Zend_Controller_Front::getInstance()->getRequest();
$requestParams = $request->getParams();
 
$request = Zend_Controller_Front::getInstance()->getRequest();
$requestParams = $request->getParams();

if((($requestParams['action'] == 'home' || $requestParams['action'] == 'index') && $requestParams['module'] == 'user' && ($requestParams['controller'] == 'index' || $requestParams['controller'] == 'profile')) || ($this->subject() && ($this->subject()->getType() == "sesgroup_group"  || $this->subject()->getType() == "sespage_page" || $this->subject()->getType() == "businesses"))) {

$this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/composer_fileupload.js');
     ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    composeInstance.addPlugin(new Composer.Plugin.Fileupload({
      title: '<?php echo $this->string()->escapeJavascript($this->translate('Add File')) ?>',
      serverLimit : '<?php echo Engine_Api::_()->sesadvancedactivity()->formatBytes(Engine_Api::_()->sesadvancedactivity()->file_upload_max_size()); ?>', 
      sesrverLimitDigits: '<?php echo Engine_Api::_()->sesadvancedactivity()->file_upload_max_size(); ?>',
      lang : {
        'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
      },
    }));
  });
</script>
<?php } ?>