<?php

/**
* SocialEngineSolutions
*
* @category   Application_Sesadvancedactivity
* @package    Sesadvancedactivity
* @copyright  Copyright 2016-2017 SocialEngineSolutions
* @license    http://www.socialenginesolutions.com/license/
* @version    $Id: _gif.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
* @author     SocialEngineSolutions
*/
?>
<?php 
$getImages = Engine_Api::_()->getDbTable('images', 'sesfeedgif')->getImages(array('fetchAll' => 1, 'limit' => 10)); 
$enablesearch = 1;
?>
<div  class="gif_content">
  <?php 
    if($this->edit)
      $class="edit";
    else
      $class = '';
    //$gifs = Engine_Api::_()->getApi('gif','sesbasic')->getEmojisArray();
  ?>
  <div class="ses_emoji_search_bar">
    <div class="ses_emoji_search_input fa fa-search sesbasic_text_light" <?php if(empty($enablesearch)): ?> style="display:none;" <?php endif; ?>>
      <input type="text" placeholder='<?php echo $this->translate("Search GIF");?>' class="search_sesgif" />
      <!--<button type="reset" value="Reset" class="fas fa-times sesadvcnt_reset_gif"></button>-->
    </div>
  </div>
  <div class="ses_emoji_search_content sesbasic_custom_scroll sesbasic_clearfix main_search_category_srn">
  
  	<ul class="">
     <?php 
      foreach($getImages as $getImage) {
      $photo = Engine_Api::_()->storage()->get($getImage->file_id, '');
      if($photo) {
        $photo = $photo->getPhotoUrl();
     ?>
      <li rel="<?php echo $getImage->image_id; ?>">
        <a href="javascript:;" class="select_gif_adv<?php echo $class; ?>">
          <img src="<?php echo $photo; ?>" alt="" />
        </a>
      </li>
      <?php } 
      } ?>
    </ul>
<!--  
  
    <ul class="">
      <?php //foreach($gifs as $key=>$gif) { ?>
        <li rel="<?php //echo $key; ?>"><a href="javascript:;" class="select_gif_adv<?php //echo $class; ?>"><?php //echo $gif; ?></a></li>  
      <?php //} ?>
    </ul>-->
  </div>
  <div style="display:none;position:relative;height:300px;" class="main_search_cnt_srn" id="main_search_cnt_srn">
    <div class="sesgifsearch sesbasic_loading_container" style="height:100%;"></div>
  </div>
  
  <?php if(!$this->edit){ ?>
    <script type="application/javascript">
      sesJqueryObject(document).on('click','.select_gif_adv > img',function(e){
        var code = sesJqueryObject(this).parent().parent().attr('rel');
        var html = sesJqueryObject('.compose-content').html();
        if(html == '<br>')
          sesJqueryObject('.compose-content').html('');
        composeInstance.setContent(composeInstance.getContent()+' '+code);
        sesJqueryObject('.gif_comment_select').trigger('click');
      });
    </script>
  <?php } ?>
</div>