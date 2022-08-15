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
<ul class="sesbasic_user_grid_list sesbasic_sidebar_block sesbasic_clearfix sesevent_ticket_buyer_info">
	<li class="sesevent_ticket_buyer_info_total sesbm">
		<?php echo $this->translate("Tickets sold:").$this->totalTicketSold; ?>
	</li>
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <?php $user = Engine_Api::_()->getItem('user',$item->owner_id); ?>
      <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')); ?>
    </li>
  <?php endforeach; ?>
  <?php if($this->paginator->getTotalItemCount() > $this->data_show){ ?>
    <li>
      <a href="javascript:;" onclick="getBuyerData('<?php echo $this->event_id; ?>')" class="sesbasic_user_grid_list_more">
       <?php echo '+';echo $this->paginator->getTotalItemCount() - $this->data_show ; ?>
      </a>
    </li>
 <?php } ?>
</ul>
<script type="application/javascript">
function getBuyerData(value){
	if(value){
		url = en4.core.staticBaseUrl+'sesevent/index/buyer-details/event_id/'+value;
		openURLinSmoothBox(url);	
		return;
	}
}
var tabIdTb = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabIdTb);	
});
</script>