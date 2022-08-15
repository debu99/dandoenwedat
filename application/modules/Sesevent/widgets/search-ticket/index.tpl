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
<h3><?php echo $this->translate("Search Sold Tickets") ?></h3>
<p><?php echo $this->translate("Below, you can search tickets sold from this event from all the orders.") ?></p>
</div>
<div class="sesbasic_browse_search sesbasic_browse_search_horizontal sesbasic_dashboard_search_form">
  <?php echo $this->searchForm->render($this); ?>
</div>
<?php if($this->backOrder){ ?>
<div class="clear sesevent_order_view_top">
   <a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'manage-orders'), 'sesevent_dashboard', true); ?>" class="buttonlink sesbasic_icon_back back_to_manage_order"><?php echo $this->translate('Back To Manage Order'); ?></a>
</div>
<?php } ?>
<?php } ?>
<div id="sesevent_manage_order_content">
<div class="sesbasic_dashboard_search_result">
  <?php echo $this->translate(array('%s ticket found.', '%s tickets found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
</div>
<?php if($this->paginator->getTotalItemCount() > 0): ?>
<div class="sesbasic_dashboard_table sesbasic_bxs">
  <form id='multidelete_form' method="post">
    <table>
      <thead>
        <tr>
          <th class="centerT"><?php echo $this->translate("ID"); ?></th>
          <th><?php echo $this->translate("QR Code") ?></th>
          <th class="centerT"><?php echo $this->translate("Registration Number"); ?></th>
          <th><?php echo $this->translate("First Name") ?></th>
          <th><?php echo $this->translate("Last Name") ?></th>
          <th><?php echo $this->translate("Mobile") ?></th>
          <th><?php echo $this->translate("Email") ?></th>
          <th><?php echo $this->translate("Order Date") ?></th>
          
        </tr>
      </thead>
      <tbody>
      	<?php $event = Engine_Api::_()->getItem("sesevent_event", $this->event_id); ?>
        <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td class="centerT">
          	<a class="openSmoothbox" href="<?php echo $this->url(array('event_id' => $event->custom_url,'action'=>'view','order_id'=>$item->order_id), 'sesevent_order', true).'?order=view'; ?>"><?php echo '#'.$item->order_id ?></a></td>
            <?php $fileName = $item->getType().'_'.$item->getIdentity().'.png'; ?>
          <?php if(!file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public/sesevent_qrcode/'.$fileName)){ 
          			$fileName = Engine_Api::_()->sesevent()->generateQrCode($item->registration_number,$fileName);
          			}else{ 
          			$fileName = ( isset($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] .Zend_Registry::get('StaticBaseUrl') .'/public/sesevent_qrcode/'.$fileName;
          		  }
          ?>
          <td><img src="<?php echo $fileName; ?>" style="height:150px;width:150px;" /></td>
          <td><?php echo $item->registration_number; ?></td>
          <td><?php echo $item->first_name; ?></td>
          <td><?php echo $item->last_name; ?></td>
          <td><?php echo $item->mobile; ?></td>
          <td><?php echo $item->email; ?></td>
          <?php $order = Engine_Api::_()->getItem("sesevent_order", $item->order_id); ?>
          <td><?php echo Engine_Api::_()->sesevent()->dateFormat($order->creation_date); ?></td> 
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
   </form>
</div>
<?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'search_ticket')); ?>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate("Please search ticket from search form.") ?>
  </span>
</div>
<?php endif; ?>
</div>
<script type="application/javascript">
var requestPagging;
function paggingNumbersearch_ticket(pageNum){
	 sesJqueryObject('.sesbasic_loading_cont_overlay').css('display','block');
	 var searchFormData = sesJqueryObject('#sesevent_search_ticket_search').serialize();
		requestPagging= (new Request.HTML({
			method: 'post',
			'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/search-ticket",
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
sesJqueryObject(document).on('click','.back_to_manage_order',function(e){
	e.preventDefault();
	sesJqueryObject('#sesevent_manage_order').trigger('click');
});
sesJqueryObject('#loadingimgsesevent-wrapper').hide();
</script>