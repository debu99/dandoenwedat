<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Serenity/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/jquery-1.12.4.min.js'); ?>
<div class="serenity_menu_top">
  <div class="serenity_top_inner">
     <div class="serenity_menu_top_settings">
         <ul>
            <li>
                <ul class="color_modes">
                   <li class="color-label"><?php echo $this->translate("Contrast") ?></li>
                   <li class="<?php echo empty($_SESSION['mode_theme']) || !$_SESSION['mode_theme'] ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Default Mode') ?>" onclick="defmode(this)"><i class="fa fa-eye"></i></a></li>
                   <li class="<?php echo !empty($_SESSION['mode_theme']) && $_SESSION['mode_theme'] == 'dark_mode' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Dark Mode') ?>" onclick="darkmode(this)"><i class="fa fa-eye"></i></a></li>
                </ul>
            </li>
            <li>
               <ul class="resizer">
                  <li class="resizer-label"><?php echo $this->translate("Font") ?></li>
                  <li class="<?php echo !empty($_SESSION['font_theme']) && $_SESSION['font_theme'] == '85%' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Small Font') ?>" onclick="smallfont(this)"><i class="fa fa-minus-circle"></i></a></li>
                  <li class="<?php echo empty($_SESSION['font_theme']) || !$_SESSION['font_theme'] ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Default Font') ?>" onclick="defaultfont(this)">A</a></li>
                  <li class="<?php echo !empty($_SESSION['font_theme']) && $_SESSION['font_theme'] == '115%' ? 'active' : '' ; ?>"><a href="javascript:void(0)" title="<?php echo $this->translate('Large Font') ?>" onclick="largefont(this)"><i class="fa fa-plus-circle"></i></a></li>
               </ul>
            </li>
         </ul>
     </div>
   </div>
</div>
<script>
   function smallfont(obj){
       jQuery1_12_4(obj).parent().parent().find('.active').removeClass('active');
       jQuery1_12_4(obj).parent().addClass('active');
       jQuery1_12_4('body').css({
        'font-size': '85%'
        });
       jQuery1_12_4.post(en4.core.baseUrl+"serenity/index/font",{size:"85%"},function (response) {

       })
	};
	function defaultfont(obj){
        jQuery1_12_4(obj).parent().parent().find('.active').removeClass('active');
        jQuery1_12_4(obj).parent().addClass('active');
        jQuery1_12_4('body').css({
        'font-size': '15px'
        });
        jQuery1_12_4.post(en4.core.baseUrl+"serenity/index/font",{size:""},function (response) {

        })
	};
	function largefont(obj){
        jQuery1_12_4(obj).parent().parent().find('.active').removeClass('active');
        jQuery1_12_4(obj).parent().addClass('active');
        jQuery1_12_4('body').css({
        'font-size': '115%'
        });
        jQuery1_12_4.post(en4.core.baseUrl+"serenity/index/font",{size:"115%"},function (response) {

        })
	};
	function darkmode(obj){
        jQuery1_12_4(obj).parent().parent().find('.active').removeClass('active');
        jQuery1_12_4(obj).parent().addClass('active');
        jQuery1_12_4('body').addClass("dark_mode");
        jQuery1_12_4.post(en4.core.baseUrl+"serenity/index/mode",{mode:"dark_mode"},function (response) {

        })
	};
	function defmode(obj){
        jQuery1_12_4(obj).parent().parent().find('.active').removeClass('active');
        jQuery1_12_4(obj).parent().addClass('active');
        jQuery1_12_4('body').removeClass("dark_mode");
        jQuery1_12_4.post(en4.core.baseUrl+"serenity/index/mode",{mode:""},function (response) {

        })
	};
</script>
