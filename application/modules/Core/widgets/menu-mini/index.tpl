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
<?php
  $themes = Engine_Api::_()->getDbtable('themes', 'core')->fetchAll();
  $activeTheme = $themes->getRowMatching('active', 1);
  $manifest = APPLICATION_PATH . '/application/themes/' . $activeTheme->name . '/manifest.php';
  $themeManifest = null;
  if( file_exists($manifest) ) {
    $themeManifest = require($manifest);
  }
?>
<div id='core_menu_mini_menu'>
  <ul>
    <?php foreach( $this->navigation as $item ): ?>
      <?php
        $linkTitle = '';
        $subclass = '';

        if( stripos($activeTheme->name, 'serenity') !== false || (isset($themeManifest['options']) && isset($themeManifest['options']['menu-icons'])) ) {
          $linkTitle = $this->translate(strip_tags($item->getLabel()));
          if( $this->showIcons ) {
            $subclass = ' show_icons';
          }
        }
        $className = explode(' ', $item->class);
        $class = !empty($item->class) ? $item->class . $subclass : null;
      ?>
      <?php if(end($className) == 'core_mini_settings') { ?>
        <li>
          <a href="javascript:void(0);" class="<?php echo $class; ?>" <?php if( $item->get('target') ): ?> target='<?php echo $item->get('target') ?>' <?php endif; ?> title="<?php echo $linkTitle; ?>" alt="<?php echo ( !empty($item->alt) ? $item->alt : null ); ?>" id="minimenu_settings" onclick="showSettingsBox();"><i class="<?php echo $item->get('icon') ? $item->get('icon') : 'far fa-star' ?>"></i><?php echo $linkTitle; ?></a>
          <div class="core_settings_dropdown" id="minimenu_settings_content">
            <?php echo $this->navigation()->menu()->setContainer($this->settingNavigation)->render();?>
          </div>
        </li>
      <?php } else if(end($className) == 'core_mini_messages') { ?>
      <li class="core_mini_messages">
        <?php if($this->message_count && $this->showIcons) { ?>
          <span class="minimenu_message_count_bubble <?php echo $subclass ?>"><?php echo $this->message_count; 
        ?></span>
        <?php } ?>
        <a href="javascript:void(0);" class="<?php echo $class; ?>" <?php if( $item->get('target') ): ?> target='<?php echo $item->get('target') ?>' <?php endif; ?> title="<?php echo $linkTitle; ?>" alt="<?php echo ( !empty($item->alt) ? $item->alt : null ); ?>" id="minimenu_message" onclick="showMessageBox();"><i class="<?php echo $item->get('icon') ? $item->get('icon') : 'far fa-star' ?>"></i><?php echo $linkTitle; ?></a>
        <div class="pulldown_contents_wrapper" id="pulldown_message" style="display:none;">
          <div class="pulldown_contents">
            <div class="core_messages_pulldown_header">
              <?php echo $this->translate("Messages "); ?><a class="icon_message_new righticon fa fa-plus" href="messages/compose" title="<?php echo $this->translate('Compose New Message'); ?>"></a>
            </div>
            <ul class="messages_menu" id="messages_menu">
              <li class='clearfix pulldown_content_list_highlighted'>
                <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/loading.gif' alt="<?php echo $this->translate('Loading'); ?>" />
              </li>
            </ul>
          </div>
          <div class="pulldown_options">
            <a id="messages_viewall_link" href="<?php echo $this->url(array('action' => 'inbox'), 'messages_general', true) ?>"><?php echo $this->translate("View All Messages") ?></a>
            <a href="javascript:void(0);" id="messages_markread_link" onclick="markAllReadMessages();"><?php echo $this->translate("Mark All Read") ?></a>
          </div>
        </div>
      </li>
      <?php } else { ?>
        <li>
          <a href='<?php echo $item->getHref() ?>' class="<?php echo $class  ?>"
            <?php if( $item->get('target') ): ?> target='<?php echo $item->get('target') ?>' <?php endif; ?> title="<?php echo $linkTitle; ?>" alt="<?php echo ( !empty($item->alt) ? $item->alt : null ); ?>">
            <?php //if($this->showIcons) {  ?>
              <?php if(end($className) == 'core_mini_profile') { ?>
                <?php echo Zend_Registry::get('Zend_View')->itemPhoto($this->viewer, 'thumb.icon'); ?>
              <?php } else { ?>
                <i class="<?php echo $item->get('icon') ? $item->get('icon') : 'far fa-star' ?>"></i>
              <?php } ?>
            <?php //} ?>
            <span><?php echo $linkTitle; ?></span>
          </a>
          <!-- For displaying count bubble : START -->
          <?php
            $countText = filter_var($item->getLabel(), FILTER_SANITIZE_NUMBER_INT);
          ?>
          <?php if($this->showIcons && stripos($item->class, 'core_mini_update') !== false ) : ?>
            <span class="minimenu_update_count_bubble <?php echo $subclass ?>" id="update_count">
              <?php echo $countText; ?>
            </span>
          <?php elseif( stripos($item->class, 'core_mini_messages') !== false && !empty($countText) ) : ?>
            <span class="minimenu_message_count_bubble <?php echo $subclass ?>" id="message_count">
              <?php echo $countText; ?>
            </span>
          <?php endif; ?>
          <!-- For displaying count bubble : END -->
        </li>
      <?php } ?>
    <?php endforeach; ?>
    <?php if($this->search_check):?>
      <li id="global_search_form_container">
        <form id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
          <input type='text' class='text suggested' name='query' id='global_search_field' size='20' maxlength='100' alt='<?php echo $this->translate('Search') ?>' />
        </form>
      </li>
    <?php endif;?>
  </ul>
