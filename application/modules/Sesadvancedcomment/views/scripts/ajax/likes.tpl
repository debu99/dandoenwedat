<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: likes.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php if(!$this->is_ajax){ ?>
<div class="sesadvcmt_mlist_popup sesbasic_clearfix sesbasic_bxs">
 <div class="sesadvcmt_mlist_popup_tabs sesbasic_clearfix">
 	<ul class="sesbasic_clearfix like_main_cnt_reaction">
  	<li <?php if($this->typeSelected == 'all'){ ?> class="active" <?php } ?>>
    	<a href="javascript:;"  data-type="comment"  data-rel="all">
      	<span>All <?php echo $this->countAll; ?></span>
      </a>
    </li>
  <?php foreach($this->AllTypesCount as $AllTypesCount){ ?>
  	<li <?php if($this->typeSelected == $AllTypesCount['type']){ ?> class="active" <?php } ?>>
    	<a href="javascript:;" data-type="comment" data-rel="<?php echo $AllTypesCount['type']; ?>">
      	<i style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($AllTypesCount['type']);?>);"></i>
        <span><?php echo $AllTypesCount['counts']; ?></span>
      </a>
    </li>
 <?php } ?>
  </ul>
 </div>
 <div class="sesadvcmt_mlist_popup_cont sesact_mlist_popup_cont sesbasic_clearfix">
<?php } ?>

<?php foreach($this->typesLikeData as $key=>$itemTypes){ ?>
 <div class="container_like_contnent_main sesadvcmt_mlist_popup_cont_inner" id="container_like_contnent_<?php echo $key; ?>" style="display:<?php echo $this->typeSelected == $key ? 'block' : 'none'; ?>">
 	<ul id="like_contnent_<?php echo $key; ?>">
  
       <?php
         echo $this->partial(
            '_reactionlikesuser.tpl',
            'sesadvancedcomment',
            array('users'=>$this->users,'paginator'=>$this->paginator,'randonNumber'=>$key,'resource_id'=>$this->resource_id,'resource_type'=>$this->resource_type,'typeSelected'=>$this->typeSelected,'execute'=>($this->typeSelected == $key),'page'=>$this->page,'type'=>$this->type,'item_id'=>$this->item_id,'isPageSubject'=>$isPageSubject)
          );                    
        ?>   
    <?php $randonNumber= $key; ?>

 </ul>
 
<div class="sesbasic_load_btn" style="display:<?php echo $this->typeSelected == $key ? 'block' : 'none'; ?>" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
<div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"> <span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span></div>
 
 </div>
 <?php } ?>
 
 </div>
</div>