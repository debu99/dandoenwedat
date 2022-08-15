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
<?php if(!$this->is_search_ajax){ ?>
<div class="sesbasic_dashboard_content_header sesbasic_clearfix">
  <h3><?php echo $this->translate("Manage Ticket Orders"); ?></h3>
  <p><?php echo $this->translate('Below, you can manage the ticket orders for this event. You can use this page to monitor these orders. Entering criteria into the filter fields will help you find specific order.'); ?></p>
</div>
<div class="sesbasic_browse_search sesbasic_browse_search_horizontal sesbasic_dashboard_search_form">
  <?php echo $this->searchForm->render($this); ?>
</div>
<?php } ?>
<div id="sesevent_manage_order_content">
<div class="sesbasic_dashboard_search_result">
	<?php echo $this->paginator->getTotalItemCount().$this->translate(' order(s) found.'); ?>
</div>
<?php if($this->paginator->getTotalItemCount() > 0): ?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
<div class="sesbasic_dashboard_table sesbasic_bxs">
  <form id='multidelete_form' method="post">
    <table>
      <thead>
        <tr>
          <th class="centerT"><?php echo $this->translate("ID"); ?></th>
           <th class="centerT" title="<?php echo $this->translate('Registration Number'); ?>"><?php echo $this->translate("Reg No."); ?></th>
          <th><?php echo $this->translate("Buyer") ?></th>
          <th><?php echo $this->translate("Mobile") ?></th>
          <th><?php echo $this->translate("Email") ?></th>
          <th><?php echo $this->translate("Tickets") ?></th>
          <th><?php echo $this->translate("Order Total") ?></th>
          <th><?php echo $this->translate("Commision") ?></th>
          <th><?php echo $this->translate("Status") ?></th>
          <th><?php echo $this->translate("Gateway") ?></th>
          <th><?php echo $this->translate("Order Date") ?></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
        <tr>
        	<?php $event = Engine_Api::_()->getItem("sesevent_event", $item->event_id); ?>
          <td class="centerT">
          	<a class="openSmoothbox" href="<?php echo $this->url(array('event_id' => $event->custom_url,'action'=>'view','order_id'=>$item->order_id), 'sesevent_order', true); ?>"><?php echo '#'.$item->order_id ?></a></td>
          <td><?php echo $item->ragistration_number; ?></td>
          <td>
              <?php $user = Engine_Api::_()->getItem('user',$item->owner_id) ?>
              <a href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a>
          </td>
          <td><?php echo $item->mobile ? $item->mobile : '-'; ?></td>
          <td title="<?php echo $item->email; ?>"><?php echo $item->email ? $this->string()->truncate($item->email, 7) : '-'; ?></td>
          <td><?php echo $item->total_tickets; ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice(round($item->total_amount+$item->total_service_tax+$item->total_entertainment_tax,2),$defaultCurrency); ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->commission_amount,$defaultCurrency); ?></td>
          <td><?php echo $item->state; ?></td> 
          <td><?php echo $item->gateway_type; ?></td> 
          <td title="<?php echo Engine_Api::_()->sesevent()->dateFormat($item->creation_date); ?>"><?php echo $this->string()->truncate(Engine_Api::_()->sesevent()->dateFormat($item->creation_date), 10); ?></td> 
          <td class="table_options">
            <?php echo $this->htmlLink($this->url(array('event_id' => $event->custom_url,'action'=>'view','order_id'=>$item->order_id), 'sesevent_order', true).'?order=view', $this->translate("View Order"), array('class' => 'openSmoothbox sesbasic_icon_view')); ?>
            <?php echo $this->htmlLink($this->url(array('action' => 'view', 'order_id' => $item->order_id, 'event_id' => $event->custom_url,'format'=>'smoothbox'), 'sesevent_order', true), $this->translate("Print Invoice"), array('class' => 'sesbasic_icon_print','target'=>'_blank')); ?>
            <?php echo $this->htmlLink($this->url(array('action' => 'print-ticket', 'order_id' => $item->order_id, 'event_id' => $event->custom_url,'format'=>'smoothbox'), 'sesevent_order', true), $this->translate("Print Ticket"), array('class' => 'sesevent_icon_ticket','target'=>'_blank')); ?>
            <?php echo $this->htmlLink($this->url(array('event_id' => $event->custom_url,'action'=>'search-ticket','order_id'=>$item->order_id), 'sesevent_order', true), $this->translate("View Tickets"), array('class' => 'sesevent_search_ticket_search sesbasic_icon_view','data-rel'=>$item->order_id)); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
   </form>
</div>
<?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'manage_order')); ?>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate("No order has been placed yet.") ?>
  </span>
</div>
<?php endif; ?>
</div>
<script type="application/javascript">
var requestPagging;
function paggingNumbermanage_order(pageNum){
	 sesJqueryObject('.sesbasic_loading_cont_overlay').css('display','block');
	 var searchFormData = sesJqueryObject('#sesevent_search_ticket_search').serialize();
		requestPagging= (new Request.HTML({
			method: 'post',
			'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/manage-orders",
			'data': {
				format: 'html',
				searchParams :searchFormData, 
				is_search_ajax:true,
				is_ajax : 1,
				page:pageNum,
				event_id:<?php echo $this->event_id; ?>,
			},
			onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject('.sesbasic_loading_cont_overlay').css('display','none');
				sesJqueryObject('#sesevent_manage_order_content').html(responseHTML);
			}
		}));
		requestPagging.send();
		return false;
}
</script>
<?php if($this->is_search_ajax) die; ?>
<script type="application/javascript">
function executeAfterLoad(){
	if(!sesBasicAutoScroll('#date-date_to').length )
		return;
	var FromEndDateOrder;
	var selectedDateOrder =  new Date(sesBasicAutoScroll('#date-date_to').val());
	sesBasicAutoScroll('#date-date_to').datepicker({
			format: 'yyyy-m-d',
			weekStart: 1,
			autoclose: true,
			endDate: FromEndDateOrder, 
	}).on('changeDate', function(ev){
		selectedDateOrder = ev.date;	
		sesBasicAutoScroll('#date-date_from').datepicker('setStartDate', selectedDateOrder);
	});
	sesBasicAutoScroll('#date-date_from').datepicker({
			format: 'yyyy-m-d',
			weekStart: 1,
			autoclose: true,
			startDate: selectedDateOrder,
	}).on('changeDate', function(ev){
		FromEndDateOrder	= ev.date;	
		 sesBasicAutoScroll('#date-date_to').datepicker('setEndDate', FromEndDateOrder);
	});	
}
sesJqueryObject(document).on('click','.sesevent_search_ticket_search',function(e){
	e.preventDefault();
	sendParamInSearch = sesJqueryObject(this).attr('data-rel');
	sesJqueryObject('#sesevent_search_ticket_search').trigger('click');
});
sesJqueryObject('#loadingimgsesevent-wrapper').hide();
</script>