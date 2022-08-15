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
<div class="sesevent_view_labels">
  <?php if(in_array('verified',$this->option) && $this->subject->verified){ ?>
    <span class="sesevent_label_verified"><?php echo $this->translate('Verified') ;?></span>
  <?php } ?>
  <?php if(in_array('featured',$this->option) && $this->subject->featured){ ?>
    <span class="sesevent_label_featured"><?php echo $this->translate('Featured') ;?></span>
  <?php } ?>
  <?php if(in_array('sponsored',$this->option) && $this->subject->sponsored){ ?>
    <span class="sesevent_label_sponsored"><?php echo $this->translate('Sponsored') ;?></span>
  <?php } ?>
   <?php if(strtotime($this->subject->enddate) < strtotime(date('Y-m-d')) && $this->subject->offtheday == 1){ 
   		$offtheday = 0;
   }else
   		$offtheday = $this->subject->offtheday ;
    ?>
  <?php if(in_array('offtheday',$this->option) && $offtheday){ ?>
    <span class="sesevent_label_hot"><?php echo $this->translate('Of The Day') ;?></span>
  <?php } ?>
</div>
<script type="application/javascript">
var tabId_label = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_label);	
});
</script>