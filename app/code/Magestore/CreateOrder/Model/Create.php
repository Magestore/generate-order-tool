<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\CreateOrder\Model;

use Magento\Sales\Model\Order;

class Create extends \Magento\Sales\Model\AdminOrder\Create
{

    const CUSTOMER_ID = 1;
    const KEY_METHOD = 'method';
    const KEY_DATA = 'method_data';

    /**
     * Cart Items
     */
    const KEY_ID = 'id';
    const KEY_QTY = 'qty';
    const MAX_PRODUCTS = 3;
    const MAX_IDS = 20;
    const MAX_QTY = 4;

    /**
     * Add multiple products to current order quote
     *
     * @param array $products
     * @return $this
     */
    public function addProducts(array $products)
    {
        foreach ($products as $productConfig) {
            $productConfig['qty'] = isset($productConfig['qty']) ? (double)$productConfig['qty'] : 1;
            try {
                $this->addProduct($productConfig[self::KEY_ID], $productConfig);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }
        }

//        return $this;
    }

    /**
     * Add product to current order quote
     * $product can be either product id or product model
     * $config can be either buyRequest config, or just qty
     *
     * @param int|\Magento\Catalog\Model\Product $webposProduct
     * @param array|float|int|\Magento\Framework\DataObject $productConfig
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProduct($webposProduct, $productConfig = 1)
    {
        if (!is_array($productConfig) && !$productConfig instanceof \Magento\Framework\DataObject) {
            $productConfig = ['qty' => $productConfig];
        }

        $productConfig = new \Magento\Framework\DataObject($productConfig);

        if (!$webposProduct instanceof \Magento\Catalog\Model\Product) {
            $productId = $webposProduct;
            $webposProduct = $this->_objectManager->create(
                'Magento\Catalog\Model\Product'
            )->setStore(
                $this->getSession()->getStore()
            )->setStoreId(
                $this->getSession()->getStoreId()
            )->load(
                $webposProduct
            );
            if (!$webposProduct->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We could not add a product to cart by the ID "%1".', $productId)
                );
            }
        }
        $item = $this->quoteInitializer->init($this->getQuote(), $webposProduct, $productConfig);
        if (is_string($item)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($item));
        }
        $item->checkData();
        $checkPromotion = $this->getSession()->getData('checking_promotion');
        if(!$checkPromotion){
            $item->setNoDiscount(true);
        }
        $this->setRecollect(true);
//        return $this;
    }

    /**
     * Retrieve quote object model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getSession()->getQuote();
    }
    /**
     * Set quote object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        return parent::setQuote($quote);
    }

    /**
     * Quote saving
     *
     * @return $this
     */
    public function saveQuote()
    {
        return parent::saveQuote();
    }


    /**
     * Validate quote data before order creation
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _validate()
    {
        return $this;
    }

    /**
     *
     * @param string $shippingMethod
     * @return \Magestore\Webpos\Model\Checkout\Create
     */
    protected  function _saveShippingMethod($shippingMethod){
        $quote = $this->getQuote();
        if (!$quote->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $this->setShippingAsBilling(1);
        }
        $this->setShippingMethod($shippingMethod);
        $this->collectShippingRates();
        return $this;
    }

    /**
     *
     * @param \Magento\Quote\Api\Data\PaymentInterface $payment
     * @return \Magestore\Webpos\Model\Checkout\Create
     */
    protected  function _savePaymentData($payment){
        $quote = $this->getQuote();
        $session = $this->getSession();
        $data = [];
        if(isset($payment[self::KEY_METHOD])){
            $data['method'] = $payment[self::KEY_METHOD];
        }
        $additional_information = [];
        if(isset($payment[self::KEY_DATA]) && count($payment[self::KEY_DATA]) > 0){
            $modelCurrency = $this->_objectManager->create("Magento\Framework\Locale\Currency");
            foreach ($payment[self::KEY_DATA] as $methodData){
                $additional_information[] = $modelCurrency->getCurrency($session->getCurrencyId())
                        ->toCurrency($methodData->getAmount()).' : '.$methodData->getTitle();
            }
        }
        if(count($additional_information)>0)
            $data['additional_information'] = $additional_information;
        $quote->getPayment()->addData($data);
        return $this;
    }

