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
  <h3>Statics of this Sopnsorship order </h3>
  <table>
    <tr>
      <?php $event = Engine_Api::_()->getItem('sesevent_event', $this->item->event_id); ?>
      <td><?php echo $this->translate('Event Title') ?>:</td>
      <td><?php if(!is_null($event->title) && $event->title != '') {
        echo  $event->title ;
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <?php $sponsorship = Engine_Api::_()->getItem('sesevent_sponsorship', $this->item->sponsorship_id); ?>
      <td><?php echo $this->translate('Sponsorship Title') ?>:</td>
      <td><?php if(!is_null($sponsorship->title) && $sponsorship->title != '') {
        echo  $sponsorship->title ;
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Commission Amount') ?>:</td>
      <td><?php echo $this->item->commission_amount ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Total Service Tax') ?>:</td>
      <td><?php echo $this->item->total_service_tax ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Total Entertainment Tax') ?>:</td>
      <td><?php echo $this->item->total_entertainment_tax ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('State') ?>:</td>
      <td><?php echo $this->item->state ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Total Amount') ?>:</td>
      <td><?php echo round($this->item->total_amount, 2) ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Currency') ?>:</td>
      <td><?php echo $this->item->currency_symbol ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Date') ?>:</td>
      <td><?php echo $this->item->creation_date; ;?></td>
    </tr>
  </table>
  <br />
  <button onclick='javascript:parent.Smoothbox.close()'>
    <?php echo $this->translate("Close") ?>
  </button>
</div>