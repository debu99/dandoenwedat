<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: saved-event.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?> 
<?php if( count($this->paginator) > 0 ): ?>
  <ul class='sesevents_browse'>
    <?php foreach( $this->paginator as $result ): 
    
      $result = Engine_Api::_()->getItem($result->resource_type, $result->resource_id);
      ?>
      <li class="sesevents_list_view sesbm sesbasic_clearfix sesbasic_bxs">
        <?php
          $href = $result->getHref(); 
          $imageURL = $result->getPhotoUrl('thumb.profile'); 
        ?>
        <div class='sesevent_thumb' style='height:200px;width:200px;'>
          <a href='<?php echo $href?>' class='sesevent_thumb_img'>
            <span style='background-image:url(<?php echo $imageURL?>);'></span>
          </a>
        </div>
        <div class="sesevents_list_info">
          <div class="sesevent_list_title">
            <?php echo $this->htmlLink($result->getHref(), $result->getTitle()) ?>
          </div>
          <div class="sesevent_list_stats">
            <span>
              <i class='fa fa-user sesbasic_text_light' title='<?php echo $this->translate('By');?>'></i>	
              <?php echo $this->htmlLink($result->getOwner()->getHref(), $result->getOwner()->getTitle()) ?>
            </span>
          </div>
          <?php if($result->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)):?>
	    <div class='sesevent_list_stats sesevent_list_location'>
	      <span class='widthfull'>
		<i class='fas fa-map-marker-alt sesbasic_text_light' title='<?php echo $this->translate('Location');?>'></i>
		<span title='<?php echo $result->location; ?>'>
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
        <a href="<?php echo $this->url(array('resource_id' => $result->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $result->location ?></a>
      <?php } else { ?>
        <?php echo $result->location;?>
      <?php } ?>
		</span>
	      </span>
	    </div> 
          <?php endif;?>
          <div class='sesevent_list_stats sesevent_list_time'>
          	<span class='widthfull'>
              <i class='far fa-calendar-alt sesbasic_text_light' title='".$this->translate('Event Time')."'></i>
              <span><span><?php echo $this->locale()->toDateTime($result->starttime) ?></span></span>
            </span>
          </div>  

          <div class="sesevent_list_des">
            <?php echo $result->getDescription() ?>
          </div>
          <div class="sesevent_list_options sesbasic_clearfix">
          <?php if( $this->viewer() && !$result->membership()->isMember($this->viewer(), null) ): ?>
            <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller'=>'member', 'action' => 'join', 'event_id' => $result->getIdentity()), $this->translate('Join Event'), array(
              'class' => 'sesbasic_button smoothbox fa fa-check'
            )) ?>
          <?php elseif( $this->viewer() && $result->membership()->isMember($this->viewer()) && !$result->isOwner($this->viewer()) ): ?>
            <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller'=>'member', 'action' => 'leave', 'event_id' => $result->getIdentity()), $this->translate('Leave Event'), array(
              'class' => 'sesbasic_button smoothbox fa fa-times'
            )) ?>
          <?php endif; ?>
        </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if( $this->paginator->count() > 1 ): ?>
    <?php echo $this->paginationControl($this->paginator, null, null, array('query' => array())); ?>
  <?php endif; ?>
<?php else: ?>
  <div class="tip">
    <span>
        <?php echo $this->translate('You have not joined any events yet.') ?>
        <?php if( $this->canCreate): ?>
          <?php echo $this->translate('Why don\'t you %1$screate one%2$s?',
            '<a href="'.$this->url(array('action' => 'create'), 'sesevent_general').'">', '</a>') ?>
        <?php endif; ?>
    </span>
  </div>
<?php endif; ?>
