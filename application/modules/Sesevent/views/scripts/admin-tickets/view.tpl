<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
<div class="sesbasic_view_stats_popup">
  <h3>Details of <?php echo $this->item->name;  ?> </h3>
  <table>
    <tr>
      <td><?php echo $this->translate('Ticket Title') ?>:</td>
      <td><?php if(!is_null($this->item->name) && $this->item->name != '') {
        echo  $this->item->name ;
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Currency') ?>:</td>
      <td><?php echo $this->item->currency ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Price') ?>:</td>
      <td><?php echo $this->item->price ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Total Quantity') ?>:</td>
      <td><?php echo $this->item->total ?></td>
    </tr>
    <?php $ticketSold = Engine_Api::_()->sesevent()->purchaseTicketCount($this->item->event_id,$this->item->ticket_id); ?>
    <tr>
      <td><?php echo $this->translate('Remaining Tickets') ?>:</td>
      <?php $remaining = $this->item->total - $ticketSold; ?>
      <td><?php echo $remaining < 0 ? 'Unlimited' : $remaining; ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Type') ?>:</td>
      <td><?php echo $this->item->type ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Maximum Quantity <br /> to be Purchased') ?>:</td>
      <td><?php echo $this->item->max_quantity ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Minimum Quantity  <br /> to be Purchased') ?>:</td>
      <td><?php echo $this->item->min_quantity ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Creation Date') ?>:</td>
      <td><?php echo $this->item->creation_date; ;?></td>
    </tr>
  </table>
  <br />
  <button onclick='javascript:parent.Smoothbox.close()'>
    <?php echo $this->translate("Close") ?>
  </button>
</div>