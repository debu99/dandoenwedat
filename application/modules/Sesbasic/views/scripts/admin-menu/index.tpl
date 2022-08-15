<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/'.ucfirst($this->moduleName).'/views/scripts/dismiss_message.tpl';?>

<?php if($this->moduleName == 'sesdating') { ?>
  <div class='tabs'>
    <ul class="navigation">
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesdating', 'controller' => 'manage', 'action' => 'header-settings'), $this->translate('Header Settings')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesdating', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesdating', 'controller' => 'manage', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
      </li>
      <li class="active">
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesdating', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
      </li>
    </ul>
  </div>
<?php } ?>
<?php if($this->moduleName == 'sesatoz') { ?>
  <div class='tabs'>
    <ul class="navigation">
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesatoz', 'controller' => 'manage', 'action' => 'header-settings'), $this->translate('Header Settings')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesatoz', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesatoz', 'controller' => 'manage', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
      </li>
      <li class="active">
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesatoz', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
      </li>
    </ul>
  </div>
<?php } ?>
<?php if($this->moduleName == 'sesmaterial') { ?>
  <div class='tabs'>
    <ul class="navigation">
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesmaterial', 'controller' => 'manage', 'action' => 'header-template'), $this->translate('Header Settings')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesmaterial', 'controller' => 'settings', 'action' => 'manage-search'), $this->translate('Manage Modules for Search')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesmaterial', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
      </li>
      <li class="active">
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesmaterial', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
      </li>
    </ul>
  </div>
<?php } ?>
<?php if($this->moduleName == 'sesariana') { ?>
  <div class='tabs'>
    <ul class="navigation">
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesariana', 'controller' => 'manage', 'action' => 'header-settings'), $this->translate('Header Settings')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesariana', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesariana', 'controller' => 'manage', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
      </li>
      <li class="active">
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesariana', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
      </li>
    </ul>
  </div>
<?php } ?>
<?php if($this->moduleName == 'sesytube') { ?>
  <div class='tabs'>
    <ul class="navigation">
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesytube', 'controller' => 'manage', 'action' => 'header-settings'), $this->translate('Header Settings')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesytube', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
      </li>
      <li>
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesytube', 'controller' => 'manage', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
      </li>
      <li class="active">
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesytube', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
      </li>
    </ul>
  </div>
<?php } ?>
<?php if($this->moduleName == 'sessportz') { ?>
<div class='tabs'>
  <ul class="navigation">
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sessportz', 'controller' => 'manage', 'action' => 'header-template'), $this->translate('Header Settings')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sessportz', 'controller' => 'settings', 'action' => 'manage-search'), $this->translate('Manage Modules for Search')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sessportz', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
    </li>
    <li class="active">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sessportz', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
    </li>
  </ul>
</div>
<?php } ?>
<?php if($this->moduleName == 'sesadvancedheader') { ?>
<div class='tabs'>
  <ul class="navigation">
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedheader', 'controller' => 'manage', 'action' => 'header-settings'), $this->translate('Header Settings')) ?>
    </li>
     <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedheader', 'controller' => 'settings', 'action' => 'manage-search'), $this->translate('Manage Module for Search')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedheader', 'controller' => 'manage', 'action' => 'index'), $this->translate('Main Menu Icons')) ?>
    </li>
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedheader', 'controller' => 'manage', 'action' => 'mini-menu-icons'), $this->translate('Mini Menu icons')) ?>
    </li>
    <li class="active">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedheader', 'controller' => 'menu'), $this->translate('Mini Menu')) ?>
    </li>
  </ul>
</div>
<?php } ?>
<h2>
  <?php echo $this->translate('Mini Menu Editor') ?>
</h2>
<p>Here, you can edit the mini menu for this theme. The new menu item you are creating here, will also be created under the SocialEngine's core Mini Menu.<br />
If there is any menu item which is in SE's core Mini Menu, but not in this theme, then please click on the "Sink Menu" link below to sink and update the mini menu of this theme with the SE menu.<br />
You can also drag and drop menu items below to reorder them.</p><br />

