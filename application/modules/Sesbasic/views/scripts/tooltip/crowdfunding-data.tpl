<?php ?>
<?php $subject = $this->subject; ?>
<div class="sescrowdfunding_info_tip sesbasic_bxs">
  <div class="sescrowdfunding_info_tip_cover"> <span class="sescrowdfunding_info_tip_cover_img" <?php if(in_array('coverphoto',$this->moduleEnableTip)):?> style="background-image:url(<?php echo $subject->getPhotoUrl(); ?>);" <?php endif; ?>></span>
    <div class="sescrowdfunding_info_tip_cover_cont">
			<?php if(in_array('ownerhoto',$this->moduleEnableTip)):?>
				<div class="sescrowdfunding_info_tip_photo"> <a href="<?php echo $subject->getOwner()->getHref(); ?>"><?php echo $this->itemPhoto($subject->getOwner(), 'thumb.icon', true); ?></a> </div>
      <?php endif; ?>
      <div class="sescrowdfunding_info_tip_cover_info">
        <div class="sescrowdfunding_info_tip_title"><a href="<?php echo $subject->getHref(); ?>"><?php echo $subject->getTitle(); ?></a></div>
        <div class="sescrowdfunding_info_tip_date">by <a href="<?php echo $subject->getOwner()->getHref(); ?>"><?php echo $subject->getOwner()->getTitle(); ?></a></div>
        <?php if(in_array('category',$this->moduleEnableTip)):?>
					<?php $category = Engine_Api::_()->getItem('sescrowdfunding_category', $subject->category_id); ?>
					<?php if($category) { ?>
						<div class="sescrowdfunding_info_tip_date"><a href="<?php echo $category->getHref(); ?>"><i class="fa fa-folder-open"></i><?php echo $category->category_name; ?></a></div>
					<?php } ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if(in_array('des',$this->moduleEnableTip)):?>
  <div class="sescrowdfunding_info_tip_content sesbasic_clearfix">
    <div class="_des"><?php echo $this->string()->truncate($this->string()->stripTags($subject->short_description), 200) ?></div>
  </div>
  <?php endif; ?>
  <div class="sesbasic_tooltip_footer sesbasic_clearfix sesbm clear">
		<?php if(in_array('stats',$this->moduleEnableTip)):?>
    <div class="floatL">
      <div class="sescf_tip_stats sesbasic_clearfix"> 
        <span title="<?php echo $this->translate(array('%s like', '%s likes', $subject->like_count), $this->locale()->toNumber($subject->like_count)); ?>"> <i class="fa fa-thumbs-up sesbasic_text_light"></i> <span><?php echo $subject->like_count; ?></span> </span> 
        <span title="<?php echo $this->translate(array('%s comment', '%s comments', $subject->comment_count), $this->locale()->toNumber($subject->comment_count)); ?>"> <i class="fa fa-comment sesbasic_text_light"></i> <span><?php echo $subject->comment_count; ?></span> </span> 
        <span title="<?php echo $this->translate(array('%s view', '%s views', $subject->view_count), $this->locale()->toNumber($subject->view_count)); ?>"> <i class="fa fa-eye sesbasic_text_light"></i> <span><?php echo $subject->view_count; ?></span> </span> 
        <span title="<?php echo $this->translate(array('%s rating', '%s ratings', round($subject->rating,2)), $this->locale()->toNumber(round($subject->rating,2))); ?>"> <i class="fa fa-star sesbasic_text_light"></i> <span><?php echo round($subject->rating,2); ?></span> </span> 
      </div>
    </div>
    <?php endif; ?>
    <div class="floatR">
      <p class="sescrowdfunding_list_btns sesbasic_animation">
				<?php if(in_array('socialshare',$this->moduleEnableTip)):?>
				<?php
        $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $subject->getHref());
        $socialshareIcon = $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $subject, 'params' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => 3));
        $socialshare = '<div>'.$socialshareIcon.'</div>';
        echo $socialshare;
      ?>
      <?php endif; ?>
      <?php if(in_array('likebutton',$this->moduleEnableTip)):?>
        <!--Like Button--> 
					<?php $canComment =  $subject->authorization()->isAllowed($this->viewer, 'comment');?>
				<?php if($canComment):?>
					<?php $LikeStatus = Engine_Api::_()->sescrowdfunding()->getLikeStatusCrowdfunding($subject->crowdfunding_id, $subject->getType()); ?>
					<a href="javascript:;" data-url="<?php echo $subject->crowdfunding_id ; ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sescrowdfunding_like_<?php echo 'sescrowdfunding'; ?> sescrowdfunding_like_<?php echo 'sescrowdfunding'; ?>_<?php echo $subject->crowdfunding_id ?> <?php echo ($LikeStatus) ? 'button_active' : '' ; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $subject->like_count; ?></span></a>
				<?php endif; ?>
				<?php endif; ?>
        </p>
    </div>
  </div>
</div>
<?php die; ?>
