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

<?php
  $this->headScript()->prependFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
  $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/mo.min.js');
  $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/scripts/animation.js');
?>
<!--[if IE]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<div class='sesbasic_popup_form settings'>
  <?php echo $this->form->render($this); ?>
</div>
<div></div>
<style>
#stringhover1-wrapper{height:1px;padding:0px;}
.sesadvancedactivity-special-link {
	position: relative;
	-webkit-transition: color 0.2s;
	transition: color 0.2s;
}
#stringhover1-wrapper, #stringhover1-element{
  overflow:visible;
}
</style>
<script type="application/javascript">
  function showanimation(obj){
    var value = obj.value;
    if(value == "")
      return;
    sesJqueryObject('#stringhover').removeAttr('class');
    sesJqueryObject('#stringhover').addClass(value);
    initSesadvAnimation();
    sesJqueryObject('#stringhover1-wrapper').show();
    sesJqueryObject('.'+value).trigger('mouseenter');
    setTimeout(function () {
        sesJqueryObject("#stringhover").trigger('mouseleave');
    }, 800);
  }
  sesJqueryObject('#stringhover').on('mouseleave',function(){
      sesJqueryObject('#stringhover').html('');
      sesJqueryObject('#stringhover').removeAttr('class');
  })
  
</script>