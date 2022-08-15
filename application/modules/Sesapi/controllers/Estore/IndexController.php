<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Estore_IndexController extends Sesapi_Controller_Action_Standard {

    public function init() {

        if (!$this->_helper->requireAuth()->setAuthParams('stores', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $store_id = $this->_getParam('store_id');

        $store = null;
        $store = Engine_Api::_()->getItem('stores', $store_id);
        if ($store) {
            if ($store) {
                Engine_Api::_()->core()->setSubject($store);
            } else {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
            }
        }
    }
    
    public function mycartAction() {
    
      //get all cart data
      $socialSharingActive = 1;
      $likeButtonActive = 1;
      $favouriteButtonActive = 1;
      $cartData = Engine_Api::_()->sesproduct()->cartTotalPrice();
      $productsArray = $cartData['productsArray'];
      $totalPrice = round($cartData['totalPrice'],2);
    
      $result = array();
      $counter = 0;
      $session = new Zend_Session_Namespace('sesproduct_product_quantity');
      if(count($productsArray)){
        $storeArray = array();
        foreach($productsArray as $cart){
          $totalPrice = 0;
//           if(count($cart['stores'])) {
//             $result['cartData'][$counter]['store_title'] = $cart['stores']->getTitle();
//             
//           }
          
          $products = array();
          $productsCounter = 0;
          foreach($cart['cartproducts'] as $itemCart) {
            $item = Engine_Api::_()->getItem('sesproduct',$itemCart['product_id']);
            if(!count($item))
              continue;
            $price = $cart['products_extra'][$itemCart['cartproduct_id']]['product_price'];
            $quantity = $cart['products_extra'][$itemCart['cartproduct_id']]['quantity'];
            $totalPrice += $price;
            
            $result['cartData'][$counter]['productData'][$productsCounter]['title'] = $item->getTitle();
            $result['cartData'][$counter]['productData'][$productsCounter]['quantity'] = $quantity;
            if(!empty($price)){
              $result['cartData'][$counter]['productData'][$productsCounter]['price'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($price,2)) ;
            } else {
              $result['cartData'][$counter]['productData'][$productsCounter]['price'] = 'FREE';
            }
            
            
            if(!empty($session->cart_product_{$itemCart['cartproduct_id']})){
              $result['cartData'][$counter]['productData'][$productsCounter]['cart_error'] = $this->view->translate("%s",$session->cart_product_{$itemCart['cartproduct_id']});
            }
            
            $images = Engine_Api::_()->sesapi()->getPhotoUrls($item,'',"");
            if(!count($images))
              $images['main'] = $this->getBaseUrl(true,$item->getPhotoUrl());
            $result['cartData'][$counter]['productData'][$productsCounter]['product_images'] = $images;
            
            //Menus
            $menuoptions= array();
            $menucounter = 0;
            
            $menuoptions[$menucounter]['name'] = "remove";
            $menuoptions[$menucounter]['id'] = $itemCart["cartproduct_id"];
            $menuoptions[$menucounter]['label'] = $this->view->translate("Remove");
            $menucounter++;
            //Menus
            
            $result['cartData'][$counter]['productData'][$productsCounter]['buttons'] = $menuoptions;
            $productsCounter++;
          }
          $result['cartData'][$counter]['sub_total'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($totalPrice,2));
          
          if(count($cart['stores'])) { 
            $storeArray[$cart['stores']->getTitle()] = $totalPrice;
          }
          
          $counter++;
        }
        $extraParams = array();
        $extraParams['empty'] = $this->view->translate('Clear All');
        $extraParams['checkout'] = $this->view->translate('Checkout');
        $result['extraParams'] = $extraParams;
        
        $result['grand_total'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($this->totalPrice,2));

        $result['checkout'] = $this->view->translate("Proceed to Checkout");
      }
      if($result <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Product Exists.'), 'result' => array()));
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
    }
    
    public function checkoutAction() {
    
      //get all cart data
      $socialSharingActive = 1;
      $likeButtonActive = 1;
      $favouriteButtonActive = 1;
      $cartData = Engine_Api::_()->sesproduct()->cartTotalPrice();
      $productsArray = $cartData['productsArray'];
      $totalPrice = round($cartData['totalPrice'],2);
      
      //update cart values
      $session = new Zend_Session_Namespace('sesproduct_product_quantity');
      //$session->unsetAll();
      
      if(count($_POST)){
          foreach ($_POST as $key=>$quantity){
            $id = str_replace('quantity_','',$key);
            
            if($id){
                $cartProduct = Engine_Api::_()->getItem('sesproduct_cartproducts',$id);
                if($cartProduct){ 
                    $product = Engine_Api::_()->getItem('sesproduct',$cartProduct->product_id);
                     $productAvailableQuantity = Engine_Api::_()->sesproduct()->checkVariations($cartProduct,true);
                    if($product && $product->type == "configurableProduct" && !empty($productAvailableQuantity)) {
                        //Check Product Order quantity And add product to cart
                        if ($quantity > $productAvailableQuantity['quantity']) {
                            if($cartProduct->quantity == $productAvailableQuantity['quantity'] ||  $productAvailableQuantity['quantity'] - $cartProduct->quantity < 0){
                                $session->cart_product_{$id} = $this->view->translate("No more quantity available for this product.");
                            }else {
                                $session->cart_product_{$id} = $this->view->translate("Only %s quantity left for this product.", $productAvailableQuantity);
                            }
                        } else {
                            $cartProduct->quantity = $quantity;
                            $cartProduct->save();
                        }
                    }else{
                        if(!empty($product->manage_stock)){
                            if((!empty($product->manage_stock) && $product->stock_quatity < $product->min_quantity) || $product->max_quatity < $quantity){
                                if ($product->stock_quatity == 1)
                                    $session->cart_product_{$id} = $this->view->translate("Only 1 quantity of this product is available in stock.");
                                else if($product->max_quatity < $quantity)
                                    $session->cart_product_{$id} = $this->view->translate("Only %s quantities of this product are available in stock. Please enter the quantity less than or equal to %s", $product->max_quatity,$product->max_quatity);
                                else
                                    $session->cart_product_{$id} = $this->view->translate("Only %s quantities of this product are available in stock. Please enter the quantity less than or equal to %s.", $product->stock_quatity, $product->stock_quatity);
                                //return;
                            }
                            $cartProduct->quantity = $quantity;
                            $cartProduct->save();
                        }else{
                            $cartProduct->quantity = $quantity;
                            $cartProduct->save();
                        }
                    }
                }
            }
          }
      }
      
    
      $result = array();
      $counter = 0;
      $session = new Zend_Session_Namespace('sesproduct_product_quantity');
      if(count($productsArray)){
        $storeArray = array();
        foreach($productsArray as $cart){
          $totalPrice = 0;
          if(count($cart['stores'])) {
            $result['cartData'][$counter]['store_title'] = $cart['stores']->getTitle();
            
          }
          
          $products = array();
          $productsCounter = 0;
          foreach($cart['cartproducts'] as $itemCart) {
          
            $item = Engine_Api::_()->getItem('sesproduct',$itemCart['product_id']);
            if(!count($item))
              continue;
            $price = $cart['products_extra'][$itemCart['cartproduct_id']]['product_price'];
            $quantity = $cart['products_extra'][$itemCart['cartproduct_id']]['quantity'];
            $totalPrice += $price;
            
            $result['cartData'][$counter]['productData'][$productsCounter]['title'] = $item->getTitle();
            $result['cartData'][$counter]['productData'][$productsCounter]['product_id'] = $itemCart["cartproduct_id"];
            $result['cartData'][$counter]['productData'][$productsCounter]['quantity'] = $itemCart["quantity"];
            if(!empty($price)){
              $result['cartData'][$counter]['productData'][$productsCounter]['price'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($price,2)) ;
            } else {
              $result['cartData'][$counter]['productData'][$productsCounter]['price'] = 'FREE';
            }
            
            
            if(!empty($session->cart_product_{$itemCart['cartproduct_id']})){
              $result['cartData'][$counter]['productData'][$productsCounter]['cart_error'] = $this->view->translate("%s",$session->cart_product_{$itemCart['cartproduct_id']});
            }
            
            $images = Engine_Api::_()->sesapi()->getPhotoUrls($item,'',"");
            if(!count($images))
              $images['main'] = $this->getBaseUrl(true,$item->getPhotoUrl());
            $result['cartData'][$counter]['productData'][$productsCounter]['product_images'] = $images;
            
            //Menus
            $menuoptions= array();
            $menucounter = 0;
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.wishlist', 1)) {
              $menuoptions[$menucounter]['name'] = "addwishlist";
              $menuoptions[$menucounter]['id'] = $itemCart["cartproduct_id"];
              $menuoptions[$menucounter]['label'] = $this->view->translate("Add to Wishlist");
              $menucounter++;
            }
            
            $menuoptions[$menucounter]['name'] = "remove";
            $menuoptions[$menucounter]['id'] = $itemCart["cartproduct_id"];
            $menuoptions[$menucounter]['label'] = $this->view->translate("Remove");
            $menucounter++;
            //Menus
            
            $result['cartData'][$counter]['productData'][$productsCounter]['buttons'] = $menuoptions;
            $productsCounter++;
          }
          $result['cartData'][$counter]['sub_total'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($totalPrice,2));
          
          if(count($cart['stores'])) { 
            $storeArray[$cart['stores']->getTitle()] = $totalPrice;
          }
          
          $counter++;
        }
        
        $extraParams = array();
        $extraParams['empty'] = $this->view->translate('Empty Cart');
        $extraParams['update'] = $this->view->translate('Update Cart');
        $extraParams['continue'] = $this->view->translate('Continue Shopping');
        $result['extraParams'] = $extraParams;
        
        $priceDetails = array();
        $priceDetailsCounter = 0;
        //$priceDetails['title'] = $this->view->translate("Price Details");
        if(count($storeArray) > 1){
          foreach($storeArray as $key=>$storePrice){
            $priceDetails[$priceDetailsCounter]['title'] = $this->view->translate("Net Amount Subtotal of %s store",$key);
            $priceDetails[$priceDetailsCounter]['price'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($storePrice,2));
            $priceDetailsCounter++;
          }
          $result['price_title'] = $this->view->translate("Price Details");
          $result['priceDetails'] = $priceDetails;
        } else {
          $result['price_title'] = $this->view->translate("Price Details");
          $result['order_total_title'] = $this->view->translate("Order Total");
        }
        $cartData = Engine_Api::_()->sesproduct()->cartTotalPrice();
        $result['order_total'] = Engine_Api::_()->sesproduct()->getCurrencyPrice(round($cartData['totalPrice'],2));

        $result['checkout'] = $this->view->translate("Proceed to Checkout");
        $result['checkouturl'] = 'stores/product/cart/checkout';
      }
      if($result <= 0)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=> $this->view->translate('No Category exists.'), 'result' => array()));
      else
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));
    }
    
  function deletecartAction() {
  
      $id = $this->_getParam('id');

      $this->view->form = $form = new Sesbasic_Form_Delete();
      if($id) {
          $form->setTitle($this->view->translate("Delete Product from Shopping Cart?"));
          $form->setDescription($this->view->translate('Are you sure that you want to delete this product from your shopping cart? Product will not be recoverable after being deleted.'));
      }else{
          $form->setTitle($this->view->translate('Delete Products from Shopping Cart?'));
          $form->setDescription($this->view->translate('Are you sure that you want to clear your shopping cart? Product’s will not be recoverable after being deleted.'));
      }
      $form->submit->setLabel('Delete');

      if (!$this->getRequest()->isPost()) {
          $this->view->status = false;
          $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
      }

      $cartProductTable = Engine_Api::_()->getDbTable('cartproducts','sesproduct');
      $db = $cartProductTable->getAdapter();
      $db->beginTransaction();
      try {
          $cartId = Engine_Api::_()->sesproduct()->getCartId();
          if($id) {
              $cartProductTable->delete(array('cartproduct_id =?' => $id, 'cart_id =?' => $cartId->getIdentity()));
              Engine_Api::_()->getDbtable('cartproductsvalues', 'sesproduct')->delete(array('item_id = ?' => $id));
              Engine_Api::_()->getDbtable('cartproductssearch', 'sesproduct')->delete(array('item_id = ?' => $id));
          }else{
              $cartProductTable = Engine_Api::_()->getDbTable('cartproducts','sesproduct');
              $select = $cartProductTable->select()->where('cart_id =?',$cartId->getIdentity());
              $products = $cartProductTable->fetchAll($select);
              foreach ($products as $product) {
                  Engine_Api::_()->getDbtable('cartproductsvalues', 'sesproduct')->delete(array('item_id = ?' => $product->getIdentity()));
                  Engine_Api::_()->getDbtable('cartproductssearch', 'sesproduct')->delete(array('item_id = ?' => $product->getIdentity()));
                  $product->delete();
              }
          }
          $db->commit();
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
      }
      if($id) {
        $message = Zend_Registry::get('Zend_Translate')->_('Product removed from your cart.');
      } else {
        $message = Zend_Registry::get('Zend_Translate')->_('All Product removed from your cart.');
      }
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
  
  public function getstateAction(){ 
      $country_id = $this->_getParam('country_id');
      if(!$country_id)
      {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Choose Country.'), 'result' => array()));
      }

      $states = Engine_Api::_()->getDbTable('states','estore')->getStates(array('country_id'=>$country_id));
      $results = array('' => 'Select State');
      foreach($states as $state){
          $results[$state['state_id']] = $state['name'];
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $results));
      
  }
	
  public function billingAction() {
  
    //$this->_helper->content->setEnabled();
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      
    $addressTable = Engine_Api::_()->getDbTable('addresses','sesproduct');
    $billingAddressArray = $addressTable->getAddress(array('user_id'=>$viewer_id,'type'=>0));
    $this->view->form = $form = new Estore_Form_Billing();
    if(count($billingAddressArray)){
        $this->view->country_id = $billingAddressArray[0]->country;
        $this->view->state_id = $billingAddressArray[0]->state;
        $form->populate($billingAddressArray[0]->toArray());
    }
    $form->setTitle('Billing form');
    $form->setAttrib('id', 'estore_billing_form');
    
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'stores'));
    }
    // If not post or form not valid, return
//     if( !$this->getRequest()->isPost() ) {
//       return;
//     }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      //$formFields[4]['name'] = "file";
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    
    if($this->getRequest()->isPost())
    {
        if(!count($billingAddressArray)){
            $billing = $addressTable->createRow();
            $billing->setFromArray($_POST);
            $billing->type = 0;
            $billing->user_id = $viewer_id;
            $billing->save();
        }
        else{
            $billing = $billingAddressArray[0];
            $billing->setFromArray($_POST);
            $billing->type = 0;
            $billing->user_id = $viewer_id;
            $billing->save();
        }
        //$this->_redirect('/estore/manage/billing');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message' => $this->view->translate('Billing Address added successfully.'))));
    }
  }

  public function subcategoryAction() {
    $category_id = $this->_getParam('category_id', null);
    if ($category_id) {
      $subcategory = Engine_Api::_()->getDbTable('categories', 'estore')->getModuleSubcategory(array('category_id' => $category_id, 'column_name' => '*'));
      $count_subcat = count($subcategory->toarray());
      if ($subcategory && $count_subcat) {
        $data = array('' => '');
        foreach ($subcategory as $category) {
          $data[$category['category_id']] = $category['category_name'];
        }
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $data));
    } else {
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
    }
   
  }
  
  public function shippingAction() {
  
    //$this->_helper->content->setEnabled();
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid() )
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      
    $addressTable = Engine_Api::_()->getDbTable('addresses','sesproduct');
    $shippingAddressArray = $addressTable->getAddress(array('user_id'=>$viewer_id,'type'=>1));
    $this->view->form = $form = new Estore_Form_Billing();
    if(count($shippingAddressArray))
        $form->populate($shippingAddressArray[0]->toArray());
    $form->setTitle('Shipping form');
    $form->setAttrib('id', 'estore_shipping_form');
    // Check if post and populate
    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      $this->generateFormFields($formFields,array('resources_type'=>'stores'));
    }
    // If not post or form not valid, return
//     if( !$this->getRequest()->isPost() ) {
//       return;
//     }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }
    
    if($this->getRequest()->isPost() )
    {
      if(!count($shippingAddressArray)){
            $shipping = $addressTable->createRow();
            $shipping->setFromArray($_POST);
            $shipping->type = 1;
            $shipping->user_id = $viewer_id;
            $shipping->save();
       } else{
            $shipping = $shippingAddressArray[0];
            $shipping->setFromArray($_POST);
            $shipping->type = 1;
            $shipping->user_id = $viewer_id;
            $shipping->save();
        }
        //$this->_redirect('/estore/manage/shipping');
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('message' => $this->view->translate('Shipping Address added successfully.'))));
    }
  }
  
  public function myWishlistsAction() {
  
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      
    $is_search_ajax = $this->_getParam('is_search_ajax', null) ? $this->_getParam('is_search_ajax') : false;
    $this->_helper->content->setEnabled();
    $this->view->formFilter = $formFilter = new Sesproduct_Form_Admin_Wishlist();

    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
    $values = $formFilter->getValues();
    $values = array_merge(array(
    'order' => isset($_GET['order']) ? $_GET['order'] :'',
    'order_direction' => isset($_GET['order_direction']) ? $_GET['order_direction'] : '',
    ), $values);

    if (isset($_POST['searchParams']) && $_POST['searchParams'])
        parse_str($_POST['searchParams'], $searchArray);

    $this->view->assign($values);

    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $productTable = Engine_Api::_()->getDbTable('wishlists', 'sesproduct');
    $productTableName = $productTable->info('name');
    $select = $productTable->select()
                        ->setIntegrityCheck(false)
                        ->from($productTableName)
                        ->where($productTableName.'.owner_id = ?',$viewer_id)
                        ->joinLeft($tableUserName, "$productTableName.owner_id = $tableUserName.user_id", 'username')
                        ->order((!empty($_GET['order']) ? $_GET['order'] : 'wishlist_id' ) . ' ' . (!empty($_GET['order_direction']) ? $_GET['order_direction'] : 'DESC' ));

    if (!empty($searchArray['name']))
        $select->where($productTableName . '.title LIKE ?', '%' . $searchArray['name'] . '%');

    if (!empty($searchArray['owner_name']))
        $select->where($tableUserName . '.displayname LIKE ?', '%' . $searchArray['owner_name'] . '%');

    if (isset($searchArray['is_featured']) && $searchArray['is_featured'] != '')
        $select->where($productTableName . '.is_featured = ?', $searchArray['is_featured']);

    if (isset($searchArray['is_sponsored']) && $searchArray['is_sponsored'] != '')
        $select->where($productTableName . '.is_sponsored = ?', $searchArray['is_sponsored']);

    if (isset($searchArray['package_id']) && $searchArray['package_id'] != '')
        $select->where($productTableName . '.package_id = ?', $searchArray['package_id']);

    if (isset($searchArray['offtheday']) && $searchArray['offtheday'] != '')
        $select->where($productTableName . '.offtheday = ?', $searchArray['offtheday']);

    if (isset($searchArray['rating']) && $searchArray['rating'] != '') {
        if ($searchArray['rating'] == 1):
            $select->where($productTableName . '.rating <> ?', 0);
        elseif ($searchArray['rating'] == 0 && $searchArray['rating'] != ''):
            $select->where($productTableName . '.rating = ?', $searchArray['rating']);
        endif;
    }

    if (!empty($searchArray['order_max']))
    $select->having("$productTableName . '.creation_date <=?", $searchArray['order_max']);
    if (!empty($searchArray['order_min']))
    $select->having("$productTableName . '.creation_date >=?", $searchArray['order_min']);

    if (isset($searchArray['subcat_id'])) {
            $formFilter->subcat_id->setValue($searchArray['subcat_id']);
            $this->view->category_id = $searchArray['category_id'];
    }
    if (isset($searchArray['subsubcat_id'])) {
            $formFilter->subsubcat_id->setValue($searchArray['subsubcat_id']);
            $this->view->subcat_id = $searchArray['subcat_id'];
    }

    $urlParams = array();
    foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $urlParamsKey=>$urlParamsVal){
    if($urlParamsKey == 'module' || $urlParamsKey == 'controller' || $urlParamsKey == 'action' || $urlParamsKey == 'rewrite')
        continue;
        $urlParams['query'][$urlParamsKey] = $urlParamsVal;
    }
    $this->view->urlParams = $urlParams;
    $paginator = Zend_Paginator::factory($select);
    
    $result = array();
    $counter = 0;
    
    foreach ($paginator as $results) {
      
      $item = $results->toArray();

      $result['wishlists'][$counter] = $item;
      $result['wishlists'][$counter]['images']['main']= $this->getBaseUrl(true, $results->getPhotoUrl());

      $result['wishlists'][$counter]['owner_name'] = $results->getOwner()->getTitle();
      
      if(!empty($viewer_id) && $viewer_id == $results->owner_id) {
          $menuoptions= array();
          $menucounter = 0;
          $menuoptions[$menucounter]['name'] = "edit";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Edit");
          $menucounter++;
          
          $menuoptions[$menucounter]['name'] = "delete";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
          $menucounter++;
          $result['wishlists'][$counter]['menus'] = $menuoptions;
      }
      $counter++;
    }
    
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  

  //Edit Action
  public function editwishlistAction() {
  
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

    //Get wishlist
    $wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $this->_getParam('wishlist_id'));
    
    //Make form
    $form = new Sesproduct_Form_Wishlist_Edit();

    $form->populate($wishlist->toarray());

    if($this->_getParam('getForm')) {
      $formFields = Engine_Api::_()->getApi('FormFields','sesapi')->generateFormFields($form);
      //$formFields[4]['name'] = "file";
      $this->generateFormFields($formFields,array('resources_type'=>'sesproduct_wishlist'));
    }
    
    // Check post/form
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $validateFields = Engine_Api::_()->getApi('FormFields','sesapi')->validateFormFields($form);
      if(count($validateFields))
        $this->validateFormFields($validateFields);
    }

    $values = $form->getValues();

    unset($values['file']);

    $db = Engine_Api::_()->getDbTable('wishlists', 'sesproduct')->getAdapter();
    $db->beginTransaction();
    try {
      $wishlist->title = $values['title'];
      $wishlist->description = $values['description'];
			$wishlist->is_private = $values['is_private'];
      $wishlist->save();

      //Photo upload for wishlist
      if (!empty($values['mainphoto'])) {
        $previousPhoto = $wishlist->photo_id;
        if ($previousPhoto) {
          $wishlistPhoto = Engine_Api::_()->getItem('storage_file', $previousPhoto);
          $wishlistPhoto->delete();
        }
        $wishlist->setPhoto($form->mainphoto, 'mainPhoto');
      }

      if (isset($values['remove_photo']) && !empty($values['remove_photo'])) {
        $storage = Engine_Api::_()->getItem('storage_file', $wishlist->photo_id);
        $wishlist->photo_id = 0;
        $wishlist->save();
        if ($storage)
          $storage->delete();
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => array('wishlist_id' => $wishlist->getIdentity(),'message' => $this->view->translate('Your changes has been successfully saved.'))));
  }
  
  public function deletewishlistAction() {

    $wishlist = Engine_Api::_()->getItem('sesproduct_wishlist', $this->_getParam('wishlist_id'));

    $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Wishlist?');
    $form->setDescription('Are you sure that you want to delete this wishlist? It will not be recoverable after being deleted. ');
    $form->submit->setLabel('Delete');


    if (!$wishlist) {
      $error = Zend_Registry::get('Zend_Translate')->_("Wishlist doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    $db = $wishlist->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      //Delete all wishlist products which is related to this wishlist
      Engine_Api::_()->getDbtable('playlistproducts', 'sesproduct')->delete(array('wishlist_id =?' => $this->_getParam('wishlist_id')));
      $wishlist->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    
    $message = Zend_Registry::get('Zend_Translate')->_('The selected wishlist has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
  
	public function vieworderAction() {
	
    $order_id = $this->_getParam('order_id', null);
    
    $order = Engine_Api::_()->getItem('sesproduct_order', $order_id);
    $viewer = Engine_Api::_()->user()->getViewer();
    if(!$order_id)
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
      
    $this->view->format = $this->_getParam('format','');
    
    $store = null;
   
    if ($order->store_id) {
      $store = Engine_Api::_()->getItem('stores', $order->store_id);
     
      if(!$store)
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));
        
        $addressTable =   Engine_Api::_()->getDbtable('orderaddresses', 'sesproduct');
        $shippingAddress  = $addressTable->getAddress(array('type'=>1,'order_id'=>$order->order_id,'view'=>1));
        $billingAddress =  $addressTable->getAddress(array('order_id'=>$order->order_id,'type'=>0 ,'view'=>1));
        
        $shipping = $shippingAddress;
        $billing = $billingAddress;
        
        
        $result = array();

        //Billing
        $billingArray = array();
        $billingCounter = 0;
        $result['billing_name'] = $this->view->translate("Name & Billing Address");
        
        $billingArray[$billingCounter]['name'] = $billing->first_name . $billing->last_name;
        $billingArray[$billingCounter]['address'] = $billing->address;
        if(isset($billing->country)) {
          $billingCountry =   Engine_Api::_()->getItem('estore_country', $billing->country);
          $billingArray[$billingCounter]['billing_name'] = $billingCountry->name;
          $billingArray[$billingCounter]['phonecode'] = $billingCountry->phonecode;
        }
        if(isset($billing->state)) {
          $billingState =   Engine_Api::_()->getItem('estore_state', $billing->state);
          $billingArray[$billingCounter]['state_name'] = $billingState->name;
        }
        $billingArray[$billingCounter]['city'] = $billing->city;
        $billingArray[$billingCounter]['phone_number'] = $billing->phone_number;
        $billingArray[$billingCounter]['email'] = $billing->email;
        
        $billingCounter++;
        $result['billing'] = $billingArray;
        
        
        //Shipping
        $shippingArray = array();
        $shippingCounter = 0;
        $result['shipping_name'] = $this->view->translate("Name & Billing Address");
        
        $shippingArray[$shippingCounter]['name'] = $shipping->first_name . $shipping->last_name;
        $shippingArray[$shippingCounter]['address'] = $shipping->address;
        if(isset($shipping->country)) {
          $shippingCountry =   Engine_Api::_()->getItem('estore_country', $shipping->country);
          $shippingArray[$shippingCounter]['shipping_name'] = $shippingCountry->name;
          $shippingArray[$shippingCounter]['phonecode'] = $shippingCountry->phonecode;
        }
        if(isset($shipping->state)) {
          $shippingState =   Engine_Api::_()->getItem('estore_state', $shipping->state);
          $shippingArray[$shippingCounter]['state_name'] = $shippingState->name;
        }
        $shippingArray[$shippingCounter]['city'] = $shipping->city;
        $shippingArray[$shippingCounter]['phone_number'] = $shipping->phone_number;
        $shippingArray[$shippingCounter]['email'] = $shipping->email;
        
        $shippingCounter++;
        $result['shipping'] = $shippingArray;
        
//         $result['order_number'] = $this->view->translate("Order Id:#").$order->order_id;
//         $result['total_amount'] = '['.$this->view->translate('Total:') . $order->total <= 0 ? $this->view->translate("FREE") : Engine_Api::_()->estore()->getCurrencyPrice($order->total,$order->currency_symbol,$order->change_rate) .']';
        
        $otherDetails = array();
        $otherDetailsCounter = 0;
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Store Details');
        $otherDetails[$otherDetailsCounter]['label'] = $store->getTitle();
        $otherDetailsCounter++;
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Ordered By');
        $otherDetails[$otherDetailsCounter]['label'] = $viewer->getOwner()->getTitle();
        $otherDetailsCounter++;
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Payment Information');
        $otherDetails[$otherDetailsCounter]['label'] = '';
        $otherDetailsCounter++;
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Payment method: ');
        $otherDetails[$otherDetailsCounter]['label'] = $order->gateway_type;
        if($order->cheque_id > 0) {
          
          $cheque =   Engine_Api::_()->getItem('sesproduct_ordercheques', $order->cheque_id);
          
          $otherDetails[$otherDetailsCounter]['cheque_number'] = $this->view->translate("Cheque No : %s",$cheque->cheque_number);
          
          $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate("Account Holder Name : %s",$cheque->name);
          
          $otherDetails[$otherDetailsCounter]['account_number'] = $this->view->translate("Account Number : %s",$cheque->account_number);
          
          $otherDetails[$otherDetailsCounter]['routing_number'] = $this->view->translate("Account Routing No : %s",$cheque->routing_number);
          
        
        }
        $otherDetailsCounter++;
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Order Information');
        $otherDetails[$otherDetailsCounter]['label'] = '';
        $otherDetailsCounter++;
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate("Ordered Date :");
        $otherDetails[$otherDetailsCounter]['label'] = Engine_Api::_()->sesproduct()->dateFormat($order->creation_date);
        $otherDetailsCounter++;
        
        $orderproduct = Engine_Api::_()->getItem('sesproduct_orderproduct', $order->order_id);
        if($order->total_admintax_cost > 0) {
          $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Admin Tax Amount :');
          $otherDetails[$otherDetailsCounter]['label'] = Engine_Api::_()->sesproduct()->getCurrencyPrice($order->total_admintax_cost,$order->currency_symbol,$order->change_rate);
          $otherDetailsCounter++;
        }
        
        if($order->total_shippingtax_cost > 0){
          $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Shipping Amount :');
          $otherDetails[$otherDetailsCounter]['label'] = Engine_Api::_()->sesproduct()->getCurrencyPrice($order->total_shippingtax_cost,$order->currency_symbol,$order->change_rate);
          $otherDetailsCounter++;
        }
        
        if($order->total_billingtax_cost > 0){
          $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Store Tax Amount:');
          $otherDetails[$otherDetailsCounter]['label'] = $order->total_billingtax_cost > 0 ? Engine_Api::_()->estore()->getCurrencyPrice($order->total_billingtax_cost,$order->currency_symbol,$order->change_rate) : "-";
          $otherDetailsCounter++;
        }
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Delivery time :');
        $otherDetails[$otherDetailsCounter]['label'] = $order->shipping_delivery_tile;
        $otherDetailsCounter++;
        
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.displayips','1')){
          $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('IP Address :');
          $otherDetails[$otherDetailsCounter]['label'] = $order->ip_address;
          $otherDetailsCounter++;
        }
        
        $otherDetails[$otherDetailsCounter]['name'] = $this->view->translate('Order Status');
        $otherDetails[$otherDetailsCounter]['label'] = ucwords($order->state);
        $otherDetailsCounter++;
        $result['otherinfo'] = $otherDetails;
        
        
        $products = array();
        $productCounter = 0;
        
        $orderedProduct =   Engine_Api::_()->getDbTable('orderproducts','sesproduct')->orderProducts(array('order_id' => $order->order_id));
        $totalTaxAmount = 0;
        $totalProductcost = 0;
        
        foreach($orderedProduct as $product) {
          $productItem = Engine_Api::_()->getItem('sesproduct', $product->product_id);
          $products['productsData'][$productCounter] = $product->toArray();
          $products['productsData'][$productCounter]['price'] = Engine_Api::_()->estore()->getCurrencyPrice($product->price/$product->quantity,$order->currency_symbol,$order->change_rate);
          $products['productsData'][$productCounter]['subtotal'] = Engine_Api::_()->estore()->getCurrencyPrice($product->price,$order->currency_symbol, $order->change_rate);
          $products['productsData'][$productCounter]['images']['main']= Engine_Api::_()->sesapi()->getPhotoUrls($productItem->photo_id, '', ""); //$this->getBaseUrl(true, $product->getPhotoUrl());
          $productCounter++;
        }
        $result['products'] = $products;
        $result['products']['title'] = $this->view->translate('Order Details');
        
        $footer = array();
        $footerCount = 0;
        if($order->total_shippingtax_cost > 0){ 
          $footer[$footerCount]['name'] = $this->view->translate('Shipping cost :');
          $footer[$footerCount]['label'] = $order->total_shippingtax_cost > 0 ? Engine_Api::_()->estore()->getCurrencyPrice($order->total_shippingtax_cost,$order->currency_symbol,$order->change_rate) : "-";
          $footerCount++;
        }
        if($totalTaxAmount > 0){
          $footer[$footerCount]['name'] = $this->view->translate('Total Tax :');
          $footer[$footerCount]['label'] = $totalTaxAmount > 0 ? Engine_Api::_()->estore()->getCurrencyPrice($totalTaxAmount,$order->currency_symbol,$order->change_rate) : "-";
          $footerCount++;
        }
        
        $footer[$footerCount]['name'] = $this->view->translate('Grand Total :');
        $groundTotal = $product->total;
        $footer[$footerCount]['label'] = $groundTotal <= 0  ? $this->view->translate("FREE") : Engine_Api::_()->estore()->getCurrencyPrice($groundTotal, $order->currency_symbol, $order->change_rate);
        
        
        $result['footer'] = $footer;
        
        Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $result),array()));

    }
	}
  
  public function deleteorderAction() {

    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $form = new Sesbasic_Form_Delete();
    $form->setTitle('Delete Order?');
    $form->setDescription('Are you sure that you want to delete this order? It will not be recoverable after being deleted. ');
    $form->submit->setLabel('Delete');

    $order = Engine_Api::_()->getItem('sesproduct_order', $this->_getParam('order_id'));
    if (!$order) {
      $error = Zend_Registry::get('Zend_Translate')->_("Order doesn't exists or not authorized to delete");
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    if (!$this->getRequest()->isPost()) {
      $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=> $error, 'result' => array()));
    }

    $db = $order->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      $order->delete();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    
    $message = Zend_Registry::get('Zend_Translate')->_('The selected order has been deleted.');
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>'', 'result' => $message));
  }
  
	public function storereviewvotesAction() {
	  $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if ($viewer_id == 0) {
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    $item_id = $this->_getParam('id');
    $type = $this->_getParam('type');
    if (intval($item_id) == 0 || ($type != 1 && $type != 2 && $type != 3)) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $itemTable = Engine_Api::_()->getItemTable('estore_review');
    $tableVotes = Engine_Api::_()->getDbtable('reviewvotes', 'estore');
    $tableMainVotes = $tableVotes->info('name');

    $select = $tableVotes->select()
            ->from($tableMainVotes)
            ->where('review_id = ?', $item_id)
            ->where('user_id = ?', $viewer_id)
            ->where('type =?', $type);
    $result = $tableVotes->fetchRow($select);
    if ($type == 1)
      $votesTitle = 'useful_count';
    else if ($type == 2)
      $votesTitle = 'funny_count';
    else
      $votesTitle = 'cool_count';

    if (count($result) > 0) {
      /*--------------------------------delete----------------------------*/		
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $result->delete();
        $itemTable->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' - 1')), array('review_id = ?' => $item_id));
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $selectReview = $itemTable->select()->where('review_id =?', $item_id);
      $review = $itemTable->fetchRow($selectReview);

      /*-----------------------------get review owner--------------------*/
      $storeId = $review->store_id;
      $estore = Engine_Api::_()->getItemTable('estore_review');
      $estore->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' - 1')), array('store_id = ?' => $storeId));
			$temp['data']['count'] = $review->{$votesTitle};
			$temp['data']['condition'] = 'reduced';
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    } else {
      /*---------------------------------update----------------------------*/
      $db = Engine_Api::_()->getDbTable('reviewvotes', 'estore')->getAdapter();
      $db->beginTransaction();
      try {
        $votereview = $tableVotes->createRow();
        $votereview->user_id = $viewer_id;
        $votereview->review_id = $item_id;
        $votereview->type = $type;
        $votereview->save();
        $itemTable->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' + 1')), array('review_id = ?' => $item_id));
        /*---------------------------------------Commit---------------------*/
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      /*-------------------Send notification and activity feed work.-------------*/
      $selectReview = $itemTable->select()->where('review_id =?', $item_id);
      $review = $itemTable->fetchRow($selectReview);

      /*--------------------get review owner-------------------------*/ 
      $storeId = $review->store_id;
      $estore = Engine_Api::_()->getItemTable('estore_review');
      $estore->update(array($votesTitle => new Zend_Db_Expr($votesTitle . ' + 1')), array('store_id = ?' => $storeId));

      $temp['data']['count'] = $review->{$votesTitle};
			$temp['data']['condition'] = 'increment';
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    }
  }
  
  public function storereviewsAction() {
    $result = array();
    $counter = 0;
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    
//     if(!$this->_helper->requireUser()->isValid())
//       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $reviewFormFilter = new Estore_Form_Admin_Review_Filter();
    $reviewFormFilter->removeElement('product_title');

    //Process form
    $values = array();
    if ($reviewFormFilter->isValid($this->_getAllParams())) {
      $values = $reviewFormFilter->getValues();
    }
    
    //Delete Review
    if(isset($_REQUEST['review_id']) && !empty($_REQUEST['review_id'])) {
      Engine_Api::_()->getItem('estore_review', $_REQUEST['review_id'])->delete();
      
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('Message' => $this->view->translate('Review Deleted Successfully.'))));
    }

    if (isset($_GET['searchParams']) && $_GET['searchParams'])
      parse_str($_GET['searchParams'], $searchArray);
      
		/*---------------------- Start Settings -------------------------*/
		$cancreate = Engine_Api::_()->sesapi()->getViewerPrivacy('estore_review', 'create');
		$reviewTable = Engine_Api::_()->getDbtable('reviews', 'estore');
		
		if($_REQUEST['store_id']) {
      $isReview = $hasReview = $reviewTable->isReview(array('store_id' => $_REQUEST['store_id'], 'column_name' => 'review_id'));
		}
		
		$editReviewPrivacy = Engine_Api::_()->sesapi()->getViewerPrivacy('estore_review', 'edit');
		if (Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.owner', 1)) {
			$allowedCreate = true;
		} else {
			if ($subject->owner_id == $viewer->getIdentity())
				$allowedCreate = false;
			else
				$allowedCreate = true;
		}
		/*---------------------- End Settings -------------------------*/
		
		/*---------------------- start Create / Update Buttons -------------*/
		if($viewer->getIdentity() && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.review', 1) && $allowedCreate){
			if($cancreate && !$isReview){
				$result['button']['label'] = $this->view->translate('Write a Review');
				$result['button']['name'] = 'create';
			}
			if($editReviewPrivacy && $isReview){
				$result['button']['label'] = $this->view->translate('Update Review');
				$result['button']['name'] = 'edit';
				$result['button']['value'] = $isReview;
			}
		}
		/*---------------------- End Create / Update Buttons ----------------*/

    $storeTable = Engine_Api::_()->getDbTable('stores', 'estore');
    $storeTableName = $storeTable->info('name');
    $table = Engine_Api::_()->getDbtable('reviews', 'estore');
    $tableName = $table->info('name');
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $select = $table->select()
            ->from($tableName)
            //->where($tableName.'.owner_id = ?',$viewer_id)
            ->setIntegrityCheck(false)
            ->joinLeft($tableUserName, "$tableUserName.user_id = $tableName.owner_id", 'username')
            ->joinLeft($storeTableName, "$storeTableName.store_id = $tableName.store_id", null)
            ->order($tableName.'.review_id DESC');
    if(isset($_REQUEST['store_id']) && !empty($_REQUEST['store_id'])) {
      $select->where($tableName.'.store_id =?', $_REQUEST['store_id']);
    }
    if (!empty($searchArray['title']))
        $select->where($tableName . '.title LIKE ?', '%' . $searchArray['title'] . '%');

    if (!empty($searchArray['rating_star']))
        $select->where($tableName . '.rating  = ?',  $searchArray['rating_star']);

    if (!empty($searchArray['store_title']))
        $select->where($storeTableName . '.title LIKE ?', '%' . $searchArray['store_title'] . '%');

    $page = $this->_getParam('page', 1);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($page);
    
