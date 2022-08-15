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
<?php 
$viewer = Engine_Api::_()->user()->getViewer();
$module_name = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
$action_name = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
$controller_name = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
?>

<script>

	function showHideInformation( id) {

    if($(id).style.display == 'block') {

      $(id).style.display = 'none';

			$('down_arrow').style.display = 'block';

			$('uparrow').style.display = 'none';

    } else {

      $(id).style.display = 'block';

			$('down_arrow').style.display = 'none';

			$('uparrow').style.display = 'block';

    }

	}

</script>

<div class="menu_left_panel_overlay"></div>

<div class="menu_left_panel" id="menu_left_panel">

	<div class="menu_left_panel_container">

    <?php if($this->viewer()->getIdentity() != 0){ ?>

      <div class="menu_left_panel_top_section clearfix" style="background-image:url(<?php echo Engine_Api::_()->sespwa()->getFileUrl($this->menuinformationimg); ?>);">

        <div class="menu_left_panel_top_section_img">

        	<?php echo $this->htmlLink($this->viewer()->getHref(), $this->itemPhoto($this->viewer(), 'thumb.profile')) ?>

        </div>

        <div class="menu_left_panel_top_section_name">

        	<span><?php echo $this->viewer()->getTitle(); ?></span>

          <a href="javascript:void(0);" class="menu_left_panel_top_section_btn" onclick="showHideInformation('sespwa_information');"><i class="fa fa-chevron-down" id="down_arrow"></i><i id="uparrow" class="fa fa-chevron-up" style="display:none;"></i></a>

        </div>

        <div class="menu_left_panel_top_section_options clearfix" style="display:none;" id="sespwa_information">

        	<div class="dropdown_caret"><span class="caret_outer"></span><span class="caret_inner"></span></div>
       		  <?php // This is rendered by application/modules/core/views/scripts/_navIcons.tpl
              echo $this->navigation()->menu()->setContainer($this->homelinksnavigation)->setPartial(array('_navIcons.tpl', 'core'))->render();

            ?>

        </div>   

    	</div>

    <?php } ?>

    

    <div class="menu_left_panel_nav_container sesbasic_clearfix" style="background-image:url(<?php echo $this->backgroundImg; ?>);">

      <ul class="menu_left_panel_nav clearfix">



        <?php foreach( $this->navigation as $navigationMenu ):  ?>

          <?php 

            $explodedString = explode(' ', @$navigationMenu->class);

            $menuName = end($explodedString); 

            $moduleName = str_replace('core_main_', '', $menuName);

            $mainMenuIcon = Engine_Api::_()->sespwa()->getMenuIcon(end($explodedString));

            if($this->submenu){

              $subMenus = Engine_Api::_()->getApi('menus', 'core')->getNavigation($moduleName.'_main'); 

              $menuSubArray = $subMenus->toArray();

            } else {

              $subMenus = array();

            }

          ?>

          <li class="<?php if ($navigationMenu->active): ?>active<?php endif; ?> <?php if(count($subMenus) > 0): ?>toggled_menu_parant<?php endif; ?>">

            <?php if(end($explodedString)== 'core_main_invite'):?>

              <a class= "<?php echo $navigationMenu->class ?>" href='<?php echo $this->url(array('module' => 'invite'), $navigationMenu->route, true) ?>'>

            <?php elseif(end($explodedString)== 'core_main_home' && ($this->viewer->getIdentity() != 0)):?>

              <a class= "<?php echo $navigationMenu->class ?>" href='<?php echo $this->url(array('action' => 'home'), $navigationMenu->route, true) ?>'>

            <?php else:?>

              <a class= "<?php echo $navigationMenu->class ?>" href='<?php echo $navigationMenu->getHref() ?>'>

            <?php endif;?>

            <?php if(count($subMenus) > 0): ?><i class="expcoll-icon sespwa_menu_main"></i><?php endif;?>

            <?php if(!empty($mainMenuIcon)):?>
              <?php $icon = $this->storage->get($mainMenuIcon, '');
              if($icon) {
              $icon = $icon->getPhotoUrl(); ?>
              <i class="menuicon" style="background-image:url(<?php echo $icon; ?>);"></i>
              <?php } else { ?>
              <?php } ?>

            <?php else: ?>

              <i class="menuicon fa <?php echo $navigationMenu->get('icon') ? $navigationMenu->get('icon') : '' ?>"></i>

            <?php endif;?>

            <span><?php echo $this->translate($navigationMenu->label); ?></span>

            </a> 

            <?php if(count($subMenus) > 0 && $this->submenu): ?>

              <ul class="sespwa_toggle_sub_menu" style="display:none;">

                <?php 

                $counter = 0; 

                foreach( $subMenus as $subMenu): 

                $active = isset($menuSubArray[$counter]['active']) ? $menuSubArray[$counter]['active'] : 0;

                ?>

                  <li class="sesbasic_clearfix <?php echo ($active) ? 'selected_sub_main_menu' : '' ?>">

                      <a href="<?php echo $subMenu->getHref(); ?>" class="<?php echo $subMenu->getClass(); ?>">

                      <?php if($this->show_menu_icon):?><i class="fa fa-angle-right"></i><?php endif;?><span><?php echo $this->translate($subMenu->getLabel()); ?></span>

                    </a>

                  </li>

                <?php 

                $counter++;

                endforeach; ?>

              </ul>

            <?php endif; ?>

          </li>

        <?php endforeach; ?>

      </ul>

      <?php if($viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.accountsetting', 1)) { ?>

        <div class="menu_left_panel_nav_section"><?php echo $this->translate("Account Settings");?></div>

        <ul class="menu_left_panel_nav clearfix">

          <?php foreach( $this->settingNavigation as $link ): ?>

            <li class="<?php echo $link->get('active') ? 'active' : '' ?>">

              <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"

                <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >

                <span><?php echo $this->translate($link->getlabel()) ?></span>

              </a>

            </li>

          <?php endforeach; ?>

          

          <?php if($viewer->getIdentity()) { ?>

            <li>

              <a href="<?php echo $this->url(array(), 'user_logout', true)?>"><span><?php echo $this->translate('Logout');?></span></a>

            </li>

          <?php } ?>

        </ul>

      <?php } ?>

      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.footer', 1)) { ?>

        <div class="menu_left_panel_nav_section"><?php echo $this->translate("Helps & Settings");?></div>

        <ul class="menu_left_panel_nav clearfix">

          <?php foreach( $this->footernavigation as $link ): ?>

            <li class="<?php echo $link->get('active') ? 'active' : '' ?>">

              <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"

                <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >

                <span><?php echo $this->translate($link->getlabel()) ?></span>

              </a>

            </li>

          <?php endforeach; ?>

        </ul>

      <?php } ?>

      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.fotrsocialshare', 1)) { ?>

        <ul class="menu_left_social clearfix">

          <?php foreach( $this->socialsharenavigation as $link ): ?>

            <li class="<?php echo $link->get('active') ? 'active' : '' ?>">

              <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"

                <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >

                <i class="fab <?php echo $link->get('icon') ? $link->get('icon') : 'fa-star' ?>"></i>

                <span><?php echo $this->translate($link->getlabel()) ?></span>

              </a>

            </li>

          <?php endforeach; ?>

        </ul>

      <?php } ?>
      
      <?php if( 1 !== count($this->languageNameList) ): ?>
        <div class="menu_left_language">
          <form method="post" action="<?php echo $this->url(array('controller' => 'utility', 'action' => 'locale'), 'default', true) ?>" style="display:inline-block">
            <?php $selectedLanguage = $this->translate()->getLocale() ?>
            <?php echo $this->formSelect('language', $selectedLanguage, array('onchange' => '$(this).getParent(\'form\').submit();'), $this->languageNameList) ?>
            <?php echo $this->formHidden('return', $this->url()) ?>
          </form>
        </div>
      <?php endif; ?>
      
      <a id="install-btn" class="menu_left_add_to_home_btn" href="javascript:;">Add to Home Screen</a>
    </div>
  </div>
