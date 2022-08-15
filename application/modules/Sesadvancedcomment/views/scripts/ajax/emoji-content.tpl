<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: emoji-content.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<div class="ses_emoji_search_content sesbasic_custom_scroll">
<?php
 if(count($this->files)){ ?>
<ul class="_sickers">
  <?php
  foreach($this->files as $key=>$emoji){ ?>   
  <?php if(!empty($emoji->files_id)){ ?>
    <li rel="<?php echo $emoji->files_id; ?>">
      <a href="javascript:;" class="_simemoji_reaction">
        <img src="<?php echo Engine_Api::_()->storage()->get($emoji->photo_id, '')->getPhotoUrl(); ?>" alt="" />
      </a>
    </li>  
    <?php } else { ?>
      <div class="tip">
        <span>
          <?php echo $this->translate("No stickers in this category."); ?>
        </span>
      </div>
    <?php } ?>
  <?php 
  } ?>
</ul>
<?php 
}else{
 ?>
 	<div class="ses_emoji_search_noresult">
  	<i class="far fa-frown sesbasic_text_light" aria-hidden="true"></i>
  	<span class="sesbasic_text_light"><?php echo $this->translate("No Stickers to Show") ?></span>
  </div>
 <?php } ?>
</div>
<?php die; ?>