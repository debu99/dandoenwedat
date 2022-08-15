<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: request-event.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="text/javascript">
  var seseventWidgetRequestSend = function(action, event_id, notification_id, rsvp)
  {
    var url;
    if( action == 'accept' )
    {
      url = '<?php echo $this->url(array('controller' => 'member', 'action' => 'accept'), 'sesevent_extended', true) ?>';
    }
    else if( action == 'reject' )
    {
      url = '<?php echo $this->url(array('controller' => 'member', 'action' => 'reject'), 'sesevent_extended', true) ?>';
    }
    else
    {
      return false;
    }

    (new Request.JSON({
      'url' : url,
      'data' : {
        'event_id' : event_id,
        'format' : 'json',
        'rsvp' : rsvp
        //'token' : '<?php //echo $this->token() ?>'
      },
      'onSuccess' : function(responseJSON)
      {
        if( !responseJSON.status )
        {
          $('sesevent-widget-request-' + notification_id).innerHTML = responseJSON.error;
        }
        else
        {
          $('sesevent-widget-request-' + notification_id).innerHTML = responseJSON.message;
        }
      }
    })).send();
  }
</script>

<li id="sesevent-widget-request-<?php echo $this->notification->notification_id ?>">
  <?php echo $this->itemPhoto($this->notification->getObject(), 'thumb.icon') ?>
  <div>
    <div>
      <?php echo $this->translate('%1$s has invited you to the event %2$s', $this->htmlLink($this->notification->getSubject()->getHref(), $this->notification->getSubject()->getTitle()), $this->htmlLink($this->notification->getObject()->getHref(), $this->notification->getObject()->getTitle())); ?>
    </div>
    <div>
      <button type="submit" onclick='seseventWidgetRequestSend("accept", <?php echo $this->string()->escapeJavascript($this->notification->getObject()->getIdentity()) ?>, <?php echo $this->notification->notification_id ?>, 2)'>
        <?php echo $this->translate('Attending');?>
      </button>
      <button type="submit" onclick='seseventWidgetRequestSend("accept", <?php echo $this->string()->escapeJavascript($this->notification->getObject()->getIdentity()) ?>, <?php echo $this->notification->notification_id ?>, 1)'>
        <?php echo $this->translate('Maybe Attending');?>
      </button>
      <?php echo $this->translate('or');?>
      <a href="javascript:void(0);" onclick='seseventWidgetRequestSend("reject", <?php echo $this->string()->escapeJavascript($this->notification->getObject()->getIdentity()) ?>, <?php echo $this->notification->notification_id ?>)'>
        <?php echo $this->translate('ignore request');?>
      </a>
    </div>
  </div>
</li>