<script type="text/javascript">

  var SortablesInstance;

  window.addEvent('domready', function() {
    $$('.item_label').addEvents({
      mouseover: showPreview,
      mouseout: showPreview
    });
  });

  var showPreview = function(event) {
    try {
      element = $(event.target);
      element = element.getParent('.admin_menus_item').getElement('.item_url');
      if( event.type == 'mouseover' ) {
        element.setStyle('display', 'block');
      } else if( event.type == 'mouseout' ) {
        element.setStyle('display', 'none');
      }
    } catch( e ) {
      //alert(e);
    }
  }


  window.addEvent('load', function() {
    SortablesInstance = new Sortables('menu_list', {
      clone: true,
      constrain: false,
      handle: '.item_label',
      onComplete: function(e) {
        reorder(e);
      }
    });
  });

 var reorder = function(e) {
     var menuitems = e.parentNode.childNodes;
     var ordering = {};
     var i = 1;
     for (var menuitem in menuitems)
     {
       var child_id = menuitems[menuitem].id;

       if ((child_id != undefined) && (child_id.substr(0, 5) == 'admin'))
       {
         ordering[child_id] = i;
         i++;
       }
     }
    ordering['menu'] = '<?php echo @$this->selectedMenu->name;?>';
    ordering['format'] = 'json';

    // Send request
    var url = '<?php echo $this->url(array('action' => 'order')) ?>';
    var request = new Request.JSON({
      'url' : url,
      'method' : 'POST',
      'data' : ordering,
      onSuccess : function(responseJSON) {
      }
    });

    request.send();
  }

  function ignoreDrag()
  {
    event.stopPropagation();
    return false;
  }

</script>
<div class="admin_menus_options">
  <?php echo $this->htmlLink(array('reset' => false, 'action' => 'create', 'name' => @$this->selectedMenu->name), $this->translate('Add Item'), array('class' => 'buttonlink sesbasic_icon_add smoothbox')) ?>
  <?php if( @$this->selectedMenu->type == 'custom' ): ?>
    <?php echo $this->htmlLink(array('reset' => false, 'action' => 'delete-menu', 'name' => $this->selectedMenu->name), $this->translate('Delete Menu'), array('class' => 'buttonlink sesbasic_icon_delete smoothbox')) ?>
  <?php endif ?>
  <?php echo $this->htmlLink(array('reset' => false, 'action' => 'sink-menu', 'name' => @$this->selectedMenu->name), $this->translate('Sink Menu'), array('class' => 'buttonlink sesbasic_icon_sink smoothbox')) ?>
</div>

<br />
<br />

<ul class="admin_menus_items" id='menu_list'>
  <?php foreach( $this->menuItems as $menuItem ): ?>
    <li class="admin_menus_item<?php if( isset($menuItem->enabled) && !$menuItem->enabled ) echo ' disabled' ?>" id="admin_menus_item_<?php echo $menuItem->name ?>">
      <span class="item_wrapper">
        <span class="item_options">
          <?php echo $this->htmlLink(array('reset' => false, 'action' => 'edit', 'name' => $menuItem->name), $this->translate('edit'), array('class' => 'smoothbox')) ?>
          <?php if( $menuItem->custom && strpos($menuItem->name, 'custom_') === 0 ): ?>
            | <?php echo $this->htmlLink(array('reset' => false, 'action' => 'delete', 'name' => $menuItem->name), $this->translate('delete'), array('class' => 'smoothbox')) ?>
          <?php endif; ?>
        </span>
        <span class="item_label">
          <?php echo $this->translate($menuItem->label) ?>
        </span>
        <span class="item_url">
          <?php
            $href = '';
            if( isset($menuItem->params['uri']) ) {
              echo $this->htmlLink($menuItem->params['uri'], $menuItem->params['uri']);
            } else if( !empty($menuItem->plugin) ) {
              echo '<a>(' . $this->translate('variable') . ')</a>';
            } else {
              echo $this->htmlLink($this->htmlLink()->url($menuItem->params), $this->htmlLink()->url($menuItem->params));
            }
          ?>
        </span>
      </span>
    </li>
  <?php endforeach; ?>
</ul>
