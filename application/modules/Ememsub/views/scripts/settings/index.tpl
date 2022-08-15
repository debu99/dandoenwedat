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
<?php if( $this->isAdmin ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Subscriptions are not required for ' .
          'administrators and moderators.') ?>
    </span>
  </div>
  <?php return; endif; ?>
  <?php $templeteId = Engine_Api::_()->getDbTable('templates','ememsub')->getSelectedTempleteId(); ?>
  <?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/styles/styles.css'); ?>
  <?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/styles/customscrollbar.css'); ?>
  <?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery.min.js'); ?>
  <?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/customscrollbar.concat.min.js'); ?>
  
  <h3>
    <?php echo $this->translate('Subscription') ?>
  </h3>
  <?php if( $this->currentPackage && $this->currentSubscription ): ?>
    <p class="form-description">
      <?php echo $this->translate('The plan you are currently subscribed ' .
          'to is: %1$s', '<strong>' .
          $this->translate($this->currentPackage->title) . '</strong>') ?>
      <br />
      <?php echo $this->translate('You are currently paying: %1$s',
          '<strong>' . $this->currentPackage->getPackageDescription()
          . '</strong>') ?>
    </p>
    <p style="padding-top: 15px; padding-bottom: 15px;">
      <?php echo $this->translate('If you would like to change your ' .
          'subscription, please select an option below.') ?>
    </p>
  <?php else: ?>
    <p class="form-description">
      <?php echo $this->translate('You have not yet selected a ' .
          'subscription plan. Please choose one now below.') ?>
    </p>
  <?php endif; ?>
  <?php $template = Engine_Api::_()->getItem('ememsub_template',$templeteId); ?>
  <?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
  <form method="get" id="signup"  name="signup" action="<?php echo $this->escape($this->url(array('action' => 'confirm'))); ?>" enctype="application/x-www-form-urlencoded">
    <div class="ememsub_pricing_table_main _bxs ememsub_clearfix <?php echo $template->overlap ? 'ememsub_pricing_table_overlap' : ''; ?>" style="background-color:#<?php echo $template->body_container_clr; ?>;">
      <div class="ememsub_pricing_table_header" style="background-color:#<?php echo $template->header_bgclr; ?>;">
        <p class="ememsub_pricing_table_heading" style="color:#<?php echo $template->header_txtclr; ?>;"><?php echo $settings->getSetting('ememsub.table.title',''); ?></p>
        <p class="ememsub_pricing_table_des" style="color:#<?php echo $template->header_txtclr; ?>;"><?php echo $settings->getSetting('ememsub.table.description',''); ?></p>
      </div>
      <div class="ememsub_pricing_table">
        <?php foreach($this->packages as $package): ?>
          <?php
           if( $package->package_id == $this->currentPackage->package_id ) {
              continue;
            }
          ?>
          <?php $feature = Engine_Api::_()->getDbtable('features', 'ememsub')->getFeatures($package->package_id); ?>
          <?php $styleTable = Engine_Api::_()->getDbtable('styles', 'ememsub');
              $column = $styleTable->getStyleId($package->package_id,$templeteId); 
          ?>
          <div class="ememsub_pricing_table_item<?php if(!empty($column->show_highlight)):?> heighlighted<?php endif;?>" style="width:<?php echo is_numeric($column->column_width) ? $column->column_width.'px' : $this->width ?>; <?php if($column->column_margin):?>margin-left:<?php echo $column->column_margin - 4;?>px;margin-right:<?php echo $column->column_margin;?>px;<?php endif;?>">
            <article style="background-color:#<?php echo $column->column_row_color;?>;w">
              <div class="ememsub_pricing_table_title" style="background-color:#<?php echo $column->column_color?>;">
                <?php if(!empty($column->column_name)):?>
                  <span style="color:#<?php echo $column->column_text_color;?>" ><?php echo $column->column_name;?></span>
                <?php endif;?>
              </div>
	          <?php if(!empty($feature) && $feature->photo_id): ?>
	            <div class="ememsub_pricing_table_img" style="background-image:url(<?php echo $feature->getPhotoUrl(); ?>);"></div>
	          <?php endif; ?>
              <!-- CONTENT -->
              <div class="ememsub_pricing_table_content" style="background-color:#<?php echo $column->column_color; ?>;color:#<?php echo $column->column_text_color; ?>">
                  <p class="price">
                    <?php // Plan is free
                        $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
                        $priceStr = $this->locale()->toCurrency($package->price, $currency);
                        if( $package->price == 0 ) {
                          $typeStr = $this->translate('Free');
                        }
                        // Plan is recurring
                        else if( $package->recurrence > 0 && $package->recurrence_type != 'forever' ) {
    
                          // Make full string
                          if( $package->recurrence == 1 ) { // (Week|Month|Year)ly
                            if( $package->recurrence_type == 'day' ) {
                              $typeStr = $this->translate('daily');
                            } else {
                              $typeStr = $this->translate($package->recurrence_type . 'ly');
                            }
                          } else { // per x (Week|Month|Year)s
                            $typeStr = $this->translate(array($package->recurrence_type, $package->recurrence_type . 's', $package->recurrence));
                          }
                        }
                    ?>
                    <span><?php echo sprintf($this->translate('%1$s'), $priceStr); ?></span>
                    <?php if($typeStr && $package->recurrence > 0):?><sub>/&nbsp;<?php echo $typeStr; ?></sub><?php endif;?>
                  </p>
                  <p class="duration">
                    <?php $typeStr = $this->translate(array($package->duration_type, $package->duration_type . 's', $package->duration)); ?>
                    <?php if($package->duration > 0) { ?>
                      <span><?php echo sprintf($this->translate('%1$s %2$s'),$package->duration, $typeStr); ?></span>
                    <?php } else { ?>
                      <span><?php echo sprintf($this->translate('%1$s'),$typeStr); ?></span>
                    <?php } ?>
                  </p>
              </div>
              <div class="ememsub_pricing_table_hint" style="color:#<?php echo $column->column_row_text_color;?>;height:<?php echo $column->column_descr_height ?>px;border-color:#<?php echo $column->row_border_color; ?>;">
                <?php echo $this->translate($package->description) ?>
              </div>
              <?php // CONTENT ?> 
              <!-- FEATURES START -->
              <ul class="ememsub_pricing_table_features <?php if($column->icon_position):?>iscenter<?php endif;?>">
                  <?php $rowCount = $settings->getSetting('ememsub.table.row',4); //$this->table->num_row;
                  $tabs_count = array();
                  for ($i = 1; $i <= $rowCount; $i++) {
                    $tabs_count[] =  $i;
                  } 
                ?> 
                <?php foreach($tabs_count as $tab):?>
                  <?php $fileIdColumn = 'row'.$tab.'_file_id';?>
                  <?php $descriptionColumn = 'row'.$tab.'_description';?>
                  <?php $textColumn = 'row'.$tab.'_text';?>
                  <li class="ememsub_custom_scroll" style="height:<?php echo $column->row_height; ?>px;border-color:#<?php echo $column->row_border_color; ?>;">
                    <?php if($feature->$fileIdColumn):?>
                      <img src="<?php echo Engine_Api::_()->storage()->get($feature->$fileIdColumn)->getPhotoUrl(); ?>"  align="middle" />
                    <?php endif;?>
                    <?php if($feature->$descriptionColumn):?><i class="fa fa-question-circle ememsub_custom_tip_show ememsub_custom_tip_show" title="<?php echo $feature->$descriptionColumn;?>" style="color:#<?php echo $column->column_row_text_color;?>"></i><?php endif;?>
                    <?php if($feature->$textColumn):?>
                      <span style="color:#<?php echo $column->column_row_text_color;?>"><?php echo $feature->$textColumn;?></span>	
                    <?php endif;?>
                  </li>
                <?php endforeach;?>
              </ul>
              <!-- FEATURES END-->           
              
              <!-- PT-FOOTER START -->
              <div class="ememsub_pricing_table_footer">
                <input type="radio" name="package_id" id="package_id_<?php echo $package->package_id ?>" value="<?php echo $package->package_id ?>" class="package-select" />
                <a href="javascript:;" class="ememsub_animation" onclick="onFormSubmit(<?php echo $package->package_id ?>);" style="background-color:#<?php echo $column->footer_bg_color?>;color:#<?php echo $column->footer_text_color;?>"><?php echo $column->upgrade_footer_text;?></a>
              </div>
              <!-- PT-FOOTER END --> 
        
              <!--PT-Ribion-->
              <?php if($column->show_label):?>
                <div class="<?php if($column->label_position):?>ememsub_pricing_table_label right<?php else:?>ememsub_pricing_table_label left<?php endif;?>">
                  <?php if($column->label_text):?><div style="color:#<?php echo $column->label_text_color;?>;background-color:#<?php echo $column->label_color;?>;"><?php echo $column->label_text;?></div><?php endif;?>
                </div>
              <?php endif;?>
              <!--PT-Ribion-->
            </article>
          </div>
        <?php endforeach;?>
      </div>
    </div>
  </form>
  <?php if($settings->getSetting('ememsub.footer.enable',1)) { ?>
    <div class="_bxs ememsub_pricing_table_note">
      <div class="ememsub_rich_content">
        <?php echo $settings->getSetting('ememsub.footer.note',''); ?>
      </div>
    </div>
  <?php } ?>
<script type="text/javascript">
  function onFormSubmit($id)
  {  
    document.getElementById("package_id_"+$id).checked = true;
    document.getElementById("signup").submit();
  }
</script>
<script type="text/javascript">
/* Tips 4 */
var Tips4 = new Tips($$('.ememsub_custom_tip_show'), {
	className: 'ememsub_custom_tip'
});
</script>