//     $result = array();
//     $counter = 0;
//     
//     foreach ($paginator as $results) {
// 
//       $item = $results->toArray();
//       unset($item['body']);
//       $result['reviews'][$counter] = $item;
//       $result['reviews'][$counter]['owner_images']['main']= $this->getBaseUrl(true, $results->getOwner()->getPhotoUrl());
//       $result['reviews'][$counter]['owner_name'] = $results->getOwner()->getTitle();
//       if(!empty($viewer_id)) {
//           $menuoptions= array();
//           $menucounter = 0;
//           $menuoptions[$menucounter]['name'] = "edit";
//           $menuoptions[$menucounter]['label'] = $this->view->translate("Edit");
//           $menucounter++;
//           
//           $menuoptions[$menucounter]['name'] = "delete";
//           $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
//           $menucounter++;
//           $result['reviews'][$counter]['menus'] = $menuoptions;
//       }
//       $counter++;
//     }
    
    $result['reviews'] = $this->getReviews($paginator);

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  
	protected function getReviews($paginator){
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		foreach($paginator as $review){
		
			$result[$counter] = $review->toArray();
			$reviewer = Engine_Api::_()->getItem('user', $review->owner_id);
			$store = Engine_Api::_()->getItem('stores', $review->store_id);
			$owner = $reviewer->getOwner();
			$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'estore')->getParameters(array('content_id'=>$review->getIdentity(),'store_id'=>$review->store_id));
			$ownerSelf = $viewerId == $review->owner_id ? true : false;
			$parameterCounter = 0;
			$likeStatus = Engine_Api::_()->estore()->getLikeStatus($review->review_id,$review->getType());
			if(count($reviewParameters)>0){
				foreach($reviewParameters as $reviewP){ 
					$result[$counter]['review_perameter'][$parameterCounter] = $reviewP->toArray();
					$parameterCounter++;
				}
			}
			
			if($store) {
			$result[$counter]['store']['images'] = $this->getBaseUrl(true, $store->getPhotoUrl());
			$result[$counter]['store']['title'] = $store->getTitle();
			$result[$counter]['store']['Guid'] = $store->getGuid();
			$result[$counter]['store']['id'] = $store->getIdentity();
			}
			$result[$counter]['owner']['id'] = $owner->getIdentity();
			$result[$counter]['owner']['Guid'] = $owner->getGuid();
			$result[$counter]['owner']['title'] = $owner->getTitle();
			$result[$counter]['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
			$result[$counter]['show_pros'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.show.pros', 1)?true:false;
			$result[$counter]['show_pros'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.show.cons', 1)?true:false;
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.votes', 1)){
				$item = $review; 
				$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','estore')->isReviewVote(array('review_id'=>$item->getIdentity(),'user_id'=>$viewer->getIdentity(),'type'=>1));
				$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','estore')->isReviewVote(array('review_id'=>$item->getIdentity(),'user_id'=>$viewer->getIdentity(),'type'=>2));
				$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','estore')->isReviewVote(array('review_id'=>$item->getIdentity(),'user_id'=>$viewer->getIdentity(),'type'=>3));
				$result[$counter]['voting']['label'] = $this->view->translate("ESTORE Was this Review...?");
				$bttonCounter	= 0 ;			
				$result[$counter]['voting']['buttons'][$bttonCounter]['name'] = 'useful';
				$result[$counter]['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.first', 'Useful'));
				$result[$counter]['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypeone ? true : false;
				$result[$counter]['voting']['buttons'][$bttonCounter]['count'] = $item->useful_count;
				$bttonCounter++;
				$result[$counter]['voting']['buttons'][$bttonCounter]['name'] = 'funny';
				$result[$counter]['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.second', 'Funny'));
				$result[$counter]['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypetwo ? true : false;
				$result[$counter]['voting']['buttons'][$bttonCounter]['count'] = $item->funny_count;
				$bttonCounter++;
				$result[$counter]['voting']['buttons'][$bttonCounter]['name'] = 'cool';
				$result[$counter]['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.third', 'Cool'));
				$result[$counter]['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypethree ? true : false;
				$result[$counter]['voting']['buttons'][$bttonCounter]['count'] = $item->cool_count;
				
			}
			if($item->authorization()->isAllowed($viewer, 'comment')){
				$result[$counter]['is_content_like'] = $likeStatus?true:false;
			}
			$optionCounter = 0;
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.show.report', 1) && $viewerId && $viewerId != $owner){
				$result[$counter]['options'][$optionCounter]['name'] = 'report';
				$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Report');
				$optionCounter++;
			}
			
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 1) && $viewerId){
				$result[$counter]['options'][$optionCounter]['name'] = 'share';
				$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('Share');
				$optionCounter++;
				
				/*------------- share object -----------------*/
				$result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $review->getPhotoUrl());
				$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$review->getHref());
				$result[$counter]["share"]["title"] = $review->getTitle();
				$result[$counter]["share"]["description"] = strip_tags($review->getDescription());
				$result[$counter]["share"]["setting"] = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 1);
				$result[$counter]["share"]['urlParams'] = array(
					"type" => $review->getType(),
					"id" => $review->getIdentity()
				);
				/*------------- share object -----------------*/
			}
			
			if($item->authorization()->isAllowed($viewer, 'edit')) { 
				$result[$counter]['options'][$optionCounter]['name'] = 'edit';
				$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('ESTORE Edit Review');
				$optionCounter++;
			}
			if($item->authorization()->isAllowed($viewer, 'delete')) {
				$result[$counter]['options'][$optionCounter]['name'] = 'delete';
				$result[$counter]['options'][$optionCounter]['label'] = $this->view->translate('ESTORE Delete Review');
				$optionCounter++;
			}
			$counter++;
		}
		
		return $result;
	}
	
	public function storecreatereviewAction() { 
		/*----------------------- check permission ------------------*/
		if (!Engine_Api::_()->sesapi()->getViewerPrivacy('estore_review', 'create'))
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
    $subjectId = $this->_getParam('store_id', 0);
    
		if(!$subjectId )
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $item = Engine_Api::_()->getItem('stores', $subjectId);
		/*----------------------- check for store ------------------*/
    if (!$item)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    /*----------------------- check review exists ------------------*/
    $isReview = Engine_Api::_()->getDbtable('reviews', 'estore')->isReview(array('store_id' => $item->store_id, 'column_name' => 'review_id'));
    $allowedCreate = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.owner', 1) ? true : ($item->owner_id == $viewerId ? false : true);
		/*----------------------- check create permission ------------------*/
    if ($isReview || !$allowedCreate)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		
		$form = new Estore_Form_Review_Create(array('storeItem'=>$item));
		if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'estore_review'));
		}
  
		if (!$form->isValid($_POST)) {
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
					$this->validateFormFields($validateFields);
		}
		
		if (!$this->getRequest()->isPost()) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
		}
		
    $values = $_POST;
    $values['rating'] = $_POST['rate_value'];
    $values['owner_id'] = $viewerId;
    $values['store_id'] = $item->store_id;
    $reviews_table = Engine_Api::_()->getDbtable('reviews', 'estore');
    $db = $reviews_table->getAdapter();
    $db->beginTransaction();
    try {
      $review = $reviews_table->createRow();
      $review->setFromArray($values);
      $review->description = $_POST['description'];
      $review->save();
      $reviewObject = $review;
      $dbObject = Engine_Db_Table::getDefaultAdapter();
			/*----------------------- tak review ids from post ------------------*/
      $parameterValueTable = Engine_Api::_()->getDbtable('parametervalues', 'estore');
      $parameterTableName = $parameterValueTable->info('name');
      foreach ($_POST as $key => $reviewC) {
				if (count(explode('_', $key)) != 4 || !$reviewC)
					continue;

        $key = str_replace('review_parameter_value_', '', $key);
        if (!is_numeric($key))
          continue;
				
        $parameter = Engine_Api::_()->getItem('estore_parameter', $key);
        $query = 'INSERT INTO ' . $parameterTableName . ' (`parameter_id`, `rating`, `store_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $item->store_id . '","' . $review->getIdentity() . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';
        $dbObject->query($query);
        $ratingP = $parameterValueTable->getRating($key);
        $parameter->rating = $ratingP;
        $parameter->save();
      }
      $db->commit();
      /*------------------------- save rating in parent table if exists --------------*/
      if (isset($item->rating)) {
        $item->rating = Engine_Api::_()->getDbtable('reviews', 'estore')->getRating($review->store_id);
        $item->review_count = $item->review_count + 1;
        $item->save();
      }
      $review->save();
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      $viewMax = array_search('everyone', $roles);
      $commentMax = array_search('everyone', $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($review, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($review, $role, 'comment', ($i <= $commentMax));
      }
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, 'estore_reviewpost');
      if ($action != null) {
        Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $review);
      }
      if ($item->owner_id != $viewerId) {
        $itemOwner = $item->getOwner('user');
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($itemOwner, $viewer, $review, 'estore_reviewpost');
      }
      $db->commit();
			
      $rating_count = Engine_Api::_()->getDbTable('reviews', 'estore')->ratingCount($reviewObject->store_id);
      $rating_sum = $item->rating;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message'=>'Review Added Review Succuessfully.','review_id'=>$reviewObject->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));
    }
	}
  
  public function storeeditreviewAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    $review_id = $this->_getParam('review_id', null);
    $subject = Engine_Api::_()->getItem('estore_review', $review_id);

    if (!Engine_Api::_()->sesapi()->getViewerPrivacy('estore_review', 'edit'))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $item = Engine_Api::_()->getItem('stores', $subject->store_id);

    if (!$review_id || !$subject)
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $form = new Estore_Form_Review_Edit(array('reviewId' => $subject->review_id,  'storeItem' => $item));
    //$form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'estore', 'controller' => 'review', 'action' => 'edit-review', 'review_id' => $review_id), 'default', true));
    $title = Zend_Registry::get('Zend_Translate')->_('Edit a Review for "<b>%s</b>".');
    $form->setTitle(sprintf($title, $subject->getTitle()));
    $form->setDescription("Please fill below information.");
    $form->setAttrib('id', 'estore_edit_review');
    
    $form->populate($subject->toArray());
    if($form->rate_value){
      $form->rate_value->setValue($subject->rating);
    }
    
    if ($this->_getParam('getForm')) {
			$formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
			$this->generateFormFields($formFields, array('resources_type' => 'storereview'));
		}
		
		if (!$form->isValid($_POST)) {
			$validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
			if (count($validateFields))
					$this->validateFormFields($validateFields);
		}
		
    if (!$this->getRequest()->isPost()) {
      $form->populate($subject->toArray());
      $form->rate_value->setValue($subject->rating);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    
    if (!$form->isValid($this->getRequest()->getPost()))
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

    $values = $_POST;
    $values['rating'] = $_POST['rate_value'];
    $reviews_table = Engine_Api::_()->getDbtable('reviews', 'estore');
    $db = $reviews_table->getAdapter();
    $db->beginTransaction();
    try {
      $subject->setFromArray($values);
      $subject->save();
      $table = Engine_Api::_()->getDbtable('parametervalues', 'estore');
      $tablename = $table->info('name');
      $dbObject = Engine_Db_Table::getDefaultAdapter();
      foreach ($_POST as $key => $reviewC) {
        if (count(explode('_', $key)) != 3 || !$reviewC)
          continue;
        $key = str_replace('review_parameter_', '', $key);
        if (!is_numeric($key))
          continue;
        $parameter = Engine_Api::_()->getItem('estore_parameter', $key);
       $query = 'INSERT INTO ' . $tablename . ' (`parameter_id`, `rating`, `user_id`, `resources_id`,`content_id`) VALUES ("' . $key . '","' . $reviewC . '","' . $viewer->getIdentity() . '","' . $item->getIdentity() . '","' . $review_id . '") ON DUPLICATE KEY UPDATE rating = "' . $reviewC . '"';

        $dbObject->query($query);
        $ratingP = $table->getRating($key);
        $parameter->rating = $ratingP;
        $parameter->save();
      }
      if (isset($item->rating)) {
        $item->rating = Engine_Api::_()->getDbtable('reviews', 'estore')->getRating($item->store_id);
        $item->save();
      }
      $subject->save();
      $reviewObject = $subject;
      $db->commit();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message'=>'The selected review has been edited.','review_id'=>$reviewObject->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }
  
	public function storereviewviewAction() {
	
		$params = array();
		$result = array();
		$params['review_id'] = $review_id = $this->_getParam('review_id',null);
		
		if(!$review_id){
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
		
		$review = Engine_Api::_()->getItem('estore_review', $review_id);
    $store = Engine_Api::_()->getItem('stores', $review->store_id);
		
		/*----------------make data-----------------------------*/
		$counter = 0;
		$result = array();
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$result = $review->toArray();
		$reviewer = Engine_Api::_()->getItem('user', $review->owner_id);
		$owner = $reviewer->getOwner();
		$reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'estore')->getParameters(array('content_id'=>$review->getIdentity(),'store_id'=>$review->store_id));
		$likeStatus = Engine_Api::_()->estore()->getLikeStatus($review->review_id,$review->getType());
		$ownerSelf = $viewerId == $review->owner_id ? true : false;
		$parameterCounter = 0;
		if(count($reviewParameters)>0){
			foreach($reviewParameters as $reviewP){ 
				$result['review_perameter'][$parameterCounter] = $reviewP->toArray();
				$parameterCounter++;
			}
		}
		$result['store']['images'] = $this->getBaseUrl(true, $store->getPhotoUrl());
		$result['store']['title'] = $store->getTitle();
		$result['store']['Guid'] = $store->getGuid();
		$result['store']['id'] = $store->getIdentity();
		
		$result['owner']['id'] = $owner->getIdentity();
		$result['owner']['Guid'] = $owner->getGuid();
		$result['owner']['title'] = $owner->getTitle();
		$result['owner']['images'] = $this->getBaseUrl(true, $owner->getPhotoUrl());
		$result['show_pros'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.show.pros', 1)?true:false;
		$result['show_pros'] = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.show.cons', 1)?true:false;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.votes', 1)){
			$item = $review; 
			$isGivenVoteTypeone = Engine_Api::_()->getDbTable('reviewvotes','estore')->isReviewVote(array('review_id'=>$item->getIdentity(),'user_id'=>$viewer->getIdentity(),'type'=>1));
			$isGivenVoteTypetwo = Engine_Api::_()->getDbTable('reviewvotes','estore')->isReviewVote(array('review_id'=>$item->getIdentity(),'user_id'=>$viewer->getIdentity(),'type'=>2));
			$isGivenVoteTypethree = Engine_Api::_()->getDbTable('reviewvotes','estore')->isReviewVote(array('review_id'=>$item->getIdentity(),'user_id'=>$viewer->getIdentity(),'type'=>3));
			$result['voting']['label'] = $this->view->translate("SESSTORE Was this Review...?");
			$bttonCounter	= 0 ;			
			$result['voting']['buttons'][$bttonCounter]['name'] = 'useful';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.first', 'Useful'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypeone ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $item->useful_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'funny';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.second', 'Funny'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypetwo ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $item->funny_count;
			$bttonCounter++;
			$result['voting']['buttons'][$bttonCounter]['name'] = 'cool';
			$result['voting']['buttons'][$bttonCounter]['label'] = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.review.third', 'Cool'));
			$result['voting']['buttons'][$bttonCounter]['value'] = $isGivenVoteTypethree ? true : false;
			$result['voting']['buttons'][$bttonCounter]['action'] = $item->cool_count;
			
		}
		if($item->authorization()->isAllowed($viewer, 'comment')){
			$result['is_content_like'] = $likeStatus?true:false;
		}
		$optionCounter = 0;
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.show.report', 1) && $viewerId && $viewerId != $owner){
			$result['options'][$optionCounter]['name'] = 'report';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Report');
			$optionCounter++;
		}
		
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 1) && $viewerId){
			$result['options'][$optionCounter]['name'] = 'share';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Share');
			$optionCounter++;
			
        /*------------- share object -----------------*/
				$result["share"]["imageUrl"] = $this->getBaseUrl(false, $review->getPhotoUrl());
				$result["share"]["url"] = $this->getBaseUrl(false,$review->getHref());
				$result["share"]["title"] = $review->getTitle();
				$result["share"]["description"] = strip_tags($review->getDescription());
				$result["share"]["setting"] = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 1);
				$result["share"]['urlParams'] = array(
					"type" => $review->getType(),
					"id" => $review->getIdentity()
				);
				/*------------- share object -----------------*/
		}
		


		if($item->authorization()->isAllowed($viewer, 'edit')) { 
			$result['options'][$optionCounter]['name'] = 'edit';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Edit Review');
			$optionCounter++;
		}
		if($item->authorization()->isAllowed($viewer, 'delete')) {
			$result['options'][$optionCounter]['name'] = 'delete';
			$result['options'][$optionCounter]['label'] = $this->view->translate('Delete Review');
			$optionCounter++;
		}
		/*----------------make data-----------------------------*/
		$data['review'] = $result;
		
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data)));
	}
	
	public function storereviewlikeAction() {

    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    }
    
    $item_id = $this->_getParam('id');
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $itemTable = Engine_Api::_()->getItemTable('estore_review');
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', 'estore_review')
            ->where('poster_id = ?', $viewer_id)
            ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $item_id);
    $result = $tableLike->fetchRow($select);
    if (count($result) > 0) {
      /*----------------------------------delete----------------------------*/
      $db = $result->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $result->delete();
        //$itemTable->update(array('like_count' => new Zend_Db_Expr('like_count - 1')), array('review_id = ?' => $item_id));
        $db->commit();
				 $temp['data']['message'] = $this->view->translate('Store Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
      $selectStoreReview = $itemTable->select()->where('review_id =?', $item_id);
      $storeReview = $itemTable->fetchRow($selectStoreReview);

			$temp['data']['like_count'] = $storeReview->like_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    } else {
      /*---------------------------------update-----------------------*/
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $like = $tableLike->createRow();
        $like->poster_id = $viewer_id;
        $like->resource_type = 'estore_review';
        $like->resource_id = $item_id;
        $like->poster_type = 'user';
        $like->save();
        $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array('review_id = ?' => $item_id));
        /*------------------------Commit --------------------------------*/
        $db->commit();
				$temp['data']['message'] = $this->view->translate('Store Successfully Liked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
      }
      /*-------------------Send notification and activity feed work.----------*/
      $selectStoreReview = $itemTable->select()->where('review_id =?', $item_id);
      $item = $itemTable->fetchRow($selectStoreReview);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer_id) {
        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'liked', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, 'liked');
        $result = $activityTable->fetchRow(array('type =?' => 'liked', "subject_id =?" => $viewer_id, "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $subject, 'liked');
          if ($action)
            $activityTable->attachActivity($action, $subject);
        }
      }

			$temp['data']['like_count'] = $item->like_count;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
    }
  }
  
  public function productreviewsAction() {
   
    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

    $reviewFormFilter = new Estore_Form_Admin_Review_Filter();
    $reviewFormFilter->removeElement('store_title');
   
    //Process form
    $values = array();
    if ($reviewFormFilter->isValid($this->_getAllParams())) {
      $values = $reviewFormFilter->getValues();
    }

    //Delete Review
    if(isset($_REQUEST['review_id']) && !empty($_REQUEST['review_id'])) {
      Engine_Api::_()->getItem('sesproductreview', $_REQUEST['review_id'])->delete();
      
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('Message' => $this->view->translate('Review Deleted Successfully.'))));
    }

    if (isset($_GET['searchParams']) && $_GET['searchParams'])
      parse_str($_GET['searchParams'], $searchArray);

    $productTable = Engine_Api::_()->getDbTable('sesproducts', 'sesproduct');
    $productTableName = $productTable->info('name');
    $table = Engine_Api::_()->getDbtable('sesproductreviews', 'sesproduct');
    $tableName = $table->info('name');
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');
    $select = $table->select()
            ->from($tableName)
            ->where($tableName.'.owner_id = ?',$viewer_id)
            ->setIntegrityCheck(false)
            ->joinLeft($tableUserName, "$tableUserName.user_id = $tableName.owner_id", 'username')
            ->joinLeft($productTableName, "$productTableName.product_id = $tableName.product_id", '*')
            ->order($tableName.'.review_id DESC');

    if (!empty($searchArray['title']))
        $select->where($tableName . '.title LIKE ?', '%' . $searchArray['title'] . '%');

    if (!empty($searchArray['rating_star']))
        $select->where($tableName . '.rating  = ?',  $searchArray['rating_star']);

    if (!empty($searchArray['product_title']))
        $select->where($productTableName . '.title LIKE ?', '%' . $searchArray['product_title'] . '%');

    $page = $this->_getParam('page', 1);
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(100);
    $paginator->setCurrentPageNumber($page);
    
    $result = array();
    $counter = 0;
    
    foreach ($paginator as $results) {

      $item = $results->toArray();
      unset($item['body']);
      $result['reviews'][$counter] = $item;
      $result['reviews'][$counter]['owner_images']['main']= $this->getBaseUrl(true, $results->getOwner()->getPhotoUrl());
      $result['reviews'][$counter]['owner_name'] = $results->getOwner()->getTitle();
      if(!empty($viewer_id)) {
          $menuoptions= array();
          $menucounter = 0;
          $menuoptions[$menucounter]['name'] = "edit";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Edit");
          $menucounter++;
          
          $menuoptions[$menucounter]['name'] = "delete";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
          $menucounter++;
          $result['reviews'][$counter]['menus'] = $menuoptions;
      }
      $counter++;
    }

    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }

  public function myorderAction() {

    $viewer = $this->view->viewer();
    $viewer_id = $viewer->getIdentity();
    if(!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      
    $is_search_ajax = $this->view->is_search_ajax = $this->_getParam('is_search_ajax', null) ? $this->_getParam('is_search_ajax') : false;
    $is_ajax = $this->view->is_ajax = $this->_getParam('is_ajax', null) ? $this->_getParam('is_ajax') : false;

    $viewer = $this->view->viewer();
    $is_ajax_content = $this->view->is_ajax_content = $this->_getParam('is_ajax_content', null) ? $this->_getParam('is_ajax_content') : false;

    if(isset($_POST['searchParams']) && $_POST['searchParams']){
      parse_str($_POST['searchParams'], $searchArray);

    }

    $searchForm = new Estore_Form_Searchorder();
    $viewer = Engine_Api::_()->user()->getViewer();

    $value['order_id'] = isset($searchArray['order_id']) ? $searchArray['order_id'] : '';
    $value['buyer_name'] = isset($searchArray['buyer_name']) ? $searchArray['buyer_name'] : '';
    $value['date_from'] = isset($searchArray['date']['date_from']) ? $searchArray['date']['date_from'] : '';
    $value['date_to'] = isset($searchArray['date']['date_to']) ? $searchArray['date']['date_to'] : '';
    $value['order_min'] = isset($searchArray['order']['order_min']) ? $searchArray['order']['order_min'] : '';
    $value['order_max'] = isset($searchArray['order']['order_max']) ? $searchArray['order']['order_max'] : '';
    $value['commision_min'] = isset($searchArray['commision']['commision_min']) ? $searchArray['commision']['commision_min'] : '';
    $value['commision_max'] = isset($searchArray['commision']['commision_max']) ? $searchArray['commision']['commision_max'] : '';
    $value['gateway'] = isset($searchArray['gateway']) ? $searchArray['gateway'] : '';
    $value['user_id'] = $viewer_id;

    $orders = Engine_Api::_()->getDbtable('orders', 'sesproduct')->manageOrders($value);
    $paginator = Zend_Paginator::factory($orders);
    
    $result = array();
    $counter = 0;
    

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;

    foreach ($paginator as $order) {
      
      $store_item = Engine_Api::_()->getItem('stores', $order->store_id);
      $user = Engine_Api::_()->getItem('user', $order->user_id);
      
      //$sesproduct = Engine_Api::_()->getItem('sesproduct', $order->product_id);
      
      $orderr = $order->toArray();
      
      $result['orders'][$counter] = $orderr;
      
      $result['orders'][$counter]['status'] = $order->state;
      
      //$result[$counter]['product_title'] = $sesproduct->getTitle();
      //$result[$counter]['images']['main']= $this->getBaseUrl(true, $sesproduct->getPhotoUrl());
      //if(!empty($viewer_id) && $viewer_id == $order->user_id) {
          $menuoptions= array();
          $menucounter = 0;
          $menuoptions[$menucounter]['name'] = "view";
          $menuoptions[$menucounter]['label'] = $this->view->translate("View Order");
          $menucounter++;
          
          $menuoptions[$menucounter]['name'] = "delete";
          $menuoptions[$menucounter]['label'] = $this->view->translate("Delete");
          $menucounter++;
          $result['orders'][$counter]['menus'] = $menuoptions;
      //}
      
      $result['orders'][$counter]['store_title'] = $store_item->title;
      $result['orders'][$counter]['owner_name'] = $user->getTitle();
      $counter++;
    }
    
    //$result['products'] = $result;
        
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
  }
  
  
  
  
  
    public function browseAction(){

        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
            ->setIntegrityCheck(false)
            ->from($corePagesTable, null)
            ->where($coreContentTableName . '.name=?', 'estore.browse-search')
            ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
            ->where($corePagesTableName . '.name = ?', 'estore_index_browse');
        $id = $select->query()->fetchColumn();
        if (!empty($_POST['location'])) {
            $latlng = Engine_Api::_()->sesapi()->getCoordinates($_POST['location']);
            if ($latlng) {
                $_POST['lat'] = $latlng['lat'];
                $_POST['lng'] = $latlng['lng'];
            }
        }

        $form = new Estore_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
        $form->populate($_POST);
        $params = $form->getValues();
        $value = array();
        $value['status'] = 1;
        $value['search'] = 1;
        $value['draft'] = "1";
        if (isset($params['search']))
            $params['text'] = addslashes($params['search']);
        $params['tag'] = isset($_GET['tag_id']) ? $_GET['tag_id'] : '';
        $params = array_merge($params, $value);
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $page = $this->_getParam('page', 1);

        if ($page == 1 && $_POST['filter_sort'] == 'estore_main_browse') {

            $categories = Engine_Api::_()->getDbtable('categories', 'estore')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());
            $category_counter = 0;
            foreach ($categories as $category) {
                if ($category->thumbnail)
                    $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon)
                    $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon)
                    $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category[$category_counter]['slug'] = $category->slug;
                $result_category[$category_counter]['category_name'] = $category->category_name;
                $result_category[$category_counter]['total_store_categories'] = $category->total_store_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;
                $category_counter++;
            }
            if (!isset($params['category_id'])) {
                $result['category'] = $result_category;
                if (count($this->getPopularStores($paginator))) {
                    $result['popularStores'] = $this->getPopularStores($paginator);
                }
            }
        }

        $result['stores'] = $this->getStores($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getStores($paginator){
        $result = array();
        $counter = 0;
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_favourite', 0);
		    $likeFollowIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.integration', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 1);
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_show_userdetail', 0);
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
		    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'bs_can_join') : 0;
        foreach ($paginator as $stores) {
            $store = $stores->toArray();
            $result[$counter] = $store;
            $result[$counter]['likeFollowIntegrate'] = $likeFollowIntegrate?true:false;
            if(!$hideIdentity)
            $result[$counter]['owner_title'] = $stores->getOwner()->getTitle();
            $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
            $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
            $result[$counter]['currency'] = $curArr[$currency];
            if ($stores->category_id) {
                $category = Engine_Api::_()->getItem('estore_category', $stores->category_id);
                if ($category) {
                    $result[$counter]['category_title'] = $category->category_name;
                    if ($stores->subcat_id) {
                        $subcat = Engine_Api::_()->getItem('estore_category', $stores->subcat_id);
                        if ($subcat) {
                            $result[$counter]['subcategory_title'] = $subcat->category_name;
                            if ($stores->subsubcat_id) {
                                $subsubcat = Engine_Api::_()->getItem('estore_category', $stores->subsubcat_id);
                                if ($subsubcat) {
                                    $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                                }
                            }
                        }
                    }
                }
            }
            $tags = array();
            foreach ($stores->tags()->getTagMaps() as $tagmap) {
                $arrayTag = $tagmap->toArray();
                if(!$tagmap->getTag())
                    continue;
                $tags[] = array_merge($tagmap->toArray(), array(
                    'id' => $tagmap->getIdentity(),
                    'text' => $tagmap->getTitle(),
                    'href' => $tagmap->getHref(),
                    'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                ));
            }
            if (count($tags)) {
                $result[$counter]['tag'] = $tags;
            }

            $result[$counter]['images']['main']= $this->getBaseUrl(true, $stores->getPhotoUrl());
            $result[$counter]['cover_image']['main'] = $this->getBaseUrl(true, $stores->getCoverPhotoUrl());
            $result[$counter]['cover_images']['main'] = $result[$counter]['cover_image']['main'];
            $showLoginformFalse = false;

            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.contact.details', 1) && $viewerId == 0) {
                $showLoginformFalse = true;
            }
            $i = 0;
            if ($stores->store_contact_email || $stores->store_contact_phone || $stores->store_contact_website) {
                if ($stores->store_contact_email) {

                    $result[$counter]['menus'][$i]['name'] = 'mail';
                    $result[$counter]['menus'][$i]['label'] = 'Send Email';
                    $result[$counter]['menus'][$i]['value'] = $stores->store_contact_email;
                    $i++;

                }
                if ($stores->store_contact_phone) {
                    $result[$counter]['menus'][$i]['name'] = 'phone';
                    $result[$counter]['menus'][$i]['label'] = 'Call';
                    $result[$counter]['menus'][$i]['value'] = $stores->store_contact_phone;
                    $i++;
                }
                if ($stores->store_contact_website) {

                    $result[$counter]['menus'][$i]['name'] = 'website';
                    $result[$counter]['menus'][$i]['label'] = 'Visit Website';
                    $result[$counter]['menus'][$i]['value'] = $stores->store_contact_website;
                    $i++;
                }
                $result[$counter]['showLoginForm'] = $showLoginformFalse;


            }

            if ($stores->is_approved) {

                if ($shareType) {
                    $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $stores->getPhotoUrl());
										$result[$counter]["share"]["url"] = $this->getBaseUrl(false,$stores->getHref());
                    $result[$counter]["share"]["title"] = $stores->getTitle();
                    $result[$counter]["share"]["description"] = strip_tags($stores->getDescription());
                    $result[$counter]["share"]["setting"] = $shareType;
                    $result[$counter]["share"]['urlParams'] = array(
                        "type" => $stores->getType(),
                        "id" => $stores->getIdentity()
                    );
                }
            }
              if ($stores->is_approved) {
                if($viewerId != $stores->owner_id){
                    $result[$counter]['menus'][$i]['name'] = 'contact';
                  $result[$counter]['menus'][$i]['label'] = 'Contact';
                  $i++;
                }
                if ($shareType) {
                    $result[$counter]['menus'][$i]['name'] = 'share';
                    $result[$counter]['menus'][$i]['label'] = 'Share';
                    $i++;
                }


                $result[$counter]['showloginform_for_join_share'] = !$viewerId ? true : false;
                if ($canJoin) {
                  //  if ($viewerId) {
                        $row = $stores->membership()->getRow($viewer);
                        if (null === $row) {
                            if ($stores->membership()->isResourceApprovalRequired()) {
                                $result[$counter]['menus'][$i]['name'] = 'request';
                                $result[$counter]['menus'][$i]['label'] = 'Request Membership';
                                $i++;
                            } else {
                                $result[$counter]['menus'][$i]['name'] = 'join';
                                $result[$counter]['menus'][$i]['label'] = 'Join Store';
                                $i++;
                            }
                        } else if ($row->active) {
                            if (!$stores->isOwner($viewer)) {
                                $result[$counter]['menus'][$i]['label'] = 'Leave Store';
                                $result[$counter]['menus'][$i]['name'] = 'leave';
                                $i++;
                            }
                        } else if (!$row->resource_approved && $row->user_approved) {
                            $result[$counter]['menus'][$i]['label'] = 'Cancel Membership Request';
                            $result[$counter]['menus'][$i]['name'] = 'cancel';
                            $i++;

                        } else if (!$row->user_approved && $row->resource_approved) {
                            $result[$counter]['menus'][$i]['label'] = 'Accept Membership Request';
                            $result[$counter]['menus'][$i]['name'] = 'accept';
                            $i++;
                            $result[$counter]['menus'][$i]['label'] = 'Ignore Membership Request';
                            $result[$counter]['menus'][$i]['name'] = 'reject';
                        }

                  //  }
                }
            }
            if ($viewerId != 0) {
                $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($stores);
                $result[$counter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($stores);
                if ($canFavourite) {
                    $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($stores, 'favourites', 'estore', 'stores', 'owner_id');
                    $result[$counter]['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($stores, 'favourites', 'estore', 'stores', 'owner_id');
                }
                if ($canFollow) {
                    $result[$counter]['is_content_follow'] = $this->contentFollow($stores, 'followers', 'estore', 'stores', 'owner_id');
                    $result[$counter]['content_follow_count'] = (int)$this->getContentFollowCount($stores, 'followers', 'estore', 'stores', 'owner_id');
                }

            }

            if ($stores->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.location', 1)) {
                unset($stores['location']);
                $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('stores', $stores->getIdentity());
                if ($location) {
                    $result[$counter]['location'] = $location->toArray();
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.map.integration', 1)) {
                        $result[$counter]['location']['showMap'] = true;
                    } else {
                        $result[$counter]['location']['showMap'] = false;
                    }
                }
            }
            $counter++;
        }

        $results['stores'] = $result;
        return $result;
    }
    
    public function getPopularStores($storePaginator){
        $storeid = array();
        foreach ($storePaginator as $stores) {
            $storeid[] = $stores->store_id;
        }
        $params['info'] = 'most_viewed';
        $params['notStoreId'] = $storeid;
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')->getStorePaginator($params);
        $paginator->setItemCountPerPage(6);
        $paginator->setCurrentPageNumber(1);
        $result = $this->getStores($paginator);
        return $result;
    }

    public function browsesearchAction()
    {
        $defaultProfileId = 1;
        $search_for = $search_for = $this->_getParam('search_for', 'store');
        $coreContentTable = Engine_Api::_()->getDbTable('content', 'core');
        $coreContentTableName = $coreContentTable->info('name');
        $corePagesTable = Engine_Api::_()->getDbTable('pages', 'core');
        $corePagesTableName = $corePagesTable->info('name');
        $select = $corePagesTable->select()
            ->setIntegrityCheck(false)
            ->from($corePagesTable, null)
            ->where($coreContentTableName . '.name=?', 'estore.browse-search')
            ->joinLeft($coreContentTableName, $corePagesTableName . '.page_id = ' . $coreContentTableName . '.page_id', $coreContentTableName . '.content_id')
            ->where($corePagesTableName . '.name = ?', 'estore_index_browse');
        $id = $select->query()->fetchColumn();
        $form = new Estore_Form_Search(array('defaultProfileId' => 1, 'contentId' => $id));
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $form->setMethod('get')->populate($request->getParams());
        if($form->getElement('lat')){
          $form->removeElement('lat');
          $form->removeElement('lng');
        }
        $form->removeElement('cancel');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
    }
    
    public function featuredAction()
    {

        $params['sort'] = 'featured';
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        if ($paginator->getCurrentPageNumber() == 1) {
            $categories = Engine_Api::_()->getDbtable('categories', 'estore')->getCategory(array('column_name' => '*', 'limit' => 25));
            $category_counter = 0;
            $menu_counter = 0;

            foreach ($categories as $category) {
                if ($category->thumbnail)
                    $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon)
                    $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon)
                    $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category[$category_counter]['slug'] = $category->slug;
                $result_category[$category_counter]['category_name'] = $category->category_name;
                $result_category[$category_counter]['total_page_categories'] = $category->total_page_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;

                $category_counter++;
            }
            $result['category'] = $result_category;
        }
        $result['stores'] = $this->getStores($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function sponsoredAction()
    {
        $params['sort'] = 'sponsored';
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        if ($paginator->getCurrentPageNumber() == 1) {
            $categories = Engine_Api::_()->getDbtable('categories', 'estore')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());

            $category_counter = 0;
            $menu_counter = 0;

            foreach ($categories as $category) {
                if ($category->thumbnail)
                    $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon)
                    $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon)
                    $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category[$category_counter]['slug'] = $category->slug;
                $result_category[$category_counter]['category_name'] = $category->category_name;
                $result_category[$category_counter]['total_page_categories'] = $category->total_page_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;
                $category_counter++;
            }
            $result['category'] = $result_category;
        }

        $result['stores'] = $this->getStores($paginator);


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function verifiedAction()
    {

        $params['sort'] = 'verified';
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        if ($paginator->getCurrentPageNumber() == 1) {
            $categories = Engine_Api::_()->getDbtable('categories', 'estore')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());

            $category_counter = 0;
            $menu_counter = 0;

            foreach ($categories as $category) {
                if ($category->thumbnail)
                    $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon)
                    $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon)
                    $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category[$category_counter]['slug'] = $category->slug;
                $result_category[$category_counter]['category_name'] = $category->category_name;
                $result_category[$category_counter]['total_page_categories'] = $category->total_page_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;
                $category_counter++;
            }
            $result['category'] = $result_category;
        }

        $result['stores'] = $this->getStores($paginator);


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function hotAction()
    {
        $params['sort'] = 'hot';
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        if ($paginator->getCurrentPageNumber() == 1) {
            $categories = Engine_Api::_()->getDbtable('categories', 'estore')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());

            $category_counter = 0;
            $menu_counter = 0;

            foreach ($categories as $category) {
                if ($category->thumbnail)
                    $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon)
                    $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon)
                    $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category[$category_counter]['slug'] = $category->slug;
                $result_category[$category_counter]['category_name'] = $category->category_name;
                $result_category[$category_counter]['total_page_categories'] = $category->total_page_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;
                $category_counter++;
            }
            $result['category'] = $result_category;
        }

        $result['stores'] = $this->getStores($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function categoriesAction(){
        $paginator = Engine_Api::_()->getDbTable('categories', 'estore')->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        if ($paginator->getCurrentPageNumber() == 1) {
            $categories = Engine_Api::_()->getDbtable('categories', 'estore')->getCategory(array('column_name' => '*', 'limit' => 25));
            $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());
            $category_counter = 0;
            $menu_counter = 0;
            foreach ($categories as $category) {
                if ($category->thumbnail)
                    $result_category[$category_counter]['category_images'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->thumbnail, '', "");
                if ($category->cat_icon)
                    $result_category[$category_counter]['icon'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->cat_icon, '', "");
                if ($category->colored_icon)
                    $result_category[$category_counter]['icon_colored'] = Engine_Api::_()->sesapi()->getPhotoUrls($category->colored_icon, '', "");
                $result_category[$category_counter]['slug'] = $category->slug;
                $result_category[$category_counter]['category_name'] = $category->category_name;
                $result_category[$category_counter]['total_store_categories'] = $category->total_store_categories;
                $result_category[$category_counter]['category_id'] = $category->category_id;

                $category_counter++;
            }
            $result['category'] = $result_category;
        }
        $result['categories'] = $this->getCategory($paginator);
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getCategory($categoryPaginator) {
        $result = array();
        $counter = 0;
        foreach ($categoryPaginator as $categories) {
            $store = $categories->toArray();
            $params['category_id'] = $categories->category_id;
            $params['limit'] = 5;
            $paginator = Engine_Api::_()->getDbTable('stores', 'estore')->getStorePaginator($params);
            $paginator->setItemCountPerPage(3);
            $paginator->setCurrentPageNumber(1);
            if($paginator->getTotalItemCount() > 0){
              $result[$counter] = $store;
              $result[$counter]['items'] = $this->getStores($paginator);
              if ($paginator->getTotalItemCount() > 3) {
                $result[$counter]['see_all'] = true;
              } else {
                $result[$counter]['see_all'] = false;
              }
              $counter++;
            }
        }
        $results = $result;
        return $results;
    }
    
    public function manageAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!$viewer_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $params['user_id'] = $viewer_id;
        $defaultOpenTab = $this->_getParam('search_type', 'recentlySPcreated');
        switch ($defaultOpenTab) {
            case 'recentlySPcreated':
                $params['sort'] = 'creation_date';
                break;
            case 'mostSPviewed':
                $params['sort'] = 'view_count';
                break;
            case 'mostSPliked':
                $params['sort'] = 'like_count';
                break;
            case 'mostSPcommented':
                $params['sort'] = 'comment_count';
                break;
            case 'mostSPfavourite':
                $params['sort'] = 'favourite_count';
                break;
            case 'mostSPfollowed':
                $params['sort'] = 'follow_count';
                break;
            case 'featured':
                $params['sort'] = 'featured';
                break;
            case 'sponsored':
                $params['sort'] = 'sponsored';
                break;
            case 'verified':
                $params['sort'] = 'verified';
                break;
            case 'hot':
                $params['sort'] = 'hot';
                break;
            case 'close':
                $params['sort'] = 'close';
                break;
            case 'open':
                $params['sort'] = 'open';
                break;
        }
        $params['widgetManage'] = true;

        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_follow', 0);
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 0);
        $viewerId = $viewer->getIdentity();
        $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'estore_can_join') : 0;
        $filterOptionsMenu = array();
        $filterMenucounter = 0;
        $resultmenu[$filterMenucounter]['name'] = 'open';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Open');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'close';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Close');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'recentlySPcreated';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Recently Created');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPliked';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Liked');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPcommented';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Commented');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'mostSPviewed';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most viewed');
        $filterMenucounter++;
        if ($canFavourite) {
            $resultmenu[$filterMenucounter]['name'] = 'mostSPfavourite';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Favourited');
            $filterMenucounter++;
        }
        if ($canJoin) {
            $resultmenu[$filterMenucounter]['name'] = 'mostSPjoined';
            $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Most Joined');
            $filterMenucounter++;
        }
        $resultmenu[$filterMenucounter]['name'] = 'featured';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Featured');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'sponsored';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Sponsored');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'verified';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Verified');
        $filterMenucounter++;
        $resultmenu[$filterMenucounter]['name'] = 'hot';
        $resultmenu[$filterMenucounter]['label'] = $this->view->translate('Hot');
        if ($paginator) {
            $storeCounter = 0;
            foreach ($paginator as $stores) {
                $storeArray = $stores->toArray();
                if (!$canFavourite)
                    unset($storeArray['favourite_count']);
                if (!$canFollow)
                    unset($storeArray['follow_count']);
                unset($storeArray['location']);
                $result[$storeCounter] = $storeArray;
                $statsCounter = 0;
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($stores, '', "");
                if (image) {
                    $result[$storeCounter]['images'] = $image;
                } else {
                    $result[$storeCounter]['images'] = $image;
                }
                $isStoreEdit = Engine_Api::_()->getDbTable('storeroles', 'estore')->toCheckUserStoreRole($viewer->getIdentity(), $stores->getIdentity(), 'manage_dashboard', 'edit');
                $isStoreDelete = Engine_Api::_()->getDbTable('storeroles', 'estore')->toCheckUserStoreRole($viewer->getIdentity(), $stores->getIdentity(), 'manage_dashboard', 'delete');
                $buttonCounter = 0;
                if ($isStoreEdit) {
                    $result[$storeCounter]['menus'][$buttonCounter]['name'] = 'edit';
                    $result[$storeCounter]['menus'][$buttonCounter]['label'] = 'Edit';
                    $buttonCounter++;
                }
                if ($isStoreDelete) {
                    $result[$storeCounter]['menus'][$buttonCounter]['name'] = 'delete';
                    $result[$storeCounter]['menus'][$buttonCounter]['label'] = 'Delete';
                    $buttonCounter++;
                }
                $storeCounter++;
            }
        }
        $data['stores'] = $result;
        if ($this->_getParam('page', 1))
          $data['filterMenuOptions'] = $resultmenu;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $data), $extraParams));
    }

    public function checkVersion($android,$ios){
        if(is_numeric(_SESAPI_VERSION_ANDROID) && _SESAPI_VERSION_ANDROID >= $android)
            return  true;
        if(is_numeric(_SESAPI_VERSION_IOS) && _SESAPI_VERSION_IOS >= $ios)
            return true;
        return false;
    }

    public function menuAction()
    {
		
        $menus = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_main', array());
        $menu_counter = 0;
        
        foreach ($menus as $menu) {
            $class = end(explode(' ', $menu->class));
            if($class != 'estore_main_browse' && $class != 'estore_main_categories' && $class != 'sesproduct_main_browsecategory' && $class != 'sesproduct_main_browse'  && $class != 'estore_main_create' && $class != 'estore_main_account' && $class != 'sesproduct_main_browseplaylist')
              continue;
            if($class == 'estore_main_browselocations')
              continue;
            if($class == 'sesproduct_main_location')
              continue;
            if($class == 'estore_main_pinboard')
             continue;
            if($class == 'estore_main_storealbumhome')
              continue;
            $result_menu[$menu_counter]['label'] = $this->view->translate($menu->label);
            $result_menu[$menu_counter]['action'] = $class;
            $result_menu[$menu_counter]['isActive'] = $menu->active;
            $menu_counter++;
        }
        $result['menus'] = $result_menu;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }




    public function contentFollow($subject = null,$tableName = "",$modulename = "",$resource_type = "",$column_name = "user_id"){
    $viewer = Engine_Api::_()->user()->getViewer();
    //return if non logged in user or content empty
    if (empty($subject) || empty($viewer))
        return;
    if ($viewer->getIdentity())
    {
          $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
          $select->where('resource_id =?',$subject->getIdentity())->where($column_name.' =?',$viewer->getIdentity());
          if($resource_type)
            $select->where('resource_type =?',$resource_type);
          $follow = (int) Zend_Paginator::factory($select)->getTotalItemCount();
    }
    return !empty($follow) ? true : false;
  }
   public function getContentFollowCount($subject,$tableName = "",$modulename = "",$resources_type = "",$column_name = "resource_id"){
      $viewer = Engine_Api::_()->user()->getViewer();
      if(!$tableName || !$modulename)
        return 0;
      $select =  Engine_Api::_()->getDbTable($tableName, $modulename)->select();
      $select->where('resource_id =?',$subject->getIdentity());
      if($resources_type)
            $select->where('resource_type =?',$resources_type);
      return (int) Zend_Paginator::factory($select)->getTotalItemCount();
  }

    public function contactAction(){
        $ownerId[] = $this->_getParam('owner_id', 0);
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        // Get form
        if (!$this->_getParam('owner_id', 0)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $form = new Estore_Form_ContactOwner();
        $form->store_owner_id->setValue($this->_getParam('owner_id', 0));

        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }


        if (!$form->isValid($this->getRequest()->getPost())) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        // Process
        $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $values = $form->getValues();
            $recipientsUsers = Engine_Api::_()->getItemMulti('user', $ownerId);
            $attachment = null;

            if ($values['store_owner_id'] != $viewer->getIdentity()) {

                // Create conversation
                $conversation = Engine_Api::_()->getItemTable('messages_conversation')->send($viewer, $ownerId, $values['title'], $values['body'], $attachment);
            }

            // Send notifications
            foreach ($recipientsUsers as $user) {
                if ($user->getIdentity() == $viewer->getIdentity()) {
                    continue;
                }

                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $conversation, 'message_new');
            }

            // Increment messages counter
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('messages.creations');

            // Commit
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $this->view->translate('Message sent successfully.')));
        } catch (Exception $e) {

            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));

        }
    }

    public function joinAction()
    {
        $store_id = $this->getParam('store_id', 0);
        if (!$store_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $item = Engine_Api::_()->getItem('stores', $store_id);
        if ($item->membership()->isResourceApprovalRequired()) {
            $row = $item->membership()->getReceiver()
                ->select()
                ->where('resource_id = ?', $item->getIdentity())
                ->where('user_id = ?', $viewer->getIdentity())
                ->query()
                ->fetch(Zend_Db::FETCH_ASSOC, 0);;


            if (empty($row)) {

                // has not yet requested an invite
                $message = $this->request();
                if ($message == 'Successfully requested.') {
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($item))));
                } else {
                    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('database_error'), 'result' => array()));

                }
            } elseif ($row['user_approved'] && !$row['resource_approved']) {

                // has requested an invite; show cancel invite store
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Has requested an invite'),'menus'=>$this->getButtonMenus($item))));
                //              return $this->_helper->redirector->gotoRoute(array('action' => 'cancel', 'format' => 'smoothbox'));
            }


        }

        $form = new Estore_Form_Member_Join();
        //      $form->store_owner_id->setValue($this->_getParam('owner_id',0));
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }

        if (!$form->isValid($this->getRequest()->getPost())) {

            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }


        $db = $item->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();

        try {

            $membership_status = $item->membership()->getRow($viewer)->active;
            if (!$membership_status) {
                $item->membership()->addMember($viewer)->setUserApproved($viewer);
                $row = $item->membership()->getRow($viewer);
                $row->save();
            }


            $owner = $item->getOwner();
            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $item, 'estore_store_join');

            Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_estore_store_storejoined', array('store_title' => $item->getTitle(), 'sender_title' => $viewer->getOwner()->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));

            //Send to all joined members
            $joinedMembers = Engine_Api::_()->estore()->getallJoinedMembers($item);

            foreach ($joinedMembers as $joinedMember) {
                if ($joinedMember->user_id == $item->owner_id) continue;
                $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($joinedMember, $viewer, $item, 'estore_store_storesijoinedjoin');

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_estore_store_joinstorejoined', array('store_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            $followerMembers = Engine_Api::_()->getDbTable('followers', 'estore')->getFollowers($item->getIdentity());

            foreach ($followerMembers as $followerMember) {
                if ($followerMember->owner_id == $item->owner_id) continue;
                $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($followerMember, $viewer, $item, 'estore_store_storesifollowedjoin');

                Engine_Api::_()->getApi('mail', 'core')->sendSystem($item->getOwner(), 'notify_estore_store_joinedstorefollowed', array('store_title' => $item->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }

            // Add activity if membership status was not valid from before
            if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $item, 'estore_store_join');
                if ($action) {
                    $activityApi->attachActivity($action, $item);
                }
            }
            $db->commit();

            $viewerId = $viewer->getIdentity();
            $result['message'] = $this->view->translate('Store Successfully Joined.');
            $result['menus']  = $this->getButtonMenus($item);

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

        } catch (Exception $e) {
            $db->rollBack();
            $result['message'] = 'Database Error.';
            //              throw $e;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => $result));


        }
    }

    public function requestAction()
    {
    
        // Check resource approval
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();

        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (!$this->_helper->requireAuth()->setAuthParams($subject, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));


        // Make form
        $form = new Estore_Form_Member_Request();
        
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }

        if (!$form->isValid($this->getRequest()->getPost())) {

            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }

        // Process form
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $subject->membership()->addMember($viewer)->setUserApproved($viewer);

                // Add notification
                $notifyApi = Engine_Api::_()->getDbTable('notifications', 'activity');
                $notifyApi->addNotification($subject->getOwner(), $viewer, $subject, 'estore_approve');

                $db->commit();
                
                
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully requested.'),'menus'=>$this->getButtonMenus($subject))));
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
            }
        }
    }

    public function likeAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if ($viewer_id == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
        
        $item_id = $this->_getParam('id',$this->_getParam('store_id'));
        $type = $this->_getParam('type', false);
		if ($type == 'stores') {
		  $dbTable = 'stores';
		  $resorces_id = 'store_id';
		  $notificationType = 'estore_store_like';
		} elseif($type == 'estore_photo') {
		  $dbTable = 'photos';
		  $resorces_id = 'photo_id';
		  $notificationType = 'estore_photo_like';
		} elseif($type == 'estore_album') {
		  $dbTable = 'albums';
		  $resorces_id = 'album_id';
		  $notificationType = 'estore_album_like';
		}
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));

        }
        $itemTable = Engine_Api::_()->getDbtable($dbTable, 'estore');
        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableMainLike = $tableLike->info('name');
        $select = $tableLike->select()
            ->from($tableMainLike)
            ->where('resource_type = ?', $type)
            ->where('poster_id = ?', $viewer_id)
            ->where('poster_type = ?', 'user')
            ->where('resource_id = ?', $item_id);

        $result = $tableLike->fetchRow($select);
        if (count($result) > 0) {
            //delete
            $db = $result->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $result->delete();
                $db->commit();
                $temp['data']['message'] = $this->view->translate('Store Successfully Unliked.');
            } catch (Exception $e) {
                $db->rollBack();
				        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            $item = Engine_Api::_()->getItem($type, $item_id);
            $owner = $item->getOwner();
            if (!empty($notificationType)) {
                Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')
                    ->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item
                        ->getType(), "object_id = ?" => $item->getIdentity()));
            }
            $temp['data']['like_count'] = $item->like_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
        } else {
            //update
            $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
            $db->beginTransaction();
            try {
                $like = $tableLike->createRow();
                $like->poster_id = $viewer_id;
                $like->resource_type = $type;
                $like->resource_id = $item_id;
                $like->poster_type = 'user';
                $like->save();
                $itemTable->update(array('like_count' => new Zend_Db_Expr('like_count + 1')), array($resorces_id . '= ?' => $item_id));
                //Commit
                $db->commit();
                $temp['data']['message'] = $this->view->translate('Store Successfully Liked.');
            } catch (Exception $e) {
                $db->rollBack();
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
            }
            //Send notification and activity feed work.
            $item = Engine_Api::_()->getItem($type, $item_id);
            $subject = $item;
            $owner = $subject->getOwner();
            if ($notificationType && $owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
                //Send to all joined members
                if ($type == 'estore_store') {
                    $joinedMembers = Engine_Api::_()->estore()->getallJoinedMembers($item);
                    foreach ($joinedMembers as $joinedMember) {
                        $joinedMember = Engine_Api::_()->getItem('user', $joinedMember->user_id);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($joinedMember, $viewer, $subject, 'estore_store_storesijoinedlike');
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_estore_store_likestorejoined', array('store_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                    $followerMembers = Engine_Api::_()->getDbTable('followers', 'estore')->getFollowers($item->getIdentity());
                    foreach ($followerMembers as $followerMember) {
                        $followerMember = Engine_Api::_()->getItem('user', $followerMember->owner_id);
                        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($followerMember, $viewer, $subject, 'estore_store_storesifollowedlike');
                        Engine_Api::_()->getApi('mail', 'core')->sendSystem($followerMember, 'notify_estore_store_likestorefollowed', array('store_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                    }
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_estore_store_storeliked', array('store_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }


                //$result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));

                if ($notificationType == 'estore_store_like') {
                    $action = $activityTable->addActivity($viewer, $subject, $notificationType);
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                } else if ($notificationType == 'estore_album_like') {
                    $store = Engine_Api::_()->getItem('estore_store', $subject->store_id);
                    $albumlink = '<a href="' . $subject->getHref() . '">' . 'album' . '</a>';
                    $storelink = '<a href="' . $store->getHref() . '">' . $store->getTitle() . '</a>';
                    $action = $activityTable->addActivity($viewer, $subject, $notificationType, null, array('albumlink' => $albumlink, 'storename' => $storelink));
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                } else if ($notificationType == 'estore_photo_like') {
                    $store = Engine_Api::_()->getItem('estore_store', $subject->store_id);
                    $photolink = '<a href="' . $subject->getHref() . '">' . 'photo' . '</a>';
                    $storelink = '<a href="' . $store->getHref() . '">' . $store->getTitle() . '</a>';
                    $action = $activityTable->addActivity($viewer, $subject, $notificationType, null, array('photolink' => $photolink, 'storename' => $storelink));
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                }
            }
            if ($type == 'estore_store') {
                $storeFollowers = Engine_Api::_()->getDbTable('followers', 'estore')->getFollowers($subject->store_id);
                if (count($storeFollowers) > 0) {
                    foreach ($storeFollowers as $follower) {
                        $user = Engine_Api::_()->getItem('user', $follower->owner_id);
                        if ($user->getIdentity()) {
                            Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'estore_store_like_followed');
                        }
                    }
                }
            }

            $temp['data']['like_count'] = $item->like_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));

        }
    }

    public function followAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

        }
        $item_id = $this->_getParam('id',$this->_getParam('store_id',0));
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));

        }
        $Fav = Engine_Api::_()->getDbTable('followers', 'estore')->getItemFollower('stores', $item_id);
        $followerItem = Engine_Api::_()->getDbtable('stores', 'estore');

        if (count($Fav) > 0) {


            //delete
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                $Fav->delete();
                $db->commit();
                $temp['data']['message'] = 'Store Successfully Unfollowed.';

            } catch (Exception $e) {

                $db->rollBack();
                $temp['data']['message'] = $e->getMessage();
            }
            $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count - 1')), array('store_id = ?' => $item_id));
            $item = Engine_Api::_()->getItem('stores', $item_id);
            Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'estore_store_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
            Engine_Api::_()->sesapi()->deleteFeed(array('type' => 'estore_store_follow', "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));

            $temp['data']['follow_count'] = $item->follow_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));


        } else {

            //update
            $db = Engine_Api::_()->getDbTable('followers', 'estore')->getAdapter();
            $db->beginTransaction();
            try {
                $follow = Engine_Api::_()->getDbTable('followers', 'estore')->createRow();
                $follow->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $follow->resource_type = 'stores';
                $follow->resource_id = $item_id;
                $follow->save();
                $followerItem->update(array('follow_count' => new Zend_Db_Expr('follow_count + 1')), array('store_id = ?' => $item_id));
                // Commit
                $db->commit();
                $temp['data']['message'] = 'Store Successfully Followed.';
            } catch (Exception $e) {

                $db->rollBack();
                $temp['data']['message'] = 'Database Error.';

                //               throw $e;
            }
            //send notification and activity feed work.
            $item = Engine_Api::_()->getItem('stores', @$item_id);
            $subject = $item;
            $owner = $subject->getOwner();

            if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
                $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($item->getOwner(), $viewer, $item, 'estore_store_follow');
                $result = $activityTable->fetchRow(array('type =?' => 'estore_store_follow', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                if (!$result) {
                    $action = $activityTable->addActivity($viewer, $subject, 'estore_store_follow');
                    if ($action)
                        $activityTable->attachActivity($action, $subject);
                }
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_estore_store_storefollowed', array('store_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
            }
            $temp['data']['follow_count'] = $item->follow_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));
        }
    }

    public function favouriteAction()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($viewer->getIdentity() == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }
		if ($this->_getParam('type') == 'stores') {
		  $type = 'stores';
		  $dbTable = 'stores';
		  $resorces_id = 'store_id';
		  $notificationType = 'estore_store_favourite';
		} elseif ($this->_getParam('type') == 'estore_photo') {
		  $type = 'estore_photo';
		  $dbTable = 'photos';
		  $resorces_id = 'photo_id';
		  $notificationType = '';
		} elseif ($this->_getParam('type') == 'estore_album') {
		  $type = 'estore_album';
		  $dbTable = 'albums';
		  $resorces_id = 'album_id';
		  $notificationType = '';
		}
        $item_id = $this->_getParam('id',$this->_getParam('store_id'));
        if (intval($item_id) == 0) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));

        }
        $Fav = Engine_Api::_()->getDbTable('favourites', 'estore')->getItemfav($type, $item_id);
        $favItem = Engine_Api::_()->getDbtable($dbTable, 'estore');
        if (count($Fav) > 0) {
            //delete
            $db = $Fav->getTable()->getAdapter();
            $db->beginTransaction();
            try {
                $Fav->delete();
                $db->commit();
                $temp['data']['message'] = 'Store Successfully Unfavourited.';
            } catch (Exception $e) {
                $db->rollBack();
                $temp['data']['message'] = 'Database Error.';
                //                throw $e;
            }
            $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
            $item = Engine_Api::_()->getItem($type, $item_id);
            if ($notificationType) {
                Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
                Engine_Api::_()->sesapi()->deleteFeed(array('type' => $notificationType, "subject_id" => $viewer->getIdentity(), "object_type" => $item->getType(), "object_id" => $item->getIdentity()));
            }


            $temp['data']['favourite_count'] = $item->favourite_count;

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));


        } else {
            //update
            $db = Engine_Api::_()->getDbTable('favourites', 'estore')->getAdapter();
            $db->beginTransaction();
            try {
                $fav = Engine_Api::_()->getDbTable('favourites', 'estore')->createRow();
                $fav->owner_id = Engine_Api::_()->user()->getViewer()->getIdentity();
                $fav->resource_type = $type;
                $fav->resource_id = $item_id;
                $fav->save();
                $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1')), array($resorces_id . '= ?' => $item_id));
                // Commit
                $db->commit();
                $temp['data']['message'] = 'Store Successfully Favourited.';
            } catch (Exception $e) {
                $db->rollBack();
                $temp['data']['message'] = 'Database Error.';
                //                throw $e;
            }
            //Send Notification and Activity Feed Work.
            $item = Engine_Api::_()->getItem(@$type, @$item_id);
            if (@$notificationType) {
                $subject = $item;
                $owner = $subject->getOwner();
                if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity() && @$notificationType) {
                    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
                    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
                    $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
                    if (!$result) {
                        $action = $activityTable->addActivity($viewer, $subject, $notificationType);
                        if ($action)
                            $activityTable->attachActivity($action, $subject);
                    }
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($subject->getOwner(), 'notify_estore_store_storefollowed', array('store_title' => $subject->getTitle(), 'sender_title' => $viewer->getTitle(), 'object_link' => $subject->getHref(), 'host' => $_SERVER['HTTP_HOST']));
                }
            }
            //End Activity Feed Work
            //            $this->view->favourite_id = 1;
            $temp['data']['favourite_count'] = $item->favourite_count;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $temp));

        }
    }

    public function viewAction()
    {
        $store_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('store_id', null);
        // $store_id = Engine_Api::_()->getDbtable('stores', 'estore')->getStoreId($id);
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('stores', $store_id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }
		if(!$store)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found'), 'result' => array()));
		 $viewer = Engine_Api::_()->user()->getViewer();
		 $viewer = ( $viewer && $viewer->getIdentity() ? $viewer : null );
	if (!$this->_helper->requireAuth()->setAuthParams($store, $viewer, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        $sesprofilelock_enable_module = (array)Engine_Api::_()->getApi('settings', 'core')->getSetting('sesprofilelock.enable.modules');
        if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesprofilelock')) && in_array('estore', $sesprofilelock_enable_module) && $viewerId != $store->owner_id) {
            $cookieData = '';
            if ($store->enable_lock && !in_array($store->store_id, explode(',', $cookieData))) {
                $locked = true;
            } else {
                $locked = false;
            }
            $password = $store->store_password;
        } else {

            $password = true;
        }
        $result = $this->getstore($store);
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));
    }

    public function getstore($store)
    {
        $storedata = $store->toArray();
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        // Get category
        if (!empty($store->category_id)) {
            $category = Engine_Api::_()->getDbtable('categories', 'estore')->find($store->category_id)->current();
        }
        $storeTags = $store->tags()->getTagMaps();
		    $likeFollowIntegrate = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.integration', 0);
        $canComment = $store->authorization()->isAllowed($viewer, 'comment');
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 1);
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_follow', 0);
        $canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_can_join');
        $isStoreEdit = Engine_Api::_()->estore()->storePrivacy($store, 'edit');
        $canUploadCover = Engine_Api::_()->authorization()->isAllowed('stores', $viewer, 'upload_cover');
        $canUploadPhoto = Engine_Api::_()->authorization()->isAllowed('stores', $viewer, 'upload_mainphoto');

        $isStoreDelete = Engine_Api::_()->estore()->storePrivacy($store, 'delete');
        $likeStatus = Engine_Api::_()->estore()->getLikeStatus($store->store_id, $store->getType());
        $followStatus = Engine_Api::_()->getDbTable('followers', 'estore')->isFollow(array('resource_id' => $store->store_id, 'resource_type' => $store->getType()));
        $favouriteStatus = Engine_Api::_()->getDbTable('favourites', 'estore')->isFavourite(array('resource_id' => $store->store_id, 'resource_type' => $store->getType()));


        $owner = $store->getOwner();
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_show_userdetail', 0);
        if(!$hideIdentity)
        $storedata['owner_title'] = $store->getOwner()->getTitle();
        $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
        $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
        $storedata['currency'] = $curArr[$currency];
		    $storedata['likeFollowIntegrate'] = $likeFollowIntegrate?true:false;
        if ($likeStatus && $viewer_id) {
            $storedata['is_content_like'] = true;
        } else {
            $storedata['is_content_like'] = false;
        }
		if($canFollow){
			$storedata['is_content_follow'] = $followStatus >0?true:false;
		}
		if($canFavourite){
			$storedata['is_content_follow'] = $favouriteStatus >0?true:false;
		}
        if ($store->category_id) {
            $category = Engine_Api::_()->getItem('estore_category', $store->category_id);
            if ($category) {
                $storedata['category_title'] = $category->category_name;

                if ($store->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('estore_category', $store->subcat_id);
                    if ($subcat) {
                        $storedata['subcategory_title'] = $subcat->category_name;
                        if ($store->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('estore_category', $store->subsubcat_id);
                            if ($subsubcat) {
                                $storedata['subsubcategory_title'] = $subsubcat->category_name;
                            }
                        }
                    }
                }
            }
        }

        $item = Engine_Api::_()->getItem('stores', $store->store_id);
        $joinedMembers = Engine_Api::_()->estore()->getallJoinedMembers($item);
        $memberCount = count($joinedMembers);
        $storedata['memberCount'] = $memberCount;

        $tags = array();
        foreach ($store->tags()->getTagMaps() as $tagmap) {
            $arrayTag = $tagmap->toArray();
            if(!$tagmap->getTag())
                continue;
            $tags[] = array_merge($tagmap->toArray(), array(
                'id' => $tagmap->getIdentity(),
                'text' => $tagmap->getTitle(),
                'href' => $tagmap->getHref(),
                'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
            ));
        }
        if (count($tags)) {
            $storedata['tag'] = $tags;

        }
        $storedata['images']['main'] = $this->getBaseUrl(true, $store->getPhotoUrl());
        $storedata['cover_image']['main'] = $this->getBaseUrl(true, $store->getCoverPhotoUrl());
        $storedata['cover_images']['main'] = $storedata['cover_image']['main'];
        $showLoginformFalse = false;
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.contact.details', 1) && $viewerId == 0) {
            $showLoginformFalse = true;
        }
        $l = 0;
        if ($store->store_contact_email || $store->store_contact_phone || $store->store_contact_website) {
            if ($store->store_contact_email) {

                $storedata['menus'][$l]['name'] = 'mail';
                $storedata['menus'][$l]['label'] = 'Send Email';
                $storedata['menus'][$l]['value'] = $store->store_contact_email;
                $l++;

            }
            if ($store->store_contact_phone) {
                $storedata['menus'][$l]['name'] = 'phone';
                $storedata['menus'][$l]['label'] = 'Call';
                $storedata['menus'][$l]['value'] = $store->store_contact_phone;
                $l++;
            }
            if ($store->store_contact_website) {

                $storedata['menus'][$l]['name'] = 'website';
                $storedata['menus'][$l]['label'] = 'Visit Website';
                $storedata['menus'][$l]['value'] = $store->store_contact_website;
                $l++;
            }

            $storedata['showLoginForm'] = $showLoginformFalse;


        }

        $canCall = Engine_Api::_()->getDbTable('callactions', 'estore')->getCallactions(array('store_id' => $store->getIdentity()));
        if ($canCall) {
            $result['callToAction']['label'] = $this->getType($canCall->type);
            if ($canCall->type == 'callnow') {
                $result['callToAction']['name'] = 'call';
                $result['callToAction']['value'] = $canCall->params;
            } else if ($canCall->type == 'sendmessage') {
                $result['callToAction']['name'] = 'message';
                $result['callToAction']['value'] = $canCall->params;
                $result['callToAction']['owner_id'] = $canCall->owner_id;
                $result['callToAction']['owner_title'] = Engine_Api::_()->getItem('user',$canCall->owner_id)->getTitle();
            }elseif ($canCall->type == 'sendemail') {
                $result['callToAction']['name'] = 'mail';
                $result['callToAction']['value'] = $canCall->params;
            }else{
                $result['callToAction']['name'] = $canCall->type;
                $result['callToAction']['value'] = $canCall->params;
            }
        }
        $storedata['is_feed_allowed'] = true;
        if( !$store->authorization()->isAllowed($this->view->viewer(), 'view') )
           $storedata['is_feed_allowed'] = false;


        $i = 0;
        if ($store->store_contact_email || $store->store_contact_phone || $store->store_contact_website) {
            if ($store->store_contact_email) {

                $result['about'][$i]['name'] = 'mail';
                $result['about'][$i]['label'] = 'Send Email';
                $result['about'][$i]['value'] = $store->store_contact_email;
                $i++;

            }
            if ($store->store_contact_phone) {
                $result['about'][$i]['name'] = 'phone';
                $result['about'][$i]['label'] = 'View Phone number';
                $result['about'][$i]['value'] = $store->store_contact_phone;
                $i++;
            }
            if ($store->store_contact_website) {

                $result['about'][$i]['name'] = 'website';
                $result['about'][$i]['label'] = 'Visit Website';
                $result['about'][$i]['value'] = $store->store_contact_website;
                $i++;
            }
            if ($store->creation_date) {
                $result['about'][$i]['name'] = 'createDate';
                $result['about'][$i]['label'] = 'Create Date';
                $result['about'][$i]['value'] = $store->creation_date;
                $i++;
            }
            if ($store->category_id) {
                $category = Engine_Api::_()->getItem('estore_category', $store->category_id);
                if ($category) {
                    $result['about'][$i]['name'] = 'category';
                    $result['about'][$i]['label'] = 'Category Title';
                    $result['about'][$i]['value'] = $category->category_name;
                    //                     $storedata['about'][$i]['id'] = $store->category_id;
                }
                $i++;
            }
            if (count($tags)) {
                $result['about'][$i]['name'] = 'tag';
                $result['about'][$i]['value'] = 'Tag';
                $i++;
            }
            $result['about'][$i]['name'] = 'seeall';
            $result['about'][$i]['value'] = 'See All';
            $result['showLoginForm'] = $showLoginformFalse;

        }

        $relatedParams['category_id'] = $store->category_id;
        $storeid = array();
        $storeid[] = $store->store_id;
        $relatedParams['notStoreId'] = $storeid;

        if ($stores = $this->relatedstores($relatedParams)) {
            $result['relatedStores'] = $stores;
        }
        $result['photo'] = $this->photo($store->store_id);


        if ($store->is_approved) {
            if ($shareType) {

                $storedata["share"]["imageUrl"] = $this->getBaseUrl(false, $store->getPhotoUrl());
								$storedata["share"]["url"] = $this->getBaseUrl(false,$store->getHref());
                $storedata["share"]["title"] = $store->getTitle();
                $storedata["share"]["description"] = strip_tags($store->getDescription());
				$storedata["share"]["setting"] = $shareType;
                $storedata["share"]['urlParams'] = array(
                    "type" => $store->getType(),
                    "id" => $store->getIdentity()
                );
            }
        }
        $m = 0;
        if ($store->is_approved) {
            if($viewerId != $store->owner_id) {
                $storedata['menus'][$m]['name'] = 'contact';
                $storedata['menus'][$m]['label'] = 'Contact';
                $m++;
            }
            if ($shareType) {
                $storedata['menus'][$m]['name'] = 'share';
                $storedata['menus'][$m]['label'] = 'Share';
                $m++;
            }
            $result['showloginform_for_join_share'] = !$viewerId ? true : false;
            if ($canJoin) {
                $joincounter = 0;
               // if ($viewerId) {
                    //                    $m++;
                    $row = $store->membership()->getRow($viewer);
                    if (null === $row) {
                        if ($store->membership()->isResourceApprovalRequired()) {
                            $storedata['join'][$joincounter]['name'] = 'request';
                            $storedata['join'][$joincounter]['label'] = 'Request Membership';
                            $joincounter++;

                        } else {
                            $storedata['join'][$joincounter]['name'] = 'join';
                            $storedata['join'][$joincounter]['label'] = 'Join Store';
                            $joincounter++;
                        }
                    } else if ($row->active) {
                        if (!$store->isOwner($viewer)) {
                            $storedata['join'][$joincounter]['label'] = 'Leave Store';
                            $storedata['join'][$joincounter]['name'] = 'leave';
                            $joincounter++;
                        }
                    } else if (!$row->resource_approved && $row->user_approved) {
                        $storedata['join'][$joincounter]['label'] = 'Cancel Membership Request';
                        $storedata['join'][$joincounter]['name'] = 'cancel';
                        $joincounter++;

                    } else if (!$row->user_approved && $row->resource_approved) {
                        $storedata['join'][$joincounter]['label'] = 'Accept Membership Request';
                        $storedata['join'][$joincounter]['name'] = 'accept';
                        $joincounter++;
                        $storedata['join'][$joincounter]['label'] = 'Ignore Membership Request';
                        $storedata['join'][$joincounter]['name'] = 'reject';
                    }
               // }
            }
        }


        if ($viewer->getIdentity() != 0) {
            $storedata['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($store);
            $storedata['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($store);
            if ($canFavourite) {
                $storedata['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($store, 'favourites', 'estore', 'stores', 'owner_id');
                $storedata['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($store, 'favourites', 'estore', 'stores', 'owner_id');
            }
            if ($canFollow) {
                $storedata['is_content_follow'] = $this->contentFollow($store, 'followers', 'estore', 'stores', 'owner_id');
                $storedata['content_follow_count'] = (int)$this->getContentFollowCount($store, 'favourites', 'estore', 'stores', 'owner_id');
            }
        }

        if ($store->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.location', 1)) {
            unset($store['location']);
            $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('stores', $store->getIdentity());
            if ($location) {
                $storedata['location'] = $location->toArray();
                if (Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.map.integration', 1)) {
                    $storedata['location']['showMap'] = true;
                } else {
                    $storedata['location']['showMap'] = false;
                }
            }
        }


        if ($isStoreDelete) {
            $storedata['storeDelete'] = true;
        } else {
            $storedata['storeDelete'] = false;
        }


        if ($isStoreEdit) {
            // cover photo upload
            if ($canUploadCover) {
                $i = 0;
                if (isset($store->cover) && $store->cover != 0 && $store->cover != '') {
                    $storedata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Change Cover Photo');
                    $storedata['updateCoverPhoto'][$i]['name'] = 'upload';
                    $i++;
                    $storedata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Remove Cover Photo');
                    $storedata['updateCoverPhoto'][$i]['name'] = 'removePhoto';
                    $i++;
                    $storedata['updateCoverPhoto'][$i]['label'] = $this->view->translate('View Cover Photo');
                    $storedata['updateCoverPhoto'][$i]['name'] = 'view';
                    $i++;
                } else {
                    $storedata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Upload Cover Photo');
                    $storedata['updateCoverPhoto'][$i]['name'] = 'upload';
                    $i++;
                }
                //$storedata['updateCoverPhoto'][$i]['label'] = $this->view->translate('Choose From Albums');
                //$storedata['updateCoverPhoto'][$i]['name'] = 'album';
            }

            // photo upload

          if($canUploadPhoto){
            $j = 0;
            if (!empty($store->photo_id)) {
                $storedata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Change Photo');
                $storedata['updateProfilePhoto'][$j]['name'] = 'upload';
                $j++;
                $storedata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Remove Photo');
                $storedata['updateProfilePhoto'][$j]['name'] = 'removePhoto';

            } else {
                $storedata['updateProfilePhoto'][$j]['label'] = $this->view->translate('Upload Photo');
                $storedata['updateProfilePhoto'][$j]['name'] = 'upload';
                $j++;

            }
          }
        }

        //navigation
        $result['options'] = $this->getNavigation($store,$viewer);
        $tabcounter = 0;
	    $result['menus'][$tabcounter]['name'] = 'posts';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Posts');
        $tabcounter++;

        if(($store instanceof Core_Model_Item_Abstract) && $store->getIdentity() && method_exists($store, 'comments') && method_exists($store, 'likes')) {
            $result['menus'][$tabcounter]['name'] = 'comments';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Comments');
            $tabcounter++;
        }
        $result['menus'][$tabcounter]['name'] = 'info';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Info');
        $tabcounter++;
        $result['menus'][$tabcounter]['name'] = 'album';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Albums');
        $tabcounter++;
        $result['menus'][$tabcounter]['name'] = 'product';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Products');
        $tabcounter++;
        if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.location', 1)){
            $result['menus'][$tabcounter]['name'] = 'map';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Locations');
            $tabcounter++;
        }
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('estorevideo')) {
           //custom change video Tab disable
          $result['menus'][$tabcounter]['name'] = 'video';
          $result['menus'][$tabcounter]['label'] = $this->view->translate('Videos');
          $tabcounter++;
        }
        if(Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'auth_substore') ) {
            $result['menus'][$tabcounter]['name'] = 'associateStores';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Associated Stores');
            $tabcounter++;
        }
        $store_allow_announcement = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_allow_announcement');
        $store_service = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_service');
        $store_overview = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'bs_overview');
        //if ($store_allow_announcement) {
            $result['menus'][$tabcounter]['name'] = 'announcements';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Announcements');
            $tabcounter++;
        //}
        $result['menus'][$tabcounter]['name'] = 'members';
        $result['menus'][$tabcounter]['label'] = $this->view->translate('Members');
        $tabcounter++;
        if ($store_service && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.service', 0)) {
            $result['menus'][$tabcounter]['name'] = 'services';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Services');
            $tabcounter++;
        }
        if ($store_overview) {
            $result['menus'][$tabcounter]['name'] = 'overview';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Overview');
			$tabcounter++;
        }
		if($viewer->getIdentity() > 0 && !$store->isOwner($viewer) && Engine_Api::_()->authorization()->getPermission($viewer, 'stores', 'auth_claim') && (_SESAPI_VERSION_ANDROID >= 2.4 || _SESAPI_VERSION_IOS >= 1.5)){
			$result['menus'][$tabcounter]['name'] = 'claim';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Claim Stores');
			$tabcounter++;
		}
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('estorepoll') && (_SESAPI_VERSION_ANDROID >= 2.6 || _SESAPI_VERSION_IOS >= 1.7)){
            $result['menus'][$tabcounter]['name'] = 'poll';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Polls');
			$tabcounter++;
        }
				//if(Engine_Api::_()->getApi('core', 'estorereview')->allowReviewRating() && Engine_Api::_()->sesapi()->getViewerPrivacy('estorereview', 'view')){
            $result['menus'][$tabcounter]['name'] = 'review';
            $result['menus'][$tabcounter]['label'] = $this->view->translate('Reviews');
            $tabcounter++;
        //}
        
        $result['store'] = $storedata;
        $result = $result;
        return $result;
    }
	public function claimAction(){
		$viewer = Engine_Api::_()->user()->getViewer();
		if( !$viewer || !$viewer->getIdentity() ) {
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if( !$this->_helper->requireUser()->isValid() ){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		if(!Engine_Api::_()->authorization()->getPermission($viewer, 'stores', 'auth_claim')){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
		}
		$store_id = $this->_getParam('store_id',0);
		$store = null;
		if($store_id){
			$store = Engine_Api::_()->getItem('stores', $store_id);
		}
		$store_title = $store->getTitle();
		if($store_title)
			$_POST['title'] = $store_title;
		$form = new Sesstore_Form_Claim();
		if(isset($_POST))
		$form->populate($_POST);
	 // check for claim already exist or not
		$storeClaimTable = Engine_Api::_()->getDbtable('claims', 'estore');
		$storeClaimTableName = $storeClaimTable->info('name');
		$selectClaimTable = $storeClaimTable->select()
		  ->from($storeClaimTableName, 'store_id')
		  ->where('user_id =?', $viewer->getIdentity());
		  $selectClaimTable->where('store_id =?', $store_id);
		$claimedStores = $storeClaimTable->fetchAll($selectClaimTable);
		if(count($claimedStores->toArray()) >0){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
		}
		if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
        }
		$values = $form->getValues();
		// Process
		$table = Engine_Api::_()->getDbtable('claims', 'estore');
		$db = $table->getAdapter();
		$db->beginTransaction();
		try {
			// Create Claim
			$viewer = Engine_Api::_()->user()->getViewer();
			$estoreClaim = $table->createRow();
			$estoreClaim->user_id = $viewer->getIdentity();
			$estoreClaim->store_id = $values['store_id'];
			$estoreClaim->title = $values['title'];
			$estoreClaim->user_email = $values['user_email'];
			$estoreClaim->user_name = $values['user_name'];
			$estoreClaim->contact_number = $values['contact_number'];
			$estoreClaim->description = $values['description'];
			$estoreClaim->save();
			// Commit
			$db->commit();
		}
		catch( Exception $e ) {
			$db->rollBack();
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
		}

		$mail_settings = array('sender_title' => $values['user_name']);
		$body = '';
		$body .= $this->view->translate("Email: %s", $values['user_email']) . '<br />';
		if(isset($values['contact_number']) && !empty($values['contact_number']))
		$body .= $this->view->translate("Claim Owner Contact Number: %s", $values['contact_number']) . '<br />';
		$body .= $this->view->translate("Claim Reason: %s", $values['description']) . '<br /><br />';
		$mail_settings['message'] = $body;
		$storeItem = Engine_Api::_()->getItem('stores', $values['store_id']);
		$storeOwnerId = $storeItem->owner_id;
		$owner = $storeItem->getOwner();
		$storeOwnerEmail = Engine_Api::_()->getItem('user', $storeOwnerId)->email;
		$fromAddress = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'admin@' . $_SERVER['HTTP_HOST']);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($storeOwnerEmail, 'estore_store_owner_claim', $mail_settings);
		Engine_Api::_()->getApi('mail', 'core')->sendSystem($fromAddress, 'estore_site_owner_for_claim', $mail_settings);
		//Send notification to store owner
		Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $storeItem, 'sesuser_claim_store');
		//Send notification to all superadmins
		$getAllSuperadmins = Engine_Api::_()->user()->getSuperAdmins();
		foreach($getAllSuperadmins as $getAllSuperadmin) {
		  Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($getAllSuperadmin, $viewer, $storeItem, 'sesuser_claimadmin_store');
		}
		Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'', 'result' => array('message'=>$this->view->translate('Your request for claim has been sent to site owner. He will contact you soon.'))));
	}
