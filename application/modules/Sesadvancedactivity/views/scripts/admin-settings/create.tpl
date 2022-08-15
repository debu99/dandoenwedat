<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class='sesbasic_popup_form settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="application/javascript">
function setModuleName(value){
  document.getElementById('module').value = value;;  
}
if(document.getElementById('module'))
document.getElementById('module').value = document.getElementById('filtertype').options[document.getElementById('filtertype').selectedIndex].text;
</script>