</div>
<script type="text/javascript">

	sesJqueryObject(document).on('click','.sespwa_menu_main',function(e){

	  if(sesJqueryObject(this).parent().parent().find('ul').children().length == 0)

	  	return true;

	  e.preventDefault();

	  if(sesJqueryObject(this).parent().hasClass('open_toggled_menu')){

		sesJqueryObject('.open_toggled_menu').parent().find('ul').slideToggle('slow');

		sesJqueryObject(this).parent().removeClass('open_toggled_menu');

	  }else{

		sesJqueryObject('.open_toggled_menu').parent().find('ul').slideToggle('slow');

		sesJqueryObject(this).parent().parent().find('ul').slideToggle('slow');

		sesJqueryObject('.open_toggled_menu').removeClass('open_toggled_menu');

		sesJqueryObject(this).parent().addClass('open_toggled_menu');

	  }

	  return false;

  });

   sesJqueryObject(document).on('click','.menu_left_panel_overlay',function(){

		 sesJqueryObject('#sidebar_panel_menu_btn').trigger('click');

	});

  sesJqueryObject(document).on('click','#sidebar_panel_menu_btn',function(){

    if(sesJqueryObject (this).hasClass('activesespwa')) {
     sesJqueryObject (this).removeClass('activesespwa');
     sesJqueryObject ("body").addClass('sidebar-toggled');
    } else {
     sesJqueryObject (this).addClass('activesespwa');
     sesJqueryObject ("body").removeClass('sidebar-toggled');
    }

 });

 sesJqueryObject(document).ready(function(){

	var menuElement = sesJqueryObject('.sespwa_menu_main').parent().parent("[class*='active']");

    menuElement.find('ul').show();

   	if(menuElement.find('ul').length)

		menuElement.find('a').addClass('open_toggled_menu');

    var slectedIndex = sesJqueryObject('.selected_sub_main_menu').index();

	if(sesJqueryObject('.selected_sub_main_menu').parent().hasClass('sespwa_toggle_sub_menu')){

	  sesJqueryObject('.selected_sub_main_menu').parent().children().each(function(index,element){

		if(index == slectedIndex)  

			return false;

		sesJqueryObject(this).addClass('sespwa_sub_menu_selected');

	  });

	}

 });
 