function getNavigation($store,$viewer){

    $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('estore_profile');
    $navigationCounter = 0;
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
	$canJoin = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_can_join');
    foreach ($navigation as $link) {
  $class = end(explode(' ', $link->class));
        $label = $this->view->translate($link->getLabel());
   if ($class != "estore_profile_addtoshortcut") {
            $action = '';
            if ($class == 'estore_profile_dashboard') {
                $label = $label;
                $action = 'dashboard';
                $baseurl = $this->getBaseUrl();
                $custumurl = $store->custom_url;
                $pluralurl = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_stores_manifest', null);
                if ($pluralurl) {
                    $url = $baseurl . $pluralurl . '/dashboard/edit/' . $custumurl;
                } else {
                    $url = $baseurl . 'dashboard/edit/' . $custumurl;
                }
                $value = $url;
            } elseif ($class == 'estore_profile_member') {
      $row = $store->membership()->getRow($viewer);
      if (null === $row) {
			if ($store->membership()->isResourceApprovalRequired()) {
					$action = 'request';
				} else {
					$action = 'join';
				}
			} else if ($row->active) {
				if (!$store->isOwner($viewer)) {
					$action = 'leave';
				}
			}
            } elseif ($class == 'estore_profile_invite') {
                $action = 'invite';
            } elseif ($class == 'estore_profile_report' && $viewer->getIdentity() != $store->owner_id) {
       if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.report', 1) )
         continue;
                $action = 'report';
            } elseif ($class  == 'estore_profile_share') {
                $action = 'share';
            } elseif ($class  == 'estore_profile_member') {
                $action = 'join';
            } elseif ($class == 'estore_profile_delete') {
                $action = 'delete';
            } elseif ($class == 'estore_profile_substore') {
                $action = 'createAssociateStore';
            } elseif ($class == 'estore_profile_like' && $viewer->getIdentity() != $store->owner_id) {
                $action = 'likeasyourstore';
            } elseif ($class == 'estore_profile_unlike' && $viewer->getIdentity() != $store->owner_id) {
                $action = 'unlikeasyourstore';
            }
            if ($class == 'estore_profile_dashboard') {
                $result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $result[$navigationCounter]['value'] = $value;
                $navigationCounter++;
				if($this->_helper->requireAuth()->setAuthParams('stores', null, 'edit')->isValid()){
					 $result[$navigationCounter]['label'] = $this->view->translate('Edit Store');
                $result[$navigationCounter]['name'] = 'edit';
                $navigationCounter++;
				}
            }elseif($class == 'estore_profile_delete'){
				if(!$this->_helper->requireAuth()->setAuthParams('stores', null, 'delete')->isValid())
					continue;
				$result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $navigationCounter++;
			} else {
                $result[$navigationCounter]['label'] = $label;
                $result[$navigationCounter]['name'] = $action;
                $navigationCounter++;
            }

        }

  }
  
  /*if ($canJoin) {
		$joincounter = 0;
		if ($viewerId) {
		$row = $store->membership()->getRow($viewer);
		if (null === $row) {
			if ($store->membership()->isResourceApprovalRequired()) {
				$result[$navigationCounter]['name'] = 'request';
				$result[$navigationCounter]['label'] = 'Request Membership';
				$joincounter++;

			} else {
				$result[$navigationCounter]['name'] = 'join';
				$result[$navigationCounter]['label'] = 'Join Store';
				$joincounter++;
			}
		} else if ($row->active) {
			if (!$store->isOwner($viewer)) {
				$result[$navigationCounter]['label'] = 'Leave Store';
				$result[$navigationCounter]['name'] = 'leave';
				$joincounter++;
			}
		} else if (!$row->resource_approved && $row->user_approved) {
			$result[$navigationCounter]['label'] = 'Cancel Membership Request';
			$result[$navigationCounter]['name'] = 'cancel';
			$joincounter++;

		} else if (!$row->user_approved && $row->resource_approved) {
			$result[$navigationCounter]['label'] = 'Accept Membership Request';
			$result[$navigationCounter]['name'] = 'accept';
			$joincounter++;
			$result[$navigationCounter]['label'] = 'Ignore Membership Request';
			$result[$navigationCounter]['name'] = 'reject';
		}
	  }
	}
	*/

    return $result;
}
    function getType($type)
    {
        switch ($type) {
            case "booknow":
                return 'Book Now';
            case "callnow":
                return "Call Now";
            case "contactus":
                return "Contact Us";
            case "sendmessage":
                return "Send Message";
            case "signup":
                return "Sign Up";
            case "sendemail":
                return "Send Email";
            case "watchvideo":
                return "Watch Video";
            case "learnmore":
                return "Learn More";
            case "shopnow":
                return "Shop Now";
            case "seeoffers":
                return "See Offers";
            case "useapp":
                return "Use App";
            case "playgames":
                return "Play Games";
        }
        return "";
    }

    public function relatedstores($params)
    {
        $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 5));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = $this->getStores($paginator);
        return $result;
    }

    public function photo($storeid)
    {
        $params['store_id'] = $storeid;
        $paginator = Engine_Api::_()->getDbTable('photos', 'estore')
            ->getPhotoPaginator($params);
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber(1);
        $i = 0;
        foreach ($paginator as $photos) {
            $images = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");
            if (!count($images)) {
                $images['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';
                $images['normal'] = $this->getBaseUrl(true, $photos->getPhotoUrl()) . 'application/modules/Group/externals/images/nophoto_group_thumb_profile.png';

            }
            $result[$i]['images'] = $images;
            $result[$i]['photo_id'] = $photos->getIdentity();
            $result[$i]['album_id'] = $photos->album_id;
            $i++;

        }
        return $result;

    }

    public function infoAction()
    {
        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('store_id', null);
        if (!$id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        $store_id = Engine_Api::_()->getDbtable('stores', 'estore')->getStoreId($id);

        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('stores', $store_id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }

        $result['information'] = $this->getInformation($store);


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result)));


    }

	public function moreMembersAction(){
		$id = $this->_getParam('store_id',null);
		if(!$id){
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		}
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('stores', $id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }
		$storecheck = false;
		if($this->_getParam('type',null) == 'like'){
			$coreLikeTable = Engine_Api::_()->getDbTable('likes', 'core');
			$select = $coreLikeTable->select()->from($coreLikeTable->info('name'), 'poster_id')
            ->where('resource_id =?', $store->store_id )
            ->where('resource_type =?', 'stores');
		}else if($this->_getParam('type',null) == 'follow'){
			$followTable = Engine_Api::_()->getDbTable('followers', 'estore');
			$select = $followTable->select()->from($followTable->info('name'), 'owner_id')
            ->where('resource_id =?', $store->store_id )
            ->where('resource_type =?', 'stores');
		}else if($this->_getParam('type',null) == 'favourite'){
			$favouriteTable = Engine_Api::_()->getDbTable('favourites', 'estore');
			$select = $favouriteTable->select()->from($favouriteTable->info('name'), 'owner_id')
            ->where('resource_id =?', $store->store_id )
            ->where('resource_type =?', 'stores');
		}else if($this->_getParam('type',null) == 'store'){
			$tableLikestores = Engine_Api::_()->getDbTable('likestores', 'estore');
			$select = $tableLikestores->select()->where('store_id =?', $store->store_id);
			$storecheck = true;
		}

		if($select){

			$Members = Zend_Paginator::factory($select);
		}
		$Members->setItemCountPerPage($this->_getParam('limit', 10));
		$Members->setCurrentPageNumber($this->_getParam('page', 1));

	   if(count($Members) && $storecheck) {
			$likeStoresCounter = 0;
			foreach ($Members as $likestore) {
				$item = Engine_Api::_()->getItem('stores', $likestore->like_store_id);
				if ($item) {
					$nameLike = $item->getTitle();;
					$image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
					if ($image) {
						$result['store_liked_by_this_store'][$likeStoresCounter]['images'] = $image;
					}
					if ($nameLike) {
						$result['store_liked_by_this_store'][$likeStoresCounter]['name'] = $nameLike;
					}
					$result['store_liked_by_this_store'][$likeStoresCounter]['store_id'] = $item->store_id;
				}
				$likeStoresCounter++;
			}
		}
	 if (count($Members) && !$storecheck && $this->_getParam('type',null) != 'like') {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        foreach ($Members as $user)
          $userIds[] = $user['owner_id'];
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
        $result = $this->memberResult($recipientsUsers);
      }else{
			  $Counter = 0;
        foreach ($Members as $member) {
          $item = Engine_Api::_()->getItem('user', $member['owner_id']);
          $nameLike = $item->getTitle();
          $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
          if ($image) {
            $result['members'][$Counter]['images'] = $image;
          }
          if ($nameLike) {
            $result['members'][$Counter]['name'] = $nameLike;
          }
          $result['members'][$Counter]['user_id'] = $item->user_id;
          $Counter++;
        }
      }
		}
		if (count($Members) && !$storecheck && $this->_getParam('type',null) == 'like') {
      if(!empty($_GET['sesapi_platform']) && $_GET['sesapi_platform'] == 1){
        foreach ($Members as $user)
          $userIds[] = $user['poster_id'];
        $recipientsUsers = Engine_Api::_()->getItemMulti('user', $userIds);
        $result = $this->memberResult($recipientsUsers);

      }else{
			  $Counter = 0;
        foreach ($Members as $member) {
          $item = Engine_Api::_()->getItem('user', $member['poster_id']);
          $itemArray = $item->toArray();
          if(!empty($itemArray)){
            $nameLike = $item->getTitle();
          $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
          if ($image) {
            $result['members'][$Counter]['images'] = $image;
          }
          if ($nameLike) {
            $result['members'][$Counter]['name'] = $nameLike;
          }
          $result['members'][$Counter]['user_id'] = $item->user_id;
          $Counter++;
          }
        }
      }
		}
		// Set item count per store and current store number

        $extraParams['pagging']['total_page'] = $Members->getStores()->storeCount;
        $extraParams['pagging']['total'] = $Members->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $Members->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
	 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));
	}

  public function memberResult($paginator){
      $result = array();
      $counterLoop = 0;
      $viewer = Engine_Api::_()->user()->getViewer();
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesmember')){
        $memberEnable = true;
      }
      $followActive = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.active',1);
      if($followActive){
        $unfollowText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.unfollowtext','Unfollow'));
        $followText = $this->view->translate(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesmember.follow.followtext','Follow'));
      }
      foreach($paginator as $member){
        $result['notification'][$counterLoop]['user_id'] = $member->getIdentity();
        $result['notification'][$counterLoop]['title'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $member->getTitle());
        if(!empty($member->location))
           $result['notification'][$counterLoop]['location'] =   $member->location;
       //follow
        if($followActive && $viewer->getIdentity() && $viewer->getIdentity() != $member->getIdentity()){
            $FollowUser = Engine_Api::_()->sesmember()->getFollowStatus($member->user_id);
            if(!$FollowUser){
                $result['notification'][$counterLoop]['follow']['action'] = 'follow';
                $result['notification'][$counterLoop]['follow']['text'] = $followText;
            }else{
                $result['notification'][$counterLoop]['follow']['action'] = 'unfollow';
                $result['notification'][$counterLoop]['follow']['text'] = $unfollowText;
            }
        }
       if(!empty($memberEnable)){
        //mutual friends
        $mfriend = Engine_Api::_()->sesmember()->getMutualFriendCount($member, $viewer);
        if(!$member->isSelf($viewer)){
           $result['notification'][$counterLoop]['mutualFriends'] = $mfriend == 1 ? $mfriend.$this->view->translate(" mutual friend") : $mfriend.$this->view->translate(" mutual friends");
        }
       }
        $result['notification'][$counterLoop]['user_image'] = $this->userImage($member->getIdentity(),"thumb.profile");
        $result['notification'][$counterLoop]['membership'] = $this->friendRequest($member);
        $counterLoop++;
      }
      return $result;
  }
  public function friendRequest($subject){

    $viewer = Engine_Api::_()->user()->getViewer();

    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return "";
    }

    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return "";
    }

    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if( !$eligible ) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

      $data = $select->query()->fetch();

      if( empty($data) ) {
        return '';
      }
    }

    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    if( !$direction ) {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        return array(
          'label' => $this->view->translate('Follow'),
          'action' => 'add',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
        return array(
          'label' => $this->view->translate('Cancel Request'),
          'action'=>'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      } else {
        // Unfollow
        return array(
          'label' => $this->view->translate('Unfollow'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',
        );
      }
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',

        );
      } else {
        // Remove as follower?
        return array(
          'label' => $this->view->translate('Unfollow'),
           'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      }
      if( count($params) == 1 ) {
        return $params[0];
      } else if( count($params) == 0 ) {
        return "";
      } else {
        return $params;
      }
    }

    // Two-way mode
    else {

      $table =  Engine_Api::_()->getDbTable('membership','user');
      $select = $table->select()
        ->where('resource_id = ?', $viewer->getIdentity())
        ->where('user_id = ?', $subject->getIdentity());
      $select = $select->limit(1);
      $row = $table->fetchRow($select);

      if( null === $row ) {
        // Add
        return array(
          'label' => $this->view->translate('Add Friend'),
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',
          'action' => 'add',
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => $this->view->translate('Cancel Friend'),
          'action' => 'cancel',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => $this->view->translate('Approve Request'),
          'action' => 'confirm',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/add.png',

        );
      } else {
        // Remove friend
        return array(
          'label' => $this->view->translate('Remove Friend'),
          'action' => 'remove',
          'icon' => $this->getBaseUrl().'application/modules/User/externals/images/friends/remove.png',

        );
      }
    }
  }
  public function userAge($member){
    $getFieldsObjectsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($member);
    if (!empty($getFieldsObjectsByAlias['birthdate'])) {
      $optionId = $getFieldsObjectsByAlias['birthdate']->getValue($member);
      if ($optionId && @$optionId->value) {
        $age = floor((time() - strtotime($optionId->value)) / 31556926);
        return $this->view->translate(array('%s year old', '%s years old', $age), $this->view->locale()->toNumber($age));
      }
    }
    return "";
  }

    public function getInformation($stores)
    {
        $result = $stores->toArray();
        $openhourstable = Engine_Api::_()->getDbTable('openhours', 'estore');
        $select = $openhourstable->select()
            ->from($openhourstable->info('name'))
            ->where('store_id =?', $stores->getIdentity());
        $row = $openhourstable->fetchRow($select);
        $color = "";
        $data = "";
        $hours = "";

        if ($row) {
            $result['operating_hours']['label'] = $row->timezone;
            $params = json_decode($row->params, true);
            $hoursCounter = 0;
            if ($params['type'] == "selected") {
                unset($params['type']);
                for ($i = date('N'); $i < 8; $i++) {
                    if (!empty($params[$i])) {
                        $time = "";
                        foreach ($params[$i] as $key => $value) {
                            $time = $value['starttime'] . ' - ' . $value['endtime'] . '<br>';
                        }
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';
                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = $time;
                        $hoursCounter++;
                    } else {
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';
                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = 'Closed';
                        $hoursCounter++;
                    }
                }

                for ($i = 1; $i < date('N'); $i++) {
                    if (!empty($params[$i])) {
                        $time = "";
                        foreach ($params[$i] as $key => $value) {
                            $time = $value['starttime'] . ' - ' . $value['endtime'] . '<br>';
                        }
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';

                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = $time;
                        $hoursCounter++;
                    } else {
                        $hours = '<div class="_day sesbasic_clearfix"><div class="label sesbasic_text_light">' . $this->getDay($i) . '</div>';

                        $result['operating_hours']['value'][$hoursCounter]['label'] = $hours;
                        $result['operating_hours']['value'][$hoursCounter]['value'] = 'Closed';
                        $hoursCounter++;
                    }
                }

            } else if ($params['type'] == "always") {

                $color = "green";
                $data = "Always Open";
                $result['operating_hours']['value'][$hoursCounter]['label'] = 'Always';
                $result['operating_hours']['value'][$hoursCounter]['value'] = $data;
            } else if ($params['type'] == "notavailable") {
                $data = "Not Available";
                $result['operating_hours']['value'][$hoursCounter]['label'] = 'Not Available';
                $result['operating_hours']['value'][$hoursCounter]['value'] = $data;
            } else if ($params['type'] == "closed") {
                $color = "red";
                $data = "Permanently closed";
                $result['operating_hours']['value'][$hoursCounter]['label'] = 'Closed';
                $result['operating_hours']['value'][$hoursCounter]['value'] = $data;
            }
        }
        $basicInformationCounter = 0;
        $owner = $stores->getOwner();
        $hideIdentity = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_show_userdetail', 0);
        if(!$hideIdentity){
          $result['basicInformation'][$basicInformationCounter]['name'] = 'createdby';
          $result['basicInformation'][$basicInformationCounter]['value'] = $owner->displayname;
          $result['basicInformation'][$basicInformationCounter]['label'] = 'Created By';
          $basicInformationCounter++;
        }
        $result['basicInformation'][$basicInformationCounter]['name'] = 'creationdate';
        $result['basicInformation'][$basicInformationCounter]['value'] = $stores->creation_date;
        $result['basicInformation'][$basicInformationCounter]['label'] = 'Created on';
        $basicInformationCounter++;
        $statsCounter = 0;


        $state[$statsCounter]['name'] = 'comment';
        $state[$statsCounter]['value'] = $stores->comment_count;
        $state[$statsCounter]['label'] = 'Comments';
        $statsCounter++;


        $state[$statsCounter]['name'] = 'like';
        $state[$statsCounter]['value'] = $stores->like_count;
        $state[$statsCounter]['label'] = 'Likes';
        $statsCounter++;


        $state[$statsCounter]['name'] = 'view';
        $state[$statsCounter]['value'] = $stores->view_count;
        $state[$statsCounter]['label'] = 'Views';
        $statsCounter++;


        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_favourite', 0);
        $canFollow = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore_allow_follow', 0);

        if ($canFavourite) {
            $state[$statsCounter]['name'] = 'favourite';
            $state[$statsCounter]['value'] = $stores->favourite_count;
            $state[$statsCounter]['label'] = 'Favourites';
            $statsCounter++;
        }

        if ($canFollow) {
            $state[$statsCounter]['name'] = 'follow';
            $state[$statsCounter]['value'] = $stores->follow_count;
            $state[$statsCounter]['label'] = 'Follows';
        }


        $statsCounter++;

        $result['basicInformation'][$basicInformationCounter]['name'] = 'stats';
        $result['basicInformation'][$basicInformationCounter]['value'] = $state;
        $result['basicInformation'][$basicInformationCounter]['label'] = 'Stats';
        $basicInformationCounter++;

        if ($stores->category_id) {
            $category = Engine_Api::_()->getItem('estore_category', $stores->category_id);
            if ($category) {
                $result['basicInformation'][$basicInformationCounter]['name'] = 'category';
                $result['basicInformation'][$basicInformationCounter]['value'] = $category->category_name;
                $result['basicInformation'][$basicInformationCounter]['label'] = 'Category';

                $basicInformationCounter++;
                if ($stores->subcat_id) {
                    $subcat = Engine_Api::_()->getItem('estore_category', $stores->subcat_id);
                    if ($subcat) {
                        $result['basicInformation'][$basicInformationCounter]['name'] = 'subcategory';
                        $result['basicInformation'][$basicInformationCounter]['value'] = $subcat->category_name;
                        $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Category';
                        $basicInformationCounter++;
                        if ($stores->subsubcat_id) {
                            $subsubcat = Engine_Api::_()->getItem('estore_category', $stores->subsubcat_id);
                            if ($subsubcat) {
                                $result['basicInformation'][$basicInformationCounter]['name'] = 'subsubcategory';
                                $result['basicInformation'][$basicInformationCounter]['value'] = $subsubcat->category_name;
                                $result['basicInformation'][$basicInformationCounter]['label'] = 'Sub Sub Category';
                                $basicInformationCounter++;
                            }
                        }
                    }
                }
            }
        }


        $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
        $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($stores);
        if (count($fieldStructure)) { // @todo figure out right logic
            $content = $this->view->fieldSesapiValueLoop($stores, $fieldStructure);;
            $counter = 0;
            foreach ($content as $key => $value) {
                $result['profileDetail'][$counter]['label'] = $key;
                $result['profileDetail'][$counter]['value'] = $value;
                $counter++;
            }
        }

        $result['Detail'] = $stores->description;
        $contactInformationCounter = 0;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'phone';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'View Phone Number';
        if ($stores->store_contact_phone)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_phone;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'mail';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Send Email';
        if ($stores->store_contact_email)
            $result['contactInformation'][$contactInformationCounter]['value'] = $$stores->store_contact_email;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'website';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Visit Website';
        if ($stores->store_contact_website)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_website;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'facebook';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Facebook.com';
        if ($stores->store_contact_facebook)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_facebook;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'linkedin';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Linkedin';
        if ($stores->store_contact_linkedin)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_linkedin;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'twitter';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Twitter';
        if ($stores->store_contact_twitter)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_twitter;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'instagram';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Instagram.com';
        if ($stores->store_contact_instagram)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_instagram;
        $contactInformationCounter++;
        $result['contactInformation'][$contactInformationCounter]['name'] = 'pinterest';
        $result['contactInformation'][$contactInformationCounter]['label'] = 'Pinterest.com';
        if ($stores->store_contact_pinterest)
            $result['contactInformation'][$contactInformationCounter]['value'] = $stores->store_contact_pinterest;

        $likeMembers = Engine_Api::_()->estore()->getMemberByLike($stores->store_id);
        $favMembers = Engine_Api::_()->estore()->getMemberFavourite($stores->store_id);
        $followMembers = Engine_Api::_()->estore()->getMemberFollow($stores->store_id);
        $tableLikestores = Engine_Api::_()->getDbTable('likestores', 'estore');
        $selelct = $tableLikestores->select()->where('store_id =?', $stores->store_id);
        $likeStoreResult = $tableLikestores->fetchAll($selelct);

        if (count($likeStoreResult)) {
            $likeStoresCounter = 0;
            $result['total_page_liked_by_this_store'] = count($likeStoreResult) > 4 ? count($likeStoreResult) - 4 : 0;

            foreach ($likeStoreResult as $likestore) {
              if($likeStoresCounter > 3)
                break;
                $item = Engine_Api::_()->getItem('stores', $likestore->like_store_id);

                if ($item) {
                    $nameLike = $item->getTitle();;
                    $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                    if ($image) {
                        $result['store_liked_by_this_store'][$likeStoresCounter]['images'] = $image;
                    }
                    if ($nameLike) {
                        $result['store_liked_by_this_store'][$likeStoresCounter]['name'] = $nameLike;
                    }
                    $result['store_liked_by_this_store'][$likeStoresCounter]['store_id'] = $item->store_id;
                }else{
                  $result['total_page_liked_by_this_store'] = $result['total_page_liked_by_this_store'] > 0 ? $result['total_page_liked_by_this_store'] - 1 : 0;
                }
                $likeStoresCounter++;
            }
        }

				if (count($likeMembers)) {
            $likeCounter = 0;
            $result['total_people_who_liked'] = count($likeMembers) > 4 ? count($likeMembers) - 4 : 0;
            foreach ($likeMembers as $member) {
                if($likeCounter > 3)
                break;
                $item = Engine_Api::_()->getItem('user', $member['poster_id']);
                if(!$item){
                  $result['total_people_who_liked'] = $result['total_people_who_liked'] > 0 ? $result['total_people_who_liked'] - 1 : 0;
                  continue;
                }
                $nameLike = $item->getTitle();
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                if ($image) {
                    $result['people_who_liked'][$likeCounter]['images'] = $image;
                }
                if ($nameLike) {
                    $result['people_who_liked'][$likeCounter]['name'] = $nameLike;
                }

                $result['people_who_liked'][$likeCounter]['user_id'] = $item->user_id;
                $likeCounter++;
            }
        }
        if (count($followMembers) && $canFollow) {

            $followCounter = 0;
            $result['total_people_who_follow_this'] = count($followMembers) > 4 ? count($followMembers) - 4 : 0;
            foreach ($followMembers as $member) {
                if($followCounter > 3)
                    break;
                $item = Engine_Api::_()->getItem('user', $member['owner_id']);


                if(count($item->toArray()) == 0){
                    $result['total_people_who_follow_this'] = $result['total_people_who_follow_this'] > 0 ? $result['total_people_who_follow_this'] - 1 : 0;
                    continue;
                }


                $name = $item->getTitle();

                $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");


                if ($image) {
                    $result['people_who_follow_this'][$followCounter]['images'] = $image;
                }
                if ($name) {
                    $result['people_who_follow_this'][$followCounter]['name'] = $name;
                }
                $result['people_who_follow_this'][$followCounter]['user_id'] = $item->user_id;
                $followCounter++;
            }

        }


        if (count($favMembers) && $canFavourite) {
            $favCounter = 0;
            $result['total_people_who_favourited'] = count($favMembers) > 4 ? count($favMembers) - 4 : 0;
            foreach ($favMembers as $member) {
                if($favCounter > 3)
                break;
                $item = Engine_Api::_()->getItem('user', $member['owner_id']);
                if(!$item){
                  $result['total_people_who_favourited'] = $result['total_people_who_favourited']> 0 ? $result['total_people_who_favourited'] - 1 : 0;
                  continue;
                }
                $image = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "");
                $nameFav = $item->getTitle();
                if ($image) {
                    $result['people_who_favourited'][$favCounter]['images'] = $image;
                } else {

                }
                if ($nameFav) {
                    $result['people_who_favourited'][$favCounter]['name'] = $nameFav;
                }
                $result['people_who_favourited'][$favCounter]['user_id'] = $item->user_id;
                $favCounter++;
            }
        }

        return $result;

    }

    public function memberAction(){

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('stores');
        if (!$subject->authorization()->isAllowed($viewer, 'view')) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }

        // Get params
        $store = $this->_getParam('page', 1);
        $search = $this->_getParam('search');
        $waiting = $this->_getParam('waiting', false);

        // Prepare data
        $store = Engine_Api::_()->core()->getSubject();

        // get viewer
        $viewer = Engine_Api::_()->user()->getViewer();

        $result = array();
        if ($viewer->getIdentity() && ($store->isOwner($viewer))) {
            $waitingMembers = Zend_Paginator::factory($store->membership()->getMembersSelect(false));
            if ($waitingMembers->getTotalItemCount() > 0 && !$waiting) {
                $result['options']["label"] = $this->view->translate('See Waiting');
                $result['options']["name"] = 'waiting';
                $result['options']["value"] = '1';
            }
        }

        // if not showing waiting members, get full members
        $select = $store->membership()->getMembersObjectSelect();

        if ($search)
            $select->where('displayname LIKE ?', '%' . $search . '%');
        $fullMembers = Zend_Paginator::factory($select);

        if ($fullMembers->getTotalItemCount() > 0 && ($viewer->getIdentity() && ($store->isOwner($viewer))) && $waiting) {
            $result['options']["label"] = $this->view->translate('View all approved members');
            $result['options']["name"] = 'waiting';
            $result['options']["value"] = '0';
        }

        // if showing waiting members, or no full members
        if (($viewer->getIdentity() && ($store->isOwner($viewer))) && ($waiting || ($fullMembers->getTotalItemCount() <= 0 && $search == ''))) {
            $paginator = $waitingMembers;
            $waiting = true;
        } else {
            $paginator = $fullMembers;
            $waiting = false;
        }

        // Set item count per store and current store number
        $paginator->setItemCountPerPage($this->_getParam('itemCountPerStore', 10));
        $paginator->setCurrentPageNumber($this->_getParam('store', $store));

        $result['members'] = array();
        $counterLoop = 0;
        foreach ($paginator as $member) {
            if (!empty($member->resource_id)) {
                $memberInfo = $member;
                $member = Engine_Api::_()->getItem('user', $memberInfo->user_id);
            } else {
                $memberInfo = $store->membership()->getMemberInfo($member);
            }

            if (!$member->getIdentity())
                continue;
            $resource = $member->toArray();
            unset($resource['lastlogin_ip']);
            unset($resource['creation_ip']);
            $result['members'][$counterLoop] = $resource;
            //$result['members'][$counterLoop]['owner_photo'] = Engine_Api::_()->sesapi()->getPhotoUrls($member, '', "");
            
            $result['members'][$counterLoop]['owner_photo'] = Engine_Api::_()->sesapi()->getPhotoUrls($member, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($member, '', "") : $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
            
//         $member->userImage($member->getIdentity(),'thumb.profile');
            if ($store->isOwner($viewer) && !$store->isOwner($member)) {
                $optionCounter = 0;
                if (!$store->isOwner($member) && $memberInfo->active == true) {
                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'removemember';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Remove Member');
                    $optionCounter++;
                }
                if ($memberInfo->active == false && $memberInfo->resource_approved == false) {
                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'approverequest';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Approve Request');
                    $optionCounter++;
                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'rejectrequest';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Reject Request');
                    $optionCounter++;
                }
                if ($memberInfo->active == false && $memberInfo->resource_approved == true) {

                    $result['members'][$counterLoop]['options'][$optionCounter]['name'] = 'cancelinvite';
                    $result['members'][$counterLoop]['options'][$optionCounter]['label'] = $this->view->translate('Cancel Invite');
                    $optionCounter++;
                }
            }
            $counterLoop++;
        }


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function getChildCount()
    {
        return $this->_childCount;
    }

    public function announcementAction()
    {

        $id = Zend_Controller_Front::getInstance()->getRequest()->getParam('store_id', null);
        if (!$id) {

            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing.'), 'result' => array()));
        }
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('stores', $id);
        } else {
            $store = Engine_Api::_()->core()->getSubject();
        }
        $paginator = Engine_Api::_()->getDbTable('announcements', 'estore')->getStoreAnnouncementPaginator(array('store_id' => $store->store_id));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result = array();
        $announcementCounter = 0;
        foreach ($paginator as $announcement) {

            $result['announcements'][$announcementCounter]['announcement_id'] = $announcement->getIdentity();
            $result['announcements'][$announcementCounter]['title'] = $announcement->title;
            $result['announcements'][$announcementCounter]['creation_date'] = $announcement->creation_date;
            $result['announcements'][$announcementCounter]['detail'] = $announcement->body;
            $announcementCounter++;

        }


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));


    }

    public function servicesAction()
    {
        // Get subject and check auth
        $subject = Engine_Api::_()->core()->getSubject('stores');

        if (!$subject) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));
        }

        $paginator = Engine_Api::_()->getDbTable('services', 'estore')->getServicePaginator(array('store_id' => $subject->getIdentity(), 'widgettype' => 'widget'));
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        //Manage Apps Check
        $isCheck = Engine_Api::_()->getDbTable('managestoreapps', 'estore')->isCheck(array('store_id' => $subject->getIdentity(), 'columnname' => 'service'));


        if (empty($isCheck)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $servicesCounter = 0;
        $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
        $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
        foreach ($paginator as $item) {
            $result['services'][$servicesCounter]['images']['main'] = $this->getBaseUrl(true,$item->getPhotoUrl());
            $result['services'][$servicesCounter]['title'] = $item->title;
            if ($item->duration && $item->duration_type) {
                $result['services'][$servicesCounter]['duration'] = $item->duration;
                $result['services'][$servicesCounter]['durationtype'] = $item->duration_type;
                $result["services"][$servicesCounter]['service_type'] = $item->duration.' '.$item->duration_type;
            }
            if ($item->price) {
                $result['services'][$servicesCounter]['price'] = $item->price;
                $result['services'][$servicesCounter]['service_price'] = $curArr[$currency].$item->price;
            }
            if ($item->description) {
                $result['services'][$servicesCounter]['description'] = $item->description;
            }

            $servicesCounter++;
        }


        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));

    }

    public function mapAction()
    {

        if (!Engine_Api::_()->core()->hasSubject() || !Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.location', 1)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        $paginator = Engine_Api::_()->getDbTable('locations', 'estore')->getStoreLocationPaginator(array('store_id' => $store->store_id));
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));

        $locationCounter = 0;
        foreach ($paginator as $location) {
            $result['locations'][$locationCounter] = $location->toArray();
            $result['locations'][$locationCounter]['title'] = $location->title;
            $result['locations'][$locationCounter]['location'] = $location->location;
            $result['locations'][$locationCounter]['venue'] = $location->venue;
            $result['locations'][$locationCounter]['address'] = $location->address;
            $result['locations'][$locationCounter]['address2'] = $location->address2;
            $result['locations'][$locationCounter]['city'] = $location->city;
            $result['locations'][$locationCounter]['zip'] = $location->zip;
            $result['locations'][$locationCounter]['state'] = $location->state;
            $result['locations'][$locationCounter]['country'] = $location->country;

            $locationPhotos = Engine_Api::_()->getDbTable('locationphotos', 'estore')->getLocationPhotos(array('store_id' => $store->store_id, 'location_id' => $location->location_id));
            $photosCounter = 0;
            foreach ($locationPhotos as $photo) {
                $result['locations'][$locationCounter]['photos'][$photosCounter]['photoId'] = $photo->locationphoto_id;
                $result['locations'][$locationCounter]['photos'][$photosCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo, '', "");
                $photosCounter++;
            }
            $locationCounter++;
        }

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result),$extraParams));
  }
  public function albumAction()
  {
      if (!Engine_Api::_()->core()->hasSubject()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
      }
        $store = Engine_Api::_()->core()->getSubject();
		$order = $this->_getParam('sort','album_id');
		$viewer = Engine_Api::_()->user()->getViewer();
		$viewerId = $viewer->getIdentity();
		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
		$search = $this->_getParam('search',null);
		switch($order){
        case 'most_commented':
          $orderval = 'comment_count';
          break;
        case 'most_viewed':
          $orderval = 'view_count';
          break;
        case "most_liked":
         $orderval = 'like_count';
          break;
        case "creation_date":
          $orderval = 'creation_date';
          break;
		}

		if(!$orderval)
			$orderval = 'album_id';

        $paginator = Engine_Api::_()->getDbTable('albums', 'estore')->getAlbumSelect(array('store_id' => $store->store_id, 'order' => $orderval,'search'=>$search));
		$albumCount = Engine_Api::_()->getDbTable('albums', 'estore')->getUserStoreAlbumCount(array('store_id' => $store->store_id, 'user_id' => $viewer->getIdentity()));
        $paginator->setItemCountPerPage(5);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $getStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'post_content', 'album', false);
        $canUpload = $getStoreRolePermission ? $getStoreRolePermission : $this->_helper->requireAuth()->setAuthParams('stores', null, 'album')->isValid();
        $optioncounter = 0;
		$quota = Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_album_count');
		if($albumCount >= $quota ){
			$allowMore = false;
		}else{
			$allowMore = true;
		}
        if ($canUpload && $allowMore) {
            $result['can_create'] = true;
        } else {
            $result['can_create'] = false;
        }
        $result['menus'][$optioncounter]['name'] = 'creation_date';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Recently Created');
        $optioncounter++;
        $result['menus'][$optioncounter]['name'] = 'most_liked';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Liked');
        $optioncounter++;
        $result['menus'][$optioncounter]['name'] = 'most_viewed';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Viewed');
        $optioncounter++;
        $result['menus'][$optioncounter]['name'] = 'most_commented';
        $result['menus'][$optioncounter]['label'] = $this->view->translate('Most Commented');
        $optioncounter++;

        $albumCounter = 0;
        foreach ($paginator as $item) {
            $owner = $item->getOwner();
            $ownertitle = $owner->displayname;
            $result['albums'][$albumCounter] = $item->toArray();
            $photo = Engine_Api::_()->getItem('estore_photo',$item->photo_id);
            if($photo)
                $result['albums'][$albumCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "") : $item->getPhotoUrl();
            else
                $result['albums'][$albumCounter]['images'] =  $this->getBaseUrl(true, $item->getPhotoUrl());
            $result['albums'][$albumCounter]['user_title'] = $ownertitle;
            $result['albums'][$albumCounter]['photo_count'] = $item->count();
            $albumCounter++;
        }

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));

    }

    public function associatedAction()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        $params['parent_id'] = $store->store_id;


        $paginator = $paginator = Engine_Api::_()->getDbTable('stores', 'estore')
            ->getStorePaginator($params);
        $paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $result['stores'] = $this->getStores($paginator);

        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;


        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));


    }

    public function albumviewAction()
    {

        $albumid = $this->_getParam('album_id', 0);
        $storeId = $this->_getParam('store_id', 0);
        if (!$albumid) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->core()->getSubject();
            $album = Engine_Api::_()->getItem('estore_album', $albumid);
        } else {
            $album = Engine_Api::_()->getItem('estore_album', $albumid);
            $store = Engine_Api::_()->getItem('stores', $album->store_id);
        }

        $photoTable = Engine_Api::_()->getItemTable('estore_photo');
        $mine = true;
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$viewer) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        }
        $viewer_id = $viewer->getIdentity();

        $result['album'] = $album->toArray();
        $result['album']['user_title'] = $viewer->getOwner()->getTitle();
        $category = Engine_Api::_()->getItem('estore_category', $store->category_id);
        if ($category)
            $result['album']['category_title'] = $category->category_name;
		 $attachmentItem = $album;
            if ($attachmentItem->getPhotoUrl())
                $result['album']["share"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());
						$result['album']["share"]["url"] = $this->getBaseUrl(false,$attachmentItem->getHref());
            $result['album']["share"]["title"] = $attachmentItem->getTitle();
            $result['album']["share"]["description"] = strip_tags($attachmentItem->getDescription());
            $result['album']["share"]['urlParams'] = array(
                "type" => $album->getType(),
                "id" => $album->getIdentity()
            );

        if ($viewer->getIdentity() > 0) {
            $canEdit = $editStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'edit');
            $editStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'edit');
            $canEditMemberLevelPermission = $editStoreRolePermission ? $editStoreRolePermission : $store->authorization()->isAllowed($viewer, 'edit');
            $deleteStoreRolePermission = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'delete');
            $canDeleteMemberLevelPermission = $deleteStoreRolePermission ? $deleteStoreRolePermission : $store->authorization()->isAllowed($viewer, 'delete');
        }

        $menusCounter = 0;
        if ($canEditMemberLevelPermission == 1) {
            if ($viewer->getIdentity() == $album->owner_id || $canEditMemberLevelPermission) {
                $result['album']['is_edit'] = true;
                $result['menus'][$menusCounter]['name'] = 'edit';
                $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
                $menusCounter++;
            } else {
                $result['album']['is_edit'] = false;
            }
        } else if ($canEditMemberLevelPermission == 2) {
            $result['album']['is_edit'] = true;
            $result['menus'][$menusCounter]['name'] = 'edit';
            $result['menus'][$menusCounter]['label'] = $this->view->translate('Edit');
            $menusCounter++;
        } else {
            $result['album']['is_edit'] = false;
        }
        $result['album']['is_delete'] = true;
        if ($canDeleteMemberLevelPermission == 1) {
            if ($viewer->getIdentity() == $album->owner_id || $canDeleteMemberLevelPermission) {
                $result['album']['is_delete'] = true;
                $result['menus'][$menusCounter]['name'] = 'delete';
                $result['menus'][$menusCounter]['label'] = $this->view->translate('Delete');
                $menusCounter++;
            } else {
                $result['album']['is_delete'] = false;
            }
        } else if ($canDeleteMemberLevelPermission == 2) {
            $result['album']['is_delete'] = true;
            $result['menus'][$menusCounter]['name'] = 'delete';
            $result[$menusCounter]['label'] = $this->view->translate('Delete');
            $menusCounter++;
        } else {
            $result['album']['is_delete'] = false;
        }

        if ($viewer_id != $album->owner_id) {
            $result['menus'][$menusCounter]['name'] = 'report';
            $result['menus'][$menusCounter]['label'] = $this->view->translate("Report");
            $result['menus'][$menusCounter]["params"]['id'] = $album->getIdentity();
            $result['menus'][$menusCounter]["params"]['type'] = $album->getType();
            $menusCounter++;
        }
        $result['menus'][$menusCounter]['name'] = 'uploadphoto';
        $result['menus'][$menusCounter]['label'] = $this->view->translate("Upload more photos");
        $menusCounter++;

        $userimage = Engine_Api::_()->sesapi()->getPhotoUrls($album->getOwner(), '', "");

        $canComment = $store->authorization()->isAllowed($viewer, 'comment');


        if ($canComment)
            $result['album']['is_comment'] = true;
        else
            $result['album']['is_comment'] = false;

        $result['album']['user_image'] = $userimage;

        $paginator = $photoTable->getPhotoPaginator(array('album' => $album));
        $paginator->setItemCountPerPage('limit', 10);
        $paginator->setCurrentPageNumber('store_number', 1);

        $photoCounter = 0;
        foreach ($paginator as $photo) {
            $result['photos'][$photoCounter] = $photo->toArray();
            $result['photos'][$photoCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photo->file_id, '', "");
            $albumLikeStatus = Engine_Api::_()->estore()->getLikeStatusStore($photo->photo_id, 'estore_photo');
            $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'estore')->isFavourite(array('resource_type' => 'estore_photo', 'resource_id' => $photo->photo_id));
            if ($albumLikeStatus)
                $result['photos'][$photoCounter]['like_status'] = true;
            else
                $result['photos'][$photoCounter]['like_status'] = false;
            if ($albumFavStatus)
                $result['photos'][$photoCounter]['fav-satus'] = true;
            else
                $result['photos'][$photoCounter]['fav-satus'] = false;

            if ($albumLikeStatus) {
                $result['photos'][$photoCounter]['is_content_like'] = true;
            } else {
                $result['photos'][$photoCounter]['is_content_like'] = false;
            }
            if ($albumFavStatus) {
                $result['photos'][$photoCounter]['is_content_favourite'] = true;
            } else {
                $result['photos'][$photoCounter]['is_content_favourite'] = false;
            }

            $photoCounter++;
        }


        $storeItem = $store;


        if (isset($album->art_cover) && $album->art_cover != 0 && $album->art_cover != '') {
            $albumArtCover = Engine_Api::_()->storage()->get($album->art_cover, '')->getPhotoUrl();
            $result['album']['albumArtCover'] = $this->getBaseurl(false, $albumArtCover);
            $result['album']['cover_pic'] = $this->getBaseurl(false, $albumArtCover);
        } else {
            $albumArtCover = '';
        }
        if(!$albumArtCover){
          $albumImage = Engine_Api::_()->estore()->getAlbumPhoto($album->getIdentity(), 0, 3);
          $countTotal = count($albumImage);
          $result['album']['image_count'] = $countTotal;
          $imageCounter = 0;
          foreach ($albumImage as $photo) {
              $imageURL[$imageCounter] =  $this->getBaseurl(false, $photo->getPhotoUrl('thumb.normalmain'));;
              $imageCounter++;
          }
          $result['album']['cover_pic'] = $imageURL;
        }
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        if (count($result) > 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate(' There are no results that match your search. Please try again.'), 'result' => array()));


    }

    public function editalbumAction()
    {
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $album_id = $this->_getParam('album_id', false);

        if (!$album_id)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        else
            $album = Engine_Api::_()->getItem('estore_album', $album_id);

        $store = Engine_Api::_()->getItem('stores', $album->store_id);

        if ($store) {
            Engine_Api::_()->core()->setSubject($store);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        // Make form
        $form = new Estore_Form_Album_Edit();
        $form->populate($album->toArray());
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }
        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
        }
        //is post
        if (!$form->isValid($this->getRequest()->getPost())) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        // Process
        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            $album->setFromArray($values);
            $album->save();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate("You have successfully edtited this album."))));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));

        }

    }

    public function deletealbumAction()
    {

        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        $album_id = $this->_getParam('album_id', false);
        if ($album_id)
            $album = Engine_Api::_()->getItem('estore_album', $album_id);
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));

        $store = Engine_Api::_()->getItem('stores', $album->store_id);
        if ($store) {
            Engine_Api::_()->core()->setSubject($store);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }

        if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'delete')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('permission_error'), 'result' => array()));

        // In smoothbox

        $form = new Sesstore_Form_Album_Delete();
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }
        if (!$album) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Album does not exists or not authorized to delete'), 'result' => array()));
        }

        if (!$this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));

        }

        $db = $album->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $album->delete();
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('Message' => $this->view->translate('album deleted successfully.'))));

        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $e->getMessage(), 'result' => array()));

        }
    }

    public function likeasstoreAction()
    {
        $id = $this->_getParam('id');
        if (!$id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => $result));
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $store = Engine_Api::_()->getItem('stores', $id);
        $store_id = $this->_getParam('store_id');
        $table = Engine_Api::_()->getDbTable('storeroles', 'estore');

        $store_ids = $this->_getParam('store_ids');
        if($store_ids){
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            foreach($store_ids as $store_id){
              $row = $table->createRow();
              $row->store_id = $store_id;
              $row->like_store_id = $store->store_id;
              $row->user_id = $viewer->getIdentity();
              $row->save();
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Stores liked succuessfully.')));
        }

        if ($store_id) {
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            $row = $table->createRow();
            $row->store_id = $store_id;
            $row->like_store_id = $store->store_id;
            $row->user_id = $viewer->getIdentity();
            $row->save();
            $message = $this->view->translate('%s has been liked as Your store.', $store->getTitle());
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message)));
        }
        $selelct = $table->select()->where('user_id =?', $viewer->getIdentity())->where('memberrole_id =?', 1)->where('store_id !=?', $store->getIdentity())
            ->where('store_id NOT IN (SELECT store_id FROM engine4_estore_likestores WHERE like_store_id = ' . $store->store_id . ")");
        $myStores = ($table->fetchAll($selelct));
        if (count($myStores)) {
            $result = array();
            $result['title'] = 'Like ' . $store->getTitle() . ' as Your Store';
            $result['description'] = "Likes will show up on your Store's timeline. Which Store do you want to like " . $store->getTitle() . " as?";
            $result['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($store, '', "");
            $counter = 0;
            foreach ($myStores as $mystore) {
                $store = Engine_Api::_()->getItem('stores', $mystore->store_id);
                if (!$store)
                    continue;
                $storedata[$counter]['store_id'] = $store->store_id;
                $storedata[$counter]['store_title'] = $store->getTitle();
                $counter++;
            }
            $result['store'] = $storedata;
        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

    }

    public function unlikeasstoreAction()
    {
        $id = $this->_getParam('id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $store = Engine_Api::_()->getItem('stores', $id);
        $store_id = $this->_getParam('store_id');
        $table = Engine_Api::_()->getDbTable('storeroles', 'estore');

        $store_ids = $this->_getParam('store_ids');
        if($store_ids){
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            foreach($store_ids as $store_id){
              $select = $table->select()->where('store_id =?', $store_id)->where('like_store_id =?', $store->getIdentity());
              $row = $table->fetchRow($select);
              if ($row)
                  $row->delete();
            }
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Stores succuessfully removed.')));
        }
        if ($store_id) {
            $table = Engine_Api::_()->getDbTable('likestores', 'estore');
            $select = $table->select()->where('store_id =?', $store_id)->where('like_store_id =?', $store->getIdentity());
            $row = $table->fetchRow($select);
            if ($row)
                $row->delete();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => 'succuessfully removed.')));
        }
        $selelct = $table->select()->where('user_id =?', $viewer->getIdentity())->where('memberrole_id =?', 1)->where('store_id !=?', $store->getIdentity())
            ->where('store_id IN (SELECT store_id FROM engine4_estore_likestores WHERE like_store_id = ' . $store->store_id . ")");

        $myStores = ($table->fetchAll($selelct));
        if (count($myStores)) {
            $result = array();
            $result['title'] = "Remove " . $store->getTitle() . " from my Store's favorites";
            $result['description'] = "For which store would you like to remove  " . $store->getTitle() . " from favorites?";
            $result['image'] = Engine_Api::_()->sesapi()->getPhotoUrls($store, '', "");
            $counter = 0;
            foreach ($myStores as $mystore) {
                $store = Engine_Api::_()->getItem('stores', $mystore->store_id);
                if (!$store)
                    continue;
                $storedata[$counter]['store_id'] = $store->store_id;
                $storedata[$counter]['store_title'] = $store->getTitle();

                $counter++;
            }
            $result['store'] = $storedata;

        }
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));

    }
    
  public function leaveAction() {
    // Check auth
    if (!$this->_helper->requireUser()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$this->_helper->requireSubject()->isValid())
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if ($subject->isOwner($viewer))
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

    // Make form
    $form = new Estore_Form_Member_Leave();
    if ($this->_getParam('getForm')) {
        $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
        $this->generateFormFields($formFields);
    }

    // Not posting
    if (!$this->getRequest()->isPost()) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
        $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
        if (count($validateFields))
            $this->validateFormFields($validateFields);
    }
        
    // Process form
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
      $db->beginTransaction();

      try {
        $subject->membership()->removeMember($viewer);
        $db->commit();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Store left')));
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
    }
  }

