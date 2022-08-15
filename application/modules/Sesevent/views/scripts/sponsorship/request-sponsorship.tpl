<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: request-sponsorship.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
  if(!$this->is_ajax):
    echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array('event' => $this->event));
?>
<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php endif; 
  echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array('event' => $this->event)); 
?>
  <div class="sesbasic_dashboard_content_header sesbasic_clearfix">
    <h3><?php echo $this->translate('Manage Sponsorships Requests') ?> </h3>
  </div>
  <div id="sesevent_manage_tickets_content">
    <?php if( count($this->sponsorshipRequest) > 0): ?>
    <div class="sesbasic_dashboard_table sesbasic_bxs">
      <form method="post" >
        <table>
          <thead>
            <tr>
              <th class="centerT"><?php echo $this->translate("ID"); ?></th>
              <th><?php echo $this->translate("Requested User") ?></th>
              <th><?php echo $this->translate("Description") ?></th>
              <th><?php echo $this->translate("Options") ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($this->sponsorshipRequest as $item): ?>
            <tr>
              <td class="centerT"><?php echo $item->sponsorshiprequest_id ?></td>
             <?php $user = Engine_Api::_()->getItem('user',$item->user_id) ?>
              <td> <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'),array('title'=>$user->getTitle())); ?></td>
              <td><?php echo strlen(strip_tags($item->description))>90 ? substr(strip_tags($item->description),0,90).'...' : strip_tags($item->description); ?></td>
              <td class="table_options">
                <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'delete-request','id'=>$item->sponsorshiprequest_id), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Delete Requests"), 'class' => 'sesevent_ajax_delete fa fa-trash')) ?>
                 <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'email-user','id'=>$item->sponsorshiprequest_id), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Email User"), 'class' => 'openSmoothbox fa fa-envelope')) ?>
                  <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'view-request','id'=>$item->sponsorshiprequest_id), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Email User"), 'class' => 'openSmoothbox fa fa-eye')) ?>
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
        <?php echo $this->translate("No Sponsorship Request created yet.") ?>
      </span>
    </div>
    <?php endif; ?>
  </div>
<?php if(!$this->is_ajax): ?>
</div>
</div>
<?php endif; ?>