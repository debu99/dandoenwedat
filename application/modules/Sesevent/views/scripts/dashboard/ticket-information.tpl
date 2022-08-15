<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ticket-information.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_ajax){ 
echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
    	<div class="sesbasic_dashboard_form">
    		<?php echo $this->form->render() ?>
      </div>
    
<?php if(!$this->is_ajax){ ?>
    </div>
</div>
</div>
</div>
<?php  } ?>
<?php if($this->is_ajax) die; ?>