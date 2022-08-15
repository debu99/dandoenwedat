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
<div class="sescredit_help_center sesbasic_bxs sesbasic_clearfix">
  <div class="help_center_main">
    <div class="head_banner sesbasic_bxs sesbasic_clearfix" style="background-image: url(application/modules/Sescredit/externals/images/help_banner.jpg);">
      <div class="header_desc">
        <div class="_container">
          <div class="banner_left">
            <div>
              <h2><?php echo $this->translate('Earn Credit Points <br/><span class="_diffcolor">& Have Fun !!');?></span></h2>
              <p><?php echo $this->translate('Earn maximum credit points by doing certain activities on content & feeds. Utilize these earned points for getting new Badges and upgrade your membership too.');?></p>
              <a href="#earn_credits" class="how_it_works"><i class="far fa-play-circle" aria-hidden="true"></i><?php echo $this->translate(' How to Earn Credits');?></a>
            </div>
          </div>
          <div class="banner_right">
            <img src="./application/modules/Sescredit/externals/images/man-happy.png"/>
          </div>
        </div>
      </div>
	</div>
    <div class="earn_free sesbasic_bxs sesbasic_clearfix">
      <div class="_container">
        <div class="earn_left">
          <img src="./application/modules/Sescredit/externals/images/badges.png" alt=""/>
        </div>
        <div class="earn_right">
          <h2><?php echo $this->translate('Earn Badges');?>, <br/>
          <?php echo $this->translate('Upgrade your Membership and do much more…');?></h2>
          <p><?php echo $this->translate('With the earned credit points your users can :');?></p>
          <ul>
            <li><?php echo $this->translate('Get attractive Badges based on number of earned credit points.');?></li>
            <li><?php echo $this->translate('Get their Membership upgrade to the next upper level.');?></li>
            <li><?php echo $this->translate('Can send Credit points to their friends & relatives.');?></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="join_us sesbasic_bxs sesbasic_clearfix" style="background-image: url(application/modules/Sescredit/externals/images/money.png);">
      <div class="_container">
        <div class="_inner_join">
          <h2><?php echo $this->totalPoint;?>+</h2>
          <h4><?php echo $this->translate('Credit Points earned till Date!');?></h4>
          <a href="<?php echo $this->signupURL;?>" class="how_it_works"><?php echo $this->translate('Get Started Now');?></a>
        </div>
      </div>
    </div>
    <div class="waysto_earn sesbasic_bxs sesbasic_clearfix" id="earn_credits">
      <div class="_container">
        <h2><?php echo $this->translate('How to Earn Credit Points');?></h2>
        <div class="waysto_earn_head">
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/activity.png" alt=""/>
            <div class="_title"><?php echo $this->translate('For New Activity');?></div>
          </div>
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/deletion.png" alt=""/>
            <div class="_title"><?php echo $this->translate('On Activity Deletion');?></div>
          </div>
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/affiliation.png" alt=""/>
            <div class="_title"><?php echo $this->translate('Inviter Affiliation');?></div>
          </div>
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/friend.jpg" alt=""/>
            <div class="_title"><?php echo $this->translate('Transferred to Friends');?></div>
          </div>
        </div>
        <div class="waysto_earn_bottom">
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/purchase.png" alt=""/>
            <div class="_title"><?php echo $this->translate('Received from Friends');?></div>
          </div>
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/upgrade.png" alt=""/>
            <div class="_title"><?php echo $this->translate('On Membership Upgrade');?></div>
          </div>
          <div class="ear_box">
            <img src="./application/modules/Sescredit/externals/images/site.png" alt=""/>
            <div class="_title"><?php echo $this->translate('Buy from Site');?></div>
          </div>
        </div>
      </div>
    </div>
    <div class="happy_hearts sesbasic_bxs sesbasic_clearfix" style="background-image: url(application/modules/Sescredit/externals/images/people.jpg);">
      <div class="_container">
        <div class="_inner_testimonials">
          <h2><?php echo $this->translate("You’ll<span><i class='fa fa-heart'></i></span>to Earn !");?></h2>
          <p><?php echo $this->translate('Allow your users to<span class="alter">earn</span>');?></p>
          <p><?php echo $this->translate('as much<span class="alter">credit points</span> as they can.');?></p>
        </div>
      </div>
    </div>
	</div>
</div>
<script type="text/javascript">
  jQuery("a.how_it_works").click(function(event) {
    event.preventDefault();
  var divid = jQuery(this).attr("href");
  jQuery('html, body').animate({
      scrollTop: jQuery(divid).offset().top - 30
  }, 1300);
  });
</script>