//     public function leaveAction()
//     {
//         // Check auth
//         if (!$this->_helper->requireUser()->isValid()) {
//             Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
//         }
//         if (!$this->_helper->requireSubject()->isValid()) {
//             Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
//         }
//         $viewer = Engine_Api::_()->user()->getViewer();
// 		$viewerId = $viewer->getIdentity();
// 		$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
//         $subject = Engine_Api::_()->core()->getSubject();
//         $viewerId = $viewer->getIdentity();
// 
// 
//         if ($subject->isOwner($viewer)) {
//             Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
//         }
//         $canJoin = $levelId ? Engine_Api::_()->authorization()->getPermission($levelId, 'stores', 'store_can_join') : 0;
// 
// 
//         if (1) {
//             $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
//             $db->beginTransaction();
// 
//             try {
//                 $subject->membership()->removeMember($viewer);
//                 $db->commit();
//                 $result['message'] = $this->view->translate('You have successfully left this store.');
//                $result['menus'] = $this->getButtonMenus($subject);
//                 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
//             } catch (Exception $e) {
//                 $db->rollBack();
//                 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
//             }
//         }
//     }

  public function inviteAction()
  {
  
      if (!$this->_helper->requireUser()->isValid())
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
      if (!$this->_helper->requireSubject('stores')->isValid())
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));

      // @todo auth
      // Prepare data
      $viewer = Engine_Api::_()->user()->getViewer();
      $store = Engine_Api::_()->core()->getSubject();

      // Prepare friends
      $friendsTable = Engine_Api::_()->getDbtable('membership', 'user');
      $friendsIds = $friendsTable->select()
          ->from($friendsTable, 'user_id')
          ->where('resource_id = ?', $viewer->getIdentity())
          ->where('active = ?', true)
          ->limit(100)
          ->query()
          ->fetchAll(Zend_Db::FETCH_COLUMN);
      if (!empty($friendsIds)) {
          $friends = Engine_Api::_()->getItemTable('user')->find($friendsIds);
      } else {
          $friends = array();
      }
      // Prepare form
      $form = new Estore_Form_Invite();

      $count = 0;
      foreach ($friends as $friend) {
          if ($store->membership()->isMember($friend, null)) {
              continue;
          }
          $form->users->addMultiOption($friend->getIdentity(), $friend->getTitle());
          $count++;
      }
      if ($count == 1)
          $form->removeElement('all');
      else if (!$count)
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have no friends you can invite.'))));
      if ($this->_getParam('getForm')) {
          if ($form->getElement('all'))
              $form->getElement('all')->setName('estore_choose_all');

          $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
          $this->generateFormFields($formFields);
      }

      // Not posting
      if (!$this->getRequest()->isPost()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
      }

      if (!$form->isValid($this->getRequest()->getPost())) {
          $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
          if (count($validateFields))
              $this->validateFormFields($validateFields);
      }