    /**
     *
     * @param \Magestore\Webpos\Api\Data\CartItemInterface[] $items
     * @return \Magestore\Webpos\Model\Checkout\Create
     */
    protected function _processCart($items){
        if (isset($items) && count($items) > 0) {
            $products = [];
            foreach ($items as $item){
                $product = [];
                $product[self::KEY_ID] = $item->getId();
                $product[self::KEY_QTY] = $item->getQty();
                $products[] = $product;
            }
            $this->addProducts($products);
        }
        return $this;
    }

    public function getCustomItems(){
		$items = [];
		for($i=0; $i< rand(1, self::MAX_PRODUCTS); $i++){
			$item = new \Magento\Framework\DataObject();
			$item->setData('id', rand(1,self::MAX_IDS));
			$item->setData('qty', rand(1,self::MAX_QTY));
			$items[$i] = $item;
		}
        // $item1 = new \Magento\Framework\DataObject();
        // $item1->setData('id', rand(1,20));
        // $item1->setData('qty', rand(1,4));
        // $item2 = new \Magento\Framework\DataObject();
        // $item2->setData('id', rand(1,20));
        // $item2->setData('qty', rand(1,4));
        // $item3 = new \Magento\Framework\DataObject();
        // $item3->setData('id', rand(1,20));
        // $item3->setData('qty', rand(1,4));
        // $items = [
                // $item1,
                // $item2,
                // $item3
        // ];
        return $items;
    }

    public function getCustomAddress($customerId){
        $name = 'King';
        if($customerId)
            $name = 'Veronica';
        $region = new \Magento\Framework\DataObject();
        $region->setData('region_code', 'MI');
        $region->setData('region', 'Michigan');
        $region->setData('region_id', 33);

        $address = new \Magento\Framework\DataObject();
        $address->setData('id', 1);
        $address->setData('customer_id', $customerId);
        $address->setData('city', 'Calder');
        $address->setData('country_id', 'US');
        $address->setData('default_billing', true);
        $address->setData('default_shipping', true);
        $address->setData('firstname', $name);
        $address->setData('lastname', 'Costello');
        $address->setData('postcode', '49628-7978');
        $address->setData('region_id', 33);
        $address->setData('street', ['6146 Honey Bluff Parkway']);
        $address->setData('telephone', 33);
        $address->setData('region', $region);

        return $address;
    }

    public function getCustomPayments($customerId){
        $address = $this->getCustomAddress($customerId);
        $methodData = new \Magento\Framework\DataObject();
        $methodData->setData('additional_data', []);
        $methodData->setData('code', 'checkmo');
//        $methodData->setData('amount', '121.09');
//        $methodData->setData('base_amount', '121.09');
//        $methodData->setData('base_real_amount', '121.09');
//        $methodData->setData('real_amount', '121.09');
//        $methodData->setData('is_pay_later', 0);
//        $methodData->setData('shift_id', null);
        $methodData->setData('title', 'Check / Money order');
        $payment = new \Magento\Framework\DataObject();
        $payment->setData('address', $address);
        $payment->setData('method', 'multipaymentforpos');
        $payment->setData('method_data', [$methodData]);
        return $payment;
    }

    public function getCustomShipping($customerId){
        $address = $this->getCustomAddress($customerId);
        $shipping = new \Magento\Framework\DataObject();
        $shipping->setData('address', $address);
        $shipping->setData('datetime', '');
        $shipping->setData('method', 'flatrate_flatrate');
        $shipping->setData('track', []);
        return $shipping;
    }

