<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Terms.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Terms extends Engine_Form {

  public function init() {
    $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';

    $editorOptions = array(
        'upload_url' => '',
        'html' => (bool) $allowed_html,
    );
    $this->addElement('TinyMce', 'terms', array(
        'disableLoadDefaultDecorators' => true,
        'required' => true,
        'allowEmpty' => false,
        'decorators' => array(
            'ViewHelper'
        ),
        'editorOptions' => $editorOptions,
        'filters' => array(
            new Engine_Filter_Censor(),
            new Engine_Filter_Html(array('AllowedTags' => $allowedHtml))),
    ));
  }

}
