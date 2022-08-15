<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Plugin_Core extends Zend_Controller_Plugin_Abstract {

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {

        if (substr($request->getPathInfo(), 1, 5) == "admin") {
            $params = $request->getParams();
            if($params['module'] == 'sespwa' &&  $params['controller'] == "admin-menu") {
                $request->setModuleName('sesbasic');
                $request->setControllerName('admin-menu');
                $request->setActionName($params['action']);
                $request->setParam('moduleName', 'sespwa');

            }
        }

        if(substr($request->getPathInfo(), 1, 5) != "admin") {

            $mobile = $request->getParam("pwa");
            $session = new Zend_Session_Namespace('sespwa');

            if($mobile == "1") {
                $mobile = true;
                $session->sespwa = true;
            } elseif($mobile == "0") {
                $mobile = false;
                $session->sespwa = false;
            } else {
                if( isset($session->sespwa) ) {
                    $mobile = $session->sespwa;
                } else {
                    // CHECK TO SEE IF MOBILE
                    if( Engine_Api::_()->sespwa()->isMobile() && Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.enablepwamode', 1)) {
                        $mobile = true;
                        $session->mobile = true;
                    } else {
                        $mobile = false;
                        $session->sespwa = false;
                    }
                }
            }
            if($mobile == '1') {
              $params = $request->getParams();

              if($params['module'] == 'core' &&  $params['controller'] == "widget" && $params['action'] == 'index') {
                  $request->setModuleName('sespwa');
                  $request->setControllerName('widget');
                  $request->setActionName('index');
                  //$request->setParam('moduleName', 'sespwa');
              }
              if($params['module'] == 'core' &&  $params['controller'] == "pages") {
                  $request->setModuleName('sespwa');
                  $request->setControllerName('pages');
                  //$request->setActionName('index');
                  //$request->setParam('moduleName', 'sespwa');

              }
            }
            if(!$mobile) { return; }
            $this->changeContent();
            // Create layout
            $layout = Zend_Layout::startMvc();
            // Set options
            $layout->setViewBasePath(APPLICATION_PATH . "/application/modules/Sespwa/layouts", 'Core_Layout_View')
                    ->setViewSuffix('tpl')
                    ->setLayout(null);
            // Add themes
            $theme = null;
            $themes = array();
            $themesInfo = array();
            $themeTable = Engine_Api::_()->getDbtable('themes', 'sespwa');
            $themeSelect = $themeTable->select()
                    ->where('active = ?', 1)
                    ->limit(1);
            $theme = $themeTable->fetchRow($themeSelect);
            if ($theme) {
                $themes[] = $theme->name;
                $themesInfo[$theme->name] = include APPLICATION_PATH_COR . DS . 'themes/sespwa' . DS . $theme->name . DS . 'manifest.php';
            }
            $layout->themes = $themes;
            $layout->themesInfo = $themesInfo;
            Zend_Registry::set('Themes', $themesInfo);
        }
    }
    public function onCorePageDeleteBefore($event) {
        $payload = $event->getPayload();
        Engine_Api::_()->getDbTable('content', 'sespwa')->delete(array('page_id = ?' => $payload->page_id));
        Engine_Api::_()->getDbTable('pages', 'sespwa')->delete(array('page_id = ?' => $payload->page_id));
    }

    public function changeContent() {
        $content = Zend_Registry::get('Engine_Content');
        $contentTable = Engine_Api::_()->getDbtable('pages', 'sespwa');
        $content->setStorage($contentTable);
        Zend_Registry::set('Engine_Content', $content);
    }

    public function getAdminNotifications($event) {
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.pluginactivated')) {
            return;
        }
        $pagesTable = Engine_Api::_()->getDbTable('pages', 'sespwa');
        $pagesTableName = $pagesTable->info('name');

        $corepagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corepagesTableName = $corepagesTable->info('name');

        $pageId = $pagesTable->select()
                    ->from($pagesTableName, 'page_id')
                    ->limit(1)
                    ->order('page_id DESC')
                    ->query()
                    ->fetchColumn();

        $select = $corepagesTable->select()
                    ->from($corepagesTableName, 'page_id')
                    ->where('page_id > ?', $pageId)
                    ->order('page_id DESC');
        $results = $corepagesTable->fetchAll($select);

        if(count($results) > 0) {

            $translate = Zend_Registry::get('Zend_Translate');
            $message = vsprintf($translate->translate(array('<div class="sespwa_notice_tip">There are <a href="%s">%d new page</a> on your website which are not synced with the Progressive Web App Plugin\'s Layout Editor. To sink the pages go to, "Progressive Web App Plugin" >> "Manage Templates" section.</div>', '<div class="sespwa_notice_tip">There are <a href="%s">%d new pages</a> on your website which are not synced with the Progressive Web App Plugin\'s Layout Editor. To sink the pages go to, "Progressive Web App Plugin" >> "Manage Templates" section.</div>', count($results))), array(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sespwa', 'controller' => 'settings', 'action' => 'sink-pages'), 'admin_default', array('class' => 'smoothbox')), count($results)));

            $event->addResponse($message);
        }
    }
}
