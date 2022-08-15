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
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/odering.js'); ?>
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
    <div class="sesbasic-form-cont">
      <h3><?php echo $this->translate("Manage Categories") ?> </h3>
      <p class="description"> Event categories can be managed here. To create new categories, use "Add New Category" form below. Below, you can also choose Slug URL, Title, Description, Profile Type to be associated with the category, Icon and Thumbnail. You can also map Categories with the Profile Types, so that questions belonging to the mapped Profile Type will appear to users while creating / editing events when they choose the associated Category.<br /><br />To create 2nd-level categories and 3rd-level categories, choose respective 1st-level and 2nd-level category from “Parent Category” dropdown below. Choose this carefully as you will not be able to edit Parent Category later.<br /><br />To reorder the categories, click on their names or row and drag them up or down.</p>
      <div class="sesbasic-categories-add-form">
        <h4 class="bold">Add New Category</h4>
        <form id="addcategory" method="post" enctype="multipart/form-data">
          <div class="sesbasic-form-field" id="name-required">
            <div class="sesbasic-form-field-label">
              <label for="tag-name">Name</label>
            </div>
            <div class="sesbasic-form-field-element">
              <input name="category_name" autocomplete="off" id="tag-name" type="text"  size="40" >
              <p>The name is how it appears on your site.</p>
            </div>
          </div>
          <div class="sesbasic-form-field" id="slug-required">
            <div class="sesbasic-form-field-label">
              <label for="tag-slug">Slug</label>
            </div>
            <div class="sesbasic-form-field-element">
              <input name="slug" id="tag-slug" type="text" value="" size="40">
              <p id="error-msg" style="color:red"></p>
              <p>The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
            </div>
          </div>
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label for="title-name">Title</label>
            </div>
            <div class="sesbasic-form-field-element">
              <input name="title" id="title-name" type="text" size="40">
              <p>Title will appear on the Category View Page of your site.</p>
            </div>
          </div>
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label for="color-name">Color</label>
            </div>
            <div class="sesbasic-form-field-element">
              <input name="color" id="color-name" value="990066" class="SEScolor" type="text" size="40">
              <p>This will be category color & will appear in Grid View of events.</p>
            </div>
          </div>
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label for="parent">Parent Category</label>
            </div>
            <div class="sesbasic-form-field-element">
              <select name="parent" id="parent" class="postform">
                <option value="-1">None</option>
               <?php foreach ($this->categories as $category): ?>
                <?php if($category->category_id == 0) : ?>
                <?php continue; ?>
                <?php endif; ?>
                  <option class="level-0" value="<?php echo $category->category_id; ?>"><?php echo $category->category_name; ?></option>
                <?php 
                  $subcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubcategory(array('column_name' => "*", 'category_id' => $category->category_id));          foreach ($subcategory as $sub_category):  
                ?>
                  <option class="level-1" value="<?php echo $sub_category->category_id; ?>">&nbsp;&nbsp;&nbsp;<?php echo $sub_category->category_name; ?></option>
              <?php 
                  endforeach;
                  endforeach; 
              ?>
              </select>
            </div>
          </div>
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label for="parent">Map Profile Type</label>
            </div>
            <div class="sesbasic-form-field-element">
              <select name="profile_type" id="profile_type" class="postform">
               <?php foreach ($this->profiletypes as $key=>$profiletype): ?>
                  <option  value="<?php echo $key ; ?>"><?php echo $profiletype; ?></option>
                <?php 
                  endforeach; 
              ?>
              </select>
            </div>
          </div>
          <div class="sesbasic-form-field" id="show_level">
            <div class="sesbasic-form-field-label">
              <label for="parent">Member Levels</label>
            </div>
            <div class="sesbasic-form-field-element">
              <select name="member_levels[]" id="member_levels" class="postform" multiple="multiple">
               <?php foreach (Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level): ?>
                  <?php if($level == 'Public'):?><?php continue;?><?php endif;?>
                  <option  value="<?php echo $level->level_id ; ?>"><?php echo $level; ?></option>
               <?php endforeach;?>
              </select>
              <p>Hold down the CTRL key to select or de-select specific Member Levels.</p>
            </div>
          </div>
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label for="tag-description">Description</label>
            </div>
            <div class="sesbasic-form-field-element">
              <textarea name="description" id="tag-description"></textarea>
              <p>Description will appear on the Category View Page of your site.</p>
            </div>
          </div>
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label>Upload Icon</label><span style="font-size: 11px;"> [Recommended size is: 40px * 40px.]</span>
            </div>
            <div class="sesbasic-form-field-element">
              <input type="file" name="icon" id="chanel_cover" alt="Icon" onchange="readImageUrl(this,'cover_photo_preview')" />
              <span style="display:none" class="error" id="chanel_cover-msg"></span>
            </div>
          </div>
          <div class="form-wrapper" id="cover_photo_preview-wrapper" style="display: none;">
          	<div class="form-label" id="cover_photo_preview-label">&nbsp;</div>
            <div class="form-element" id="cover_photo_preview-element">
            	<input width="100" type="image" height="100" alt="Icon" src="" id="cover_photo_preview" name="cover_photo_preview">
            </div>
          </div>
          
          
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label>Upload Colored Icon</label><span style="font-size: 11px;"> [Recommended size is: 40px * 40px.]</span>
            </div>
            <div class="sesbasic-form-field-element">
              <input type="file" name="colored_icon" id="colored_icon" alt="Colored Icon" onchange="readImageUrl(this,'colored_icon_preview')" />
              <span style="display:none" class="error" id="colored_icon-msg"></span>
            </div>
          </div>
          <div class="form-wrapper" id="colored_icon_preview-wrapper" style="display: none;">
          	<div class="form-label" id="colored_icon_preview-label">&nbsp;</div>
            <div class="form-element" id="colored_icon_preview-element">
            	<input width="100" type="image" height="100" alt="Icon" src="" id="colored_icon_preview" name="colored_icon_preview">
            </div>
          </div>
          
          
          <div class="sesbasic-form-field">
            <div class="sesbasic-form-field-label">
              <label>Upload Thumbnail</label><span style="font-size: 11px;"> [Recommended size is: 500px * 300px.]</span>
            </div>
            <div class="sesbasic-form-field-element">
              <input type="file" name="thumbnail" id="chanel_thumbnail" alt="Thumbnail" onchange="readImageUrl(this,'thumbnail_photo_preview')" />
              <span style="display:none" class="error" id="chanel_thumbnail-msg"></span>
            </div>
          </div>
           <div class="form-wrapper" id="thumbnail_photo_preview-wrapper" style="display: none;">
          	<div class="form-label" id="thumbnail_photo_preview-label">&nbsp;</div>
            <div class="form-element" id="thumbnail_photo_preview-element">
            	<input width="100" type="image" height="100" alt="Thumbnail" src="" id="thumbnail_photo_preview" name="thumbnail_photo_preview">
            </div>
          </div>
          <div class="submit sesbasic-form-field">
            <button type="button" id="submitaddcategory" class="upload_image_button button">Add New Category</button>
          </div>
        </form>
        <div class="sesbasic-categories-add-form-overlay" id="add-category-overlay" style="display:none"></div>
      </div>
      <div class="sesbasic-categories-listing">
      	<div id="error-message-category-delete"></div>
        <form id="multimodify_form" method="post" onsubmit="return multiModify();">
          <table class='admin_table' style="width: 100%;">
            <thead>
              <tr>
                <th><input type="checkbox" onclick="selectAll()"  name="checkbox" /></th>
                <th><?php echo $this->translate("Icon") ?></th>
                <th><?php echo $this->translate("Name") ?></th>
                <th><?php echo $this->translate("Slug") ?></th>
                <th><?php echo $this->translate("Options") ?></th>
              </tr>
            </thead>
            <tbody>
              <?php //Category Work ?>
              <?php foreach ($this->categories as $category): ?>
              <tr id="categoryid-<?php echo $category->category_id; ?>" data-article-id="<?php echo $category->category_id; ?>">
                <td><input type="checkbox" class="checkbox check-column" name="delete_tag[]" value="<?php echo $category->category_id; ?>" /></td>
               <td><?php if($category->cat_icon): ?>
                    <?php $photo = Engine_Api::_()->storage()->get($category->cat_icon);
                    if($photo) {
                    $photo = $photo->getPhotoUrl('thumb.icon');
                    ?>
                     <img class="sesbasic-category-icon" src="<?php echo $photo; ?>" />
                  <?php } else { ?>
                      <?php echo "---"; ?>
                  <?php } ?>
                  <?php else: ?>
                  <?php echo "---"; ?>
                  <?php endif; ?></td>
                <td><?php echo $category->category_name ?>
                <div class="hidden" style="display:none" id="inline_<?php echo $category->category_id; ?>">
                	<div class="parent">0</div>
                </div>
                </td>
                <td><?php  echo $category->slug ; ?></td>
                <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'categories', 'action' => 'edit-category', 'id' => $category->category_id), $this->translate('Edit'), array()) ?> 
                <?php if($category->category_id != 0){ ?>
                | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Delete'), array('class' => 'deleteCat','data-url'=>$category->category_id)); ?>
                <?php } ?>
              </tr>
              	<?php if($category->category_id == 0): ?>
                    <?php continue; ?>
                    <?php endif; ?>
              <?php //Subcategory Work
                    $subcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubcategory(array('column_name' => "*", 'category_id' => $category->category_id));              foreach ($subcategory as $sub_category):  ?>
              <tr id="categoryid-<?php echo $sub_category->category_id; ?>" data-article-id="<?php echo $sub_category->category_id; ?>">
                <td><input type="checkbox"  class="checkbox check-column" name="delete_tag[]" value="<?php echo $sub_category->category_id; ?>" /></td>
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
                <td><?php  echo $sub_category->slug ; ?></td>
                <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'categories', 'action' => 'edit-category', 'id' => $sub_category->category_id), $this->translate('Edit'), array()) ?> | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Delete'), array('class' => 'deleteCat','data-url'=>$sub_category->category_id)) ?> 		</td>
              </tr>
              <?php 
                		//SubSubcategory Work
                    $subsubcategory = Engine_Api::_()->getDbtable('categories', 'sesevent')->getModuleSubsubcategory(array('column_name' => "*", 'category_id' => $sub_category->category_id));
                    foreach ($subsubcategory as $subsub_category): ?>
              <tr id="categoryid-<?php echo $subsub_category->category_id; ?>" data-article-id="<?php echo $subsub_category->category_id; ?>">
                <td><input type="checkbox" class="checkbox check-column" name="delete_tag[]" value="<?php echo $subsub_category->category_id; ?>" /></td>
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
                <td><?php  echo $subsub_category->slug ; ?></td>
                <td><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'categories', 'action' => 'edit-category', 'id' => $subsub_category->category_id), $this->translate('Edit'), array()) ?> | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Delete'), array('class' => 'deleteCat','data-url'=>$subsub_category->category_id)) ?>
              </tr>
              <?php endforeach; ?>
              <?php endforeach; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
          <span class='buttons'>
           <button type="button" id="deletecategoryselected" class="upload_image_button button"><?php echo $this->translate("Delete Selected") ?></button>
          </span>
        </form>
      </div>
    </div>
  </div>
