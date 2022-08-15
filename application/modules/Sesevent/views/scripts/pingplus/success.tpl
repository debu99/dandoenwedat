<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: success.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="layout_middle">
	<div class="generic_layout_container layout_core_content">
    <div class="sesevent_order_success_box sesbm">
       <div class="sesevent_order_error_msg" style="color:green;">
         <?php echo $this->error; ?>
       </div>
		</div>
    <div class="sesbasic_clearfix clear sesevent_order_btns">
    	<a href="<?php echo $this->url(array('action'=>'my-tickets'), 'sesevent_my_ticket', true); ?>" class="sesbasic_link_btn floatL"><?php echo $this->translate("Go To My Ticket"); ?></a>
    </div>
	</div>
	</div>
</div>