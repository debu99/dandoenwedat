<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesadvancedactivity/externals/styles/styles.css'); ?>
<ul class="sesbasic_sidebar_block sesact_trends_block sesbasic_bxs sesbasic_clearfix">
  <?php foreach($this->trends as $trend){ ?>
    <?php if(!empty($trend->title)) { ?>
    <li class="sesbasic_clearfix">
	    <a href="hashtag?hashtag=<?php echo $trend->title; ?>">#<?php echo $trend->title; ?></a>
      <span class="sesbasic_text_light"><?php echo $this->translate(array('%s people talking about this.', '%s peoples talking about this.', $trend->total), $this->locale()->toNumber($trend->total))?></span>
    </li>
    <?php } ?>
  <?php } ?>
</ul>
