<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: showhidepassword.tpl 2017-06-17 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div id="showhide_password" class="showhide_password ka">
<a class="fa fa-eye" id="show_password" href="javascript:void(0);" onclick="showhidepassword('show')" title='<?php echo $this->translate("Show Password"); ?>'></a>
<a class="fa fa-eye-slash" id="hide_password" href="javascript:void(0);" onclick="showhidepassword('hide')" style="display:none;" title='<?php echo $this->translate("Hide Password"); ?>'></a>
</div>
<script>
function showhidepassword(showhidepassword) {
	if(showhidepassword == 'show'){
		if($('show_password'))
			$('show_password').style.display = 'none';
		if($('hide_password'))
			$('hide_password').style.display = 'block';
		if(sesJqueryObject('#password'))
			sesJqueryObject('#password').attr('type', 'text');
		if(sesJqueryObject('#showhide_password'))
			sesJqueryObject('#showhide_password').addClass('m');
	} else if(showhidepassword == 'hide') {
		if($('show_password'))
			$('show_password').style.display = 'block';
		if($('hide_password'))
			$('hide_password').style.display = 'none';
		if(sesJqueryObject('#password'))
			sesJqueryObject('#password').attr('type', 'password');
		if(sesJqueryObject('#showhide_password'))
			sesJqueryObject('#showhide_password').removeClass('m');
	}
}
</script>
