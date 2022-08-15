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
    <h2><?php echo $this->translate("You're going to %s", $this->event->title); ?></h2>
  <?php } else {?>
  	<h2><?php echo $this->translate("Error Occured"); ?></h2>
  <?php }?>
    <?php if (empty($this->error)) {?>

    <div class="sesbasic_clearfix sesevent_order_complete_head">
      <!-- Add to calendar -->
      <!-- Share With Friends -->
      <?php $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $this->event->getHref());

    ?>
      <div class="sesevent_order_share">
        <span><?php echo $this->translate("Share with friends"); ?></span>

        <?php echo $this->partial('_socialShareIcons.tpl', 'sesbasic', array('resource' => $this->event)); ?>

	      <div>
		      <?php echo $this->content()->renderWidget('sesevent.add-to-calendar', array('event_id' => $this->event->getIdentity())); ?>
	      </div>
	      <?php $allowFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.followeventowner', 1);
    $isFollow = Engine_Api::_()->getDbTable('follows', 'sesevent')->isFollow(array('resource_type' => 'sesevent_host', 'resource_id' => $this->event->host));
    //if (Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && $allowFollow):
    if (0):
    ?>
        <div class="" id="sesevent_host_follow_<?php echo $this->event->host; ?>" style ='display:<?php echo $isFollow ? "none" : "inline-block" ?>' >
          <a  class="sesbasic_link_btn" href = "javascript:void(0);" onclick = "followButton('<?php echo $this->event->host; ?>', 'sesevent_host');">
            <i class="fa fa-check"></i>
            <span><?php echo $this->translate("Follow") ?></span>
          </a>
        </div>
        <div id="sesevent_host_unfollow_<?php echo $this->event->host; ?>" style ='display:<?php echo $isFollow ? "inline-block" : "none" ?>' >
          <a  class="sesbasic_link_btn" href = "javascript:void(0);" onclick = "followButton('<?php echo $this->event->host; ?>', 'sesevent_host');">
            <i class="fa fa-check"></i>
            <span><?php echo $this->translate("Unfollow") ?></span>
          </a>
        </div>
        <input type ="hidden" id = "sesevent_host_hiddenfollowunfollow_<?php echo $this->event->host; ?>" value = '<?php echo $isFollow ? $isFollow : 0; ?>' />
      <?php endif;?>
      </div>
    </div>
    <?php }?>
    <div class="sesevent_order_success_box sesbm">
      <?php if (empty($this->error)) {?>
        <div class="sesevent_order_success_msg"><?php echo $this->translate("Your order has been saved to My Tickets"); ?></div>
        <ul>
          <li><i class="fa fa-check sesbasic_text_light floaL"></i><span>
            <?php echo $this->translate("%s Order #%s  %s %s %s %s", '<a href="' . $this->url(array('action' => 'my-tickets'), 'sesevent_my_ticket', true) . '">', $this->order->order_id, '</a>', '', '', ''); ?>
          </li>
          <li><i class="fa fa-check sesbasic_text_light floaL"></i><span><?php echo $this->translate(" Congratulations! your Order #%s is successfully completed and the ticket has been sent to %s.", $this->order->order_id, isset($this->order->email) && $this->order->email != '' ? '<b>' . $this->order->email . '</b>' : '<b>' . $this->viewer->email . '</b>'); ?></span></li>
        </ul>
      <?php } else {?>
        <div class="sesevent_order_error_msg"><?php echo $this->error; ?></div>
      <?php }?>
		</div>
    <div class="sesbasic_clearfix clear sesevent_order_btns">
    	<a href="<?php echo $this->url(array('action' => 'my-tickets'), 'sesevent_my_ticket', true); ?>" class="sesbasic_link_btn floatL"><?php echo $this->translate("Go To My Ticket"); ?></a>
      <a href="<?php echo $this->event->getHref(); ?>" class="sesbasic_link_btn floatL"><?php echo $this->translate("Go To Event"); ?></a>
    </div>
	</div>
	</div>
</div>
<script>

function followButton(id, type) {

	if ($(type + '_hiddenfollowunfollow_' + id))
	var contentId = $(type + '_hiddenfollowunfollow_' + id).value

	en4.core.request.send(new Request.JSON({
	url: en4.core.baseUrl + 'sesevent/index/follow',
	data: {
	format: 'json',
		'id': id,
		'type': type,
		'contentId': contentId
	},
	onSuccess: function(responseJSON) {
		if (responseJSON.follow_id) {
			if ($(type + '_hiddenfollowunfollow_' + id))
				$(type + '_hiddenfollowunfollow_' + id).value = responseJSON.follow_id;
			if ($(type + '_follow_' + id))
				$(type + '_follow_' + id).style.display = 'none';
			if ($(type + '_unfollow_' + id))
				$(type + '_unfollow_' + id).style.display = 'inline-block';

		} else {
			if ($(type + '_hiddenfollowunfollow_' + id))
				$(type + '_hiddenfollowunfollow_' + id).value = 0;
			if ($(type + '_follow_' + id))
				$(type + '_follow_' + id).style.display = 'inline-block';
			if ($(type + '_unfollow_' + id))
				$(type + '_unfollow_' + id).style.display = 'none';
		}
	}
	}));
}
</script>