//        if (!$form->isValid($this->getRequest()->getPost())) {
//            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('validation_fail'), 'result' => array()));
//        }
      // Process
      $table = $store->getTable();
      $db = $table->getAdapter();
      $db->beginTransaction();

      try {
          $usersIds = $form->getValue('users');

          $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
          foreach ($friends as $friend) {
              if (!in_array($friend->getIdentity(), $usersIds)) {
                  continue;
              }

              $store->membership()->addMember($friend)->setResourceApproved($friend);
              $notifyApi->addNotification($friend, $viewer, $store, 'estore_invite');
          }
          if ($count > 1) {
              $message = $this->view->translate('All members invited.');
          } else {
              $message = $this->view->translate('member invited.');
          }
          $db->commit();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $message)));
      } catch (Exception $e) {
          $db->rollBack();
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
  }
    public function editoverviewAction(){
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!Engine_Api::_()->core()->hasSubject()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
      }
      $subject = Engine_Api::_()->core()->getSubject();
      if ($this->_getParam('getForm')) {
          $formFields = array();
          $formFields[0]['name'] = "overview";
          $formFields[0]['type'] = "Textarea";
          $formFields[0]['multiple'] = "";
          $formFields[0]['label'] = "Store Overview";
          $formFields[0]['description'] = "";
          $formFields[0]['isRequired'] = "1";
          $formFields[0]['value'] = $subject->overview;
          $formFields[1]['name'] = "submit";
          $formFields[1]['type'] = "Button";
          $formFields[1]['multiple'] = "";
          $formFields[1]['label'] = "Save Changes";
          $formFields[1]['description'] = "";
          $formFields[1]['isRequired'] = "0";
          $formFields[1]['value'] = '';
          $this->generateFormFields($formFields, array('resources_type' => 'stores'));
      }
      $subject->overview = $_POST['overview'];
      $subject->save();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Store overview saved successfully.'))));
  }
  public function overviewAction()
  {
      $viewer = Engine_Api::_()->user()->getViewer();
      if (!Engine_Api::_()->core()->hasSubject()) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
      }

      $subject = Engine_Api::_()->core()->getSubject();
      $editOverview = $subject->authorization()->isAllowed($viewer, 'edit');

      if (!$editOverview && (!$subject->overview || is_null($subject->overview))) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('There are no results that match your search. Please try again.'), 'result' => array()));
      }

      if ($editOverview) {
        if ($subject->overview) {
            $result['button'][0]['name'] = "editoverview";
            $result['button'][0]['lable'] = $this->view->translate("Change Overview");
        } else {
            $result['button'][0]['name'] = "editoverview";
            $result['button'][0]['lable'] = $this->view->translate("Add Overview");
        }
      }
      if ($subject->overview) {
          $result['overview'] = $subject->overview;
      } else {
          $result['overview'] = $this->view->translate("There is currently no overview.");
      }


      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => $result));
  }

    public function deleteAction()
    {

        $storeid = $this->getParam('store_id',$this->getParam('id'));
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        $estore = Engine_Api::_()->getItem('stores', $this->getRequest()->getParam('store_id'));
        if (!$estore)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This store does not exist.'), 'result' => array()));
        if (!Engine_Api::_()->getDbTable('storeroles', 'estore')->toCheckUserStoreRole($this->view->viewer()->getIdentity(), $estore->getIdentity(), 'manage_dashboard', 'delete'))
            if (!$this->_helper->requireAuth()->setAuthParams($estore, null, 'delete')->isValid())
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        // In smoothbox
//    $this->_helper->layout->setLayout('default-simple');
        $form = new Estore_Form_Delete();
        if (!$estore) {
            $status['status'] = false;
            $error = Zend_Registry::get('Zend_Translate')->_("Store entry doesn't exist or not authorized to delete");
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
        }

        if (!$this->getRequest()->isPost()) {
            $status['status'] = false;
            $error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $error, 'result' => $status));
        }
        $db = $estore->getTable()->getAdapter();
        $db->beginTransaction();
        try {
			
            $estore->delete();
            $db->commit();
            $status['status'] = true;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('You have successfully deleted to this store.'), $status)));

        } catch (Exception $e) {
            $db->rollBack();
			echo '<pre>';print_r($e->getLine());die;
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

    public function addmorephotosAction()
    {
        $album_id = $this->_getParam('album_id', false);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('estore_album', $album_id);
            $store_id = $album->store_id;
        } 

        $form = new Estore_Form_Album();
        $store = Engine_Api::_()->getItem('stores', $store_id);

        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();

        $photoTable = Engine_Api::_()->getDbTable('photos', 'estore');
        $uploadSource = $_FILES['attachmentImage'];



        $photoArray = array(
            'store_id' => $store->store_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'estore')->getAdapter();
        $db->beginTransaction();
        try {
            foreach ($uploadSource['name'] as $name) {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
                
                $photo = $photo->setAlbumPhoto($images, false, false, $album);

                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['store_id'] = $store_id;
        $_POST['file'] = implode(' ', $uploadSource);

        $form->album->setValue($album_id);
        $album = $form->saveValues();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('album_id' => $album->album_id, 'message' => $this->view->translate('Photo added successfully.'))));
    }

    public function uploadphotoAction()
    {

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('This store does not exist.'), 'result' => array()));
        $photo = $store->photo_id;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store->setPhoto($data, '', 'profile');

        $viewer = Engine_Api::_()->user()->getViewer();
        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'estore')->getPhotoId($store->photo_id);
        $photo = Engine_Api::_()->getItem('estore_photo', $getPhotoId);

        $storelink = '<a href="' . $store->getHref() . '">' . $store->getTitle() . '</a>';
    $action = Engine_Api::_()->getDbTable('actions', 'activity')->addActivity($viewer, $photo, 'estore_store_pfphoto', null, array('storename' => $storelink));
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
      $detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
      if($detail_id) {
        $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
        $detailAction->sesresource_id = $store->getIdentity();
        $detailAction->sesresource_type = $store->getType();
        $detailAction->save();
      }
    }
    if ($action)
      Engine_Api::_()->getDbTable('actions', 'activity')->attachActivity($action, $photo);

        $file = array('main' => $store->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully photo uploaded.'), 'images' => $file)));
    }
