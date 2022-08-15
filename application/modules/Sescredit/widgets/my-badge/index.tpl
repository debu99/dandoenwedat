<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<div class="sescredit_mybadge sesbasic_bxs">
  <?php if($this->badge->photo_id):?>
	<div class="sescredit_mybadge_cont">		
	  <div class="_badgeimg centerT">
	    <a href="javascript:void(0);"><img src="<?php echo Engine_Api::_()->getItem('storage_file',$this->badge->photo_id)->getPhotoUrl(); ?>" /></a>
	  </div>
	</div>
  <?php endif;?>
  <div class="_badgename centerT">
    <span><?php echo $this->badge->title;?></span>
  </div>
  <div class="_badgedes centerT sesbasic_animation">
  	<span><?php echo $this->badge->description;?></span>
  </div>
</div>
