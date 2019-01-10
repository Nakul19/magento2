<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\CustomerGraphQl\Model\Resolver\Address;

use Dentalkart\CustomerGraphQl\Model\Customer\CheckCustomerAccount;
use Dentalkart\CustomerGraphQl\Model\Customer\Address\UpdateExistingAddress;
use Dentalkart\CustomerGraphQl\Model\Customer\CustomerDataProvider;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Update customer data resolver
 */
class UpdateAddress implements ResolverInterface
{
    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @var UpdateExistingAddress
     */
    private $updateExistingAddress;

    /**
     * @var CustomerDataProvider
     */
    private $customerDataProvider;

    /**
     * @param CheckCustomerAccount $checkCustomerAccount
     * @param UpdateExistingAddress $updateExistingAddress
     * @param CustomerDataProvider $customerDataProvider
     */
    public function __construct(
        CheckCustomerAccount $checkCustomerAccount,
        UpdateExistingAddress $updateExistingAddress,
        CustomerDataProvider $customerDataProvider
    ) {
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->updateExistingAddress = $updateExistingAddress;
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
        $this->updateExistingAddress->execute($currentUserId, $args['input']);

        $data = $this->customerDataProvider->getCustomerById($currentUserId);
        return ['customer' => $data];

    }
}
