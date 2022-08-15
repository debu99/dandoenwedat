<div class="sespage_info_tip sesbasic_bxs">
  <div class="sespage_info_tip_cover">
  	<span class="sespage_info_tip_cover_img" style="background-image:url(http://image.pbs.org/video-assets/rc6moTn-asset-mezzanine-16x9-RVQaqL3.jpg');"></span>
    <div class="sespage_info_tip_cover_cont">
      <div class="sespage_info_tip_photo">
      	<a href=""><img src="https://media.mnn.com/assets/images/2010/02/baby-orangutan.jpg.1000x0_q80_crop-smart.jpg"></a>
      </div>
      <div class="sespage_info_tip_cover_info">
        <div class="sespage_info_tip_title"><a href="">Page Title Will Come here</a></div>
        <div class="sespage_info_tip_date">by <a href="">Owner Name</a></div>
        <div class="sespage_info_tip_date"><a href="">Category</a></div>
      </div>
    </div>
  </div>
  <div class="sespage_info_tip_content sesbasic_clearfix">
    <div class="_des">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut fringilla ultricies dolor vel porttitor. Proin id tincidunt mauris, in ...</div>
    <div class="_contactinfo">
      <p class="sesbasic_clearfix">
        <i class="sesbasic_text_light fa fa-globe"></i>
        <span><a class="sesbasic_linkinherit" target="_blank" href='' title=''>websiteurl will come here</a></span>
      </p>
      <p class="sesbasic_clearfix">
      	<i class="sesbasic_text_light fa fa-phone-square "></i>
        <span>
          <span><a href='' target="_blank" class="sesbasic_linkinherit">owneremail@email.com</a></span>
          <span>9876543210</span>
          <?php if($page->page_contact_facebook || $page->contest_contact_linkedin || $page->page_contact_twitter):?>
            <span class="_sociallinks">
              <a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($page->page_contact_facebook, PHP_URL_SCHEME) === null ? 'https://' . $page->page_contact_facebook : $page->page_contact_facebook; ?>'><i class="fab fa-facebook-f-square"><?php echo parse_url($page->page_contact_facebook, PHP_URL_SCHEME) === null ? '' . $page->page_contact_facebook : $page->page_contact_facebook; ?></i></a>
              <a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($page->page_contact_linkedin, PHP_URL_SCHEME) === null ? 'https://' . $page->page_contact_linkedin : $page->page_contact_linkedin; ?>'><i class="fa fa-linkedin-square"><?php echo parse_url($page->page_contact_linkedin, PHP_URL_SCHEME) === null ? '' . $page->page_contact_linkedin : $page->page_contact_linkedin; ?></i></a>
              <a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($page->page_contact_twitter, PHP_URL_SCHEME) === null ? 'https://' . $page->page_contact_twitter : $page->page_contact_twitter; ?>'><i class="fab fa-twitter-square"><?php echo parse_url($page->page_contact_twitter, PHP_URL_SCHEME) === null ? '' . $page->page_contact_twitter : $page->page_contact_twitter; ?></i></a>
            </span>
          <?php endif;?>  
       	</span>
      </p>	
    </div>
    <div class="_btn"><?php include APPLICATION_PATH .  '/application/modules/Sespage/views/scripts/_dataSharing.tpl';?></div>
  </div>
  <div class="sesbasic_tooltip_footer sespage_info_tip_footer sesbasic_clearfix">
    <?php include APPLICATION_PATH .  '/application/modules/Sespage/views/scripts/_databuttons.tpl';?>
	</div>
</div>


<?php die; ?>