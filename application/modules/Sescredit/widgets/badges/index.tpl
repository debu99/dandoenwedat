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
<div class="sescredit_badges sesbasic_bxs">
	<?php if(count($this->badges) > 0):?>
		<section>
			<div class="sescredit_badges_heading sesbasic_lbg"><?php echo $this->translate("My Badge");?></div>
	    <ul class="sescredit_badges_listing">
	      <?php foreach($this->badges as $badge):?>
	        
	        <li class="sescredit_badges_item<?php if($badge->active):?> _current<?php endif;?>">
	          <article class="sescredit_badge_tip_wrapper">
	        		<?php if($badge->active):?><span class="_currentlabel"><i class="fa fa-check-circle"></i><span><?php echo $this->translate("Current Badge"); ?></span></span><?php endif;?>
	            <div class="sescredit_badges_item_img">
	              <div class="_img centerT">
	                <a href="javascript:void(0);"><img src="<?php echo Engine_Api::_()->getItem('storage_file',$badge->photo_id)->getPhotoUrl(); ?>" /></a>
	              </div>
	            </div>
	            <div class="sescredit_badges_item_info">
	              <div class="_title centerT"><?php echo $badge->title;?></div>
	            </div>
	          </article>
	          <div class="sescredit_badge_tip sesbasic_bxs sesbasic_bg">
	            <div class="_title"><?php echo $badge->title;?></div>
	            <div class="_des"><?php echo $badge->description;?></div>
	          </div>	
	        </li>
	      <?php endforeach;?>
	    </ul>
	  </section>
	<?php endif;?>
	<?php if(count($this->allBadges) > 0):?>
		<section>
			<div class="sescredit_badges_heading sesbasic_lbg"><?php echo $this->translate("All Badges");?></div>
			<ul class="sescredit_badges_listing">
				<?php foreach($this->allBadges as $badge):?>
					<li class="sescredit_badges_item">
						<article class="sescredit_badge_tip_wrapper">
							<div class="sescredit_badges_item_img">
								<div class="_img centerT">
									<a href="javascript:void(0);"><img src="<?php echo Engine_Api::_()->getItem('storage_file',$badge->photo_id)->getPhotoUrl(); ?>" /></a>
								</div>
							</div>
							<div class="sescredit_badges_item_info">
								<div class="_title centerT"><?php echo $badge->title;?></div>
								<div class="_membercount centerT"><?php echo $this->translate(array('%s Member', '%s Members', $badge->countMember), $this->locale()->toNumber($badge->countMember)) ?></div>
							</div>
						</article>
						<div class="sescredit_badge_tip sesbasic_bxs sesbasic_bg">
							<div class="_title"><?php echo $badge->title;?></div>
							<div class="_des"><?php echo $badge->description;?></div>
						</div>	
					</li>
				<?php endforeach;?>
			</ul>
		</section>
	<?php endif;?>
 </div>	