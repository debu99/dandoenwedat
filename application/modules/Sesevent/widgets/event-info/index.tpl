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
<?php
    $whereUrl = ($this->subject->is_webinar) ? $this->subject->meeting_url : $this->url(array('resource_id' => $this->subject->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true);
?>
<div class='sesevent_profile_info sesbasic_clearfix sesbasic_bxs'>
  <div class="sesevent_profile_info_row">
    <div class="sesevent_profile_info_head"><h3> <?php echo $this->event_title;?></h3></div>
    <ul class="sesevent_profile_info_row_info">
      <!-- <li class="sesbasic_clearfix">
        <span><?php echo $this->translate("Created by"); ?></span>
        <span><a href="<?php echo $this->subject->getOwner()->getHref(); ?>"><?php echo $this->subject->getOwner()->getTitle(); ?></a></span>
      </li> -->
      <?php $host = Engine_Api::_()->getItem('sesevent_host', $this->subject->host);; ?>
      <li class="sesbasic_clearfix">
        <span>
          <i title="host" class="fas fa-user-tie sesbasic_text_light"></i>&nbsp;&nbsp;&nbsp;<?php echo $this->translate("Host") ?>
        </span>
        
        <span><a href="<?php echo $host->getHref(); ?>"><?php echo $host->getTitle(); ?></a></span>

      </li>
      <!-- <li class="sesbasic_clearfix">
        <span><?php echo $this->translate("Created on"); ?></span>
        <span><?php echo $this->translate('%1$s', $this->timestamp($this->subject->creation_date)); ?></span>
      </li> -->
      
      <li class="sesbasic_clearfix">
          <span>
            <i title="Location" class="fas fa-map-marker-alt sesbasic_text_light"></i>&nbsp;&nbsp;&nbsp;<?php echo $this->translate("Location") ?>
          </span>
          <span><?php echo $this->location == ""? "Online":  $this->shortLocation($this->location); ?></span>
      </li>
      <?php if($this->gender_destribution) {?>
        <li class="sesbasic_clearfix">
          <span>
            <i class="fa fa-venus-mars"></i>&nbsp;&nbsp;<?php echo $this->translate("Gender") ?>
          </span>
          <span><?php echo $this->gender_destribution ?></span>
        </li>
      <?php }?>  

      <li class="sesbasic_clearfix">
        <span>
          <i class="fa fa-calendar-alt"></i>&nbsp;&nbsp;&nbsp;<?php echo $this->translate("Age") ?>
        </span>
        <span><?php echo $this->age_from . " - " . $this->age_to . " " . $this->translate("Year")?></span>
      </li>
      <li class="sesbasic_clearfix">
        <span>
          <i class="fa fa-user"></i>&nbsp;&nbsp;&nbsp;<?php echo $this->translate("Participants"); ?>
        </span>
        <?php if($this->fiftyfifty){ ?>
          <span><?php echo "Min. " . $this->min_participants. " - " . "Max. " . $this->max_participants . "  | " . $this->translate("Available") . ": " . '<i class="fa fa-venus"></i>&nbsp;&nbsp;'.$this->female_available.'&nbsp;&nbsp<i class="fa fa-mars"></i>&nbsp;&nbsp;'.$this->male_available?></span>
        <?php } else { ?>
          <span><?php echo "Min. " . $this->min_participants. " - " . "Max. " . $this->max_participants . "  | " . $this->translate("Available") . ": " . $this->available_spots?></span>
        <?php }?>
          </li>
    </span>
      <?php if($this->additional_costs) {?>
        <li class="sesbasic_clearfix">
          <span>
            <i class="fas fa-euro-sign"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->translate("Costs") ?>
          </span>
          <span><?php echo $this->additional_costs_amount_currency . " " . $this->additional_costs_amount ?></span>
        </li>
         <?php if($this->additional_costs_description) {?>
            <li class="sesbasic_clearfix">
              <span>
                <small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $this->translate("description") ?></small>
              </span>
              <span><small><?php echo $this->additional_costs_description ?></small></span>
            </li>
         <?php }?>   
      <?php }?>  

      <li class="sesbasic_clearfix">

        <span><i class="fas fa-chart-bar"></i>&nbsp;&nbsp;<?php echo $this->translate("Stats"); ?></span>
        <span>
          <span><?php echo $this->translate(array('<b>%s</b> Favourite', '<b>%s</b> Favourites', $this->subject->favourite_count), $this->locale()->toNumber($this->subject->favourite_count)) ?>, </span>
          <span><?php echo $this->translate(array('<b>%s</b> Comment', '<b>%s</b> Comments', $this->subject->comment_count), $this->locale()->toNumber($this->subject->comment_count)) ?>, </span>
          <span><?php echo $this->translate(array('<b>%s</b> View', '<b>%s</b> Views', $this->subject->view_count), $this->locale()->toNumber($this->subject->view_count)) ?></span>
        </span>
      </li>
    </ul>
  </div>
  <div class="sesevent_profile_info_row">
    <div class="sesevent_profile_info_head"><h3><?php echo $this->translate("When & Where"); ?></h3>&nbsp;&nbsp;<?php  echo  $this->isAttending? "": "<i class='fas fa-lock'></i>"; ?></div>
    <ul class="sesevent_profile_info_row_info">
      <li class="sesbasic_clearfix">
        <span><?php echo $this->translate("When"); ?></span>
        <span><?php echo $this->eventStartEndDates($this->subject); ?></span>
      </li>
     <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)){ ?>
      <li class="sesbasic_clearfix">
        <?php if($this->isAttending) { ?>
          <span><?php echo $this->translate("Where"); ?></span>
          <span> <a href="<?php echo $whereUrl ?>" <?php echo ($this->subject->is_webinar) ? "target='_blank'" : "class='openSmoothbox'" ?> ><?php echo $this->location == ""? "Online":  $this->location; ?> </a> </span>
        <?php } else { ?>
          <span>
            <?php echo $this->translate("Where"); ?>
          </span>
          <span><i class="fas fa-lock"></i>      <?php echo $this->translate("This will become visible after you've joined.")?></span>
        <?php } ?>
      </li>
      <?php if($this->venue && $this->isAttending) { ?>
          <li class="sesbasic_clearfix">
            <span><?php echo $this->translate("Venue"); ?></span>
            <span><?php echo $this->venue ?> </span>
          </li>
      <?php } else if($this->venue) { ?>
          <li> 
            <span>
              <?php echo $this->translate("Venue"); ?>
            </span>
            <span><i class="fas fa-lock"></i>     <?php echo $this->translate("This will become visible after you've joined.")?></span>
          </li>  
      <?php } ?>

      <?php if($this->meeting_point && $this->isAttending) { ?>
          <li class="sesbasic_clearfix">
            <span><?php echo $this->translate("Meeting Point"); ?></span>
            <span><?php echo $this->meeting_point ?> </span>
          </li>
      <?php } else if($this->meeting_point) { ?>
          <li> 
            <span>
              <?php echo $this->translate("Meeting Point"); ?>
            </span>
            <span><i class="fas fa-lock"></i>     <?php echo $this->translate("This will become visible after you've joined.")?></span>
          </li>  
      <?php } ?>

      <?php if($this->meeting_time && $this->isAttending) { ?>
          <li class="sesbasic_clearfix">
            <span><?php echo $this->translate("Meeting Time"); ?></span>
            <span><?php echo $this->meeting_time ?> </span>
          </li>
      <?php } else if($this->meeting_time) { ?>
          <li> 
            <span>
              <?php echo $this->translate("Meeting Time"); ?>
            </span>
            <span><i class="fas fa-lock"></i>     <?php echo $this->translate("This will become visible after you've joined.")?></span>
          </li>  
        <?php } ?>

      <?php if($this->tel_host && $this->isAttending && $this->eventOngoing) { ?>
          <li class="sesbasic_clearfix">
            <span><?php echo $this->translate("Tel. Host"); ?></span>
            <span><?php echo $this->tel_host ?> </span>
          </li>
        <?php } else if($this->tel_host && !$this->isAttending) { ?>
          <li> 
            <span>
              <?php echo $this->translate("Tel. Host"); ?>
            </span>
            <span><i class="fas fa-lock"></i>     <?php echo $this->translate("This will become visible after you've joined.")?></span>
          </li>  
      <?php } else if($this->tel_host && !$this->eventOngoing) { ?>
          <li> 
            <span>
              <?php echo $this->translate("Tel. Host"); ?>
            </span>
            <span><i class="fas fa-lock"></i>     <?php echo $this->translate("This event has expired.")?></span>
          </li>  
      <?php } ?>
    <?php } ?>  
    </ul>
  </div>
  <?php if( !empty($this->subject->description) ): ?>
  <div class="sesevent_profile_info_row">
      <div class="sesevent_profile_info_head"><h3><?php echo $this->translate("Description"); ?></h3></div>
      <ul class="sesevent_profile_info_row_info">
        <li class="sesbasic_clearfix"><?php echo nl2br($this->subject->description) ?></li>
      </ul>
    </div>
  <?php endif ?>
  <div class="sesevent_profile_info_row" id="sesevent_custom_fields_val">
    <div class="sesevent_profile_info_head"><?php echo $this->translate("Other Info"); ?></div>
    <div class="sesevent_view_custom_fields">
      <?php
        //custom field data
        echo $this->sesbasicFieldValueLoop($this->subject);
      ?>
    </div>
  </div>
</div> 
<script type="application/javascript">
sesJqueryObject(document).ready(function(e){
	//var lengthCustomFi	= sesJqueryObject('#sesevent_profile_info_row_info').children().length;
	if(!sesJqueryObject('.sesevent_view_custom_fields').html().trim()){
		sesJqueryObject('#sesevent_custom_fields_val').hide();
	}
})
var tabId_info = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_info);	
});
var tagAction = window.tagAction = function(tag,value){
	var url = "<?php echo $this->url(array('module' => 'sesevent','action'=>'browse'), 'sesevent_general', true) ?>?tag_id="+tag+'&tag_name='+value;
 window.location.href = url;
}
</script>