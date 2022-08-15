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
<?php if($this->editOverview){ ?>
	<?php 
   if($this->subject->overview){
   	$overviewicon = "sesbasic_icon_edit";
   	$overviewtext = $this->translate("Change Overview");
   }else{
    $overviewicon = "sesbasic_icon_add";
   	$overviewtext = $this->translate("Add Overview");
   } ?>
  <div class="sesbasic_profile_tabs_top sesbasic_clearfix">
    <a href="<?php echo $this->url(array('event_id' => $this->subject->custom_url, 'action'=>'overview'), 'sesevent_dashboard', true); ?>" class="sesbasic_button <?php echo $overviewicon; ?>">
      <?php echo $overviewtext; ?>
    </a>
  </div>
<?php } ?>
<div class="sesbasic_html_block">
	<?php if($this->subject->overview) {
  					echo $this->subject->overview;
  			}else{ ?>
       		<div class="tip">
            <span>
              <?php echo $this->translate("There are currently no Overview.");?>
            </span>
          </div>     
  <?php   } ?>
</div>

<div>
<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){ ?>
      <?php echo $this->action("list", "comment", "sesadvancedcomment", array("type" => $this->subject->getType(), "id" => $this->subject->getIdentity(),'is_ajax_load'=>true)); 
        }else{
         echo $this->action("list", "comment", "core", array("type" => $this->subject->getType(), "id" => $this->subject->getIdentity())); 
         }
         ?>
</div>
<script type="application/javascript">
var tabId_overv = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_overv);	
});
</script>