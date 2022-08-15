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

<h3><?php echo $this->translate("Manage Sponsorship Orders") ?></h3>
<p><?php echo $this->translate('This page lists all of the sponsorship orders your users have purchased. You can use this page to monitor these sponsorship orders. Entering criteria into the filter fields will help you find specific sponsorship order. Leaving the filter fields blank will show all the sponsorship order on your social network.'); ?></p>
<br />

<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />

<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s sponsorship order found.', '%s sponsorship orders found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
    <table class='admin_table'>
      <thead>
        <tr>
          <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('sponsorshiporder_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
          <th><?php echo $this->translate("Sponsorship Title") ?></th>
          <th><?php echo $this->translate("Event Title") ?></th>          
          <th><?php echo $this->translate("Owner Name") ?></th>
          
          <th><?php echo $this->translate("Gateway Type"); ?></th>
          <th><?php echo $this->translate("Currency") ?></th>
          <th><?php echo $this->translate("Total Amount"); ?></th>          
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><?php echo $item->sponsorshiporder_id ?></td>
          <td><?php echo $item->spo_title; ?></td>
          <td><?php echo $item->title; ?></td>
          <td><?php echo $item->getOwner(); ?></td>
          
          <td><?php echo $item->gateway_type; ?></td>
          <td><?php echo $item->currency_symbol; ?></td>
          <td><?php echo round($item->total_amount, 2); ?></td>
          <td>
            <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-sponsorship', 'action' => 'view', 'id' => $item->sponsorshiporder_id), $this->translate("View Details"), array('class' => 'smoothbox')) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </form>
  <br/>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no event created by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>