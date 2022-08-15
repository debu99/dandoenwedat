<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeelingactivity
 * @package    Sesfeelingactivity
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: add-feelingicon.tpl  2017-08-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<div class='settings sesbasic_popup_form'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
 function changemodule(modulename) {
  var type = '<?php echo $this->type ?>';
  var feeling_id = '<?php echo $this->feeling_id ?>';
  window.location.href="<?php echo $this->url(array('module'=>'sesfeelingactivity','controller'=>'feeling', 'action'=>'add-feelingicon'),'admin_default',true)?>/module_name/"+modulename + "/type/" +type+"/feeling_id/"+feeling_id;
 }
</script>