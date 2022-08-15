<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view-paymentrequest.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
<div class="sesbasic_view_stats_popup">
  <h3>Statics of this entry </h3>
  <table>
    <tr>
      <?php $event = Engine_Api::_()->getItem('sesevent_event', $this->item->event_id); ?>
      <td><?php echo $this->translate('Event Title') ?>:</td>
      <td><?php if(!is_null($event->title) && $event->title != '') { ?>
       <a href="<?php echo $event->getHref(); ?>" target="_blank"><?php echo $event->getTitle(); ?></a>
       <?php
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Owner Name') ?>:</td>
      <td><?php echo $this->item->getOwner() ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Requested Amount') ?>:</td>
      <td><?php echo $this->item->requested_amount ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Release Amount') ?>:</td>
      <td><?php echo $this->item->release_amount ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('User Message') ?>:</td>
      <td><?php echo $this->item->user_message ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Admin Message') ?>:</td>
      <td><?php echo $this->item->admin_message ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('State') ?>:</td>
      <td><?php echo $this->item->state ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Gateway Type') ?>:</td>
      <td><?php echo $this->item->gateway_type ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Currency') ?>:</td>
      <td><?php echo $this->item->currency_symbol ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Creation Date') ?>:</td>
      <td><?php echo $this->item->creation_date; ;?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Release Date') ?>:</td>
      <td><?php echo $this->item->release_date; ;?></td>
    </tr>
  </table>
  <br />
  <button onclick='javascript:parent.Smoothbox.close()'>
    <?php echo $this->translate("Close") ?>
  </button>
</div>