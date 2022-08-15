<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage-ticket.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
if(!$this->is_search_ajax):
if(!$this->is_ajax):
echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array('event' => $this->event));?>
<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
  <?php endif; endif;
  echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array('event' => $this->event)); ?>
  <?php if(!$this->is_search_ajax): ?>

  <div class="sesbasic_dashboard_content_header sesbasic_clearfix">
    <h3><?php echo $this->translate('Manage Tickets') ?>
      <a class="sesbasic_link_btn_alt fa fa-plus floatR" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'create-ticket'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate('Create New Ticket');?></a>
    </h3>
    <p><?php echo $this->translate("Below, create tickets for your event. You can also manage the existing tickets here."); ?></p>
  </div>

  <div class="sesbasic_browse_search sesbasic_browse_search_horizontal sesbasic_dashboard_search_form">
    <?php echo $this->searchForm->render($this); ?>
  </div>
  <?php endif;?>
  <div id="sesevent_manage_tickets_content">
    <?php if( count($this->eventTickets) > 0): ?>
    <div class="sesbasic_dashboard_table sesbasic_bxs">
      <form method="post" >
        <table>
          <thead>
            <tr>
              <th class="centerT"><?php echo $this->translate("ID"); ?></th>
              <th><?php echo $this->translate("Ticket Name") ?></th>
              <th><?php echo $this->translate("Price") ?></th>
              <th><?php echo $this->translate("Service Tax") ?></th>
              <th><?php echo $this->translate("Entertainment Tax") ?></th>
              <th><?php echo $this->translate("Start Time") ?></th>
              <th><?php echo $this->translate("End Time") ?></th>
              <th><?php echo $this->translate("Quantity") ?></th>
              <th><?php echo $this->translate("Options") ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($this->eventTickets as $item): ?>
            <tr>
            <?php $ticketSold = Engine_Api::_()->sesevent()->purchaseTicketCount($this->event_id,$item->ticket_id) ?>
              <td class="centerT"><?php echo $item->ticket_id ?></td>
              <td><?php echo $item->name; ?></td>
              <td><?php echo $item->type == 'free' ? '-' : Engine_Api::_()->sesevent()->getCurrencyPrice($item->price); ?></td>
              <td><?php echo $item->service_tax > 0 ? @round($item->service_tax,2).'%' : '-';  ?></td>
	            <td><?php echo $item->entertainment_tax > 0 ? @round($item->entertainment_tax,2).'%' : '-'; ?></td>
              <td title="<?php echo Engine_Api::_()->sesevent()->dateFormat($item->starttime,'changetimezone',$this->event); ?>"><?php echo $this->string()->truncate(Engine_Api::_()->sesevent()->dateFormat($item->starttime,'changetimezone', $this->event), 20); ?></td> 
              <td title="<?php echo Engine_Api::_()->sesevent()->dateFormat($item->endtime,'changetimezone', $this->event); ?>"><?php echo $this->string()->truncate(Engine_Api::_()->sesevent()->dateFormat($item->endtime,'changetimezone', $this->event), 20); ?></td> 
              <td><?php echo (!$ticketSold ? '0': $ticketSold) .'/'.$item->total; ?></td>
              <td class="table_options">
                <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'edit-ticket','ticket_id'=>$item->ticket_id), 'sesevent_dashboard', true), $this->translate(""), array('class' => 'sesbasic_icon_edit','title'=>$this->translate("Edit Ticket"))) ?>
                <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'delete-ticket','ticket_id'=>$item->ticket_id), 'sesevent_dashboard', true), $this->translate(""), array('class' => 'sesevent_ajax_delete sesbasic_icon_delete','data-value'=>'Are you sure want to delete this ticket? It will not be recoverable after being deleted.','title'=>$this->translate("Delete Ticket"))) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </form>
    </div>
    <?php else: ?>
    <div class="tip">
      <span>
      	<?php if(!$this->is_search_ajax){ ?>
        <?php echo $this->translate('You have not added any ticket for your event yet. Get started by <a href="'. $this->url(array('event_id' => $this->event->custom_url,'action'=>'create-ticket'), 'sesevent_dashboard', true).'">creating</a> one.'); 
        	}else{
          	echo $this->translate('No tickets were found matching your selection.');
          }
        ?>
      </span>
    </div>
    <?php endif; ?>
  </div>
  <?php if(!$this->is_search_ajax): 
  if(!$this->is_ajax): ?>
</div>
</div>
</div>
</div>
<?php endif; endif; ?>
<script>
  sesJqueryObject('#loadingimgsesevent-wrapper').hide();
  sesJqueryObject(document).on('submit', '#manage_tickets_search_form', function (event) {
    event.preventDefault();
    var searchFormData = sesJqueryObject(this).serialize();
    sesJqueryObject('#loadingimgsesevent-wrapper').show();
    new Request.HTML({
      method: 'post',
      url: en4.core.baseUrl + 'sesevent/dashboard/manage-ticket/',
      data: {
        format: 'html',
        event_id: '<?php echo $this->event_id; ?>',
        searchParams: searchFormData,
        is_search_ajax: true,
      },
      onComplete: function (response) {
        sesJqueryObject('#loadingimgsesevent-wrapper').hide();
        sesJqueryObject('#sesevent_manage_tickets_content').html(response);
      }
    }).send();
  });
</script>