</div>

<span  style="display: none;" class="updates_pulldown" id="core_mini_updates_pulldown">
  <div class="pulldown_contents_wrapper">
    <div class="pulldown_contents">
      <ul class="notifications_menu" id="notifications_menu">
        <div class="notifications_loading" id="notifications_loading">
          <i class="fa fa-spin fa-spinner" style='margin-right: 5px;' ></i>
          <?php echo $this->translate("Loading ...") ?>
        </div>
      </ul>
    </div>
    <div class="pulldown_options">
      <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'notifications'),
         $this->translate('View All Updates'),
         array('id' => 'notifications_viewall_link')) ?>
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Mark All Read'), array(
        'id' => 'notifications_markread_link',
      )) ?>
    </div>
  </div>
</span>


<script type='text/javascript'>

  window.addEvent('domready', function() {
    $(document.body).addEvent('click', function(event) {
      if(event.target.id != '' && event.target.id != 'updates_toggle' && event.target.id != 'minimenu_message' && event.target.id != 'minimenu_settings') {
        if($$(".updates_pulldown_active").length > 0)
          $$('.updates_pulldown_active').set('class', 'updates_pulldown');

        if($("pulldown_message").style.display == 'block')
          $("pulldown_message").style.display = 'none';
          
        if($('minimenu_settings_content').style.display == 'block')
          $('minimenu_settings_content').style.display = 'none';
      }
    });
  });
  
  function showSettingsBox() {
    if($$(".updates_pulldown_active").length > 0)
      $$('.updates_pulldown_active').set('class', 'updates_pulldown');
      
    if($('pulldown_message').style.display == 'block')
      $('pulldown_message').style.display = 'none';

    if($('minimenu_settings_content').style.display == 'block')
      $('minimenu_settings_content').style.display = 'none';
    else
      $('minimenu_settings_content').style.display = 'block';
  }

  function showMessageBox() {
    if($('minimenu_settings_content').style.display == 'block')
      $('minimenu_settings_content').style.display = 'none';
    if($$(".updates_pulldown_active").length > 0)
      $$('.updates_pulldown_active').set('class', 'updates_pulldown');
    if($('pulldown_message').style.display == 'block')
      $('pulldown_message').style.display = 'none';
    else
      $('pulldown_message').style.display = 'block';
    showMessages();
  }
  
  var abortRequest;
  
  function showMessages() {

    abortRequest = new Request.HTML({
      url : en4.core.baseUrl + 'core/index/inbox',
      data : {
        format : 'html'
      },
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript)
      {
        document.getElementById('messages_menu').innerHTML = responseHTML;
      }
    }); 
    en4.core.request.send(abortRequest, {
      'force': true
    });
  }


  var notificationUpdater;

  en4.core.runonce.add(function(){
    if($('notifications_markread_link')){
      $('notifications_markread_link').addEvent('click', function() {
        //$('notifications_markread').setStyle('display', 'none');
        en4.activity.hideNotifications('<?php echo $this->string()->escapeJavascript($this->translate("0 Updates"));?>');
      });
    }

    <?php if ($this->updateSettings && $this->viewer->getIdentity()): ?>
    notificationUpdater = new NotificationUpdateHandler({
              'delay' : <?php echo $this->updateSettings;?>
            });
    notificationUpdater.start();
    window._notificationUpdater = notificationUpdater;
    <?php endif;?>

    // combining mini-menu and search widget if next to each other
    var menuElement = $('global_header').getElement('.layout_core_menu_mini');
    var nextWidget = menuElement.getNext();
    if( nextWidget && nextWidget.hasClass('layout_core_search_mini') ) {
      nextWidget.removeClass('generic_layout_container').inject(menuElement, 'top');
      return;
    }
    previousWidget = menuElement.getPrevious();
    if( previousWidget && previousWidget.hasClass('layout_core_search_mini') ) {
      previousWidget.removeClass('generic_layout_container').inject(menuElement, 'top');
    }

  });

  var updateElement = $('core_menu_mini_menu').getElement('.core_mini_update');
  if( updateElement ) {
    updateElement.set('id', 'updates_toggle');
    $('core_mini_updates_pulldown').setStyle('display', 'inline-block').inject(updateElement.getParent().set('id', 'core_menu_mini_menu_update'));
    updateElement.inject($('core_mini_updates_pulldown'));
    $('core_mini_updates_pulldown').addEvent('click', function() {
      var element = $(this);
      if( element.className=='updates_pulldown' ) {
        element.className= 'updates_pulldown_active';
        showNotifications();
      } else {
        element.className='updates_pulldown';
      }
    });
  }

  var showNotifications = function() {
    if($("pulldown_message").style.display == 'block')
      $("pulldown_message").style.display = 'none';
    en4.activity.updateNotifications();
    new Request.HTML({
      'url' : en4.core.baseUrl + 'activity/notifications/pulldown',
      'data' : {
        'format' : 'html',
        'page' : 1
      },
      'onComplete' : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if( responseHTML ) {
          // hide loading icon
          if($('notifications_loading')) $('notifications_loading').setStyle('display', 'none');

          $('notifications_menu').innerHTML = responseHTML;
          $('notifications_menu').addEvent('click', function(event){
            event.stop(); //Prevents the browser from following the link.

            var current_link = event.target;
            var notification_li = $(current_link).getParent('li');

            // if this is true, then the user clicked on the li element itself
            if( notification_li.id == 'core_menu_mini_menu_update' ) {
              notification_li = current_link;
            }

            var forward_link;
            if( current_link.get('href') ) {
              forward_link = current_link.get('href');
            } else{
              forward_link = $(current_link).getElements('a:last-child').get('href');
            }

            if( notification_li.get('class') == 'notifications_unread' ){
              notification_li.removeClass('notifications_unread');
              en4.core.request.send(new Request.JSON({
                url : en4.core.baseUrl + 'activity/notifications/markread',
                data : {
                  format     : 'json',
                  'actionid' : notification_li.get('value')
                },
                onSuccess : function() {
                  window.location = forward_link;
                }
              }));
            } else {
              window.location = forward_link;
            }
          });
        } else {
          $('notifications_loading').innerHTML = '<?php echo $this->string()->escapeJavascript($this->translate("You have no new updates."));?>';
        }
      }
    }).send();
  };
</script>
