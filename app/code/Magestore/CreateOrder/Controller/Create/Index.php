<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\CreateOrder\Controller\Create;
/**
 * Class Index
 * @package Magestore\Webpos\Controller\Create
 */
class Index extends \Magento\Framework\App\Action\Action
{
    protected $numberOrders = '1';
    protected $type = 0;
    /**
     * \Magestore\CreateOrder\Model\Create
     **/
    protected $order;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magestore\CreateOrder\Model\Create $order
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magestore\CreateOrder\Model\Create $order
    ){
        $this->order = $order;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $numberOrders = $this->getNumberOrders();
        $type = $this->getType();
        $shippingMethod = $this->getShippingMethod();
//        try{
            $this->order->create($numberOrders, $type, $shippingMethod);
            echo __('%1 order(s) was created successfully!', $numberOrders).'<br/>';
//        }catch(Exception $e){
//            echo $e->getMessage();
//        }

        echo '<script>
                setTimeout(function () {
                    location.reload()
                }, '.$this->getTimeStep().');
              </script>';
    }

    public function getNumberOrders() {
        return $this->getRequest()->getParam('number_orders') ? : rand(1,5);
    }

    public function getType() {
        return $this->getRequest()->getParam('use_guest') ? : rand(0,1);
    }

    public function getShippingMethod() {
        return $this->getRequest()->getParam('shipping_method') ? : 'flatrate_flatrate';
    }

    public function getTimeStep() {
        return $this->getRequest()->getParam('time_step') ? : 2000000;
    }
}
