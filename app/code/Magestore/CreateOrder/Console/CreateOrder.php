<?php
namespace Magestore\CreateOrder\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magestore\CreateOrder\Model\Create as OrderCreate;

class CreateOrder extends Command
{

    protected $numberOrders = '1';
    protected $type = '1';
	/**
	* OrderCreate $order
	**/
	protected $order;

//	public function __construct(
//        OrderCreate $orderCreate
//	)
//    {
//        $this->order = $orderCreate;
//        parent::__construct();
//    }
   protected function configure()
   {
       $this->setName('magestore:createorder');
       $this->setDescription('Create orders command line');
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
	   try{
//           $this->order->create($this->numberOrders, $this->type);
//		   $output->writeln(__('%1 order(s) was created successfully!', $this->numberOrders));
		   $output->writeln('Cannot create order. Please use controller');
	   }catch(Exception $e){
		   $output->writeln($e->getMessage());
	   }
   }
}