<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: edit.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $events = $this->list->getEvents(); ?>

<?php echo $this->form->render($this) ?>

<div style="display:none;">
  <?php if (!empty($events)): ?>
    <ul id="sesevent_list">
      <?php foreach ($events as $event): 
      	$eventMain = Engine_Api::_()->getItem('sesevent_event', $event->file_id); 
      ?>
      <li id="song_item_<?php echo $event->listevent_id ?>" class="file file-success">
        <a href="javascript:void(0)" class="event_action_remove file-remove"><?php echo $this->translate('Remove') ?></a>
        <span class="file-name">
          <?php echo $eventMain->getTitle() ?>
        </span>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    
    $('demo-status').style.display = 'none';

    //IMPORT SONGS INTO FORM
    if ($$('#sesevent_list li.file').length) {
      $$('#sesevent_list li.file').inject($('demo-list'));
      //$$('#demo-list li span.file-name').setStyle('cursor', 'move');
      $('demo-list').show()
    }
    
    //REMOVE/DELETE SONG FROM LIST
    $$('a.event_action_remove').addEvent('click', function(){
      var event_id  = $(this).getParent('li').id.split(/_/);
          event_id  = event_id[ event_id.length-1 ];
      
      $(this).getParent('li').destroy();
      new Request.JSON({
        url: '<?php echo $this->url(array('module'=> 'sesevent' ,'controller'=>'list','action'=>'delete-listevent'), 'default') ?>',
        data: {
          'format': 'json',
          'listevent_id': event_id,
          'list_id': <?php echo $this->list->list_id ?>
        }
      }).send();
      return false;
    });
});
</script>