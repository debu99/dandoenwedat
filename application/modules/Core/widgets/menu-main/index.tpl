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
<?php if( $this->menuFromTheme ): ?>
  <ul class="navigation">
    <?php foreach( $this->navigation as $link ): ?>
      <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
        <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
          <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
          <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
          <span><?php echo $this->translate($link->getlabel()) ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <?php $countMenu = 0; ?>
  <div class="main_menu_navigation scrollbars">
    <ul class="navigation">
      <?php foreach( $this->navigation as $link ): ?>
        <?php if( $countMenu < $this->menuCount ): ?>
          <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
            <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
              <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
              <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
              <span><?php echo $this->translate($link->getlabel()) ?></span>
            </a>
          </li>
        <?php else:?>
          <?php break;?>
        <?php endif;?>
        <?php $countMenu++;?>
      <?php endforeach; ?>
      <?php if (count($this->navigation) > $this->menuCount):?>
        <?php $countMenu = 0; ?>
        <li class="more_tab">
          <a href="javascript:void(0);">
            <span><?php echo $this->translate("More") ?></span>
            <i class="fa fa-angle-down"></i>
          </a>
          <ul class="navigation_submenu">
            <?php foreach( $this->navigation as  $link ): ?>
              <?php if ($countMenu >= $this->menuCount): ?>
                <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                  <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                    <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                    <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                    <span><?php echo $this->translate($link->getlabel()) ?></span>
                  </a>
                </li>
              <?php endif;?>
              <?php $countMenu++;?>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endif;?>
    </ul>
  </div>
  <div class="core_main_menu_toggle panel-toggle"></div>
  <script type="text/javascript">
    en4.core.layout.setLeftPannelMenu('<?php echo $this->menuType?>');
  </script>
<?php endif;
