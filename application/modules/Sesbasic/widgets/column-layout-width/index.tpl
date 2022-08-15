<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2015-10-28 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div id="layoutcolumn_width_<?php echo $this->identity ?>"></div>

<script type="text/javascript">

var layoutWidth = '<?php echo $this->finalValue ?>';

var layoutWidthWidget = sesJqueryObject('#layoutcolumn_width_<?php echo $this->identity ?>');
if(layoutWidthWidget) {
  
	if(layoutWidthWidget.parent().parent('.layout_right')) {
		layoutWidthWidget.parent().parent('.layout_right').css('width', layoutWidth);
		layoutWidthWidget.parent().css('display', 'none');
	} 
	if(layoutWidthWidget.parent().parent('.layout_left')) {
		layoutWidthWidget.parent().parent('.layout_left').css('width', layoutWidth);
	  layoutWidthWidget.parent().css('display', 'none');
	} 
	if(layoutWidthWidget.parent().parent('.layout_middle')) {
		layoutWidthWidget.parent().parent('.layout_middle').css('width', layoutWidth);
	  layoutWidthWidget.parent().css('display', 'none');
	}
	
}
</script>