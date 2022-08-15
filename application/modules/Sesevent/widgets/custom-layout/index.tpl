<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<form action="" method="" id="myCustomLayoutForm">
<select name="layout" id="layoutSelect">
	<option value="">Layout Main</option>
  <option value="1" <?php if(isset($_GET['layout']) && $_GET['layout'] == '1'){ echo "selected=selected"; } ?>>Layout 1</option>
   <option value="2" <?php if(isset($_GET['layout']) && $_GET['layout'] == '2'){ echo "selected=selected"; } ?>>Layout 2</option>
    <option value="3" <?php if(isset($_GET['layout']) && $_GET['layout'] == '3'){ echo "selected=selected"; } ?>>Layout 3</option>
</select>
</form>
<script type="application/javascript">
sesJqueryObject('#layoutSelect').on('change',function(){
		sesJqueryObject('#myCustomLayoutForm').trigger('submit');
});
</script>