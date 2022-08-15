<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: comment-likes.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_ajax){ ?>
<div class="advact_mlist_popup sesbasic_clearfix sesbasic_bxs">
 <div class="advact_mlist_popup_header sesbasic_clearfix">
 	  <?php echo $this->translate($this->title); ?>
 </div>
 <div class="sesact_mlist_popup_cont">
<?php } ?>

 <div class="container_like_contnent_main sesact_mlist_popup_cont_inner" id="container_like_contnent">
 	<ul id="like_contnent">
  
       <?php
         echo $this->partial(
            '_contentlikesuser.tpl',
            'sesadvancedactivity',
            array('users'=>$this->users,'paginator'=>$this->paginator,'randonNumber'=>'contentlikeusers','resource_id'=>$this->resource_id,'resource_type'=>$this->resource_type,'execute'=>true,'page'=>$this->page,'comment_id'=>$this->comment_id)
          );                    
        ?>   
    <?php $randonNumber= 'contentlikeusers'; ?>

 </ul>
 
<div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
<div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"> <span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span></div>
 
 </div> 
 </div>
</div>