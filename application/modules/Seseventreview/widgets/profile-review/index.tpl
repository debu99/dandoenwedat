<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seseventreview/externals/styles/styles.css'); ?> 
<div class="sesevent_review_view sesbasic_bxs sesbasic_clearfix">
	<div class="sesevent_review_view_top">
    <?php if(in_array('title', $this->stats)): ?>
      <h2 class="sesevent_review_view_title">
        <?php echo $this->review->getTitle() ?>
      </h2>
    <?php endif; ?>
    <div class="sesevent_review_view_item_info sesbasic_clearfix">
    <?php if(in_array('postedin', $this->stats)): ?>
      <?php echo $this->htmlLink($this->item->getHref(), $this->itemPhoto($this->item, 'thumb.icon')); ?>
    <?php endif; ?> 
      <div class="sesbasic_clearfix">
        <p class='sesevent_review_view_stats sesbasic_text_light'>
          <?php if(in_array('postedin', $this->stats)): ?>
          <?php echo $this->translate('In');?> <?php echo $this->htmlLink($this->item, $this->item) ?>
          <?php endif; ?> 
        </p>
        <?php if(in_array('creationDate', $this->stats)): ?><p><?php echo $this->translate('about').' '.$this->timestamp($this->review->creation_date) ?></p><?php endif; ?>
        <p class="sesevent_review_view_stats">
          <?php if(in_array('likeCount', $this->stats)): ?>
          <span><i class="fa fa-thumbs-up sesbasic_text_light"></i><?php echo $this->translate(array('%s like', '%s likes', $this->review->like_count), $this->locale()->toNumber($this->review->like_count)); ?></span>
          <?php endif; ?>
          <?php if(in_array('commentCount', $this->stats)): ?>
          <span><i class="fa fa-comment sesbasic_text_light"></i><?php echo $this->translate(array('%s comment', '%s comments', $this->review->comment_count), $this->locale()->toNumber($this->review->comment_count))?></span>
          <?php endif; ?>
          <?php if(in_array('viewCount', $this->stats)): ?>
          <span><i class="fa fa-eye sesbasic_text_light"></i><?php echo $this->translate(array('%s view', '%s views', $this->review->view_count), $this->locale()->toNumber($this->review->view_count))?></span>
          <?php endif; ?>
        </p>
      </div>
  	</div>
  </div>
  <div class="sesevent_review_view_ratings">
    <?php if(in_array('rating', $this->stats)){ ?>
    <div class="sesbasic_rating_star sesevent_review_listing_star">
      <?php $ratingCount = $this->review->rating;?>
      <?php for($i=0; $i<5; $i++){?>
        <?php if($i < $ratingCount):?>
          <span id="" class="fa fa-star"></span>
        <?php else:?>
          <span id="" class="fa fa fa-star-o star-disable"></span>
        <?php endif;?>
      <?php }?>
    </div>
    <?php } ?>
    <?php if(in_array('parameter', $this->stats)){ ?>
    <?php $reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview')->getParameters(array('content_id'=>$this->review->getIdentity(),'user_id'=>$this->review->owner_id)); ?>
    <?php if(count($reviewParameters)>0){ ?>
      <div class="sesevent_review_show_rating_parameter sesbasic_clearfix sesbasic_bxs">
        <?php foreach($reviewParameters as $reviewP){ ?>
          <div class="sesbasic_clearfix">
            <div class="sesbasic_rating_parameter sesbasic_rating_parameter_small">
              <?php $ratingCount = $reviewP['rating'];?>
              <?php for($i=0; $i<5; $i++){?>
                <?php if($i < $ratingCount):?>
                  <span id="" class="sesbasic-rating-parameter-unit"></span>
                <?php else:?>
                  <span id="" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable"></span>
                <?php endif;?>
              <?php }?>
            </div>
            <div class="sesevent_rating_parameter_label"><?php echo $reviewP['title']; ?></div>
          </div>
        <?php } ?>
      </div>
    <?php } 
    }
    ?>
  </div>
  <?php if(in_array('pros', $this->stats) && $this->review->pros): ?>
    <p class="sesevent_review_view_body">
    <b class="label"><?php echo $this->translate("Pros: "); ?></b>
    <?php echo $this->review->pros;  ?></p>
  <?php endif; ?>
  <?php if(in_array('cons', $this->stats) && $this->review->cons): ?>
    <p class="sesevent_review_view_body">
      <b class="label"><?php echo $this->translate("Cons: "); ?></b>
      <?php echo $this->review->cons;  ?>
    </p>
  <?php endif; ?>
  <?php if(in_array('customfields', $this->stats)): ?>
  	<?php $customFieldsData = Engine_Api::_()->seseventreview()->getCustomFieldMapData($this->review); 
    	if(count($customFieldsData) > 0){ 
         foreach($customFieldsData as $valueMeta){
         if(!$valueMeta['value'])	
         	continue;
          echo '<p class="sesevent_review_view_body"><b class="label">'. $valueMeta['label']. ': </b>'.
                $valueMeta['value'].'</p>';
         }     
 			 } ?>
  <?php endif; ?>
  <?php if(in_array('recommended', $this->stats)): ?>
  	<p class="sesevent_review_view_recommended">
      <b><?php echo $this->translate("Recommended: "); ?>
      <?php if($this->review->recommended): ?>
      	<i class="fa fa-check"></i></b>
      <?php else: ?>
        <i class="fa fa-times"></i></b>
      <?php endif; ?>
    </p>
  <?php endif; ?>
  <?php if(in_array('description', $this->stats)): ?>
    <p class='sesevent_review_view_body'>
      <?php echo $this->review->description;  ?>
    </p>
  <?php endif; ?>
</div>
