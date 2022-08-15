<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<div class="clear sesbasic-form">
	<div>
		<?php if( count($this->subnavigation) ): ?>
			<div class='sesbasic-admin-sub-tabs'>
				<?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render();?>
			</div>
		<?php endif; ?>
		<div class="clear sesbasic-form-cont">
			<p><H3><?php echo $this->translate("Manage Badges") ?></H3></p>
			<p><?php echo $this->translate("This page lists all the badges created by you. You can add a new badge from below and edit their settings.") ?></p>
			<br />
			<div>
				<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sescredit', 'controller' => 'badges', 'action' => 'add'), $this->translate('Add New Badge'), array('class' => 'buttonlink sesbasic_icon_add smoothbox'))
				?><br/>
			</div><br />
			<div class='admin_search sesbasic_search_form'>
				<?php echo $this->formFilter->render($this) ?>
			</div>
			<br />
			<div class='admin_results'>
				<div>
					<?php $count = $this->paginator->getTotalItemCount() ?>
					<?php echo $this->translate(array("%s badge found.", "%s badges found.", $count),
							$this->locale()->toNumber($count)) ?>
				</div>
				<div>
					<?php echo $this->paginationControl($this->paginator, null, null, array(
						'pageAsQuery' => true,
						'query' => $this->formValues,
					)); ?>
				</div>
			</div>
			<br />
			<?php if(count($this->paginator) > 0):?>
			<div class="admin_table_form">
			<form>
				<table class='admin_table'>
					<thead>
						<tr>
							<th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
							<th><?php echo $this->translate("Badge Name") ?></th>
							<th align="center"><?php echo $this->translate("Credit Points") ?></th>
							<th align="center"><?php echo $this->translate("Status") ?></th>
							<th align="center"><?php echo $this->translate("Badge Photo") ?></th>
							<th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
						</tr>
					</thead>
					<tbody id='menu_list'>
						<?php if( count($this->paginator) ): ?>
							<?php foreach( $this->paginator as $item ): ?>
								<tr id="users_<?php echo $item->badge_id ?>">
									<td><?php echo $item->badge_id ?></td>
									<td class='admin_table_bold'>
										<?php echo $item->title; ?>
									</td>
									<td class='admin_table_centered'>
										<?php echo $item->credit_value; ?>
									</td>
									<td class='admin_table_centered'>
										<?php if($item->enabled == 1):?>
											<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sescredit', 'controller' => 'admin-badges', 'action' => 'enable', 'id' => $item->badge_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Enable')))) ?>
										<?php else: ?>
											<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sescredit', 'controller' => 'admin-badges', 'action' => 'enable', 'id' => $item->badge_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Disabled')))) ?>
										<?php endif; ?>
									</td>
									<td class='admin_table_centered'>
										<?php if($item->photo_id): ?>
											<img width="100px;" alt="" src="<?php echo Engine_Api::_()->storage()->get($item->photo_id, '')->getPhotoUrl(); ?>" />
										<?php else: ?>
											<?php echo "---"; ?>
										<?php endif; ?>
									</td>
									<td class='admin_table_options'>
										<a class='smoothbox' href='<?php echo $this->url(array('action' => 'edit', 'id' => $item->badge_id));?>'><?php echo $this->translate("Edit") ?></a>
										|
										<a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->badge_id));?>'><?php echo $this->translate("Delete") ?></a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<br />
			</form>
			</div>
			<?php else:?>
				<div class="tip">
					<span><?php echo "There are no badges created by you yet.";?></span>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>		