function doResizeForButton(){
  sesJqueryObject('.layout_sespwa_menu_main').show();
  sesJqueryObject ('#sidebar_panel_menu_btn').addClass('activesespwa');
  // sesJqueryObject ("body").addClass('sidebar-toggled');
	var height = sesJqueryObject(".layout_sespwa_header").height();
	if($("menu_left_panel")) {
		$("menu_left_panel").setStyle("top", height+"px");
	}
	var heightPannel = sesJqueryObject("#menu_left_panel").height();
	sesJqueryObject('#global_content').css('min-height',heightPannel+'px');
}

sesJqueryObject(document).ready(function(){
	doResizeForButton();
});

setTimeout(function(){ sesJqueryObject ("body").addClass('showmenupanel'); }, 100);
</script>

<script type="application/javascript">
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
  // Stash the event so it can be triggered later.
  deferredPrompt = e;
});
var btnAdd = document.getElementById('install-btn');
btnAdd.addEventListener('click', (e) => {
  if(deferredPrompt) {
    // hide our user interface that shows our A2HS button
    btnAdd.style.display = 'none';
    // Show the prompt
    deferredPrompt.prompt();
    // Wait for the user to respond to the prompt
    deferredPrompt.userChoice
      .then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          console.log('User accepted the A2HS prompt');
        } else {
          console.log('User dismissed the A2HS prompt');
        }
        deferredPrompt = null;
      });
  }
});
</script>
