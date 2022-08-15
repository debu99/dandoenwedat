<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _events.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $events = $this->events;
$viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity(); 
?>
<div class="sesact_tip_box sesadct_event sesbasic_clearfix sesbasic_bxs centerT" id="anfevent_top">
  
  <a href="javascript:;" onclick="closeanfevent('<?php echo $events->event_id ?>', '<?php echo $viewer_id ?>');" class="sesact_addbday_close close_parent_notification_sesadv"><i class="fas fa-times sesbasic_text_light"></i></a>
  
	<i style="background-image:url(favicon.ico);"></i>
  <span class="sesadct_event_img">
    <?php if($events->file_id){ ?>
      <img src="<?php echo Engine_Api::_()->storage()->get($events->file_id, '')->getPhotoUrl(); ?>" alt="">
    <?php } ?>
  </span>
  <span class="sesadct_event_title">
   <?php if($events->title){
     echo $events->title;  
   } ?>
  </span>
  <span class="sesadct_event_des">
   <?php if($events->description){
   echo $events->description;
   } ?>
  </span>
  <?php if($this->share){ ?>
  <span class="sesadct_event_btn">
    <a href="javascript:;" class="sesbasic_button fa fa-share" onClick="openSmoothBoxInUrl('<?php echo $this->escape($this->url(array('module'=>'sesadvancedactivity', 'controller'=>'index', 'action'=>'share', 'route'=>'default', 'type'=>$events->getType(), 'id' => $events->getIdentity(), 'format' => 'smoothbox'), 'default' , true)); ?>');return false;">Share</a>
  </span>
  <?php  } ?>
</div>
<script>

function closeanfevent(event_id, user_id) {

  en4.core.request.send(new Request.HTML({
    method: 'post',              
    'url': en4.core.baseUrl + 'sesadvancedactivity/index/updateuserevent',
    'data': {
      format: 'html',
      event_id: event_id,
      user_id: user_id,
      
    },
    onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
      if(document.getElementById('anfevent_top'))
        document.getElementById('anfevent_top').destroy();
    }
  }));
  return false;

}

</script>