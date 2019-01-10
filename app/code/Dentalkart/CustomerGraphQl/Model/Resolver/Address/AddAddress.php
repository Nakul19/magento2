<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\CustomerGraphQl\Model\Resolver\Address;

use Dentalkart\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Dentalkart\CustomerGraphQl\Model\Customer\Address\AddNewAddress;
use Dentalkart\CustomerGraphQl\Model\Customer\CustomerDataProvider;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Update customer data resolver
 */
class AddAddress implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var AddNewAddress
     */
    private $addNewAddress;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param AddNewAddress $addNewAddress
     * @param CustomerDataProvider $customerDataProvider
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        AddNewAddress $addNewAddress,
        CustomerDataProvider $customerDataProvider
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->addNewAddress = $addNewAddress;
        $this->customerDataProvider = $customerDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();

        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        $currentUserId = (int)$currentUserId;
        $addAddressResponse = $this->addNewAddress->execute($currentUserId, $args['input']);
        if ($addAddressResponse) {
            $data = $this->customerDataProvider->getCustomerById($currentUserId);
            return ['customer' => $data];
        }
    }
}
