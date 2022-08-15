<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sestwitterclone
 * @package    Sestwitterclone
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-09-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php if( $this->paginator->getTotalItemCount() <= 0 ): ?>
  <div class="pulldown_loading">
    <?php echo $this->translate('You have no message.'); ?>
  </div>
<?php endif; ?>
<?php if( count($this->paginator) ): ?>
  <div class="pulldown_content_list">
    <ul id="sestwitterclone_messages_menu">
      <?php foreach( $this->paginator as $conversation ):
        $message = $conversation->getInboxMessage($this->viewer());
        $recipient = $conversation->getRecipientInfo($this->viewer());
        $resource = "";
        $sender   = "";
        if( $conversation->hasResource() &&
                  ($resource = $conversation->getResource()) ) {
          $sender = $resource;
        } else if( $conversation->recipients > 1 ) {
          $sender = $this->viewer();
        } else {
          foreach( $conversation->getRecipients() as $tmpUser ) {
            if( $tmpUser->getIdentity() != $this->viewer()->getIdentity() ) {
              $sender = $tmpUser;
            }
          }
        }
        if( (!isset($sender) || !$sender) && $this->viewer()->getIdentity() !== $conversation->user_id ){
          $sender = Engine_Api::_()->user()->getUser($conversation->user_id);
        }
        if( !isset($sender) || !$sender ) {
          //continue;
          $sender = new User_Model_User(array());
        }
        ?>
        <li class='clearfix <?php if( !$recipient->inbox_read ): ?>pulldown_content_list_highlighted<?php endif; ?>' id="message_conversation_<?php echo $conversation->conversation_id ?>" onclick="messageProfilePage('<?php echo $conversation->getHref(); ?>');">
          <div class="pulldown_item_photo">
            <?php echo $this->htmlLink($sender->getHref(), $this->itemPhoto($sender, 'thumb.icon')) ?>
          </div>
          <div class="pulldown_item_content">
            <p class="pulldown_item_content_title">
              <?php if( !empty($resource) ): ?>
                <b><?php echo $resource->toString() ?></b>
              <?php elseif( $conversation->recipients == 1 ): ?>
                <?php echo $this->htmlLink($sender->getHref(), $sender->getTitle()) ?>
              <?php else: ?>
                <b><?php echo $this->translate(array('%s person', '%s people', $conversation->recipients),
                  $this->locale()->toNumber($conversation->recipients)) ?></b>
              <?php endif; ?>
            </p>
            <p class="pulldown_item_content_des msg_body">
              <?php
                ! ( isset($message) && '' != ($title = trim($message->getTitle())) ||
                ! isset($conversation) && '' != ($title = trim($conversation->getTitle())) ||
                $title = '<em>' . $this->translate('(No Subject)') . '</em>' );
              ?>
              <?php echo $this->htmlLink($conversation->getHref(), $title) ?>:
              <?php echo html_entity_decode($message->body) ?>
            </p>
            <p class="pulldown_item_content_date">
              <?php echo $this->timestamp($message->date) ?>
            </p>
            <p class="pulldown_item_content_btns clearfix">
              <?php echo $this->htmlLink($conversation->getHref(), 'Reply', array('class'=>'button')) ?>
              <a href='javascript:void(0);' class="delete_message button button_alt" onclick="deleteMessage('<?php echo $conversation->conversation_id;?>', event);return false;" ><?php echo $this->translate('Delete');?></a>
            </p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="pulldown_options">
    <a href="<?php echo $this->url(array('action' => 'inbox'), 'messages_general', true) ?>"><?php echo $this->translate("View All Messages") ?></a>
    <a href="javascript:void(0);" onclick="markallmessage();"><?php echo $this->translate("Mark All Read") ?></a>
   </div>
<?php endif; ?>


<script type="text/javascript">

  function markallmessage() {
  
    event.stopPropagation();
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sestwitterclone/index/markallmessage',
      data : {
        format: 'json'
      },
      onSuccess : function(responseJSON) {
        if($('sestwitterclone_messages_menu')){
          var message_children = $('sestwitterclone_messages_menu').getChildren('li');
//           console.log(message_children);
          message_children.each(function(el){
            el.setAttribute('class', '');
          });
        }
      }
    }));
  
  }
  
  function messageProfilePage(pageUrl){
    if(pageUrl != 'null' ) {
        window.location.href=pageUrl;
    }
  }
  
  function deleteMessage(id, event) {

    event.stopPropagation();
    document.getElementById('message_conversation_'+id).remove();

    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'sestwitterclone/index/delete-message',
      data : {
        format     : 'json',
        'id' : id
      }
    }));
  };
  
</script>
