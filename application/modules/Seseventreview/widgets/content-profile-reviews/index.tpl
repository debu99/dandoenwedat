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

<?php if($this->allowedCreate && $this->cancreate && $this->viewer()->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.review', 1) && !$this->isReview): ?>
  <div class="sesbasic_profile_tabs_top sesbasic_clearfix">
    <?php echo $this->htmlLink(array('route' => 'seseventreview_extended', 'controller' => 'index', 'action' => 'create', 'type'=>$this->subject()->getGuid()), $this->translate('Write a Review'), array('class' => 'sesbasic_button fa fa-plus'));?>
  </div>
<?php endif;?>

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
<ul class="sesevent_review_listing sesbasic_clearfix sesbasic_bxs">
  <?php foreach( $this->paginator as $item ): ?>
  <li class="sesbasic_clearfix">
    <div class="sesevent_review_listing_left_column">
      <div class="sesevent_review_listing_reviewer sesbasic_clearfix">
      <?php if(in_array('postedBy', $this->stats)): ?>
        <div class='sesevent_review_listing_reviewer_photo'>
          <?php echo $this->htmlLink($item->getOwner()->getHref(), $this->itemPhoto($item->getOwner(), 'thumb.icon')) ?>
        </div>
       <?php endif; ?>
        <div class='sesevent_review_listing_reviewer_info'>
         <?php if(in_array('postedBy', $this->stats)): ?>
        	<div class="sesevent_review_listing_reviewer_name"><?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?></div>
         <?php endif; ?>
          <?php if(in_array('creationDate', $this->stats)): ?>
          <p class="sesbasic_text_light">
            <?php echo $this->translate('about');?>
            <?php echo $this->timestamp(strtotime($item->creation_date)) ?>
          </p>
          <?php endif; ?>
          <p class="sesbasic_text_light">
           <?php if(in_array('likeCount', $this->stats)): ?>
            <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up"></i><?php echo $item->like_count; ?></span>
            <?php endif; ?>
            <?php if(in_array('commentCount', $this->stats)): ?>
            <span title="<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count))?>"><i class="fa fa-comment"></i><?php echo $item->comment_count;?></span>
             <?php endif; ?>
             <?php if(in_array('viewCount', $this->stats)): ?>
            <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye"></i><?php echo $item->view_count; ?></span>
             <?php endif; ?>
          </p>
        </div>	
      </div>
      <div class='sesevent_review_listing_options clear sesbasic_animation'>
      <?php if($item->authorization()->isAllowed($this->viewer(), 'edit')) { ?>
        <div>
        	<?php echo $this->htmlLink(array('slug' => $item->getSlug(), 'action' => 'edit', 'review_id' => $item->getIdentity(), 'route' => 'seseventreview_view', 'reset' => true), $this->translate('Edit Review'), array('class' => 'fa fa-pencil')) ?>
        </div>
        <?php } ?>
     <?php if($item->authorization()->isAllowed($this->viewer(), 'delete')) { ?>
        <div>
        	<?php echo $this->htmlLink(array('route' => 'seseventreview_extended', 'action' => 'delete', 'type' => $item->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete Review'), array('class' => 'fa fa-trash smoothbox')); ?>
        </div>
        <?php } ?>
         <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.report', 1) && $this->viewer()->getIdentity() && in_array('report', $this->stats)): ?>
        	<div>
            	<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'report', 'action' => 'create', 'subject' => $item->getGuid(), 'format' => 'smoothbox',), $this->translate('Report'), array('class' => 'fa fa-flag smoothbox')); ?>
            </div>
        <?php endif; ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.allow.share', 1) && $this->viewer()->getIdentity() && in_array('share', $this->stats)): ?>
        	<div>
            	<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $item->getType(), 'id' => $item->getIdentity(), 'format' => 'smoothbox'), $this->translate('Share Review'), array('class' => 'fa fa-share smoothbox')); ?>
            </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="sesevent_review_listing_right_column">
     <?php if(in_array('title', $this->stats)): ?>
      <div class='sesevent_review_listing_title'>
      	<?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
      </div>
     <?php endif; ?>
    	<div class="sesevent_review_listing_top sesbasic_clearfix">
       <?php if(in_array('rating', $this->stats)): ?>
        <div class="sesbasic_rating_star">
          <?php $ratingCount = $item->rating;?>
          <?php for($i=0; $i<5; $i++){?>
            <?php if($i < $ratingCount):?>
              <span id="" class="fa fa-star"></span>
            <?php else:?>
              <span id="" class="fa fa fa-star-o star-disable"></span>
            <?php endif;?>
          <?php }?>
        </div>
       <?php endif ?>
        <?php if(in_array('parameter', $this->stats)){ ?>
        <?php $reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview')->getParameters(array('content_id'=>$item->getIdentity(),'user_id'=>$item->owner_id)); ?>
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
      <?php if(in_array('pros', $this->stats) && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.pros', 1)): ?>
        <div class="sesevent_review_listing_body">
          <b><?php echo $this->translate("Pros: "); ?></b>
          <?php echo $this->string()->truncate($this->string()->stripTags($item->pros), 300) ?>
        </div>
      <?php endif; ?>
      <?php if(in_array('cons', $this->stats) && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventreview.show.cons', 1)): ?>
        <div class="sesevent_review_listing_body">
          <b><?php echo $this->translate("Cons: "); ?></b>
          <?php echo $this->string()->truncate($this->string()->stripTags($item->cons), 300) ?>
        </div>
      <?php endif; ?>
      <?php if(in_array('description', $this->stats) && $item->description): ?>
      <div class='sesevent_review_listing_body'>
        <b><?php echo $this->translate("Description: "); ?></b>
        <?php echo $this->string()->truncate($this->string()->stripTags($item->description), 300) ?>
      </div>
      <?php endif; ?>
      	<a href="<?php echo $item->getHref(); ?>" class="floatR"><?php echo $this->translate("Continue Reading"); ?> &raquo;</a>
		</div>
  </li>
  <?php endforeach; ?>
</ul>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate('No review have been posted in this event yet.');?>
  </span>
</div>
<?php endif; ?>
<script type="application/javascript">
var tabId_review = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_review);	
});
</script>
