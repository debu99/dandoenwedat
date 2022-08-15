<?php $this->viewer = Engine_Api::_()->user()->getViewer();?>
<script type="text/javascript">
  function viewMore() {
   
    document.getElementById('view_more').style.display = 'none';
    document.getElementById('loading_image').style.display = '';
    var id = '<?php echo $this->user_id; ?>';
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + '<?php echo $this->urlpage; ?>/' + id ,
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        viewmore: 1        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				 document.getElementById('view_more').destroy();
				 document.getElementById('loading_image').destroy();
      	 document.getElementById('like_results').innerHTML = document.getElementById('like_results').innerHTML + responseHTML;
         document.getElementById('loading_image').style.display = 'none';
      }
    })).send();
    return false;
  }
</script>
<?php if (empty($this->viewmore)): ?>
  <div class="sesbasic_items_listing_popup">
    <div class="sesbasic_items_listing_header">
         <?php echo $this->translate($this->titlePage); ?>
      <a class="fas fa-times" href="javascript:;" onclick='smoothboxclose();' title="<?php echo $this->translate('Close') ?>"></a>
    </div>
    <div class="sesbasic_items_listing_cont" id="like_results">
<?php endif;
 ?>
    <?php if (count($this->paginator) > 0) : ?>
      <?php foreach ($this->paginator as $user):
      	if(isset($this->checkFriend)){
       		if( !isset($this->friendUsers[$user->resource_id]) ) continue;
    	 		$user = $this->friendUsers[$user->resource_id];
        }
    	?>
        <div class="item_list">
          <div class="item_list_thumb">
            <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'), array('title' => $user->getTitle(), 'target' => '_parent')); ?>
          </div>
          <div class="item_list_info">
            <div class="item_list_title">
              <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('title' => $user->getTitle(), 'target' => '_parent')); ?>
            </div>
          </div>
          <div class="item_list_options">
          
          <?php if($this->viewer->getIdentity() && $user->getIdentity() != $this->viewer->getIdentity()){ ?>
        <?php
          $subject = $user;
          $viewer = $this->viewer;
        ?>
        <span>
        <?php include APPLICATION_PATH .  '/application/modules/Sesbasic/views/scripts/_addfriend.tpl';?>
        </span>
        <?php if($this->viewer->getIdentity() && $user->getIdentity() != $this->viewer->getIdentity()){ ?>
         <!-- get Message Btn -->
         <a href="messages/compose/to/<?php echo $user->getIdentity(); ?>" target="_blank" class="sesbasic_btn sesmember_message_btn menu_user_profile user_profile_message" target=""><i class="fa fa-envelope"></i><span><i class="fa fa-caret-down"></i> messages</span></a>
     <?php } ?>     
      <?php
      if($this->viewer->getIdentity() != 0 && $user->getIdentity() != $this->viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active',1)){
     	  $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($user->user_id);
        $followClass = (!$FollowUser) ? 'fa-check' : 'fa-times' ;
        $followText = ($FollowUser) ?  $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow')) : $this->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow')) ;
    ?>
        <span>
        <a href='javascript:;' data-url='<?php echo $user->getIdentity(); ?>' class='sesbasic_btn sesmember_add_btn sesmember_follow_user sesmember_follow_user_<?php echo $user->getIdentity(); ?>'><i class='fa <?php echo $followClass; ?>'></i><span><i class="fa fa-caret-down"></i><?php echo $followText;  ?></span></a>  
        </span>
    	<?php
      }
      ?>
      <?php } ?>
          <?php
						if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0){
              $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($user->user_id,$user->getType());
              $likeClass = (!$LikeStatus) ? 'fa-thumbs-up' : 'fa-thumbs-down' ;
              $likeText = ($LikeStatus) ?  $this->translate('Unlike') : $this->translate('Like') ; ?>  
            <span>
            	<a href="javascript:;" data-url='<?php echo $user->getIdentity(); ?>' class="sesbasic_btn sesmember_add_btn sesmember_button_like_user sesmember_button_like_user_<?php echo $user->getIdentity(); ?>">
            		<i class="fa <?php echo $likeClass; ?>"></i><span><i class="fa fa-caret-down"></i><?php echo $likeText; ?></span>
              </a>
            </span>
         <?php } ?>            
          </div>
        </div>
      <?php endforeach; ?> 
      <?php endif; ?>     
    <?php if (!empty($this->paginator) && $this->paginator->count() > 1): ?>
      <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
        <div class="sesbasic_view_more" id="view_more" onclick="viewMore();" >
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => 'feed_viewmore_link', 'class' => 'buttonlink icon_viewmore')); ?>
        </div>
        <div class="sesbasic_view_more_loading" id="loading_image" style="display: none;">
          <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sesbasic/externals/images/loading.gif' alt="Loading" />
          <?php echo $this->translate("Loading ...") ?>
        </div>
  <?php endif; ?>
     </div>
    </div>
<?php endif; ?>
<script type="text/javascript">
  function smoothboxclose() {
    parent.Smoothbox.close();
  }
</script>