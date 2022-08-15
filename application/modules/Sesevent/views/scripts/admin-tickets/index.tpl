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
    <h3><?php echo $this->translate("Manage Events Tickets") ?></h3>
    <p><?php echo $this->translate('This page lists all of the tickets your users have purchased. You can use this page to monitor these tickets and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific ticket. Leaving the filter fields blank will show all the tickets purchased from the events on your social network.'); ?></p>
    <br />

    <div class='admin_search sesbasic_search_form'>
      <?php echo $this->formFilter->render($this) ?>
    </div>
    <br />

    <?php $counter = $this->paginator->getTotalItemCount(); ?> 
    <?php if( count($this->paginator) ): ?>
      <div class="sesbasic_search_reasult">
        <?php echo $this->translate(array('%s event ticket found.', '%s event tickets found.', $counter), $this->locale()->toNumber($counter)) ?>
      </div>
      <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
        <div class="clear" style="overflow:auto;">
          <table class='admin_table'>
          <thead>
            <tr>
              <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
              <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('ticket_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
              <th><a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');"><?php echo $this->translate("Ticket Name") ?></a></th>
              <th><a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');"><?php echo $this->translate("Event Title") ?></a></th>
              <th class='admin_table_short'><?php echo $this->translate("Owner Name") ?> </th>
              <th class="admin_table_centered"><a href="javascript:void(0);" onclick="javascript:changeOrder('type', 'ASC');"><?php echo $this->translate("Type") ?></a></th>
              <th class="admin_table_centered"><?php echo $this->translate("Currency"); ?></th>
              <th class="admin_table_centered"><a href="javascript:void(0);" onclick="javascript:changeOrder('price', 'ASC');"><?php echo $this->translate("Price") ?></a></th>
              <th class="admin_table_centered" title="Total Quantity"><?php echo $this->translate("T.QTY"); ?></th>
               <th class="admin_table_centered" title="Total Sold"><?php echo $this->translate("T.Sold") ?></th>
              <th><?php echo $this->translate("Options") ?></th>
            </tr>
          </thead>
          <tbody> 
            <?php 
                $currentTime = time();
                foreach ($this->paginator as $item): ?>
             <?php $ticketSold = Engine_Api::_()->sesevent()->purchaseTicketCount($item->event_id,$item->ticket_id) ?>
             <?php  $event = Engine_Api::_()->getItem('sesevent_event', $item->event_id); 
                    if(!$event)
                      continue;
                     $noTicket = false;
                  if(strtotime($event->endtime) < ($currentTime))
                    $noTicket = true;
             ?>
            <tr>
              <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->ticket_id;?>' value="<?php echo $item->ticket_id; ?>" /></td>
              <td><?php echo $item->ticket_id ?></td>

              <td><a title="<?php  if( $noTicket) { echo $item->name.' (Event Expired)'; }else{ echo $item->name;} ?>"  href="<?php echo $noTicket ? 'javascript:;' : $this->url(array('event_id' => $event->custom_url), 'sesevent_ticket', true); ?>" target="_blank"><?php echo $this->translate(Engine_Api::_()->sesbasic()->textTruncation($item->name,16));?></a></td>
              <td><?php echo $this->htmlLink($event->getHref(), $this->translate(Engine_Api::_()->sesbasic()->textTruncation($event->getTitle(),16)), array('title' => $event->getTitle(), 'target' => '_blank')); ?></td>
              <?php  $owner = Engine_Api::_()->getItem('user', $event->user_id); ?>
              <td><?php echo $this->htmlLink($owner->getHref(), $this->translate(Engine_Api::_()->sesbasic()->textTruncation($owner->getTitle(),16)), array('title' => $owner->getTitle(), 'target' => '_blank')); ?></td>
              <td class="admin_table_centered"><?php echo ucfirst($item->type); ?></td>
              <td class="admin_table_centered"><?php echo $item->currency; ?></td>
              <td class="admin_table_centered"><?php echo $item->price == '0.00' ? '-' : $item->price; ?></td>
              <td class="admin_table_centered"><?php echo $item->total; ?></td>
              <td class="admin_table_centered"><?php echo (!$ticketSold ? '0': $ticketSold); ?></td>          
              <td>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-tickets', 'action' => 'view', 'id' => $item->ticket_id), $this->translate("View Details"), array('class' => 'smoothbox')) ?>
                |
                 <?php echo $this->htmlLink($this->url(array('event_id' => $event->custom_url,'action'=>'edit-ticket','ticket_id'=>$item->ticket_id), 'sesevent_dashboard', true), $this->translate("Edit"), array('target'=>'_blank')) ?>
                |
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-tickets', 'action' => 'delete', 'id' => $item->ticket_id), $this->translate("Delete"), array('class' => 'smoothbox')) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
        <br />
        <div class='buttons'>
          <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
        </div>
      </form>
      <br/>
      <div>
        <?php echo $this->paginationControl($this->paginator); ?>
      </div>
    <?php else:?>
      <div class="tip">
        <span>
          <?php echo $this->translate("No tickets were found matching your selection.") ?>
        </span>
      </div>
    <?php endif; ?>
    </div>
  </div>
</div>