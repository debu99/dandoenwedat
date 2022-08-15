<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery1.11.js'); ?>
<h3>
  <?php echo $this->translate("Manage Plan Features") ?>
</h3>
<p class="ememsub_search_reasult"><?php echo $this->translate("From here, you can manage features to be displayed in the Pricing Table for all the subscription plans on your website.") ?></p>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <table class='admin_table admin_packages_table'>
    <thead>
      <tr>
        <?php $class = ($this->order == 'package_id' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 1%;' class="<?php echo $class ?>">
            <?php echo $this->translate("ID") ?>
        </th>
        <?php $class = ( $this->order == 'title' ? 'admin_table_ordering admin_table_direction_' . strtolower($this->direction) : '' ) ?>
        <th style='width: 40%;' class="<?php echo $class ?>">
            <?php echo $this->translate("Title") ?>
        </th>
         <th style='width: 19%;' class="<?php echo $class ?>" align="center">
            <?php echo $this->translate("Image") ?>
        </th>
        <th style='width: 20%;' class='admin_table_options'>
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td><?php echo $item->package_id ?></td>
          <td class='admin_table_bold'>
            <?php echo $item->title ?>
          </td>
          <td class='admin_table_centered'>
            <?php $features = Engine_Api::_()->getDbtable('features', 'ememsub')->getFeatures($item->package_id); ?>
            <?php if(!empty($features) && $features->photo_id){?>
              <div class="ememsub_manage_img_preview_wrap">
              <img src="application/modules/Ememsub/externals/images/photo.png" alt="" />
                <div>
                  <img src="<?php echo $features->getPhotoUrl("icon"); ?>" alt="" />
                </div>
              </div>
            <?php }else{ ?>
              --
            <?php } ?>
          </td>
          <td class='admin_table_options'>
            <?php if(count($features)) { ?>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'ememsub', 'controller' => 'package', 'action' => 'edit-features','feature_id'=>$features->feature_id, 'reset' => true), $this->translate('Manage Features'), array()) ?>
              |
              <?php $text = $features->photo_id ? 'Change photo' : 'Upload photo'; ?>
              <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'ememsub', 'controller' => 'package', 'action' => 'upload-photo', 'feature_id' => $features->feature_id, 'reset' => true), $this->translate($text), array('class'=>"file_upload")); ?>
              <?php if($features->photo_id){ ?>
                |
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'ememsub', 'controller' => 'package', 'action' => 'remove-photo', 'feature_id' => $features->feature_id, 'reset' => true), $this->translate('Remove Photo'), array('class'=>"remove_photo")); ?>
              <?php } ?>
              <img src="<?php echo $features->getPhotoUrl(); ?>" id="preview_photo" style="display:none" alt="" />
            <?php } else { ?>
               <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'ememsub', 'controller' => 'package', 'action' => 'add-features', 'package_id' => $item->package_id, 'reset' => true), $this->translate('Manage Features'), array()) ?>
            <?php } ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <input type="file" name="Filedata" id="uploadPhotoFile" onchange="uploadCoverArt(this,'cover');" style="display:none" />
    </tbody>
  </table>
<?php else: ?>
<div class="tip">
    <span>
      <?php echo $this->translate("Currently, there are no Plans on your website. Please create plans to enter their features. %s.",$this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'package','reset' => true), $this->translate('here'), array())) ?>
    </span>
</div>

<?php endif; ?>
<script type="text/javascript">
  jqueryObjectOfSes(document).on('click','.file_upload',function(e){
    e.preventDefault();
    jqueryObjectOfSes("#uploadPhotoFile").attr("data-url",jqueryObjectOfSes(this).attr("href"));
    document.getElementById('uploadPhotoFile').click(); 
  });
  jqueryObjectOfSes(document).on('click','.remove_photo',function(e){
    e.preventDefault();
    var jqXHR=jqueryObjectOfSes.ajax({
    url: jqueryObjectOfSes(this).attr("href"),
    type: "POST",
    contentType:false,
    processData: false,
        cache: false,
        success: function(response){
          location.reload();
        }
    }); 
  });
  function uploadCoverArt(input){
    var url = input.value;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
      uploadFileToServer(input.files[0]);
    }
  }
  function uploadFileToServer(files){
    var formData = new FormData();
    formData.append('Filedata', files);
    var jqXHR=jqueryObjectOfSes.ajax({
    url: jqueryObjectOfSes("#uploadPhotoFile").attr("data-url"),
    type: "POST",
    contentType:false,
    processData: false,
        cache: false,
        data: formData,
        success: function(response){
          response = jqueryObjectOfSes.parseJSON(response);
          jqueryObjectOfSes('#uploadPhotoFile').val('');
          jqueryObjectOfSes('#preview_photo').attr('src', response.file);
          location.reload();
        }
    }); 
  }
</script>
