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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css');?>
<div class="layout_middle">
	<div class="generic_layout_container layout_core_content">
  <?php if (empty($this->error)) {?>
    <h2><?php echo $this->translate("Spnsorship Confirmed for %s", $this->sponsorship->title); ?></h2>
  <?php } else {?>
  	<h2><?php echo $this->translate("Error Occured"); ?></h2>
  <?php }?>
  <?php if (empty($this->error)) {?>
    <div class="sesbasic_clearfix sesevent_order_complete_head">
      <!-- Share With Friends -->
      <?php $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $this->event->getHref());

    ?>
      <div class="sesevent_order_share">
        <span><?php echo $this->translate("Share with friends"); ?></span>

        <?php echo $this->partial('_socialShareIcons.tpl', 'sesbasic', array('resource' => $this->event)); ?>

     <div>
      <?php //echo $this->content()->renderWidget('sesevent.add-to-calendar',array('event_id'=>$this->event->getIdentity())); ?>
      </div>
      </div>
    </div>
    <?php }?>
    <div class="sesevent_order_success_box sesbm">
      <?php if (empty($this->error)) {?>
        <div class="sesevent_order_success_msg"><?php echo $this->translate("Your Sponsorship has been confirmed for Event %s", $this->sponsorship->title); ?></div>
        <!--<ul>
          <li><i class="fa fa-check sesbasic_text_light floaL"></i><span><a href="<?php echo $this->url(array('action' => 'my-tickets'), 'sesevent_my_ticket', true); ?>">Order #<?php echo $this->ticketCode; ?></a> <b><?php echo $this->getTicketCount ?></b> tickets</span></li>
          <li><i class="fa fa-check sesbasic_text_light floaL"></i><span><?php echo $this->translate("Your ticket has been sent to %s", $this->viewer->email); ?></span></li>
        </ul>-->
      <?php } else {?>
        <div class="sesevent_order_error_msg"><?php echo $this->error; ?></div>
      <?php }?>
		</div>
    <div class="sesbasic_clearfix clear sesevent_order_btns">
    	<!--<a href="<?php echo $this->url(array('action' => 'my-tickets'), 'sesevent_my_ticket', true); ?>" class="sesbasic_link_btn floatL"><?php echo $this->translate("Go To My Ticket"); ?></a>-->
      <a href="<?php echo $this->event->getHref(); ?>" class="sesbasic_link_btn floatL"><?php echo $this->translate("Go To Event"); ?></a>
    </div>
	</div>
	</div>
</div>