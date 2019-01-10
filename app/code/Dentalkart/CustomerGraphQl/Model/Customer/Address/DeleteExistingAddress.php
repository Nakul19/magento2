<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

/**
 * Update account information
 */
class DeleteExistingAddress{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
    }

    /**
     * Update account information
     *
     * @param int $customerId
     * @param array $data
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     * @throws GraphQlAlreadyExistsException
     */

    public function execute(int $customerId, array $data): void
    {
        $addressId = $data['id'];
        try {
            $address = $this->addressRepository->deleteById($addressId);
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}
