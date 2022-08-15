<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view.tpl 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="sesgdpr_details_popup">
  <div class="_row">
    <div class="_l">Name</div>
    <div class="_v"><?php echo $this->item->name ? $this->item->name : '-'; ?></div>
  </div>
  <div class="_row">
    <div class="_l">Email</div>
    <div class="_v"><?php echo $this->item->email ? $this->item->email : '-'; ?></div>
	</div>
  <?php if(!empty($this->item->message)){ ?>
  	<div class="_row">  
    	<div class="_l">Message</div>
  		<div class="_v"><?php echo $this->item->message ? $this->item->message : '-'; ?></div>
  	</div>
  <?php  } ?>
  <?php if(!empty($this->item->note)){ ?>
  	<div class="_row">
      <div class="_l">Note</div>
      <div class="_v"><?php echo $this->item->note ? $this->item->note : '-'; ?></div>
  	</div>
  <?php  } ?>
  <div class="_row">
  	<div class="_l">Creation Date</div>
  	<div class="_v"><?php echo $this->item->creation_date ? $this->item->creation_date : '-'; ?></div>
  </div>
  <div class="_row">
  	<button onclick="javascript:parent.Smoothbox.close();"><?php echo $this->translate("Close");?></button>
  </div>
</div>