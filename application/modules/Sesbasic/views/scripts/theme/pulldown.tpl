<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: pulldown.tpl  2017-09-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<ul class='' id="notifications_main" onclick="redirectPage(event);">
  <?php foreach( $this->notifications as $notification ): ?>
  <?php $user = Engine_Api::_()->getItem('user', $notification->subject_id);?>
    <li class="clearfix <?php if( !$notification->read ): ?>pulldown_content_list_highlighted<?php endif; ?>"  value="<?php echo $notification->getIdentity();?>">
      <div class="pulldown_item_photo">
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
      </div>
      <div class="pulldown_item_content">
        <p class="pulldown_item_content_title">
          <?php echo $notification->__toString() ?>
        </p>
        <p class="pulldown_item_content_date notification_item_general notification_type_<?php echo $notification->type ?>">
          <?php echo $this->timestamp(strtotime($notification->date)) ?>
        </p>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<script type="text/javascript">
  
  function redirectPage(event) {
    event.stopPropagation();
    var url;
    var current_link = event.target;
    var notification_li = $(current_link).getParent('div');
    if(current_link.get('href') == null && $(current_link).get('tag')!='img') {
      if($(current_link).get('tag') == 'li') {
        var element = $(current_link).getElements('div:last-child');
        var html = element[0].outerHTML;
        var doc = document.createElement("html");
        doc.innerHTML = html;
        var links = doc.getElementsByTagName("a");
        var url = links[links.length - 1].getAttribute("href");
      }
      else
      url = $(notification_li).getElements('a:last-child').get('href');
      if(typeof url == 'object') {
        url = url[0];
      }
      notification_li.removeClass('pulldown_content_list_highlighted');
      new Request.JSON({
        url : en4.core.baseUrl + 'sesbasic/theme/markreadmention',
        data : {
          format     : 'json',
          'actionid' : sesJqueryObject(current_link).closest('li').attr('value')
        },
        onSuccess : function() {
          window.location = url;
        }
      }).send();      
    }
  }
</script>
