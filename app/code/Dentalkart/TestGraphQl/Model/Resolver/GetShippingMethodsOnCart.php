<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\TestGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\ArrayManager;
use Dentalkart\TestGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address;

class GetShippingMethodsOnCart implements ResolverInterface
{
    /**
     * @var ShippingMethodManagement
     */
    private $shippingMethodManagement;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
    * @var AddressInterface
    */
    private $addressInterface;

    /**
     * @var Address
     */
    private $addressModel;

    /**
     * @param ArrayManager $arrayManager
     * @param GetCartForUser $getCartForUser
     * @param ShipmentEstimationInterface $shipmentEstimationInterface
     */
    public function __construct(
        ArrayManager $arrayManager,
        GetCartForUser $getCartForUser,
        ShipmentEstimationInterface $shipmentEstimationInterface,
        AddressInterface $addressInterface,
        Address $addressModel,
        ShippingMethodManagement $shippingMethodManagement
    ) {
        $this->arrayManager = $arrayManager;
        $this->getCartForUser = $getCartForUser;
        $this->shipmentEstimationInterface = $shipmentEstimationInterface;
        $this->addressInterface = $addressInterface;
        $this->addressModel = $addressModel;
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $address = $this->arrayManager->get('input/address', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $shippingAddress = $this->addressModel->addData($address);

        // foreach ($shippingAddress as $key => $value) {
        //     $this->addressInterface[$key] = $value;
        // }



        $userId = $context->getUserId();

        // $data['shippingMethods'] = array (
        //   0 => 
        //     array (
        //         'carrier_code' => 'tablerate',
        //         'method_code' => 'bestway',
        //         'carrier_title' => 'Delivery Charge',
        //         'amount' => 8976,
        //         'base_amount' => 8976,
        //         'available' => true,
        //         'error_message' => '',
        //         'price_excl_tax' => 8976,
        //         'price_incl_tax' => 8976,
        //     ),
        // );

        $data['shippingMethods'] = $this->getShippingMethods($maskedCartId, $shippingAddress, $userId);

        return $data;
    }

   	/**
     * @param mixed $cartId
     * @param Address $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods
    */
    public function getShippingMethods($maskedCartId, Address $address, $userId){
    	try{

    		$cart = $this->getCartForUser->execute((string) $maskedCartId, $userId);    
    		$cartId = $cart->getId();

    		$methods = $this->shippingMethodManagement->estimateByExtendedAddress($cartId, $address);


    	} catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    	return $methods;
    }

}
