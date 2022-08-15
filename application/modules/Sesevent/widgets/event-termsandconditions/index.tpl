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
<?php if($this->edit){ ?>
	<?php 
   if($this->subject->custom_term_condition){
   	$icon = "fa fa-edit";
   	$text = $this->translate("Change Terms & Conditions");
   } else {
   	$icon = "fa fa-plus";
   	$text = $this->translate("Add Terms & Conditions");
   } ?>
  <div class="sesbasic_profile_tabs_top sesbasic_clearfix">
    <a href="<?php echo $this->url(array('event_id' =>$this->subject->custom_url,'action'=>'edit'),'sesevent_dashboard'); ?>#custom_term_condition-wrapper" class="sesbasic_button <?php echo $icon; ?>">
      <?php echo  $text; ?>
    </a>
  </div>
<?php } ?>
<div class="sesbasic_html_block">
	<?php if($this->subject->custom_term_condition) {
  					echo $this->subject->custom_term_condition;
  			}else{ ?>
       		<div class="tip">
            <span>
              <?php echo $this->translate("There are currently no terms & conditions.");?>
            </span>
          </div>     
  <?php   } ?>
</div>
<script type="application/javascript">
var tabId_tac = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_tac);	
});
</script>