<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<div class="header_top clearfix sesbasic_bxs">
	<div class="header_top_container clearfix">
    <?php if($this->show_menu) { ?>	
      <div class="sidebar_panel_menu_btn">
        <a href="javascript:void(0);" id="sidebar_panel_menu_btn"><i></i></a>
      </div>
    <?php } ?>
    <?php if($this->show_logo) { ?>
    <div class="header_logo">
      <?php echo $this->content()->renderWidget('sespwa.menu-logo'); ?>
    </div>
    <?php } ?>
    <?php if($this->show_search) { ?>
    <div class="header_search">
      <a href="javascript:void(0);" class="mobile_search_toggle_btn fa fa-search" id="mobile_search_toggle"></a>
      <div class="minimenu_search_box" id="minimenu_search_box">
        <div class="header_searchbox">
          <?php
          if(defined('sesadvancedsearch')){
            echo $this->content()->renderWidget("advancedsearch.search");
          }else{
          echo $this->content()->renderWidget("sespwa.search");
          }
          ?>
        </div>
      </div>
    </div>
    <?php } ?>
    <?php if($this->show_mini) { ?>
    <div class="header_minimenu">
      <?php echo $this->content()->renderWidget("sespwa.menu-mini"); ?>
    </div>
    <?php } ?>
  </div>
</div>
<?php if($this->show_menu) { ?>
<div class="header_main_menu clearfix" id="sespwa_main_menu">
  <div class="header_main_menu_container">
    <?php echo $this->content()->renderWidget("sespwa.menu-main"); ?>
  </div>
</div>
<?php } ?>
<script type="text/javascript">
  sesJqueryObject(document).on('click','#mobile_search_toggle',function(){
    if(sesJqueryObject (this).hasClass('active')){
     sesJqueryObject (this).removeClass('active');
     sesJqueryObject ('.minimenu_search_box').removeClass('open_search');
    }else{
     sesJqueryObject (this).addClass('active');
     sesJqueryObject ('.minimenu_search_box').addClass('open_search');
    }
 });
</script>
