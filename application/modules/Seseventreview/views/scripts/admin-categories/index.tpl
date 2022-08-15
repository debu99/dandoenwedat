<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<style>
.error {
	color:#FF0000;
}
</style>
<div class='sesbasic-form sesbasic-categories-form'>
  <div>
  	<?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <?php if( count($this->subsubNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs sesbasic-admin-sub-inner-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subsubNavigation)->render();?>
      </div>
    <?php endif; ?>
    <div class="sesbasic-form-cont">
      <h3><?php echo $this->translate("Manage Rating Parameters & Custom Review Fields") ?> </h3>
      <p class="description"> Below, you can map Categories with the Profile Types, so that review questions belonging to the mapped Profile Type will appear to users while creating / editing reviews on the event created in the associated Category.<br /><br />You can also add rating parameters for each category by clicking on "Add Parameter" link below.</p>
      <div class="sesbasic-categories-listing" style="width:100%">
      	<div id="error-message-category-delete"></div>
        <form id="multimodify_form" method="post" onsubmit="return multiModify();">
          <table class='admin_table' style="width: 100%;">
            <thead>
              <tr>
                <th><?php echo $this->translate("Icon") ?></th>
                <th><?php echo $this->translate("Name") ?></th>
                <th><?php echo $this->translate("Review Parameters") ?></th>
                <th><?php echo $this->translate("Profile Type") ?></th>
                <th><?php echo $this->translate("Options") ?></th>
              </tr>
            </thead>
            <tbody>
              <?php //Category Work ?>
              <?php foreach ($this->categories as $category): 
              		if(!$category->category_id)
                  	continue;
              ?>
              <tr id="categoryid-<?php echo $category->category_id; ?>" data-article-id="<?php echo $category->category_id; ?>">
                <td><?php if($category->cat_icon): ?>
                  <img class="sesbasic-category-icon" src="<?php echo Engine_Api::_()->storage()->get($category->cat_icon)->getPhotoUrl('thumb.icon'); ?>" />
                  <?php else: ?>
                  <?php echo "---"; ?>
                  <?php endif; ?></td>
                <td><?php echo $category->category_name ?>
                <div class="hidden" style="display:none" id="inline_<?php echo $category->category_id; ?>">
                	<div class="parent">0</div>
                </div>
                </td>                
                <?php $reviewParameter = Engine_Api::_()->getDbtable('parameters', 'seseventreview')->getParameterResult(array('category_id'=>$category->category_id)); ?>
                <td>
                <?php 
                $titleEAC = 'Add';
                if(count($reviewParameter)){ 
                	$titleEAC = 'Edit';
                ?>
                <ul class="seseventreview_parameters_list">
                  <?php foreach($reviewParameter as $val){ ?>
                  	<li><?php echo $val['title']; ?></li>
                  <?php } ?>
                  </ul>
                <?php }else{ ?>
                	-
                <?php } ?>
                </td>
                
                <td><?php  echo $category->profile_type_review && isset($this->profiletypes[$category->profile_type_review]) ? $this->profiletypes[$category->profile_type_review] : '-' ; ?></td>
                <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'seseventreview', 'controller' => 'categories', 'action' => 'edit-category', 'id' => $category->category_id), $this->translate('Map Profile Type'), array()) ?> | <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'seseventreview', 'controller' => 'categories', 'action' => 'review-parameter', 'id' => $category->category_id), $this->translate($titleEAC.' Parameter'), array('class'=>"smoothbox")); ?>
              </tr>
              	<?php if($category->category_id == 0): ?>
                    <?php continue; ?>
                    <?php endif; ?>
              <?php //Subcategory Work
                    $subcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubcategory(array('column_name' => "*", 'category_id' => $category->category_id));              foreach ($subcategory as $sub_category):  ?>
              <tr id="categoryid-<?php echo $sub_category->category_id; ?>" data-article-id="<?php echo $sub_category->category_id; ?>">
                <td><?php if($sub_category->cat_icon): ?>
                  <img class="sesbasic-category-icon" src="<?php echo Engine_Api::_()->storage()->get($sub_category->cat_icon)->getPhotoUrl( 'thumb.icon'); ?>" />
                  <?php else: ?>
                  <?php echo "---"; ?>
                  <?php endif; ?></td>
                <td>-&nbsp;<?php echo $sub_category->category_name ?>
                <div class="hidden" style="display:none" id="inline_<?php echo $sub_category->category_id; ?>">
                	<div class="parent"><?php echo $sub_category->subcat_id; ?></div>
                </div>
                </td>
                <?php $reviewParameter = Engine_Api::_()->getDbtable('parameters', 'seseventreview')->getParameterResult(array('category_id'=>$sub_category->category_id)); ?>
                <td>
                <?php $titleEASC = 'Add';
                	if(count($reviewParameter)){ 
                  $titleEASC = 'Edit';
                  ?>
                	<ul>
                  <?php foreach($reviewParameter as $val){ ?>
                  	<li><?php echo $val['title']; ?></li>
                  <?php } ?>
                  </ul>
                <?php }else{ ?>
                	-
                <?php } ?>
                </td>
                 <td><?php  echo $sub_category->profile_type_review && isset($this->profiletypes[$sub_category->profile_type_review]) ? $this->profiletypes[$sub_category->profile_type_review] : '-' ; ?></td>
                <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'seseventreview', 'controller' => 'categories', 'action' => 'edit-category', 'id' => $sub_category->category_id), $this->translate('Map Profile Type'), array()) ?> 	| <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'seseventreview', 'controller' => 'categories', 'action' => 'review-parameter', 'id' => $sub_category->category_id), $this->translate($titleEASC.' Parameter'), array('class'=>smoothbox)) ?>  	</td>
              </tr>
              <?php 
                		//SubSubcategory Work
                    $subsubcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubsubcategory(array('column_name' => "*", 'category_id' => $sub_category->category_id));
                    foreach ($subsubcategory as $subsub_category): ?>
              <tr id="categoryid-<?php echo $subsub_category->category_id; ?>" data-article-id="<?php echo $subsub_category->category_id; ?>">
                <td><?php if($subsub_category->cat_icon): ?>
                  <img  class="sesbasic-category-icon"  src="<?php echo Engine_Api::_()->storage()->get($subsub_category->cat_icon)->getPhotoUrl( 'thumb.icon'); ?>" />
                  <?php else: ?>
                  <?php echo "---"; ?>
                  <?php endif; ?></td>
                <td>--&nbsp;<?php echo $subsub_category->category_name ?>
                <div class="hidden" style="display:none" id="inline_<?php echo $sub_category->category_id; ?>">
                	<div class="parent"><?php echo $subsub_category->subsubcat_id; ?></div>
                </div>
                </td>
                <?php $reviewParameter = Engine_Api::_()->getDbtable('parameters', 'seseventreview')->getParameterResult(array('category_id'=>$subsub_category->category_id)); ?>
                <td>
                <?php 
                $titleEASSC = 'Add';
                if(count($reviewParameter)){ 
                $titleEASSC = 'Edit';
                ?>
                	<ul>
                  <?php foreach($reviewParameter as $val){ ?>
                  	<li><?php echo $val['title']; ?></li>
                  <?php } ?>
                  </ul>
                <?php }else{ ?>
                	-
                <?php } ?>
                </td>
                 <td><?php  echo $subsub_category->profile_type_review && isset($this->profiletypes[$subsub_category->profile_type_review]) ? $this->profiletypes[$subsub_category->profile_type_review] : '-' ; ?></td>
                <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'seseventreview', 'controller' => 'categories', 'action' => 'edit-category', 'id' => $subsub_category->category_id), $this->translate('Map Profile Type'), array()) ?> | <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'seseventreview', 'controller' => 'categories', 'action' => 'review-parameter', 'id' => $subsub_category->category_id), $this->translate($titleEASSC.' Parameter'), array('class'=>smoothbox)) ?>  
              </tr>
              <?php endforeach; ?>
              <?php endforeach; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>