<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _emoji.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $getGallery = Engine_Api::_()->getDbTable('emotiongalleries', 'sesadvancedcomment')->getGallery(array('fetchAll' => 1, 'type' => 1)); 

$show = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablestickers', 1);

$enablesearch = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablesearch', 1);
$viewer = Engine_Api::_()->user()->getViewer();
$enableattachement = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'cmtattachement');
?>
<?php if(!$this->edit && !empty($show) && $enablesearch && in_array('stickers', $enableattachement)){ ?>
<!-- Sickers Search Box -->
<div class="ses_emoji_search_container sesbasic_clearfix emoji_content" id="only_stickers" <?php if(!empty($show) && in_array('stickers', $enableattachement)):?><?php if(count($getGallery) == 0 || empty($enablesearch)): ?> style="display:none;" <?php endif; ?> <?php else: ?> style="display:none;" <?php endif; ?>>
  <?php if(!empty($show) && in_array('stickers', $enableattachement)): ?>
    <div class="ses_emoji_search_bar">
      <div class="ses_emoji_search_input fa fa-search sesbasic_text_light" <?php if(empty($enablesearch)): ?> style="display:none;" <?php endif; ?>>
        <input type="text" placeholder='<?php echo $this->translate("Search stickers");?>' class="search_reaction_adv" />
        <button type="reset" value="Reset" class="fas fa-times sesadvcnt_reset_emoji"></button>
      </div>	
    </div>
  <?php endif; ?>
  <div class="ses_emoji_search_content sesbasic_custom_scroll sesbasic_clearfix main_search_category_srn">
  	<div class="ses_emoji_search_cat">
     <?php $useremoji = Engine_Api::_()->getDbTable('emotioncategories','sesadvancedcomment')->getCategories(array('fetchAll'=>true)); 
        foreach($useremoji as $cat){
     ?>
    	<div class="ses_emoji_search_cat_item">
      	<a href="javascript:;" data-title="<?php echo $cat->title; ?>" class="sesbasic_animation sesadv_reaction_cat" style="background-color:<?php echo $cat->color ?>;">
        	<img src="<?php echo Engine_Api::_()->storage()->get($cat->file_id, '')->getPhotoUrl(); ?>" alt="<?php echo $cat->title; ?>" />
          <span><?php echo $cat->getTitle() ?></span>
        </a>
      </div>
    <?php } ?>
    </div>
  </div>
    <div style="display:none;position:relative;height:300px;" class="main_search_cnt_srn">
      <div class="sesbasic_loading_container" style="height:100%;"></div>
    </div>
</div>
<?php } ?>

<?php if(!$this->edit){ ?>
<?php $useremoji = Engine_Api::_()->getDbTable('useremotions','sesadvancedcomment')->getEmotion(); 
    foreach($useremoji as $emoji){
?>
<div style="display:none;position:relative;height:100%;" class="emoji_content" ><div class="sesbasic_loading_container" style="height:100%;"></div></div>
<?php } ?>
<?php } ?>