    public function getCustomConfig(){
        $config = new \Magento\Framework\DataObject();
        $config->setApplyPromotion(0);
        $config->setCartBaseDiscountAmount(0);
        $config->setCartDiscountAmount(0);
        $config->setCartDiscountName(0);
        $config->setCreateInvoice(false);
        $config->setCurrencyCode('USD');
        $config->setNote();
        return $config;
    }

    public function create($numberOrder = 1, $type = 1){
        $customerId = ($type ==1) ? self::CUSTOMER_ID : null;
        $payment = $this->getCustomPayments($customerId);
        $shipping = $this->getCustomShipping($customerId);
        $config = $this->getCustomConfig();
        for ($i=0; $i<$numberOrder; $i++) {
			$items = $this->getCustomItems();
            $session = $this->getSession();
            $order = $this->createOrderByParams($session, $customerId, $items, $payment, $shipping, $config);
            if ($order) {
                $order->setIncrementId(null);
                $order->save();
                $session->clearStorage();
            } else {
                throw new \Exception(__('Cannot create order %1!', $i));
            }
        }
    }

    public function createOrderByParams($session, $customerId, $items, $payment, $shipping, $config){
        $session->clearStorage();
        $store = $session->getStore();
        $storeId = $store->getId();
        $session->setCurrencyId($config->getCurrencyCode());
        $session->setStoreId($storeId);
        $session->setData('checking_promotion',false);
//        $session->setData('webpos_order', 1);
        $store->setCurrentCurrencyCode($config->getCurrencyCode());
        $this->getQuote()->setQuoteCurrencyCode($config->getCurrencyCode());
        if ($customerId) {
            $customerResource = $this->_objectManager->create('Magento\Customer\Model\ResourceModel\Customer');
            $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $customerResource->load($customerModel, $customerId);
            if ($customerModel->getId()) {
                $session->setCustomerId($customerId);
                $this->getQuote()->setCustomer($customerModel->getDataModel());
            }
        }else{
            $this->getQuote()->setCustomerIsGuest(true);
            $this->getQuote()->setCustomerEmail('s@e.com');
        }
        $this->_processCart($items);
        $this->setWebPosBillingAddress($payment);
        if(!$this->getQuote()->isVirtual()){
            $this->setWebPosShippingAddress($shipping);
            $this->_saveShippingMethod($shipping->getMethod());
        }
        $this->_savePaymentData($payment);
        $this->getQuote()->getShippingAddress()->unsCachedItemsAll();
        $this->getQuote()->setTotalsCollectedFlag(false);
        $this->quoteRepository->save($this->getQuote());
        $order = $this->createOrder();
        return $order;
    }

    /**
     * set billing address
     *
     * @param array, array
     *
     * @return void
     */
    public  function setWebPosBillingAddress($payment){
        if(!empty($payment->getAddress())){
            $billingData = $payment->getAddress()->getData();
            if(empty($billingData['id']) || strpos($billingData['id'], "nsync") !== false ){
                unset($billingData['id']);
            }
            $billingData['saveInAddressBook'] = false;
            if(isset($billingData['region'])){
                $region = $billingData['region'];
                $billingData['region'] = [
                    'region' => $region->getRegion(),
                    'region_id' => $region->getRegionId(),
                    'region_code' => $region->getRegionCode()
                ];
            }
            $this->setBillingAddress($billingData);
        }
    }

    /**
     * set shipping address
     *
     * @param array, array
     *
     * @return void
     */
    public function setWebPosShippingAddress($shipping){
        if(!empty($shipping->getAddress())){
            $shippingData = $shipping->getAddress()->getData();
            if(empty($shippingData['id']) || strpos($shippingData['id'], "nsync") !== false ){
                unset($shippingData['id']);
            }
            $shippingData['saveInAddressBook'] = false;
            if(isset($shippingData['region'])){
                $region = $shippingData['region'];
                $shippingData['region'] = [
                    'region' => $region->getRegion(),
                    'region_id' => $region->getRegionId(),
                    'region_code' => $region->getRegionCode()
                ];
            }
            $this->setShippingAddress($shippingData);
        }
    }

}