protected function setPhoto($photo, $id) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    if (!$fileName) {
      $fileName = $file;
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
        'parent_type' => 'video',
        'parent_id' => $id,
        'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        'name' => $fileName,
    );
    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_main.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
            ->resize(500, 500)
            ->write($mainPath)
            ->destroy();
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
    } catch (Exception $e) {
      // Remove temp files
      @unlink($mainPath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
        throw new Sesstorevideo_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    // Remove temp files
    @unlink($mainPath);
    // Update row
    // Delete the old file?
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }
    return $iMain->file_id;
  }

    public function removephotoAction()
    {

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        } else {
            $store = Engine_Api::_()->core()->getSubject();

        }
        if (!$store)
            $store = Engine_Api::_()->getItem('stores', $this->_getparam('store_id', null));

        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        if (isset($store->photo_id) && $store->photo_id > 0) {
            $store->photo_id = 0;
            $store->save();
        }
        $file = array('main' => $store->getPhotoUrl());
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => 'Successfully photo deleted.'), 'images' => $file));

//    echo json_encode(array('file' => $store->getPhotoUrl()));
//    die;
    }

    public function uploadcoverAction()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            $store = Engine_Api::_()->getItem('stores', $this->_getparam('store_id', null));
      }else{
        $store = Engine_Api::_()->core()->getSubject();
      }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $cover_photo = $store->cover;
        if (isset($_FILES['Filedata']))
            $data = $_FILES['Filedata'];
        else if (isset($_FILES['webcam']))
            $data = $_FILES['webcam'];
        else if(isset($_FILES['image']))
          $data = $_FILES['image'];
        if (!$data) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store->setCoverPhoto($data);

        $viewer = Engine_Api::_()->user()->getViewer();
        $getPhotoId = Engine_Api::_()->getDbTable('photos', 'estore')->getPhotoId($store->cover);
        $photo = Engine_Api::_()->getItem('estore_photo', $getPhotoId);
        $storelink = '<a href="' . $store->getHref() . '">' . $store->getTitle() . '</a>';
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $photo, 'estore_store_coverphoto', null, array('storename' => $storelink));
        if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesadvancedactivity')) {
			$detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
      if($detail_id) {
        $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
        $detailAction->sesresource_id = $store->getIdentity();
        $detailAction->sesresource_type = $store->getType();
        $detailAction->save();
      }
        }
        if ($action)
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $photo);
        if ($cover_photo != 0) {
            $im = Engine_Api::_()->getItem('storage_file', $cover_photo);
            $im->delete();
        }
        $file['main'] = $store->getCoverPhotoUrl();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully cover photo uploaded.'), 'images' => $file)));
    }

    public function removecoverAction()
    {

        if (!Engine_Api::_()->core()->hasSubject()) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        if (isset($store->cover) && $store->cover > 0) {
            $im = Engine_Api::_()->getItem('storage_file', $store->cover);
            $store->cover = 0;
            $store->save();
            $im->delete();
        }

        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => '', 'result' => array('success_message' => $this->view->translate('Successfully deleted cover photo.'))));

    }

    function getDay($number)
    {
        switch ($number) {
            case 1:
                return "Monday";
                break;
            case 2:
                return "Tuesday";
                break;
            case 3:
                return "Wednesday";
                break;
            case 4:
                return "Thursday";
                break;
            case 5:
                return "Friday";
                break;
            case 6:
                return "Saturday";
                break;
            case 7:
                return "Sunday";
                break;
        }
    }
    //edit photo details from lightbox
  public function editDescriptionAction() {
    $status = true;
    $error = false;

    if (!$this->_helper->requireAuth()->setAuthParams(null, null, 'edit')->isValid()) {
      $status = false;
      $error = true;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $photo = Engine_Api::_()->core()->getSubject('estore_photo');
    if ($status && !$error) {
      $values['title'] = $_POST['title'];
      $values['description'] = $_POST['description'];
      $values['location'] = $_POST['location'];
			//update location data in sesbasic location table
      if ($_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $_POST['photo_id'] . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","sesalbum_photo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      $db = $photo->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $photo->setFromArray($values);
        $photo->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array('')));
      }
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array($this->view->translate('Photo edited successfully.'))));
  }
    public function lightboxAction()
    {

        $photo = Engine_Api::_()->getItem('estore_photo', $this->_getParam('photo_id'));
        $store_id = $this->_getparam('store_id', $photo->store_id);

        if ($photo && !$this->_getParam('album_id', null)) {
            $album_id = $photo->album_id;
        } else {
            $album_id = $this->_getParam('album_id', null);
        }
        $store = Engine_Api::_()->getItem('stores', $store_id);

        if ($album_id && null !== ($album = Engine_Api::_()->getItem('estore_album', $album_id))) {
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid Request'), 'result' => array()));
        }

        $photo_id = $photo->getIdentity();

//        if (!$this->_helper->requireSubject('estore_photo')->isValid())
//          Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'permission_error', 'result' => array()));

        if (!$this->_helper->requireAuth()->setAuthParams('stores', null, 'view')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'permission_error', 'result' => array()));

        $viewer = Engine_Api::_()->user()->getViewer();

        $albumData = array();
        if ($viewer->getIdentity() > 0) {

            $menu = array();
            $counterMenu = 0;
            $menu[$counterMenu]["name"] = "save";
            $menu[$counterMenu]["label"] = $this->view->translate("Save Photo");
            $counterMenu++;

            $canEdit = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'edit');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "edit";
                $menu[$counterMenu]["label"] = $this->view->translate("Edit Photo");
                $counterMenu++;
            }

            $can_delete = Engine_Api::_()->estore()->getStoreRolePermission($store->getIdentity(), 'allow_plugin_content', 'delete');
            if ($canEdit) {
                $menu[$counterMenu]["name"] = "delete";
                $menu[$counterMenu]["label"] = $this->view->translate("Delete Photo");
                $counterMenu++;
            }
            $menu[$counterMenu]["name"] = "report";
            $menu[$counterMenu]["label"] = $this->view->translate("Report Photo");

            $counterMenu++;

            $menu[$counterMenu]["name"] = "makeprofilephoto";
            $menu[$counterMenu]["label"] = $this->view->translate("Make Profile Photo");
            $counterMenu++;
            $albumData['menus'] = $menu;
            $canComment = $store->authorization()->isAllowed($viewer, 'comment') ? true : false;

            $albumData['can_comment'] = $canComment;

            $sharemenu = array();
            if ($viewer->getIdentity() > 0) {
                $sharemenu[0]["name"] = "siteshare";
                $sharemenu[0]["label"] = $this->view->translate("Share");
            }
            $sharemenu[1]["name"] = "share";
            $sharemenu[1]["label"] = $this->view->translate("Share Outside");
            $albumData['share'] = $sharemenu;
        }

        $condition = $this->_getParam('condition');
        if (!$condition) {
            $next = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, ">="), true);
            $previous = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, "<"), true);
            $array_merge = array_merge($previous, $next);

            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')) {
                $recArray = array();
                $reactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getPaginator();
                $counterReaction = 0;

                foreach ($reactions as $reac) {
                    if (!$reac->enabled)
                        continue;
                    $albumData['reaction_plugin'][$counterReaction]['reaction_id'] = $reac['reaction_id'];
                    $albumData['reaction_plugin'][$counterReaction]['title'] = $this->view->translate($reac['title']);
                    $icon = Engine_Api::_()->sesapi()->getPhotoUrls($reac->file_id, '', '');
                    $albumData['reaction_plugin'][$counterReaction]['image'] = $icon['main'];
                    $counterReaction++;
                }

            }
        } else {
            $array_merge = $this->getPhotos($this->nextPreviousImage($photo_id, $album_id, $condition), true);
        }
        $albumData['module_name'] = 'album';
        $albumData['photos'] = $array_merge;

        if (count($albumData['photos']) <= 0)
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('No photo created in this album yet.'), 'result' => array()));
        else
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $albumData)));
    }

    public function nextPreviousImage($photo_id, $album_id, $condition = "<=")
    {

        $photoTable = Engine_Api::_()->getItemTable('estore_photo');
        $select = $photoTable->select()
            ->where('album_id =?', $album_id)
            ->where('photo_id ' . $condition . ' ?', $photo_id)
            ->order('order ASC')
            ->limit(20);
        return $photoTable->fetchAll($select);
    }

    public function getPhotos($paginator, $updateViewCount = false)
    {


        $result = array();
        $counter = 0;

        foreach ($paginator as $photos) {
            $photo = $photos->toArray();
            $photos->view_count = new Zend_Db_Expr('view_count + 1');
            $photos->save();
            $photo['user_title'] = $photos->getOwner()->getTitle();
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer_id = $viewer->getIdentity();
            if ($viewer_id != 0) {
                $photo['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $photo['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($photos);
            }

            $attachmentItem = $photos;
            if ($attachmentItem->getPhotoUrl())
                $photo["shareData"]["imageUrl"] = $this->getBaseurl(false, $attachmentItem->getPhotoUrl());

            $photo["shareData"]["title"] = $attachmentItem->getTitle();
            $photo["shareData"]["description"] = strip_tags($attachmentItem->getDescription());

            $photo["shareData"]['urlParams'] = array(
                "type" => $photos->getType(),
                "id" => $photos->getIdentity()
            );

            if (is_null($photo["shareData"]["title"]))
                unset($photo["shareData"]["title"]);

            $owner = $photos->getOwner();
            $photo['owner']['title'] = $owner->getTitle();
            $photo['owner']['id'] = $owner->getIdentity();
            $photo["owner"]['href'] = $owner->getHref();
            $album_photo['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($photos->file_id, '', "");

            $photo['can_comment'] = $photos->getParent()->authorization()->isAllowed($viewer, 'comment') ? true : false;
            $photo['module_name'] = 'estore';
            if ($photo['can_comment']) {
                if ($viewer_id) {
                    $itemTable = Engine_Api::_()->getItemTable($photos->getType(), $photos->getIdentity());
                    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
                    $tableMainLike = $tableLike->info('name');
                    $select = $tableLike->select()
                        ->from($tableMainLike)
                        ->where('resource_type = ?', $photos->getType())
                        ->where('poster_id = ?', $viewer_id)
                        ->where('poster_type = ?', 'user')
                        ->where('resource_id = ?', $photos->getIdentity());
                    $resultData = $tableLike->fetchRow($select);
                    if ($resultData) {
                        $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($resultData->like_id);
                        $photo['reaction_type'] = $item_activity_like->type;
                    }
                }

                $photo['resource_type'] = $photos->getType();
                $photo['resource_id'] = $photos->getIdentity();

                $table = Engine_Api::_()->getDbTable('likes', 'core');
                $coreliketable = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity');
                $coreliketableName = $coreliketable->info('name');
                $recTable = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->info('name');
                $select = $table->select()->from($table->info('name'), array('total' => new Zend_Db_Expr('COUNT(like_id)')))->where('resource_id =?', $photos->getIdentity())->group('type')->setIntegrityCheck(false);
                $select->where('resource_type =?', $photos->getType());
                $select->joinLeft($coreliketableName, $table->info('name') . '.like_id =' . $coreliketableName . '.core_like_id', array('type'));
                $select->joinLeft($recTable, $recTable . '.reaction_id =' . $coreliketableName . '.type', array('file_id'))->where('enabled =?', 1)->order('total DESC');
                $resultData = $table->fetchAll($select);
                $photo['is_like'] = Engine_Api::_()->sesapi()->contentLike($photos);
                $reactionData = array();
                $reactionCounter = 0;
                if (count($resultData)) {
                    foreach ($resultData as $type) {
                        $reactionData[$reactionCounter]['title'] = $this->view->translate('%s (%s)', $type['total'], Engine_Api::_()->sesadvancedcomment()->likeWord($type['type']));
                        $reactionData[$reactionCounter]['imageUrl'] = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']));
                        $reactionCounter++;
                    }
                    $photo['reactionData'] = $reactionData;

                }

                if ($photo['is_like']) {
                    $photo['is_like'] = true;
                    $like = true;
                    $type = $photo['reaction_type'];
                    $imageLike = Engine_Api::_()->sesapi()->getBaseUrl(false, Engine_Api::_()->sesadvancedcomment()->likeImage($type));
                    $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
                } else {
                    $photo['is_like'] = false;
                    $like = false;
                    $type = '';
                    $imageLike = '';
                    $text = 'Like';
                }
                if (empty($like)) {
                    $photo["like"]["name"] = "like";
                } else {
                    $photo["like"]["name"] = "unlike";
                }

                // Get tags
                $tags = array();
                foreach ($photos->tags()->getTagMaps() as $tagmap) {
                    $arrayTag = $tagmap->toArray();
                    if(!$tagmap->getTag())
                        continue;
                    $tags[] = array_merge($tagmap->toArray(), array(
                        'id' => $tagmap->getIdentity(),
                        'text' => $tagmap->getTitle(),
                        'href' => $tagmap->getHref(),
                        'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
                    ));
                }

                if ($tags)
                    $photo["tags"] = $tags;
                if ($type)
                    $photo["like"]["type"] = $type;
                if ($imageLike)
                    $photo["like"]["image"] = $imageLike;
                $photo["like"]["label"] = $this->view->translate($text);
                $photo['reactionUserData'] = $this->view->FluentListUsers($photos->likes()->getAllLikesUsers(), '', $photos->likes()->getLike($viewer), $viewer);
            }
            if (!count($album_photo['images']))
                $album_photo['images']['main'] = $this->getBaseUrl(true, $photos->getPhotoUrl());
            $result[$counter] = array_merge($photo, $album_photo);
            $counter++;
        }
        return $result;
    }

	 public function addAction() {
		$video_id = $this->_getParam('video_id', false);
		if ($video_id) {
		  $params['video_id'] = $video_id;
		  $insertVideo = Engine_Api::_()->estorevideo()->deleteWatchlaterVideo($params);
		}else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>"parameter_missing", 'result' => array()));
		}
		if($insertVideo['status'] == 'insert'){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully added to watch later.'))));
		}else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'0','error_message'=>"", 'result' => array('message'=>$this->view->translate('Video Successfully deleted from watch later.'))));
		}
	}
	function getVideos($paginator,$checkProfile){

		$counter = 0;
		$result = array();
		foreach ($paginator as $item){
			$result[$counter] = $item->toArray();
			if($checkProfile['store_id'] > 0){
				$viewer = Engine_Api::_()->user()->getViewer();
				$viewerId = $viewer->getIdentity();
				$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
				if($viewerId>0) {
					$can_edit = Engine_Api::_()->authorization()->getPermission($levelId, 'storevideo', 'edit');
					$can_delete = Engine_Api::_()->authorization()->getPermission($levelId, 'storevideo', 'delete');
					if($can_edit &&  $item->status !=2 && $can_delete && $item->owner_id == Engine_Api::_()->user()->getViewer()->getIdentity()){
						$optionCounter = 0;
					if($can_edit){
						$result[$counter]['options'][$optionCounter]['label'] =$this->view->translate('Edit Video');
						$result[$counter]['options'][$optionCounter]['name'] = 'edit';
						$optionCounter++;
					}
					if($can_delete && $item->status !=2){
						$result[$counter]['options'][$optionCounter]['label'] =$this->view->translate('Delete Video');
						$result[$counter]['options'][$optionCounter]['name'] = 'delete';
						$optionCounter++;
					}
					}
				}
				$store = Engine_Api::_()->getItem('stores',$checkProfile['store_id']);
				$result[$counter]['store_title'] = $store->getTitle();

			}else{
				$store = Engine_Api::_()->getItem('stores',$item->store_id);
				$result[$counter]['store_title'] = $store->getTitle();
			}

			if($item->code && $item->type == 'iframely'){
				$embedded = $item->code;
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				$result[$counter]['video']  = str_replace('//','https://',$match[1]);
			  }else{

				$result[$counter]['video']  = $match[1];
			  }
			}else{
				$embedded = $item->getRichContent(true,array(),'','');
			  preg_match('/src="([^"]+)"/', $embedded, $match);
			  if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){

				$result[$counter]['video']  = str_replace('//','https://',$match[1]);
			  }else{

				$result[$counter]['video']  = $match[1];
			  }
			}
			 if($item->getType() == 'storevideo'){
				$allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.video.rating',1);
				$allowShowPreviousRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.show',1);
			if($allowRating == 0){
				if($allowShowPreviousRating == 0){
					$ratingShow = false;
				}
				 else{
					  $ratingShow = true;
				 }
			  }else{
				  $ratingShow = true;
			  }
			}else{
				$ratingShow = true;
			}
			if($ratingShow)
			$result[$counter]['rating_show'] = $ratingShow;
			$result[$counter]['image'] = $this->getBaseUrl(true, $item->getPhotoUrl());
			if( $item->duration >= 3600 ) {
				$result[$counter]['duration'] = gmdate("H:i:s", $item->duration);
			  } else {
				$result[$counter]['duration'] = gmdate("i:s", $item->duration);
			  }
			  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.watchlater',1)){
				  if(isset($item->watchlater_id)){
				  $result[$counter]['watch_later']['option']['label'] = $this->view->translate('Remove from Watch Later');
				  $result[$counter]['watch_later']['option']['name'] = 'removewatchlater';
				  $result[$counter]['hasWatchlater'] = true;
				  }else{
					  $result[$counter]['watch_later']['option']['label'] =$this->view->translate('Add to Watch Later');
					  $result[$counter]['watch_later']['option']['name'] = 'addtowatchlater';
					  $result[$counter]['hasWatchlater'] = false;
				  }
			  }
			  $viewer = Engine_Api::_()->user()->getViewer();
			  $viewerId = $viewer->getIdentity();
			  if($viewerId != 0 ){
				   $canComment =  $item->authorization()->isAllowed($viewer, 'comment');
				   $result[$counter]['can_comment'] = $canComment?true:false;
				   $result[$counter]['can_like'] = true;
				  $LikeStatus = Engine_Api::_()->estorevideo()->getLikeStatusVideo($item->video_id,'storevideo');
				  $result[$counter]['is_content_like'] = $LikeStatus?true:false;
			  }else{
				  $result[$counter]['can_comment'] = false;
				  $result[$counter]['can_like'] = false;
			  }
			  $itemtype = 'storevideo';
              $getId = 'video_id';
			  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.favourite', 1) && isset($item->favourite_count)){
				  $favStatus = Engine_Api::_()->getDbtable('favourites', 'estorevideo')->isFavourite(array('resource_type'=>$itemtype,'resource_id'=>$item->$getId));
				  $result[$counter]['is_content_favourite'] = $favStatus?true:false;
				  $result[$counter]['can_favourite'] = true;
				  $result[$counter]['fovourite_show'] = true;
			  }
			  $owner = $item->getOwner();
			  $result[$counter]['owner_title'] = $ownerTitle = $owner->getTitle();
			  if($item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo_enable_location', 1)){
				  $result[$counter]['location_show'] =true;
			  }else{
				  $result[$counter]['location_show'] =false;
			  }
			  $counter++;
		}
		return $result;
	}
	public function browsevideoAction(){
		$value['status'] = 1;
		$value['watchLater'] = true;
		$value['search'] = 1;
		$paginator = Engine_Api::_()->getDbTable('videos', 'estorevideo')->getVideo($value,$paginator = true);
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$checkProfile['store_id'] = null;
		$data['videos'] = $this->getVideos($paginator,$checkProfile);
			$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
			$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
			$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
			$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
	
	
    public function profileproductsAction() {
    
      $storeId = $this->_getParam('store_id',null);
      
      if(!$storeId)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        
      if (Engine_Api::_()->core()->hasSubject()) {
        $store = Engine_Api::_()->core()->getSubject();
      } else {
        $store = Engine_Api::_()->getItem('stores',$storeId);
      }
      
      if(!$store) {
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
      }
    
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();
      $this->view->identityForWidget = isset($_POST['identity']) ? $_POST['identity'] : '';
      $this->view->defaultOptionsArray = $defaultOptionsArray = $this->_getParam('search_type',array('recentlySPcreated','mostSPviewed','mostSPliked','mostSPcommented','mostSPrated','mostSPfavourite','featured','sponsored', 'verified', 'week', 'month'));

      if (isset($_POST['params']))
          $params = $_POST['params'];


      if(!isset($params['store_id']) && empty($params['store_id'])) {
          $subject = Engine_Api::_()->core()->getSubject();
        $value['store_id'] =  $params['store_id'] = $subject->getIdentity();
      } else {
          $value['store_id'] =  $params['store_id'];
      }

      $this->view->is_ajax = $is_ajax = isset($_POST['is_ajax']) ? true : false;
      $this->view->view_more = isset($_POST['view_more']) ? true : false;

      if(!$is_ajax) {
        if( !Engine_Api::_()->core()->hasSubject() ) {
          Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        }
      }

      $defaultOpenTab = array();
      $defaultOptions = $arrayOptions = array();
      if (!$is_ajax && is_array($defaultOptionsArray)) {
        foreach ($defaultOptionsArray as $key => $defaultValue) {
          if ($this->_getParam($defaultValue . '_order'))
            $order = $this->_getParam($defaultValue . '_order');
          else
            $order = (777 + $key);
          if ($this->_getParam($defaultValue.'_label'))
            $valueLabel = $this->_getParam($defaultValue . '_label'). '||' . $defaultValue;
          else {
            if ($defaultValue == 'recentlySPcreated')
              $valueLabel = 'Recently Created'. '||' . $defaultValue;
            else if ($defaultValue == 'mostSPviewed')
              $valueLabel = 'Most Viewed'. '||' . $defaultValue;
            else if ($defaultValue == 'mostSPliked')
              $valueLabel = 'Most Liked'. '||' . $defaultValue;
            else if ($defaultValue == 'mostSPcommented')
              $valueLabel = 'Most Commented'. '||' . $defaultValue;
            else if ($defaultValue == 'mostSPrated')
              $valueLabel = 'Most Rated'. '||' . $defaultValue;
            else if ($defaultValue == 'mostSPfavourite')
              $valueLabel = 'Most Favourite'. '||' . $defaultValue;
            else if ($defaultValue == 'featured')
              $valueLabel = 'Featured'. '||' . $defaultValue;
            else if ($defaultValue == 'sponsored')
              $valueLabel = 'Sponsored'. '||' . $defaultValue;
            else if ($defaultValue == 'verified')
              $valueLabel = 'Verified'. '||' . $defaultValue;
            else if ($defaultValue == 'week')
              $valueLabel = 'This Week'. '||' . $defaultValue;
            else if ($defaultValue == 'month')
              $valueLabel = 'This Month'. '||' . $defaultValue;
          }
          $arrayOptions[$order] = $valueLabel;
        }
        ksort($arrayOptions);
        $counter = 0;
        foreach ($arrayOptions as $key => $valueOption) {
          $key = explode('||', $valueOption);
          if ($counter == 0)
            $this->view->defaultOpenTab = $defaultOpenTab = $key[1];
          $defaultOptions[$key[1]] = $key[0];
          $counter++;
        }
      }
      $this->view->defaultOptions = $defaultOptions;

      if (isset($_GET['openTab']) || $is_ajax) {
        $this->view->defaultOpenTab = $defaultOpenTab = (isset($_GET['openTab']) ? str_replace('_', 'SP', $_GET['openTab']) : ($this->_getParam('openTab') != NULL ? $this->_getParam('openTab') : (isset($params['openTab']) ? $params['openTab'] : '' )));
      }
      //simple list view
      $this->view->title_truncation_simplelist = $title_truncation_simplelist = isset($params['title_truncation_simplelist']) ? $params['title_truncation_simplelist'] : $this->_getParam('title_truncation_simplelist', '100');
      $this->view->width_simplelist = $width_simplelist = isset($params['width_simplelist']) ? $params['width_simplelist'] : $this->_getParam('width_simplelist','140');
      $this->view->height_simplelist = $height_simplelist = isset($params['height_simplelist']) ? $params['height_simplelist'] : $this->_getParam('height_simplelist','160');
      $this->view->description_truncation_simplelist = $description_truncation_simplelist = isset($params['description_truncation_simplelist']) ? $params['description_truncation_simplelist'] : $this->_getParam('description_truncation_simplelist', '100');
      $this->view->limit_data_simplelist = $limit_data_simplelist = isset($params['limit_data_simplelist']) ? $params['limit_data_simplelist'] : $this->_getParam('limit_data_simplelist', '10');
      //advanced list view
      $this->view->title_truncation_advlist = $title_truncation_advlist = isset($params['title_truncation_advlist']) ? $params['title_truncation_advlist'] : $this->_getParam('title_truncation_advlist', '100');
      $this->view->description_truncation_advlist= $description_truncation_advlist = isset($params['description_truncation_advlist']) ? $params['description_truncation_advlist'] : $this->_getParam('description_truncation_advlist', '100');
      $this->view->limit_data_advlist= $limit_data_advlist = isset($params['limit_data_advlist']) ? $params['limit_data_advlist'] : $this->_getParam('limit_data_advlist', '10');
      //advanced grid view
      $this->view->title_truncation_advgrid = $title_truncation_advgrid = isset($params['title_truncation_advgrid']) ? $params['title_truncation_advgrid'] : $this->_getParam('title_truncation_advgrid', '100');
      $this->view->width_advgrid = $width_advgrid = isset($params['width_advgrid']) ? $params['width_advgrid'] : $this->_getParam('width_advgrid','140');
      $this->view->height_advgrid = $height_advgrid = isset($params['height_advgrid']) ? $params['height_advgrid'] : $this->_getParam('height_advgrid','160');
      $this->view->description_truncation_advgrid = $description_truncation_advgrid = isset($params['description_truncation_advgrid']) ? $params['description_truncation_advgrid'] : $this->_getParam('description_truncation_advgrid', '100');
      $this->view->limit_data_advgrid = $limit_data_advgrid = isset($params['limit_data_advgrid']) ? $params['limit_data_advgrid'] : $this->_getParam('limit_data_advgrid', '10');
      //super advanced grid view
      $this->view->title_truncation_supergrid = $title_truncation_supergrid = isset($params['title_truncation_supergrid']) ? $params['title_truncation_supergrid'] : $this->_getParam('title_truncation_supergrid', '100');
      $this->view->width_supergrid = $width_supergrid = isset($params['width_supergrid']) ? $params['width_supergrid'] : $this->_getParam('width_supergrid','140');
      $this->view->height_supergrid = $height_supergrid = isset($params['height_supergrid']) ? $params['height_supergrid'] : $this->_getParam('height_supergrid','160');
      $this->view->description_truncation_supergrid = $description_truncation_supergrid = isset($params['description_truncation_supergrid']) ? $params['description_truncation_supergrid'] : $this->_getParam('description_truncation_supergrid', '100');
      $this->view->limit_data_supergrid = $limit_data_supergrid = isset($params['limit_data_supergrid']) ? $params['limit_data_supergrid'] : $this->_getParam('limit_data_supergrid', '10');
      //end
      $this->view->htmlTitle = $htmlTitle = isset($params['htmlTitle']) ? $params['htmlTitle'] : $this->_getParam('htmlTitle','1');
      $this->view->tab_option = $tab_option = isset($params['tabOption']) ? $params['tabOption'] : $this->_getParam('tabOption','vertical');
      $this->view->height_list = $defaultHeightList = isset($params['height_list']) ? $params['height_list'] : $this->_getParam('height_list','160');

      $this->view->width_list = $defaultWidthList = isset($params['width_list']) ? $params['width_list'] : $this->_getParam('width_list','140');

      $this->view->height_grid = $defaultHeightGrid = isset($params['height_grid']) ? $params['height_grid'] : $this->_getParam('height_grid','160');

      $this->view->width_grid = $defaultWidthGrid = isset($params['width_grid']) ? $params['width_grid'] : $this->_getParam('width_grid','140');

      $this->view->width_pinboard = $defaultWidthPinboard = isset($params['width_pinboard']) ? $params['width_pinboard'] : $this->_getParam('width_pinboard','300');

      $this->view->height = $defaultHeight = isset($params['height']) ? $params['height'] : $this->_getParam('height', '200px');

      $this->view->title_truncation_list = $title_truncation_list = isset($params['title_truncation_list']) ? $params['title_truncation_list'] : $this->_getParam('title_truncation_list', '100');

      $this->view->title_truncation_grid = $title_truncation_grid = isset($params['title_truncation_grid']) ? $params['title_truncation_grid'] : $this->_getParam('title_truncation_grid', '100');

      $this->view->title_truncation_pinboard = $title_truncation_pinboard = isset($params['title_truncation_pinboard']) ? $params['title_truncation_pinboard'] : $this->_getParam('title_truncation_pinboard', '100');

      $this->view->description_truncation_list = $description_truncation_list = isset($params['description_truncation_list']) ? $params['description_truncation_list'] : $this->_getParam('description_truncation_list', '100');

      $this->view->description_truncation_grid = $description_truncation_grid = isset($params['description_truncation_grid']) ? $params['description_truncation_grid'] : $this->_getParam('description_truncation_grid', '100');

      $this->view->description_truncation_pinboard = $description_truncation_pinboard = isset($params['description_truncation_pinboard']) ? $params['description_truncation_pinboard'] : $this->_getParam('description_truncation_pinboard', '100');


      $this->view->socialshare_enable_plusicon = $socialshare_enable_plusicon =isset($params['socialshare_enable_plusicon']) ? $params['socialshare_enable_plusicon'] : $this->_getParam('socialshare_enable_plusicon', 1);
      $this->view->socialshare_icon_limit = $socialshare_icon_limit =isset($params['socialshare_icon_limit']) ? $params['socialshare_icon_limit'] : $this->_getParam('socialshare_icon_limit', 2);

      //Need to Discuss
      $show_criterias = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'rating', 'by', 'title', 'featuredLabel','sponsoredLabel','verifiedLabel', 'category','description_list','description_grid','description_pinboard', 'favouriteButton','likeButton', 'socialSharing', 'view', 'creationDate', 'readmore'));
      if(is_array($show_criterias)) {
        foreach ($show_criterias as $show_criteria)
        $this->view->{$show_criteria . 'Active'} = $show_criteria;
      }

      $this->view->limit_data_pinboard = $limit_data_pinboard = isset($params['limit_data_pinboard']) ? $params['limit_data_pinboard'] : $this->_getParam('limit_data_pinboard', '10');

      $this->view->limit_data_grid = $limit_data_grid = isset($params['limit_data_grid']) ? $params['limit_data_grid'] : $this->_getParam('limit_data_grid', '10');

      $this->view->limit_data_list = $limit_data_list = isset($params['limit_data_list']) ? $params['limit_data_list'] : $this->_getParam('limit_data_list', '10');

    //  $value['user_id'] = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($params['user_id']) ?  $params['user_id'] : $viewer_id);

      $this->view->bothViewEnable = false;
      if (!$is_ajax) {
        $optionsEnable = $this->_getParam('enableTabs', array('list', 'advlist', 'grid', 'advgrid', 'supergrid', 'pinboard', 'map'));
        if($optionsEnable == '')
          $this->view->optionsEnable = array();
        else
          $this->view->optionsEnable = $optionsEnable;
        $view_type = $this->_getParam('openViewType', 'list');
        if (count($optionsEnable) > 1) {
          $this->view->bothViewEnable = true;
        }
      }

      $this->view->view_type = $view_type = (isset($_POST['type']) ? $_POST['type'] : (isset($params['view_type']) ? $params['view_type'] : $view_type));

      $this->view->loadOptionData = $loadOptionData = isset($params['pagging']) ? $params['pagging'] : $this->_getParam('pagging', 'auto_load');
      $params = array('height' => $defaultHeight,'openTab' => $defaultOpenTab,'height_list' => $defaultHeightList, 'width_list' => $defaultWidthList,'height_grid' => $defaultHeightGrid, 'width_grid' => $defaultWidthGrid,'width_pinboard'=>$defaultWidthPinboard,'limit_data_pinboard'=>$limit_data_pinboard,'limit_data_list'=>$limit_data_list,'limit_data_grid'=>$limit_data_grid,'pagging' => $loadOptionData, 'show_criterias' => $show_criterias, 'view_type' => $view_type,  'description_truncation_list' => $description_truncation_list, 'title_truncation_list' => $title_truncation_list, 'title_truncation_grid' => $title_truncation_grid,'title_truncation_pinboard'=>$title_truncation_pinboard,'description_truncation_grid'=>$description_truncation_grid,'description_truncation_pinboard'=>$description_truncation_pinboard, 'user_id'=>$value['user_id'],'title_truncation_simplelist'=>$title_truncation_simplelist,'width_simplelist'=>$width_simplelist,'height_simplelist'=>$height_simplelist,'description_truncation_simplelist'=>$description_truncation_simplelist,'limit_data_simplelist'=>$limit_data_simplelist,'title_truncation_advlist'=>$title_truncation_advlist,'description_truncation_advlist'=>$description_truncation_advlist,'limit_data_advlist'=>$limit_data_advlist,'title_truncation_advgrid'=>$title_truncation_advgrid,'width_advgrid'=>$width_advgrid,'height_advgrid'=>$height_advgrid,'description_truncation_advgrid'=>$description_truncation_advgrid,'limit_data_advgrid'=>$limit_data_advgrid,	'title_truncation_supergrid'=>$title_truncation_supergrid,'width_supergrid'=>$width_supergrid,'height_supergrid'=>$height_supergrid,'description_truncation_supergrid'=>$description_truncation_supergrid,'limit_data_supergrid'=>$limit_data_supergrid, 'socialshare_enable_plusicon' => $socialshare_enable_plusicon, 'socialshare_icon_limit' => $socialshare_icon_limit);

      $this->view->loadJs = true;

      // custom list grid view options
      $this->view->can_create = Engine_Api::_()->authorization()->isAllowed('sesproduct', null, 'create');

      $type = '';
      switch ($defaultOpenTab) {
        case 'recentlySPcreated':
          $popularCol = 'creation_date';
          $type = 'creation';
          break;
        case 'mostSPviewed':
          $popularCol = 'view_count';
          $type = 'view';
          break;
        case 'mostSPliked':
          $popularCol = 'like_count';
          $type = 'like';
          break;
        case 'mostSPcommented':
          $popularCol = 'comment_count';
          $type = 'comment';
          break;
        case 'mostSPrated':
          $popularCol = 'rating';
          $type = 'rating';
          break;
        case 'mostSPfavourite':
          $popularCol = 'favourite_count';
          $type = 'favourite';
          break;
        case 'featured':
          $popularCol = 'featured';
          $type = 'featured';
          $fixedData = 'featured';
          break;
        case 'sponsored':
          $popularCol = 'sponsored';
          $type = 'sponsored';
          $fixedData = 'sponsored';
          break;
        case 'verified':
          $popularCol = 'verified';
          $type = 'verified';
          $fixedData = 'verified';
          break;
        case 'week':
          $popularCol = 'week';
          $type = 'week';
          break;
        case 'month':
          $popularCol = 'month';
          $type = 'month';
          break;
      }

      $this->view->type = $type;
      $value['popularCol'] = isset($popularCol) ? $popularCol : '';
      $value['fixedData'] = isset($fixedData) ? $fixedData : '';
      $value['draft'] = 0;
      $value['search'] = 1;
      $options = array('tabbed' => true, 'paggindData' => true);
      $this->view->optionsListGrid = $options;
      $this->view->widgetName = 'profile-sesproducts';
      $params = array_merge($params, $value);

      // Get Products
      $paginator = Engine_Api::_()->getDbtable('sesproducts', 'sesproduct')->getSesproductsPaginator($value);
      
      $result['products'] = $this->getProducts($paginator);
      
      // Set item count per page and current page number
      $limit_data = $this->view->{'limit_data_'.$view_type};
      $paginator->setItemCountPerPage($limit_data);
      $page = isset($_POST['page']) ? $_POST['page'] : 1;
      $this->view->page = $page;
      $paginator->setCurrentPageNumber($page);
      
      
      $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
      $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
      $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
      $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $result), $extraParams));

    }
	
    public function getProducts($paginator) {
        $result = array();
        $counter = 0;
        $canFavourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.favourite', 0);

        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.sharing', 1);

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
		    $levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;

        foreach ($paginator as $stores) {
          $store_item = Engine_Api::_()->getItem('stores', $stores->store_id);
          $store = $stores->toArray();
          $result[$counter]['description'] = strip_tags($stores->body);
          unset($result['body']);
          $result[$counter] = $store;
          $result[$counter]['owner_title'] = $stores->getOwner()->getTitle();
          $currency = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
          $curArr = Zend_Locale::getTranslationList('CurrencySymbol');
          $result[$counter]['currency'] = $curArr[$currency];
          if ($stores->category_id) {
              $category = Engine_Api::_()->getItem('sesproduct_category', $stores->category_id);
              if ($category) {
                  $result[$counter]['category_title'] = $category->category_name;
                  if ($stores->subcat_id) {
                      $subcat = Engine_Api::_()->getItem('sesproduct_category', $stores->subcat_id);
                      if ($subcat) {
                          $result[$counter]['subcategory_title'] = $subcat->category_name;
                          if ($stores->subsubcat_id) {
                              $subsubcat = Engine_Api::_()->getItem('sesproduct_category', $stores->subsubcat_id);
                              if ($subsubcat) {
                                  $result[$counter]['subsubcategory_title'] = $subsubcat->category_name;
                              }
                          }
                      }
                  }
              }
          }
          $tags = array();
          foreach ($stores->tags()->getTagMaps() as $tagmap) {
              $arrayTag = $tagmap->toArray();
              if(!$tagmap->getTag())
                  continue;
              $tags[] = array_merge($tagmap->toArray(), array(
                  'id' => $tagmap->getIdentity(),
                  'text' => $tagmap->getTitle(),
                  'href' => $tagmap->getHref(),
                  'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
              ));
          }
          if (count($tags)) {
              $result[$counter]['tag'] = $tags;
          }

          $result[$counter]['images']['main']= $this->getBaseUrl(true, $stores->getPhotoUrl());
          //$result[$counter]['cover_image']['main'] = $this->getBaseUrl(true, $stores->getCoverPhotoUrl());
          $result[$counter]['cover_images']['main'] = $result[$counter]['cover_image']['main'];
          $i = 0;
          if ($stores->is_approved) {
              if ($shareType) {
                  $result[$counter]["share"]["imageUrl"] = $this->getBaseUrl(false, $stores->getPhotoUrl());
                  $result[$counter]["share"]["url"] = $this->getBaseUrl(false,$stores->getHref());
                  $result[$counter]["share"]["title"] = $stores->getTitle();
                  $result[$counter]["share"]["description"] = strip_tags($stores->getDescription());
                  $result[$counter]["share"]["setting"] = $shareType;
                  $result[$counter]["share"]['urlParams'] = array(
                      "type" => $stores->getType(),
                      "id" => $stores->getIdentity()
                  );
              }
          }

          if((empty($stores->manage_stock) || $stores->stock_quatity) && empty($stores->outofstock) ){
            $result[$counter]['stock'] = $this->view->translate('In Stock');
          } else {
            $result[$counter]['stock'] = $this->view->translate('Out of Stock');
          }

          //Rating Count
          $rating = Engine_Api::_()->getDbTable('sesproductreviews','sesproduct')->getRating($stores->getIdentity());
          $result[$counter]['ratings'] = round($rating,1);
          $totalReviewCount = (int)Engine_Api::_()->getDbTable('sesproductreviews','sesproduct')->getReviewCount(array('product_id'=>$stores->getIdentity()))[0];
          $result[$counter]['review_count'] = '('.(int) $totalReviewCount.')';

          $result[$counter]['store_title'] = $store_item->getTitle();

          if ($stores->is_approved) {

            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesproduct.enable.wishlist', 1)) {
              $result[$counter]['is_content_wishlist'] = true;
            }

            if($stores->discount && $priceDiscount = Engine_Api::_()->sesproduct()->productDiscountPrice($stores)){
              $result[$counter]['total_price'] = $price = Engine_Api::_()->sesproduct()->getCurrencyPrice($priceDiscount);
              $afterDiscount = Engine_Api::_()->sesproduct()->getCurrencySymbol(Engine_Api::_()->sesproduct()->getCurrentCurrency()) . '<strike>' . $stores->price . '</strike>';
              $result[$counter]['discount_price'] = $afterDiscount;
              if($stores->discount_type == 0) {
                $result[$counter]['percentage_discount_value'] = $this->view->translate("%s%s OFF",str_replace('.00','', $stores->percentage_discount_value),"%");
              } else {
                $result[$counter]['percentage_discount_value'] = $this->view->translate("%s OFF",Engine_Api::_()->sesproduct()->getCurrencyPrice($stores->fixed_discount_value));
              }
            } else {
              $result[$counter]['total_price'] = $price = Engine_Api::_()->sesproduct()->getCurrencyPrice($stores->price);
            }
//                 if ($shareType) {
//                     $result[$counter]['buttons'][$i]['name'] = 'share';
//                     $result[$counter]['buttons'][$i]['label'] = 'Share';
//                     $i++;
//                 }

            if ($viewerId != 0) {
                $result[$counter]['is_content_like'] = Engine_Api::_()->sesapi()->contentLike($stores);
                $result[$counter]['content_like_count'] = (int)Engine_Api::_()->sesapi()->getContentLikeCount($stores);
                if ($canFavourite) {
                    $result[$counter]['is_content_favourite'] = Engine_Api::_()->sesapi()->contentFavoutites($stores, 'favourites', 'sesproduct', 'sesproduct', 'user_id');
                    $result[$counter]['content_favourite_count'] = (int)Engine_Api::_()->sesapi()->getContentFavouriteCount($stores, 'favourites', 'sesproduct', 'sesproduct', 'user_id');
                }
            }

            if ($stores->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.location', 1)) {
                unset($stores['location']);
                $location = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('sesproduct', $stores->getIdentity());
                if ($location) {
                    $result[$counter]['location'] = $location->toArray();
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.map.integration', 1)) {
                        $result[$counter]['location']['showMap'] = true;
                    } else {
                        $result[$counter]['location']['showMap'] = false;
                    }
                }
            }
            $counter++;
          }
        }
        $results['products'] = $result;
        return $result;

    }
	
	
	public function profileVideosAction(){
		$storeId = $this->_getParam('store_id',null);
		if(!$storeId)
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
		if (Engine_Api::_()->core()->hasSubject()){
			$store = Engine_Api::_()->core()->getSubject();
		}else{
			$store = Engine_Api::_()->getItem('stores',$storeId);
		}
		if(!$store){
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
		}
		$sort = $this->_getParam('sort', null);
		$search = $this->_getParam('search', null);
		$paginator = Engine_Api::_()->getDbTable('videos', 'estorevideo')->getVideo(array('parent_id' => $store->getIdentity(), 'parent_type' =>$store->getType(), 'text' => $search, 'sort' => $sort));
		$paginator->setItemCountPerPage($this->_getParam('limit', 10));
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
		$checkProfile['store_id'] = $store->getIdentity();
		$allowVideo  = Engine_Api::_()->authorization()->isAllowed('stores', $viewer, 'video');
		$canUpload = $canUpload = $store->authorization()->isAllowed($viewer, 'video');
		if($allowVideo && $canUpload){
			$data['button']['label'] = $this->view->translate('Post New Video');
			$data['button']['name'] = 'create';
		}
		$sortCounter = 0;
		$data['sort'][$sortCounter]['name'] = 'creation_date';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Recently Created');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_liked';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Liked');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_viewed';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Viewed');
		$sortCounter++;
		$data['sort'][$sortCounter]['name'] = 'most_commented';
		$data['sort'][$sortCounter]['label'] = $this->view->translate('Most Commented');
		$sortCounter++;
		$data['videos'] = $this->getVideos($paginator,$checkProfile);
			$extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
			$extraParams['pagging']['total'] = $paginator->getTotalItemCount();
			$extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
			$extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
			Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '', 'error_message' => '', 'result' => $data), $extraParams));
	}
    public function createVideoAction() {
    if (!$this->_helper->requireUser->isValid())
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    // Upload video
	  if (isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name']))
      $_POST['id'] = $this->uploadVideoAction();
    $viewer = Engine_Api::_()->user()->getViewer();
	$viewerId = $viewer->getIdentity();
	$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
    $values['user_id'] = $viewer->getIdentity();
    $parent_id = $parent_id = $this->_getParam('parent_id', null);
    $parent_type = $parent_type = 'stores';
    if( $parent_id &&  $parent_type)
        $parentItem = Engine_Api::_()->getItem($parent_type, $parent_id);
    if(!$parentItem)
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Data not found.'), 'result' => array()));
    $paginator = Engine_Api::_()->getApi('core', 'estorevideo')->getVideosPaginator($values);
    $quota = $quota = Engine_Api::_()->authorization()->getPermission($levelId, 'storevideo', 'max');
    $current_count = $currentCount = $paginator->getTotalItemCount();
	if (($current_count >= $quota) && !empty($quota)){
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('You have already uploaded the maximum If you would like to upload a new video, please an old one first.'), 'result' => array()));
	}
    //Create form
    $form = $form = new Sesstorevideo_Form_Video();
	if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));
		$form->removeElement('lat');
		$form->removeElement('map-canvas');
		$form->removeElement('ses_location');
		$form->removeElement('lng');
		if($form->removeElement('is_locked'))
            $form->removeElement('password');
		if($form->removeElement('password'))
            $form->removeElement('password');

	 if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues('url');
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
    }

    // Process
    $values = $form->getValues();
    $values['parent_id'] = $parent_id;
    $values['parent_type'] = $parent_type;
    $values['owner_id'] = $viewer->getIdentity();
    $insert_action = false;
    $db = Engine_Api::_()->getDbtable('videos', 'estorevideo')->getAdapter();
    $db->beginTransaction();
    try {
		$viewer = Engine_Api::_()->user()->getViewer();
		$isApproveUploadOption = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('storevideo', $viewer, 'video_approve');
		$approveUploadOption = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('storevideo', $viewer, 'video_approve_type');
		$approve = 1;
		if($isApproveUploadOption){
			foreach($approveUploadOption as $valuesIs){
				if ($values['type'] == 3 && $valuesIs == 'myComputer') {
					//my computer
					$approve = 0;
					break;
				}elseif($valuesIs == "iframely"){
             $approve = 0;
						 break;
          }
				}
			}

		//Create video
		$table = Engine_Api::_()->getDbtable('videos', 'estorevideo');
		if($values['type'] == 'iframely') {
			$information = $this->handleIframelyInformation($values['url']);
			if (empty($information)) {
				$form->addError('We could not find a video there - please check the URL and try again.');
			}
			$values['code'] = $information['code'];
			$values['thumbnail'] = $information['thumbnail'];
			$values['duration'] = $information['duration'];
			$video = $table->createRow();
		}
	  else if ($values['type'] == 3) {
		$video = Engine_Api::_()->getItem('storevideo', $this->_getParam('id'));
	  } else
        $video = $table->createRow();
		  if ($values['type'] == 3 && isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {
			$values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
		  }
        //disable lock if password not set.
        if (isset($values['is_locked']) && $values['is_locked'] && $values['password'] == '')
          $values['is_locked'] = '0';
		if(empty($_FILES['photo_id']['name'])){
			unset($values['photo_id']);
		}
		$values['approve'] = $approve;
        $video->setFromArray($values);
        $video->save();
        // Add fields
        $customfieldform = $form->getSubForm('fields');
        if (!is_null($customfieldform)) {
          $customfieldform->setItem($video);
          $customfieldform->saveValues();
        }
        $thumbnail = $values['thumbnail'];
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);
        if (@GetImageSize($thumbnail)) {
            $valid_thumb = true;
        } else {
            $valid_thumb = false;
        }
        if(isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '' && $values['type'] != 3 ){
            $video->photo_id = $this->setPhoto($form->photo_id, $video->video_id, true);
            $video->save();
        } else if($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
          $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
          $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
          $src_fh = fopen($thumbnail, 'r');
          $tmp_fh = fopen($tmp_file, 'w');
          stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
          //resize video thumbnails
          $image = Engine_Image::factory();
          $image->open($tmp_file)
                  ->resize(500, 500)
                  ->write($thumb_file)
                  ->destroy();
          try {
            $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                'parent_type' => $video->getType(),
                'parent_id' => $video->getIdentity()
            ));
            // Remove temp file
            @unlink($thumb_file);
            @unlink($tmp_file);
						$video->photo_id = $thumbFileRow->file_id;
						$video->save();
          } catch (Exception $e){
						 @unlink($thumb_file);
             @unlink($tmp_file);
						}
        }
			if($values['type'] == 'iframely') {
				$video->status = 1;
				$video->save();
				$video->type = 'iframely';
				$insert_action = true;
			}
			if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
            $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
            $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $video->video_id . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","storevideo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
          }
        if ($values['ignore'] == true) {
          $video->status = 1;
          $video->save();
          $insert_action = true;
        }
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_view']))
          $auth_view = $values['auth_view'];
        else
          $auth_view = "everyone";
        $viewMax = array_search($auth_view, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
        }
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        if (isset($values['auth_comment']))
          $auth_comment = $values['auth_comment'];
        else
          $auth_comment = "everyone";
        $commentMax = array_search($auth_comment, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
        }
        // Add tags
        $tags = preg_split('/[,]+/', $values['tags']);
        $video->tags()->addTagMaps($viewer, $tags);
        $db->commit();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' ,'result' => array('message'=>$this->view->translate('Video created successfully.'),'video_id' => $video->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage() , 'result' =>array()));
      //throw $e;
    }
    $db->beginTransaction();
    try {
      if ($approve) {
        $owner = $video->getOwner();
        //Create Activity Feed

        if($parent_id && $parent_type) {
		      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $parentItem, 'estore_store_editeventvideo');
	        if ($action != null) {
	          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
	        }
        } else {
	        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'sespgvido_crte');
	        if ($action != null) {
	          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
	        }
        }
				// Rebuild privacy
				$actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
				foreach ($actionTable->getActionsByObject($video) as $action) {
					$actionTable->resetActivityBindings($action);
				}
      }
      $db->commit();
	  $values = $form->getValues('url');
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Video created successfully.'),'approve'=>'1','video_id' => $video->getIdentity())));
    } catch (Exception $e) {
      $db->rollBack();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>$e->getMessage() , 'result' =>array()));
      //throw $e;
    }
  }
    public function uploadVideoAction() {
    if (!$this->_helper->requireUser()->checkRequire()) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    if (!$this->getRequest()->isPost()) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $values = $this->getRequest()->getPost();
    if (empty($_FILES['Filedata'])) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('No file');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt','zip');
    if (in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
    $db = Engine_Api::_()->getDbtable('videos', 'estorevideo')->getAdapter();
    $db->beginTransaction();
    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();
      $params = array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
      );
      $video = Engine_Api::_()->estorevideo()->createVideo($params, $_FILES['Filedata'], $values);
      $result['status'] = true;
      $result['name'] = $_FILES['Filedata']['name'];
      $result['code'] = $video->code;
      $result['video_id'] = $video->video_id;
      // sets up title and owner_id now just incase members switch store as soon as upload is completed
      $video->title = $_FILES['Filedata']['name'];
      $video->owner_id = $viewer->getIdentity();
      $video->save();
      $db->commit();
	   return $video->video_id;
    } catch (Exception $e) {
      $db->rollBack();
      $result['status'] = false;
      $result['error'] = Zend_Registry::get('Zend_Translate')->_('An error occurred.') . $e;
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$result['error'], 'result' =>array('status'=>$result['status'])));
    }
  }
    public function handleIframelyInformation($uri) {
        $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
        if (parse_url($uri, PHP_URL_SCHEME) === null) {
            $uri = "http://" . $uri;
        }
        $uriHost = Zend_Uri::factory($uri)->getHost();
        if ($iframelyDisallowHost && in_array($uriHost, $iframelyDisallowHost)) {
            return;
        }
        $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
        $iframely = Engine_Iframely::factory($config)->get($uri);
        if (!in_array('player', array_keys($iframely['links']))) {
            return;
        }
        $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
        if (!empty($iframely['links']['thumbnail'])) {
            $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
            if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
                $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
                $information['thumbnail'] = "http://" . $information['thumbnail'];
            }
        }
        if (!empty($iframely['meta']['title'])) {
            $information['title'] = $iframely['meta']['title'];
        }
        if (!empty($iframely['meta']['description'])) {
            $information['description'] = $iframely['meta']['description'];
        }
        if (!empty($iframely['meta']['duration'])) {
            $information['duration'] = $iframely['meta']['duration'];
        }
        $information['code'] = $iframely['html'];
        return $information;
    }
    public function viewVideoAction(){
		$videoid = $this->_getParam('video_id',null);
        if (Engine_Api::_()->core()->hasSubject()){
            $video = Engine_Api::_()->core()->getSubject('storevideo');
        }
        else if($videoid)
        {
            $video = Engine_Api::_()->getItem('storevideo', $videoid);
        }else{
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' => array()));
        }
		$result = $video->toArray();
		$result['user_title'] = $video->getOwner()->getTitle();
		$owneritem = Engine_Api::_()->getItem('user', $video->owner_id);
        $ownerimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "");
        $thumbimage = Engine_Api::_()->sesapi()->getPhotoUrls($owneritem, "", "thumb.profile");

		$favStatus = Engine_Api::_()->getDbtable('favourites', 'estorevideo')->isFavourite(array('resource_type'=>$video->getType(),'resource_id'=>$video->getIdentity()));
		$LikeStatus = Engine_Api::_()->estorevideo()->getLikeStatusVideo($video->getIdentity(),$video->getType());
		if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.favourite', 1)){
			$result['is_content_favourite'] = $favStatus?true:false;
		}
		$result['is_content_like'] = $LikeStatus?true:false;
		if ($ownerimage){
			$result['owner_image'] = $ownerimage;

			$result['user_image'] = $thumbimage['main'];
		}

		$viewer = Engine_Api::_()->user()->getViewer();
		 if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
			 $viewer = Engine_Api::_()->user()->getViewer();
			  if ($viewer->getIdentity() == 0)
				$result['level'] = $level = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			  else
				$result['level'] = $level = $viewer;
			$viewerId = $viewer->getIdentity();
			$levelId = ($viewerId) ? $viewer->level_id : Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
			  if (!Engine_Api::_()->authorization()->getPermission($levelId, 'storevideo', 'locked') && $video->is_locked) {
				$result['is_locekd'] = $locked = true;
			  } else {
				$result['is_locekd'] = $locked = false;
			  }
			  $result['password'] = $video->password;
		 }else{
			  $result['password'] =  true;
		 }
		 $videoTags = $video->tags()->getTagMaps();
			$can_embed = true;
			if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1)) {
			  $can_embed = false;
			} else if (isset($video->allow_embed) && !$video->allow_embed) {
			  $can_embed = false;
			}
			$result['can-embed'] = $can_embed = $can_embed;
			// increment count
			$embedded = "";
			$mine = true;
			if ($video->status == 1) {
			  if (!$video->isOwner($viewer)) {
				$video->view_count++;
				$video->save();
				$mine = false;
			  }
			 $embe = $video->getRichContent(true,array(),'',$autoPlay);
			  $result['embedded'] = str_replace("//cdn.iframe","https://cdn.iframe",$embe);
			}
			if($video->code && $video->type == 'iframely'){

				$embe = $video->code;
				//$embe = $video->getRichContent(true,array(),'',true);
			  //preg_match('/src="([^"]+)"/', $embe, $match);
			  //if(strpos($match[1],'https://') === false && strpos($match[1],'http://') === false){
				//$result['iframeURL']  = str_replace('//','https://',$match[1]);
			 // }else{
				$result['iframeURL']  = $embe;
			  //}
			}else{

				$storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
				if ($storage_file) {
				  $result['iframeURL'] = $this->getBaseUrl('false',$storage_file->map());
				  $result['video_extension'] = $storage_file->extension;
				}
			}
			$photo = $this->getBaseUrl(false,$video->getPhotoUrl());
			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.socialshare', 1)){
				if($photo)
			 $result["share"]["imageUrl"] = $photo;
		 	$result["share"]["url"] = $this->getBaseUrl(false,$video->getHref());
			$result["share"]["title"] = $video->getTitle();
			  $result["share"]["description"] = strip_tags($video->getDescription());
			  $result["share"]['urlParams'] = array(
				  "type" => $video->getType(),
				  "id" => $video->getIdentity()
			  );
			}
			if(is_null($response['video']["share"]["title"]))
			  unset($response['video']["share"]["title"]);
			if ($video->type == 3 && $video->status == 1) {
			  if (!empty($video->file_id)) {
				$storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
				if ($storage_file) {
				  $result['video_location']  = $storage_file->map();
				  $result['video_extension']  = $storage_file->extension;
				}
			  }
			}
			 $result['allowShowRating']  = $allowShowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.show', 1);
			$result['allowRating'] = $allowRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.video.rating', 1);
			$result['getAllowRating'] = $allowRating;
			if ($allowRating == 0) {
			  if ($allowShowRating == 0)
				$showRating = false;
			  else
				$showRating = true;
			} else
			  $showRating = true;
			$result['showRating']  = $showRating;
			if ($showRating) {
			  $result['canRate'] = $canRate = Engine_Api::_()->authorization()->isAllowed('storevideo', $viewer, 'rating');
			   $result['allowRateAgain'] = $allowRateAgain = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.again', 1);
			  $result['allowRateOwn'] = $allowRateOwn = Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.ratevideo.own', 1);
			  if ($canRate == 0 || $allowRating == 0)
				$allowRating = false;
			  else
				$allowRating = true;
			  if ($allowRateOwn == 0 && $mine)
				$allowMine = false;
			  else
				$allowMine = true;
			 $result['allowMine'] = $allowMine;
			 $result['allowRating']  = $allowRating;
			  $result['rating_type']  = $rating_type = 'storevideo';
			  $result['rating_count'] = $rating_count = Engine_Api::_()->getDbTable('ratings', 'estorevideo')->ratingCount($video->getIdentity(), $rating_type);
		  $result['rated'] = $rated = Engine_Api::_()->getDbTable('ratings', 'estorevideo')->checkRated($video->getIdentity(), $viewer->getIdentity(), $rating_type);
		  $rating_sum = Engine_Api::_()->getDbTable('ratings', 'estorevideo')->getSumRating($video->getIdentity(), $rating_type);
		  if ($rating_count != 0) {
			$result['total_rating_average']  = $rating_sum / $rating_count;
		  } else {
			$result['total_rating_average'] = 0;
		  }
		  if (!$allowRateAgain && $rated) {
				$rated = false;
			  } else {
				$rated = true;
			  }
			  $result['ratedAgain'] = $rated;
		}
		 if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.watchlater',1)){
				  if(isset($video->watchlater_id)){
				  $result['watch_later']['option']['label'] = $this->view->translate('Remove from Watch Later');
				  $result['watch_later']['option']['name'] = 'removewatchlater';
				  $result['hasWatchlater'] = true;
				  }else{
					  $result['watch_later']['option']['label'] =$this->view->translate('Add to Watch Later');
					  $result['watch_later']['option']['name'] = 'addtowatchlater';
					   $result['hasWatchlater'] = false;
				  }
			  }

		//$this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
		 $result['can_edit'] = 0;
		$result['can_delete'] = 0;
			$videoCounter = 0;
		if($viewer->getIdentity() != 0){
			$resourceItem = Engine_Api::_()->getItem('stores', $video->parent_id);
			if(count($resourceItem)>0)
			$result['resourceItem'] = $resourceItem;
			$result['parentedit'] = $parentedit = $resourceItem->authorization()->isAllowed($viewer, 'edit');
			$canEdit = $video->authorization()->isAllowed($viewer, 'edit');
			if(!$parentedit && !$canEdit){
				$result['can_edit'] = false;
			}
			else{
				$result['can_edit'] = true;
				$can[$videoCounter]['name'] = 'edit';
				$can[$videoCounter]['label'] = $this->view->translate('Edit Video');
				$videoCounter++;
			}
			$result['parentDelete'] = $parentDelete = $resourceItem->authorization()->isAllowed($viewer, 'delete');
			$canDelete = $video->authorization()->isAllowed($viewer, 'delete');
			if(!$parentDelete && !$canDelete){
				$result['can_delete'] = false;
			}
			else{
				$result['can_delete'] = true;
				$can[$videoCounter]['name'] = 'delete';
				$can[$videoCounter]['label'] = $this->view->translate('Delete Video');
				$videoCounter++;
			}

			if(Engine_Api::_()->getApi('settings', 'core')->getSetting('estorevideo.enable.report',1) && $viewer_id != $video->owner_id){
				$can[$videoCounter]['name'] = 'report';
				$can[$videoCounter]['label'] = $this->view->translate('Report');
				$videoCounter++;
			}
		}
		$rating['code']  = 100;
        $rating['message']  = '';
        $rating['total_rating_average']  = $video->rating;
		$result['rating'] = $rating;
		$data['video'] = $result;
		$data['menus'] = $can;
        $video = $video;
        if( !$video || $video->status != 1 ){
             Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('The video you are looking for does not exist or has not been processed yet.'), 'result' => array()));
        }else{
			 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => $data));
		}
	}
	public function geturlAction(){
		$video_id = $this->_getParam('video_id',null);

		if(!$video_id){
			Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' =>array()));
		}
		$video = Engine_Api::_()->getItem('storevideo', $video_id);
		//$embe = $video->code;
        echo $this->view->embe = $video->getRichContent(true,array(),'',true);
        exit;
		//Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>'parameter_missing', 'result' =>array('url'=>$embe)));

	}
    public function rateAction() {
	 if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $viewer->getIdentity();
    $rating = $this->_getParam('rating');
    $resource_id = $this->_getParam('resource_id');
    $resource_type = $this->_getParam('resource_type');
	if(!$rating || !$resource_id || !$resource_type)
		 Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$this->view->translate('parameter_missing'), 'result' => array()));
    $table = Engine_Api::_()->getDbtable('ratings', 'estorevideo');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getDbtable('ratings', 'estorevideo')->setRating($resource_id, $user_id, $rating, $resource_type);
      if ($resource_type && $resource_type == 'storevideo')
        $item = Engine_Api::_()->getItem('storevideo', $resource_id);
      $item->rating = Engine_Api::_()->getDbtable('ratings', 'estorevideo')->getRating($item->getIdentity(), $resource_type);
      $item->save();
      if ($resource_type == 'storevideo') {
        $type = 'sespgvido_rating';
      }
      $result = Engine_Api::_()->getDbtable('actions', 'activity')->fetchRow(array('type =?' => $type, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      if (!$result) {
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $item, $type);
        if ($action)
          Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $item);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
    }
    $total = Engine_Api::_()->getDbtable('ratings', 'estorevideo')->ratingCount($item->getIdentity(), $resource_type);
    $rating_sum = Engine_Api::_()->getDbtable('ratings', 'estorevideo')->getSumRating($item->getIdentity(), $resource_type);
    $data = array();
    $totalTxt = $this->view->translate(array('%s rating', '%s ratings', $total), $total);
    $data = array(
        'total' => $total,
        'rating' => $rating,
        'totalTxt' => str_replace($total, '', $totalTxt),
        'rating_sum' => $rating_sum
    );
	Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'','error_message'=>'', 'result' => array('message'=>$this->view->translate('Successfully rated.'))));
  }
    public function editVideoAction() {
    if (!$this->_helper->requireUser()->isValid())
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $viewer = Engine_Api::_()->user()->getViewer();
    $video = Engine_Api::_()->getItem('storevideo', $this->_getParam('video_id'));
    // Render
    $this->_helper->content->setEnabled();
    $this->view->parentItem = $resourceItem = Engine_Api::_()->getItem('stores', $video->parent_id);
    $canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'edit');
    $canEdit = $video->authorization()->isAllowed($viewer, 'edit');
    if(!$canEdit && !$canEditParent)
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    $form = new Sesstorevideo_Form_Edit();

	$latLng = Engine_Api::_()->getDbTable('locations', 'sesbasic')->getLocationData('storevideo',$video->video_id);
	//if($latLng){
        if($form->getElement('lat'))
            $form->removeElement('lat');
        if($form->getElement('lng'))
            $form->removeElement('lng');
        if($form->getElement('ses_location'))
            $form->removeElement('ses_location');
        if($form->getElement('map-canvas'))
            $form->removeElement('map-canvas');
        if($form->removeElement('is_locked'))
           $form->removeElement('password');
        if($form->removeElement('password'))
           $form->removeElement('password');
	//}
	if($form->getElement('location'))
	$form->getElement('location')->setValue($video->location);
    $form->getElement('search')->setValue($video->search);
    $form->getElement('title')->setValue($video->title);
    $form->getElement('description')->setValue($video->description);
    if ($form->getElement('is_locked'))
      $form->getElement('is_locked')->setValue($video->is_locked);
    if ($form->getElement('password'))
      $form->getElement('password')->setValue($video->password);
    // authorization
    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    foreach ($roles as $role) {
      if (1 === $auth->isAllowed($video, $role, 'view')) {
        $form->auth_view->setValue($role);
      }
      if (1 === $auth->isAllowed($video, $role, 'comment')) {
        $form->auth_comment->setValue($role);
      }
    }
    // prepare tags
    $videoTags = $video->tags()->getTagMaps();
    $tagString = '';
    foreach ($videoTags as $tagmap) {
      if ($tagString !== '')
        $tagString .= ', ';
      $tagString .= $tagmap->getTag()->getTitle();
    }
    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);
     if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields);
        }
        if (!$form->isValid($_POST)) {
            $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }

    if (!$this->getRequest()->isPost()) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    //if (!$form->isValid($this->getRequest()->getPost())) {
      // Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('validation_error'), 'result' => array()));
   // }
    // Process
    $db = Engine_Api::_()->getDbtable('videos', 'estorevideo')->getAdapter();
    $db->beginTransaction();
    try {
      $values = $form->getValues();
      if (isset($_FILES['photo_id']['name']) && $_FILES['photo_id']['name'] != '') {

        $values['photo_id'] = $this->setPhoto($form->photo_id, $video->video_id, true);
      } else {
        if (empty($values['photo_id'])){
          unset($values['photo_id']);
				}
      }
		if (Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('seslock'))) {
			//disable lock if password not set.
			if (!$values['is_locked']) {
				$values['is_locked'] = '0';
				$values['password'] = '';
			}else
				unset($values['password']);
		}
      if (isset($_POST['lat']) && isset($_POST['lng']) && $_POST['lat'] != '' && $_POST['lng'] != '') {
        $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
        $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type) VALUES ("' . $this->_getParam('video_id') . '", "' . $_POST['lat'] . '","' . $_POST['lng'] . '","storevideo")	ON DUPLICATE KEY UPDATE	lat = "' . $_POST['lat'] . '" , lng = "' . $_POST['lng'] . '"');
      }
      $video->setFromArray($values);
      $video->save();
      // Add fields
      $customfieldform = $form->getSubForm('fields');
      if (!is_null($customfieldform)) {
        $customfieldform->setItem($video);
        $customfieldform->saveValues();
      }
      // CREATE AUTH STUFF HERE
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_view'])
        $auth_view = $values['auth_view'];
      else
        $auth_view = "everyone";
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
      }
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      if ($values['auth_comment'])
        $auth_comment = $values['auth_comment'];
      else
        $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
      }
      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $video->tags()->setTagMaps($viewer, $tags);
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    $db->beginTransaction();
    try {
      // Rebuild privacy
      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($video) as $action) {
        $actionTable->resetActivityBindings($action);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' =>'' , 'result' => array('message'=>$this->view->translate('Video edited successfully.'),'video_id' => $video->getIdentity())));
  }
	public function deleteVideoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
	$videoid = $this->_getParam('video_id');
	if(!$videoid)
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
    $video = Engine_Api::_()->getItem('storevideo',$videoid);
    $resourceItem = Engine_Api::_()->getItem('stores', $video->parent_id);
	if(!$resourceItem)
	Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('data not found'), 'result' => array()));
    $canEdit = $video->authorization()->isAllowed($viewer, 'delete');
	$canEditParent = $resourceItem->authorization()->isAllowed($viewer, 'delete');
    if(!$canEdit && !$canEditParent)
	   Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
    if (!$video) {
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Video doesn\'t exists or not authorized to delete'), 'result' => array()));
    }
    if (!$this->getRequest()->isPost()) {
     Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('invalid_request'), 'result' => array()));
    }
    $db = $video->getTable()->getAdapter();
    $db->beginTransaction();
    try {
      Engine_Api::_()->getApi('core', 'estorevideo')->deleteVideo($video);
      $db->commit();
	  Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message'=>$this->view->translate('video deleted successfully'))));
    } catch (Exception $e) {
      $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
    }
  }

  //item liked as per item tye given
  function likeVideoAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
	    Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    $type = 'storevideo';
    $dbTable = 'videos';
    $resorces_id = 'video_id';
    $notificationType = 'liked';
    $item_id = $this->_getParam('resource_id');
    if (intval($item_id) == 0) {
		 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
    $tableMainLike = $tableLike->info('name');
    $itemTable = Engine_Api::_()->getDbtable($dbTable, 'estorevideo');
    $select = $tableLike->select()->from($tableMainLike)->where('resource_type =?', $type)->where('poster_id =?', Engine_Api::_()->user()->getViewer()->getIdentity())->where('poster_type =?', 'user')->where('resource_id =?', $item_id);
    $Like = $tableLike->fetchRow($select);
    if (count($Like) > 0) {
      //delete
      $db = $Like->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Like->delete();
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully Unliked.');
      } catch (Exception $e) {
        $db->rollBack();
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //$itemTable->update(array(
        //  'like_count' => new Zend_Db_Expr('like_count - 1'),
          //    ), array(
          //$resorces_id . ' = ?' => $item_id,
     // ));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
       //$temp['like_count'] = $item->like_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('likes', 'core')->getAdapter();
      $db->beginTransaction();
      try {
        $like = $tableLike->createRow();
        $like->poster_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $like->resource_type = $type;
        $like->resource_id = $item_id;
        $like->poster_type = 'user';
        $like->save();
        $itemTable->update(array(
            'like_count' => new Zend_Db_Expr('like_count + 1'),
                ), array(
            $resorces_id . '= ?' => $item_id,
        ));
        // Commit
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully liked.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem($type, $item_id);
      $subject = $item;
      $owner = $subject->getOwner();
      if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
        Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
        $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
        if (!$result) {
          $action = $activityTable->addActivity($viewer, $subject, $notificationType);
          if ($action)
            $activityTable->attachActivity($action, $subject);
        }
      }
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }

   function favouriteVideoAction() {
    if (Engine_Api::_()->user()->getViewer()->getIdentity() == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
    }
    $type = 'storevideo';
    $dbTable = 'videos';
    $resorces_id = 'video_id';
    $notificationType = 'sespgvido_fav';

    $item_id = $this->_getParam('resource_id');
    if (intval($item_id) == 0) {
      Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Invalid argument supplied.'), 'result' => array()));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $Fav = Engine_Api::_()->getDbTable('favourites', 'estorevideo')->getItemfav($type, $item_id);
    $favItem = Engine_Api::_()->getDbtable($dbTable, 'estorevideo');
    if (count($Fav) > 0) {
      //delete
      $db = $Fav->getTable()->getAdapter();
      $db->beginTransaction();
      try {
        $Fav->delete();
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully unfavourited.');
      } catch (Exception $e) {
        $db->rollBack();
       Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count - 1')), array($resorces_id . ' = ?' => $item_id));
      $item = Engine_Api::_()->getItem($type, $item_id);
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $item->getType(), "object_id = ?" => $item->getIdentity()));
      Engine_Api::_()->getDbtable('actions', 'activity')->detachFromActivity($item);
	  //$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    } else {
      //update
      $db = Engine_Api::_()->getDbTable('favourites', 'estorevideo')->getAdapter();
      $db->beginTransaction();
      try {
        $fav = Engine_Api::_()->getDbTable('favourites', 'estorevideo')->createRow();
        $fav->user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $fav->resource_type = $type;
        $fav->resource_id = $item_id;
        $fav->save();
        $favItem->update(array('favourite_count' => new Zend_Db_Expr('favourite_count + 1'),
                ), array(
            $resorces_id . '= ?' => $item_id,
        ));
        // Commit
        $db->commit();
		$temp['message'] = $this->view->translate('Video Successfully favourited.');
      } catch (Exception $e) {
        $db->rollBack();
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
      }
      //send notification and activity feed work.
      $item = Engine_Api::_()->getItem(@$type, @$item_id);
      if ($this->_getParam('type') != 'estorevideo_artist') {
        $subject = $item;
        $owner = $subject->getOwner();
        if ($owner->getType() == 'user' && $owner->getIdentity() != $viewer->getIdentity()) {
          $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
          Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($owner, $viewer, $subject, $notificationType);
          $result = $activityTable->fetchRow(array('type =?' => $notificationType, "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subject->getType(), "object_id = ?" => $subject->getIdentity()));
          if (!$result) {
            $action = $activityTable->addActivity($viewer, $subject, $notificationType);
            if ($action)
              $activityTable->attachActivity($action, $subject);
          }
        }
      }
       //$temp['data']['favourite_count'] = $item->favourite_count;
		Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => $temp));
    }
  }


	public function browsealbumAction(){
        // Default param options
        if (count($_POST)) {
            $params = $_POST;
        }
        $searchArray = array();
        if (isset($_POST['searchParams']) && $_POST['searchParams'])
            parse_str($_POST['searchParams'], $searchArray);
        $value['store'] = isset($_POST['store']) ? $_POST['store'] : 1;
        $value['sort'] = isset($searchArray['sort']) ? $searchArray['sort'] : (isset($_GET[' ']) ? $_GET['sort'] : (isset($params['sort']) ? $params['sort'] : $this->_getParam('sort', 'mostSPliked')));
        $value['show'] = isset($searchArray['show']) ? $searchArray['show'] : (isset($_GET['show']) ? $_GET['show'] : (isset($params['show']) ? $params['show'] : ''));
        $value['search'] = isset($searchArray['search']) ? $searchArray['search'] : (isset($_GET['search']) ? $_GET['search'] : (isset($params['search']) ? $params['search'] : ''));
        $value['user_id'] = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($params['user_id']) ? $params['user_id'] : '');
        $value['show_criterias'] = isset($params['show_criterias']) ? $params['show_criterias'] : $this->_getParam('show_criteria', array('like', 'comment', 'by', 'title', 'socialSharing', 'view', 'photoCount', 'favouriteCount', 'favouriteButton', 'likeButton', 'featured', 'sponsored'));
        foreach ($value['show_criterias'] as $show_criteria)
            if (isset($value['sort']) && $value['sort'] != '') {
                $value['getParamSort'] = str_replace('SP', '_', $value['sort']);
            } else {
                $value['getParamSort'] = 'creation_date';
            }
        switch ($value['getParamSort']) {
            case 'most_viewed':
                $value['order'] = 'view_count';
                break;
            case 'most_favourite':
                $value['order'] = 'favourite_count';
                break;
            case 'most_liked':
                $value['order'] = 'like_count';
                break;
            case 'most_commented':
                $value['order'] = 'comment_count';
                break;
            case 'featured':
                $value['order'] = 'featured';
                break;
            case 'sponsored':
                $value['order'] = 'sponsored';
                break;
            case 'creation_date':
            default:
                $value['order'] = 'creation_date';
                break;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        //Check for deault album
        $value['showdefaultalbum'] = 0;
        $value['store_id'] = $this->_getParam('store_id',0);
        $paginator = Engine_Api::_()->getDbTable('albums', 'estore')->getAlbumSelect($value);
        $paginator->setItemCountPerPage($this->_getParam('limit', 1));
        $paginator->setCurrentPageNumber($this->_getParam('store', 10));
        $albumCounter = 0;
        foreach ($paginator as $item) {
            $owner = $item->getOwner();
            $ownertitle = $owner->displayname;
            $result['albums'][$albumCounter] = $item->toArray();
            $result['albums'][$albumCounter]['images'] = Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "") ? Engine_Api::_()->sesapi()->getPhotoUrls($item, '', "") : $result['members'][$counterLoop]['owner_photo'] = $this->getBaseUrl(true, '/application/modules/User/externals/images/nophoto_user_thumb_icon.png');
            $result['albums'][$albumCounter]['user_title'] = $ownertitle;
            $albumLikeStatus = Engine_Api::_()->estore()->getLikeStatus($item->getIdentity(), $item->getType());
            $albumFavStatus = Engine_Api::_()->getDbTable('favourites', 'estore')->isFavourite(array('resource_type' => 'album', 'resource_id' => $item->album_id));
            if ($albumLikeStatus) {
                $result['albums'][$albumCounter]['is_content_like'] = true;
            } else {
                $result['albums'][$albumCounter]['is_content_like'] = false;
            }
            if ($albumFavStatus) {
                $result['albums'][$albumCounter]['is_content_favourite'] = true;
            } else {
                $result['albums'][$albumCounter]['is_content_favourite'] = false;
            }
            $albumCounter++;
        }
        $canCreate = Engine_Api::_()->authorization()->isAllowed('album', null, 'create');
        $result['can_create'] = $canCreate?true:false;
        $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
        $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
        $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
        $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page'] + 1;
        Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => $result), $extraParams));
    }

    public function albumsearchformAction(){
        $filterOptions = (array)$this->_getParam('search_type', array('recentlySPcreated' => 'Recently Created', 'mostSPviewed' => 'Most Viewed', 'mostSPliked' => 'Most Liked', 'mostSPcommented' => 'Most Commented', 'mostSPfavourite' => 'Most Favourite'));
        $search_for = $this->_getParam('search_for', 'album');
        $default_search_type = $this->_getParam('default_search_type', 'mostSPliked');
        $searchForm = new Sesstore_Form_AlbumSearch(array('searchTitle' => $this->_getParam('search_title', 'yes'), 'browseBy' => $this->_getParam('browse_by', 'yes'), 'searchFor' => $search_for, 'FriendsSearch' => $this->_getParam('friend_show', 'yes'), 'defaultSearchtype' => $default_search_type));
        if (isset($_GET['tag_name'])) {
            $searchForm->getElement('search')->setValue($_GET['tag_name']);
        }
        if ($this->_getParam('search_type') !== null && $this->_getParam('browse_by', 'yes') == 'yes') {
            $arrayOptions = $filterOptions;
            $filterOptions = array();
            foreach ($arrayOptions as $filterOption) {
                $value = str_replace(array('SP', ''), array(' ', ' '), $filterOption);
                $filterOptions[$filterOption] = ucwords($value);
            }
            $filterOptions = array('' => '') + $filterOptions;
            $searchForm->sort->setMultiOptions($filterOptions);
            $searchForm->sort->setValue($default_search_type);
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $searchForm->setMethod('get')->populate($request->getParams());
        $searchForm->removeElement('loading-img-estore');
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($searchForm);
            $this->generateFormFields($formFields);
        } else {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array())));

        }
    }

	function getButtonMenus($stores){
        $viewer = $this->view->viewer();
        $showLoginformFalse = false;
         if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.enable.contact.details', 1)) {
            $showLoginformFalse = true;
        }
        $shareType = Engine_Api::_()->getApi('settings', 'core')->getSetting('estore.allow.share', 0);
        $i = 0;
        if ($stores->store_contact_email || $stores->store_contact_phone || $stores->store_contact_website) {
            if ($stores->store_contact_email) {

                $result[$i]['name'] = 'mail';
                $result[$i]['label'] = 'Send Email';
                $result[$i]['value'] = $stores->store_contact_email;
                $i++;

            }
            if ($stores->store_contact_phone) {
                $result[$i]['name'] = 'phone';
                $result[$i]['label'] = 'Call';
                $result[$i]['value'] = $stores->store_contact_phone;
                $i++;
            }
            if ($stores->store_contact_website) {

                $result[$i]['name'] = 'website';
                $result[$i]['label'] = 'Visit Website';
                $result[$i]['value'] = $stores->store_contact_website;
                $i++;
            }
        }

    if ($stores->is_approved) {
        $result[$i]['name'] = 'contact';
        $result[$i]['label'] = 'Contact';
        $i++;
        if ($shareType) {
            $result[$i]['name'] = 'share';
            $result[$i]['label'] = 'Share';
            $i++;
        }
        if ($viewerId) {
          $row = $stores->membership()->getRow($viewer);
          if (null === $row) {
              if ($stores->membership()->isResourceApprovalRequired()) {
                  $result[$i]['name'] = 'request';
                  $result[$i]['label'] = 'Request Membership';
                  $i++;
              } else {
                  $result[$i]['name'] = 'join';
                  $result[$i]['label'] = 'Join Store';
                  $i++;
              }
          } else if ($row->active) {
              if (!$stores->isOwner($viewer)) {
                  $result[$i]['label'] = 'Leave Store';
                  $result[$i]['name'] = 'leave';
                  $i++;
              }
          } else if (!$row->resource_approved && $row->user_approved) {
              $result[$i]['label'] = 'Cancel Membership Request';
              $result[$i]['name'] = 'cancel';
              $i++;

          } else if (!$row->user_approved && $row->resource_approved) {
              $result[$i]['label'] = 'Accept Membership Request';
              $result[$i]['name'] = 'accept';
              $i++;
              $result[$i]['label'] = 'Ignore Membership Request';
              $result[$i]['name'] = 'reject';
          }
      }

    }
      return $result;
}
    public function createalbumAction(){

        $store_id = $this->_getParam('store_id', false);
        $album_id = $this->_getParam('album_id', 0);
        if ($album_id) {
            $album = Engine_Api::_()->getItem('estore_album', $album_id);
            $store_id = $album->store_id;
        } else {
            $store_id = $store_id;
        }
        $store = Engine_Api::_()->getItem('stores', $store_id);
		if(!$store_id)
			 Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array())));
        // set up data needed to check quota
        $viewer = Engine_Api::_()->user()->getViewer();
        $values['user_id'] = $viewer->getIdentity();
        $current_count = Engine_Api::_()->getDbTable('albums', 'estore')->getUserAlbumCount($values);
        $quota = $quota = 0;
        // Get form
        $form = new Sesstore_Form_Album();
        $form->removeElement('fancyuploadfileids');
        $form->removeElement('tabs_form_albumcreate');
        $form->removeElement('drag-drop');
        $form->removeElement('from-url');
        $form->removeElement('file_multi');
        $form->removeElement('uploadFileContainer');

        // Render
        $form->populate(array('album' => $album_id));
        if ($this->_getParam('getForm')) {
            $formFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->generateFormFields($form);
            $this->generateFormFields($formFields, array('resources_type' => 'stores'));
        }
        if (!$form->isValid($this->getRequest()->getPost())){
          $validateFields = Engine_Api::_()->getApi('FormFields', 'sesapi')->validateFormFields($form);
            if (count($validateFields))
                $this->validateFormFields($validateFields);
        }
        $db = Engine_Api::_()->getItemTable('estore_album')->getAdapter();
        $db->beginTransaction();
        try {
            $photoTable = Engine_Api::_()->getDbTable('photos', 'estore');
			$uploadSource = $_FILES['image'];
			$photoArray = array(
            'store_id' => $store->store_id,
            'user_id' => $viewer->getIdentity(),
            'title' => '',
        );
        $photosource = array();
        $counter = 0;
        // Process
        $db = Engine_Api::_()->getDbtable('photos', 'estore')->getAdapter();
        $db->beginTransaction();
        try {
                $images['name'] = $name;
                $images['tmp_name'] = $uploadSource['tmp_name'][$counter];
                $images['error'] = $uploadSource['error'][$counter];
                $images['size'] = $uploadSource['size'][$counter];
                $images['type'] = $uploadSource['type'][$counter];
                $photo = $photoTable->createRow();
                $photo->setFromArray($photoArray);
                $photo->save();
				$albumdata = $album?$album:false;
                $photo = $photo->setAlbumPhoto($uploadSource, false, false, $albumdata);
                $photo->collection_id = $photo->album_id;
                $photo->save();
                $photosource[] = $photo->getIdentity();
                $counter++;
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
        $_POST['store_id'] = $store->store_id;
        $_POST['file'] = implode(' ', $photosource);
            $album = $form->saveValues();
            // Add tags
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Successfully Created.'), album_id => $album->getIdentity()))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array_merge(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array())));
        }
    }

    public function acceptAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject('stores')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $membership_status = $subject->membership()->getRow($viewer)->active;
            $subject->membership()->setUserApproved($viewer);
            $row = $subject->membership()->getRow($viewer);
            $row->save();
            // Add activity
            if (!$membership_status) {
                $activityApi = Engine_Api::_()->getDbTable('actions', 'activity');
                $action = $activityApi->addActivity($viewer, $subject, 'estore_store_join');
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('You have accepted the invite to the store'),'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }

    }

    public function rejectAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('stores')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_misssing', 'result' => array()));
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $user = Engine_Api::_()->getItem('user', (int)$this->_getParam('user_id'));
            $subject->membership()->removeMember($user);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'estore_reject');
            // Set the request as handled
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $viewer, $subject, 'estore_invite');
            if ($notification) {
                $notification->mitigated = true;
                $notification->save();
            }
            $db->commit();
            $message = Zend_Registry::get('Zend_Translate')->_('You have ignored the invite to the store');
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $message,'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

    public function removeAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        // Get user
        if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('member does not exist.'), 'result' => array()));
        }
        $store = Engine_Api::_()->core()->getSubject();
        if (!$store->membership()->isMember($user)) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('Cannot remove a non-member.'), 'result' => array()));
        }
        $db = $store->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            // Remove membership
            $store->membership()->removeMember($user);
            // Remove the notification?
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $store->getOwner(), $store, 'estore_approve');
            if ($notification) {
                $notification->delete();
            }
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '', 'error_message' => '', 'result' => array('message' => $this->view->translate('The selected member has been removed from this store.'),'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }
    }

    public function approveAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'user_not_autheticate', 'result' => array()));
        if (!$this->_helper->requireSubject('stores')->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => 'parameter_missing', 'result' => array()));
        // Get user
        if (0 === ($user_id = (int)$this->_getParam('user_id')) ||
            null === ($user = Engine_Api::_()->getItem('user', $user_id))) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => $this->view->translate('user does not exist.'), 'result' => array()));
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            $subject->membership()->setResourceApproved($user);
            Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($user, $viewer, $subject, 'estore_accepted');
            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Store request approved'),'menus'=>$this->getButtonMenus($subject))));
        } catch (Exception $e) {
            $db->rollBack();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $e->getMessage(), 'result' => array()));
        }

    }

    public function cancelAction()
    {
        // Check auth
        if (!$this->_helper->requireUser()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        if (!$this->_helper->requireSubject()->isValid())
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('parameter_missing'), 'result' => array()));


        $user_id = $this->_getParam('user_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = Engine_Api::_()->core()->getSubject();
        if (!$subject->authorization()->isAllowed($viewer, 'invite') &&
            $user_id != $viewer->getIdentity() &&
            $user_id) {
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
        }

        if ($user_id) {
            $user = Engine_Api::_()->getItem('user', $user_id);
            if (!$user) {
                Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' => $this->view->translate('user_not_autheticate'), 'result' => array()));
            }
        } else {
            $user = $viewer;
        }

        $subject = Engine_Api::_()->core()->getSubject('stores');
        $db = $subject->membership()->getReceiver()->getTable()->getAdapter();
        $db->beginTransaction();
        try {
            $subject->membership()->removeMember($user);

            // Remove the notification?
            $notification = Engine_Api::_()->getDbTable('notifications', 'activity')->getNotificationByObjectAndType(
                $subject->getOwner(), $subject, 'estore_approve');
            if ($notification) {
                $notification->delete();
            }

            $db->commit();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '0', 'error_message' => '', 'result' => array('message' => $this->view->translate('Your invite request has been cancelled.'),'menus'=>$this->getButtonMenus($subject))));

        } catch (Exception $e) {
            $db->rollBack();
            $message = $e->getMessage();
            Engine_Api::_()->getApi('response', 'sesapi')->sendResponse(array('error' => '1', 'error_message' =>$e->getMessage(), 'result' => array()));
        }
    }

}
