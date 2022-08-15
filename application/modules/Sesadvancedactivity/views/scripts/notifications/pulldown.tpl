<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: pulldown.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<ul class='' id="notifications_main" onclick="redirectPage(event);">
  <?php foreach( $this->notifications as $notification ): ?>
    <?php $user = Engine_Api::_()->getItem('user', $notification->subject_id);?>
    <li class="sesbasic_clearfix <?php if( !$notification->read ): ?>pulldown_content_list_highlighted<?php endif; ?>"  value="<?php echo $notification->getIdentity();?>">
      <div class="sesact_pulldown_item_photo">
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
      </div>
      <div class="sesact_pulldown_item_content">
        <p class="sesact_pulldown_item_content_title">
          <?php //Work for sesalbum and sesvideo plugin ?>
            <?php if($notification->object_type == 'album_photo' && Engine_Api::_()->sesbasic()->isModuleEnable('sesalbum')): ?>
              <?php $photo = Engine_Api::_()->getItem('album_photo', $notification->object_id); 
              $types = Engine_Api::_()->getDbTable('notificationtypes', 'activity')->getNotificationType($notification->type); 
              $string1 = str_replace('{item:$subject}',"",$types->body);
              $type = explode('{', $string1); 
              ?>
              <a class="feed_item_username" href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a> <?php echo " " . $type[0]. " "; ?> <a href="<?php echo $photo->getHref(); ?>" title="Open image in image viewer" onclick="getRequestedAlbumPhotoForImageViewer('<?php echo $photo->getPhotoUrl(); ?>','/albums/photo/image-viewer-detail/album_id/<?php echo $photo->album_id ?>/photo_id/<?php echo $photo->photo_id ?>');return false;" class="feed_item_username">photo</a>.</p>
            <?php elseif($notification->object_type == 'video' && Engine_Api::_()->sesbasic()->isModuleEnable('sesvideo')): 
              $types =  Engine_Api::_()->getDbTable('notificationtypes', 'activity')->getNotificationType($notification->type);
              $string1 = str_replace('{item:$subject}',"",$types->body);
              $type = explode('{', $string1);
            ?>
              <?php $video = Engine_Api::_()->getItem('video', $notification->object_id); ?>  
              <a class="feed_item_username" href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a> <?php echo " " . $type[0]. " "; ?> <a href="<?php echo $video->getHref(); ?>" onclick="getRequestedVideoForImageViewer('<?php echo $video->getPhotoUrl(); ?>','video/imageviewerdetail/<?php echo $video->owner_id ?>/<?php echo $video->video_id ?>/<?php echo $video->getSlug() ?>');return false;" class="feed_item_username">video</a>.</p>
            <?php else: ?>
              <?php echo $notification->__toString() ?>
            <?php endif; ?>
          <?php //Work for sesalbum and sesvideo plugin ?>
        </p>
        <p class="sesact_pulldown_item_content_date notification_item_general notification_type_<?php echo $notification->type ?>">
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
        var notificationD_li = $(current_link).getParent('li');
        
        // if this is true, then the user clicked on the li element itself
				if( notificationD_li.id == 'core_menu_mini_menu_update' ) {
					notificationD_li = current_link;
				}
				
        if( notificationD_li.get('class') == 'clearfix pulldown_content_list_highlighted' ) {
          notification_li.removeClass('pulldown_content_list_highlighted');
          en4.core.request.send(new Request.JSON({
            url : en4.core.baseUrl + 'sesadvancedactivity/notifications/markread',
            data : {
              format     : 'json',
              'actionid' : notificationD_li.get('value')
            },
            onSuccess : function() {
              window.location = url;
              return;
            }
          }));
        } else {
          window.location = url;
        }
      return;
    }
  }
</script>