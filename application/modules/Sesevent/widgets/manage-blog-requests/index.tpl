<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_search_ajax){ ?>
<h3>Manage Blog Requests</h3>
<?php } ?>
<div id="sesevent_manage_order_content">
<div class="sesbasic_dashboard_search_result">
	<?php echo $this->paginator->getTotalItemCount().' request(s) found.'; ?>
</div>
<?php if($this->paginator->getTotalItemCount() > 0): ?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
<div class="sesbasic_dashboard_table sesbasic_bxs">
  <form id='multidelete_form' method="post">
    <table>
      <thead>
        <tr>
          <th><?php echo $this->translate("Blog Name") ?></th>
          <th><?php echo $this->translate("Blog Owner") ?></th>
          <th><?php echo $this->translate("Approved") ?></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
        <tr>
          <?php $blog = Engine_Api::_()->getItem("sesblog_blog", $item->blog_id); ?>	
          <td>
	    <a href="<?php echo $blog->getHref(); ?>"><?php echo $blog->getTitle(); ?></a>
          </td>
          <td>
	    <?php $user = Engine_Api::_()->getItem('user',$blog->owner_id) ?>
	    <a href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a>
          </td>
          <td>
	    <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'dashboard', 'action' => 'approved', 'event_id' => $item->event_id, 'blog_id' => $item->blog_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Unmark as Approve')))) ?>
          </td>
          <td class="table_options">
	    <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'dashboard', 'action' => 'reject-request', 'event_id' => $item->event_id, 'blog_id' => $item->blog_id), $this->translate('Reject Request')) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
   </form>
</div>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate("No request yet.") ?>
  </span>
</div>
<?php endif; ?>
</div>
<?php if($this->is_search_ajax) die; ?>
<script type="application/javascript">
sesJqueryObject('#loadingimgsesevent-wrapper').hide();
</script>