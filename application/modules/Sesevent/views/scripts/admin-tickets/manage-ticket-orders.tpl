<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage-ticket-orders.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }

  function multiDelete() {
    return confirm("<?php echo $this->translate('Are you sure you want to delete the selected tickets?');?>");
  }

  function selectAll() {
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length; i++) {
      if (!inputs[i].disabled) {
        inputs[i].checked = inputs[0].checked;
      }
    }
  }

</script>

<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<div class='sesbasic-form sesbasic-categories-form'>
  <div>
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <div class="sesbasic-form-cont">
    <?php if( count($this->subsubNavigation) ): ?>
      <div class='tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subsubNavigation)->render();?>
      </div>
    <?php endif; ?>
<h3><?php echo $this->translate("Manage Ordered Tickets") ?></h3>
<p><?php echo $this->translate('This page lists all of the orders for tickets on your website. You can use this page to monitor these orders. Entering criteria into the filter fields will help you find specific ticket order. Leaving the filter fields blank will show all the orders on your social network.'); ?></p>
<br />
    <div class='admin_search sesbasic_search_form'>
      <?php echo $this->formFilter->render($this) ?>
    </div>
    <br />

    <?php $counter = $this->paginator->getTotalItemCount(); ?> 
    <?php if( count($this->paginator) ): ?>
      <div class="sesbasic_search_reasult">
        <?php echo $this->translate(array('%s order found.', '%s orders found.', $counter), $this->locale()->toNumber($counter)) ?>
      </div>
      <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
        <div class="clear" style="overflow: auto;">  
        <table class='admin_table'>
          <thead>
            <tr>
              <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('order_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
              <th><?php echo $this->translate("Event Title") ?></th>          
              <th><?php echo $this->translate("Owner Name") ?></th>
              <th class="admin_table_centered"><?php echo $this->translate("Quantity") ?></th>
              <th class="admin_table_centered"><?php echo $this->translate("Gateway"); ?></th>
              <th class="admin_table_centered"><?php echo $this->translate("Currency") ?></th>
              <th class="admin_table_centered"><?php echo $this->translate("Total Amount"); ?></th>          
              <th><?php echo $this->translate("Options") ?></th>
            </tr>
          </thead>
          <?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
          <tbody>
            <?php foreach ($this->paginator as $item): ?>
            <tr>
              <td><?php echo $item->order_id ?></td>
              <?php $event = Engine_Api::_()->getItem('sesevent_event',$item->event_id); ?>
              <td><a href="<?php echo $event->getHref(); ?>" target="_blank"><?php echo $item->title; ?></a></td>
              <td><?php echo $item->getOwner(); ?></td>
              <td class="admin_table_centered"><?php echo $item->total_tickets; ?></td>
              <td class="admin_table_centered"><?php echo $item->gateway_type; ?></td>
              <td class="admin_table_centered"><?php echo $item->currency_symbol ? $item->currency_symbol : '-'; ?></td>
              <td class="admin_table_centered"><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice(round($item->total_amount+$item->total_service_tax+$item->total_entertainment_tax,2),$defaultCurrency); ?></td>
              <?php $event = Engine_Api::_()->getItem("sesevent_event", $item->event_id);  ?>
              <td>
                <?php echo $this->htmlLink($this->url(array('event_id' => $event->custom_url,'action'=>'view','order_id'=>$item->order_id), 'sesevent_order', true).'?order=view', $this->translate("View Order"), array('title' => $this->translate("View Order"), 'class' => 'smoothbox')); ?>
                |
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-tickets', 'action' => 'view-ticket-order', 'id' => $item->order_id), $this->translate("View Details"), array('class' => 'smoothbox')) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </form>
      <br/>
      <div>
        <?php echo $this->paginationControl($this->paginator); ?>
      </div>
    <?php else:?>
      <div class="tip">
        <span>
          <?php echo $this->translate("No tickets have been ordered from your website yet.") ?>
        </span>
      </div>
    <?php endif; ?>
    </div>
  </div>
</div>