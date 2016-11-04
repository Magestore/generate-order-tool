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
        if($this->getRequest()->getParam('number_orders'))
            $this->numberOrders = $this->getRequest()->getParam('number_orders');
        if($this->getRequest()->getParam('use_guest'))
            $this->type = $this->getRequest()->getParam('use_guest');
        try{
           $this->order->create($this->numberOrders, $this->type);
            echo __('%1 order(s) was created successfully!', $this->numberOrders).'<br/>';
        }catch(Exception $e){
            \Zend_Debug::dump($e->getMessage());
        }
    }
}
