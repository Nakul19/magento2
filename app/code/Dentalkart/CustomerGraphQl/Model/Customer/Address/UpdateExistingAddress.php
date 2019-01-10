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
class UpdateExistingAddress{
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
        $addressId = $data['address']['id'];
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Wrong address id provided.'));
        }
        foreach ($data['address'] as $key => $value) {
            if (isset($value)) {
                $address->setData($key, $value);
            }
        }
        try {
            $this->addressRepository->save($address);
        } catch (AlreadyExistsException $e) {
            throw new GraphQlAlreadyExistsException(
                __('Some problem occured try again later.'),
                $e
            );
        }
    }
}
