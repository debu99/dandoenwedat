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
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
<h3><?php echo $this->translate("Sponsorship Payment Requests") ?></h3>
<p><?php echo $this->translate(''); ?></p>
<br />
<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s event sponsorship request found.', '%s event sponsorship request found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
<div style="overflow-x:auto">
  <form method="post" >
    <table class='admin_table'>
      <thead>
        <tr>
          <th><?php echo $this->translate("Request Id"); ?></th>
          <th><?php echo $this->translate("Amount Requested"); ?></th>
          <th><?php echo $this->translate("Requested Date"); ?></th>
          <!--<th><?php echo $this->translate("Requested Message"); ?></th>-->
          <th><?php echo $this->translate("Release Amount"); ?></th>
          <th><?php echo $this->translate("Release Date"); ?></th>
          <!--<th><?php echo $this->translate("Release Message"); ?></th>-->
          <th><?php echo $this->translate("Status"); ?></th>
          <th><?php echo $this->translate("Options"); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php 
          foreach ($this->paginator as $item): ?>
        <tr>
          <td><?php echo $item->usersponsorshippayrequest_id; ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->requested_amount,$defaultCurrency); ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->dateFormat($item->creation_date	); ?></td> 
          <!--<td><?php echo $this->string()->truncate(empty($item->user_message) ? '-' : $item->user_message, 30) ?></td>-->
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->release_amount	,$defaultCurrency); ?></td>
          <td><?php echo $item->release_date && (bool)strtotime($item->release_date) ? Engine_Api::_()->sesevent()->dateFormat($item->release_date) :  '-'; ?></td> 
          <!--<td><?php echo $this->string()->truncate(empty($item->admin_message	) ? '-' : $item->admin_message, 30) ?></td>-->
          <td><?php echo $item->state; ?></td>
          <td>
          	<?php  $event = Engine_Api::_()->getItem('sesevent_event', $item->event_id); ?>
          	<?php if ($item->state == 'pending'){ ?>
                <?php echo $this->htmlLink($this->url(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'payment-sponsorship','event_id' => $event->event_id,'action'=>'approve','id'=>$item->usersponsorshippayrequest_id)), $this->translate("Approve"), array('class' => 'smoothbox')); ?> |
                <?php echo $this->htmlLink($this->url(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'payment-sponsorship','action' => 'cancel', 'id' => $item->usersponsorshippayrequest_id, 'event_id' => $event->event_id)), $this->translate("Cancel"), array('class' => 'smoothbox')); ?> |
            <?php } ?>
            <?php echo $this->htmlLink($this->url(array('action' => 'payment-requests', 'event_id' => $event->custom_url), 'sesevent_sponsorship', true), $this->translate("payment details"), array('class' => '','target'=>'_blank')); ?> |
            		<?php echo $this->htmlLink($this->url(array('action' => 'detail-payment', 'id' => $item->usersponsorshippayrequest_id, 'event_id' => $event->custom_url), 'sesevent_sponsorship', true), $this->translate("Details"), array('class' => 'smoothbox')); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
   </form>
</div>
  <br/>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no sponsorship payment request.") ?>
    </span>
  </div>
<?php endif; ?>