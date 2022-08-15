<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: link-blog.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="text/javascript">

  function viewMore() {
    
    if ($('view_more'))
    $('view_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>"; 
      
    document.getElementById('view_more').style.display = 'none';
    document.getElementById('loading_image').style.display = '';
  
    var id = '<?php echo $this->blog_id; ?>';
    
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'sesevent/index/link-blog/event_id/' + id ,
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        viewmore: 1        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById('event_results').innerHTML = document.getElementById('event_results').innerHTML + responseHTML;
        document.getElementById('view_more').destroy();
        document.getElementById('loading_image').style.display = 'none';
      }
    })).send();
    return false;
  }
</script>

<?php if (empty($this->viewmore)): ?>
  <div class="sesbasic_items_listing_popup">
    <div class="sesbasic_items_listing_header">
         <?php echo $this->translate('Blogs in which you can link to event') ?>
      <a class="fa fa-times" href="javascript:;" onclick='smoothboxclose();' title="<?php echo $this->translate('Close') ?>"></a>
    </div>
    <div class="sesbasic_items_listing_cont" id="event_results">
<?php endif; ?>

    <?php if (count($this->paginator) > 0) : ?>
      <form id='link_blog' name="link_blog" method="post" action="<?php echo $this->url();?>">
	<?php foreach ($this->paginator as $blog): ?>
	  <div class="item_list">
	    <input type='checkbox' class='checkbox' name="blog[]" value="<?php echo $blog->blog_id;?>" />
	    <div class="item_list_thumb">
	      <?php $user = Engine_Api::_()->getItem('user', $blog->owner_id);?>
	      <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon'), array('title' => $user->getTitle(), 'target' => '_parent')); ?>
	    </div>
	    <div class="item_list_info">
	      <div class="item_list_title">
		<?php echo $this->htmlLink($blog->getHref(), $blog->getTitle(), array('title' => $blog->getTitle(), 'target' => '_parent')); ?>
	      </div>
	    </div>
	  </div>
	<?php endforeach; ?>
        <div class='buttons'>
	  <button type='submit'><?php echo $this->translate("Link Event") ?></button>
        </div>
      </form><br />
    <?php endif; ?>     
      
    <?php if (!empty($this->paginator) && $this->paginator->count() > 1 && empty($this->viewmore)): ?>
      <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>        
         <div class="sesbasic_load_btn" id="view_more" onclick="viewMore();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn" id="loading_image" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
        
  <?php endif; ?>
     </div>
    </div>
<?php endif; ?>
<script type="text/javascript">
  function smoothboxclose() {
    parent.Smoothbox.close();
  }
</script>