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
<ul class='sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block sesevent_sidebar_info_block'>
  <?php if(in_array('location',$this->criteria) && $this->subject->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)){ ?>
    <li class="sesbasic_clearfix sesevent_list_stats sesevent_list_location">
    	<span class="widthfull"> 
        <i title="<?php echo $this->translate('Location'); ?>" class="fas fa-map-marker-alt sesbasic_text_light"></i>
        <span title=" <?php echo $this->subject->location; ?>">
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
          <a href="<?php echo $this->url(array('resource_id' => $this->subject->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $this->subject->location ?></a>
        <?php } else { ?>
          <?php echo $this->subject->location;?>
        <?php } ?>
        </span>
      </span>
    </li>
  <?php } ?>
  <?php if(in_array('date',$this->criteria)){ ?>
    <li class="sesbasic_clearfix sesevent_list_stats sesevent_list_time">
      <span class="widthfull">
        <i title="<?php echo $this->translate('Start & End Date'); ?>" class="far fa-calendar-alt sesbasic_text_light"></i>
        <?php $dateinfoParams['starttime'] = true; ?>
        <?php $dateinfoParams['endtime']  =  true; ?>
        <?php $dateinfoParams['timezone']  =  true; ?>
        <?php echo $this->eventStartEndDates($this->subject,$dateinfoParams); ?>
      </span>
    </li>
   <?php } ?>
    <?php if(in_array('tag',$this->criteria) && count($this->eventTags)){ ?>
   <li class="sesbasic_clearfix sesevent_list_stats">
    <span class="widthfull">
          <i title="<?php echo $this->translate("Tags"); ?>" class="fa fa-tag sesbasic_text_light"></i>
          <span>
            <?php 
            	$counter = 1;
            	 foreach($this->eventTags as $tag):
                if($tag->getTag()->text != ''){ ?>
                  <a href='javascript:void(0);' onclick='javascript:tagAAAction(<?php echo $tag->getTag()->tag_id; ?>,"<?php echo $tag->getTag()->text; ?>");'>#<?php echo $tag->getTag()->text ?></a>
                  <?php if(count($this->eventTags) != $counter){ 
                  	echo ",";	
                   } ?>
          <?php	 } 
          		$counter++;
              endforeach;  ?>
          </span>
          </span>
        </li>
     <?php } ?>
    <li class="sesbasic_clearfix sesevent_list_stats">
      <span class="widthfull">
        <i title="<?php echo $this->translate('Statistics'); ?>" class="fa fa-bar-chart sesbasic_text_light"></i>
      	<span>
        <?php if(in_array('comment',$this->criteria)){ ?>
        	<span title="<?php echo $this->translate(array('%s comment', '%s comments', $this->subject->comment_count), $this->locale()->toNumber($this->subject->comment_count)); ?>"><?php echo $this->translate(array('%s comment', '%s comments', $this->subject->comment_count), $this->locale()->toNumber($this->subject->comment_count)); ?></span>, 
          <?php } ?>
         <?php if(in_array('like',$this->criteria)){ ?>
          <span title="<?php echo $this->translate(array('%s like', '%s likes', $this->subject->like_count), $this->locale()->toNumber($this->subject->like_count)); ?>"><?php echo $this->translate(array('%s like', '%s likes', $this->subject->like_count), $this->locale()->toNumber($this->subject->like_count)); ?></span>, 
           <?php } ?>
         <?php if(in_array('view',$this->criteria)){ ?>
          <span title="<?php echo $this->translate(array('%s view', '%s views', $this->subject->view_count), $this->locale()->toNumber($this->subject->view_count)); ?>"><?php echo $this->translate(array('%s view', '%s views', $this->subject->view_count), $this->locale()->toNumber($this->subject->view_count)); ?></span>, 
           <?php } ?>
         <?php if(in_array('favourite',$this->criteria)){ ?>
          <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $this->subject->favourite_count), $this->locale()->toNumber($this->subject->favourite_count)); ?>"><?php echo $this->translate(array('%s favourite', '%s favourites', $this->subject->favourite_count), $this->locale()->toNumber($this->subject->favourite_count)); ?></span>
           <?php } ?>         
  	</span>
	</li>   
  <?php if(in_array('rating',$this->criteria)){ 
          				$ratingstatstics = '';
                     if(Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating()){?> 
  <li class="sesbasic_clearfix sesevent_list_stats ">
  	 <?php
                    $ratingstatstics .= '
             					 <span class="sesevent_list_grid_rating" title="'.$this->translate(array('%s rating', '%s ratings', $this->subject->rating), $this->locale()->toNumber($this->subject->rating)).'">';?>
                        <?php if( $this->subject->rating > 0 ): 
                          	 		 for( $x=1; $x<= $this->subject->rating; $x++ ): 
                                	$ratingstatstics .= '<span class="sesbasic_rating_star_small fa fa-star"></span>';
                                 endfor; 
                                 if( (round($this->subject->rating) - $this->subject->rating) > 0): 
                                	 $ratingstatstics.= '<span class="sesbasic_rating_star_small fa fa-star-half"></span>';
                                 endif; 
                              endif;  
                          $ratingstatstics .= '</span>';
                      ?>
  </li>
  <?php
    }              
           } ?>
  <?php if(in_array('guestinfo',$this->criteria) && $this->guestInfo){ ?>
            	<li class="sesbasic_clearfix sesevent_list_stats ">
              		 <span class="widthfull">
              <i title="<?php echo $this->translate('Guests RSVP'); ?>" class="fas fa-users sesbasic_text_light"></i>
                <span>
              		<?php echo $this->attending.' '.$this->translate("Attending").'<br />' ?>
                  <?php echo $this->maybeattending.' '.$this->translate("Maybe Attending").'<br />' ?>
                  <?php echo $this->notattending.' '.$this->translate("Not Attending").'<br />' ?>
                  <?php echo $this->newattending.' '.$this->translate("Awaiting Approval").'<br />' ?>
                  </span>
                </span>
              </li>
            
          <?php } ?>
</ul>
<script type="application/javascript">
var tabId_pI = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_pI);	
});
var tagAAAction = window.tagAAAction = function(tag,value){
	var url = "<?php echo $this->url(array('module' => 'sesevent','action'=>'browse'), 'sesevent_general', true) ?>?tag_id="+tag+'&tag_name='+value;
 window.location.href = url;
}
</script>
