<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Update account information
 */
class AddNewAddress
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ObjectManagerInterface $objectManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * Update account information
     *
     * @param int $customerId
     * @param array $data
     * @return bool
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     * @throws GraphQlAlreadyExistsException
     */

    public function execute(int $customerId, array $data)
    {
        $addresses = $this->objectManager->get('\Magento\Customer\Model\AddressFactory');
        $address = $addresses->create();
        $address->setCustomerId($customerId);
        foreach ($data['address'] as $key => $value) {
            if (isset($value)) {
                if ($key == "default_shipping") {
                    $address->setIsDefaultBilling($value);
                }
                $address->setData($key, $value);
            }
        }
        try {
            $address->save();
            return true;
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Some error occured while creating new address.'));
        }
    }
}