</div>
<script type="application/javascript">
ajaxurl = en4.core.baseUrl+"admin/sesevent/categories/change-order";
function readImageUrl(input,id) {
    var url = input.value;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
		if(id == 'cover_photo_preview')
		 var idMsg = 'chanel_cover';
		else if(id == 'colored_icon_preview')
			var idMsg = 'colored_icon';
		else
			var idMsg = 'chanel_thumbnail';
    if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
        var reader = new FileReader();
        reader.onload = function (e) {
					 jqueryObjectOfSes('#'+id+'-wrapper').show();
           jqueryObjectOfSes('#'+id).attr('src', e.target.result);
        }
				jqueryObjectOfSes('#'+id+'-wrapper').show();
				jqueryObjectOfSes('#'+idMsg+'-msg').hide();
        reader.readAsDataURL(input.files[0]);
    }else{
				 jqueryObjectOfSes('#'+id+'-wrapper').hide();
				 jqueryObjectOfSes('#'+idMsg+'-msg').show();
				 jqueryObjectOfSes('#'+idMsg+'-msg').html("<br><?php echo $this->translate('Please select png,jpeg,jpg image only.'); ?>");
         jqueryObjectOfSes('#'+idMsg).val('');
		}
  }
jqueryObjectOfSes (document).ready(function (e) {
    jqueryObjectOfSes ('#addcategory').on('submit',(function(e) {
			var error = false;
			var nameFieldRequired = jqueryObjectOfSes('#tag-name').val();
			var slugFieldRequired = jqueryObjectOfSes('#tag-slug').val();
			if(!nameFieldRequired){
					jqueryObjectOfSes('#name-required').css('background-color','#ffebe8');
					jqueryObjectOfSes('#tag-name').css('border','1px solid red');
					error = true;
			}else{
				jqueryObjectOfSes('#name-required').css('background-color','');
				jqueryObjectOfSes('#tag-name').css('border','');
			}
			if(!slugFieldRequired){
				jqueryObjectOfSes('#slug-required').css('background-color','#ffebe8');
					jqueryObjectOfSes('#tag-slug').css('border','1px solid red');
					 jqueryObjectOfSes('html, body').animate({
            scrollTop: jqueryObjectOfSes('#addcategory').offset().top },
            1000
       		 );
					error = true;
			}else{
				jqueryObjectOfSes('#slug-required').css('background-color','');
				jqueryObjectOfSes('#tag-slug').css('border','');
			}
			if(error){
				jqueryObjectOfSes('html, body').animate({
            scrollTop: jqueryObjectOfSes('#addcategory').offset().top },
            1000
       		 );
				return false;
			}
				jqueryObjectOfSes('#add-category-overlay').css('display','block');
        e.preventDefault();
				var form = jqueryObjectOfSes('#addcategory');
        var formData = new FormData(this);
				formData.append('is_ajax', 1);
        jqueryObjectOfSes .ajax({
            type:'POST',
            url: jqueryObjectOfSes(this).attr('action'),
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
								jqueryObjectOfSes('#cover_photo_preview-wrapper').css('display','none');
								jqueryObjectOfSes('#thumbnail_photo_preview-wrapper').css('display','none');
								jqueryObjectOfSes('#add-category-overlay').css('display','none');
								data = jqueryObjectOfSes.parseJSON(data); 
								if(data.slugError){
											jqueryObjectOfSes('#error-msg').html('Unavailable');
											jqueryObjectOfSes('#slug-required').css('background-color','#ffebe8');
											jqueryObjectOfSes('#tag-slug').css('border','1px solid red');
											 jqueryObjectOfSes('html, body').animate({
												scrollTop: jqueryObjectOfSes('#addcategory').offset().top },
												1000
											 );
										return false;
								}else{
									jqueryObjectOfSes('#error-msg').html('');
									jqueryObjectOfSes('#slug-required').css('background-color','');
									jqueryObjectOfSes('#tag-slug').css('border','');
								}
                parent = jqueryObjectOfSes('#parent').val();
								if ( parent > 0 && jqueryObjectOfSes('#categoryid-' + parent ).length > 0 ){ // If the parent exists on this page, insert it below. Else insert it at the top of the list.
								var scrollUpTo= '#categoryid-' + parent;
									jqueryObjectOfSes( '.admin_table #categoryid-' + parent ).after( data.tableData ); // As the parent exists, Insert the version with - - - prefixed
								}else{
									var scrollUpTo = '#multimodify_form';
									jqueryObjectOfSes( '.admin_table' ).prepend( data.tableData ); // As the parent is not visible, Insert the version with Parent - Child - ThisTerm					
								}
								if ( jqueryObjectOfSes('#parent') ) {
									// Create an indent for the Parent field
									indent = data.seprator;
									if(indent != 3)
										form.find( 'select#parent option:selected' ).after( '<option value="' + data.id + '">' + indent + data.name + '</option>' );
								}
								console.log(scrollUpTo,jqueryObjectOfSes(scrollUpTo).offset().top);
								jqueryObjectOfSes('html, body').animate({
									scrollTop: jqueryObjectOfSes(scrollUpTo).offset().top },
									1000
								 );
								jqueryObjectOfSes('#addcategory')[0].reset();
            },
            error: function(data){
            	//silence
						}
        });
    }));
		jqueryObjectOfSes("#submitaddcategory").on("click", function() {
       jqueryObjectOfSes("#addcategory").submit();
    });
});
jqueryObjectOfSes("#tag-name").keyup(function(){
		var Text = jqueryObjectOfSes(this).val();
		Text = Text.toLowerCase();
		Text = Text.replace(/[^a-zA-Z0-9]+/g,'-');
		jqueryObjectOfSes("#tag-slug").val(Text);        
});
function selectAll()
{
  var i;
  var multimodify_form = $('multimodify_form');
  var inputs = multimodify_form.elements;
  for (i = 1; i < inputs.length - 1; i++) {
    if (!inputs[i].disabled) {
      inputs[i].checked = inputs[0].checked;
    }
  }
}
jqueryObjectOfSes("#deletecategoryselected").click(function(){
		var n = jqueryObjectOfSes(".checkbox:checked").length;
   if(n>0){
	  var confirmDelete = confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected categories?")) ?>');
		if(confirmDelete){
				var selectedCategory = new Array();
        if (n > 0){
            jqueryObjectOfSes(".checkbox:checked").each(function(){
								jqueryObjectOfSes('#categoryid-'+jqueryObjectOfSes(this).val()).css('background-color','#ffebe8');
                selectedCategory.push(jqueryObjectOfSes(this).val());
            });
						var scrollToError = false;
        		jqueryObjectOfSes.post(window.location.href,{data:selectedCategory,selectDeleted:'true'},function(response){
						  response = jqueryObjectOfSes.parseJSON(response); 
							var ids = response.ids;
							if(response.diff_ids.length>0){
									jqueryObjectOfSes('#error-message-category-delete').html("Red mark category can't delete.You need to delete lower category of that category first.<br></br>");
									jqueryObjectOfSes('#error-message-category-delete').css('color','red');
									 scrollToError = true;
							}else{
								jqueryObjectOfSes('#error-message-category-delete').html("");
									jqueryObjectOfSes('#error-message-category-delete').css('color','');
							}
							jqueryObjectOfSes('#multimodify_form')[0].reset();
							if(response.ids){
								//error-message-category-delete;
								for(var i =0;i<=ids.length;i++){
									jqueryObjectOfSes('select#parent option[value="' + ids[i] + '"]').remove();
									jqueryObjectOfSes('#categoryid-'+ids[i]).fadeOut("normal", function() {
											jqueryObjectOfSes(this).remove();
									});
								}
							}
							if(scrollToError){
								jqueryObjectOfSes('html, body').animate({
												scrollTop: jqueryObjectOfSes('#addcategory').offset().top },
												1000
								);
							}
						});
						return false;
				}
		}
	 }
});
jqueryObjectOfSes(document).on('click','.deleteCat',function(){
	var id = jqueryObjectOfSes(this).attr('data-url');
	var confirmDelete = confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected category?")) ?>');
	if(confirmDelete){
			jqueryObjectOfSes('#categoryid-'+id).css('background-color','#ffebe8');
			var selectedCategory=[id]
			jqueryObjectOfSes.post(window.location.href,{data:selectedCategory,selectDeleted:'true'},function(response){
			response = jqueryObjectOfSes.parseJSON(response); 
				if(response.ids){
					var ids = response.ids;
					if(response.diff_ids.length>0){
						jqueryObjectOfSes('#error-message-category-delete').html("Red mark category can't delete.You need to delete lower category of that category first.<br></br>");
						jqueryObjectOfSes('#error-message-category-delete').css('color','red');
						 scrollToError = true;
					}else{
						jqueryObjectOfSes('#error-message-category-delete').html("");
							jqueryObjectOfSes('#error-message-category-delete').css('color','');
					}
					for(var i =0;i<=ids.length;i++){
						jqueryObjectOfSes('select#parent option[value="' + ids[i] + '"]').remove();
						jqueryObjectOfSes('#categoryid-'+ids[i]).fadeOut("normal", function() {
								jqueryObjectOfSes(this).remove();
						});
					}
					if(scrollToError){
						jqueryObjectOfSes('html, body').animate({
									scrollTop: jqueryObjectOfSes('#addcategory').offset().top },
									1000
						);
					}
				}
		});
	}
});
</script>
