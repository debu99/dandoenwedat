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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?> 
<ul class="sesbasic_quick_links sesbasic_bxs">
  <?php foreach( $this->quickNavigation as $link ): ?>
    <li>
      <!-- <?php echo $this->htmlLink($link->getHref(), $this->translate($link->getLabel()), array(
        'class' => 'sesbasic_link_btn sesbasic_icon_add sesevent_quick_create',
        'target' => $link->get('target'),
      )) ?> -->
      <a href="/events/create" class="sesbasic_link_btn sesbasic_icon_add " target="">Create New Event</a>
    </li>
  <?php endforeach; ?> 
</ul>
<?php if($this->popup){ ?>
<?php $this->headScript()->appendFile( 'externals/tinymce/tinymce.min.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>
<script type="application/javascript">
sesJqueryObject('.sesevent_quick_create').addClass('sessmoothbox');
</script>
<?php } ?>