<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Update account information
 */
class UpdateAccountInformation
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckCustomerPassword
     */
    private $checkCustomerPassword;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param StoreManagerInterface $storeManager
     * @param CheckCustomerPassword $checkCustomerPassword
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        StoreManagerInterface $storeManager,
        CheckCustomerPassword $checkCustomerPassword
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->storeManager = $storeManager;
        $this->checkCustomerPassword = $checkCustomerPassword;
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

    private function updateAddress(array $data){
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

    public function execute(int $customerId, array $data): void
    {

        $customer = $this->customerRepository->getById($customerId);

        if (isset($data['firstname'])) {
            $customer->setFirstname($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $customer->setLastname($data['lastname']);
        }

        if (isset($data['email']) && $customer->getEmail() !== $data['email']) {
            if (!isset($data['password']) || empty($data['password'])) {
                throw new GraphQlInputException(__('Provide the current "password" to change "email".'));
            }

            $this->checkCustomerPassword->execute($data['password'], $customerId);
            $customer->setEmail($data['email']);
        }

        if (isset($data['taxvat'])) {
            $customer->setTaxvat($data['taxvat']);
        }

        $customer->setStoreId($this->storeManager->getStore()->getId());

        try {
            if (isset($data['address'])) {
                if (!isset($data['address']['id'])) {
                    throw new GraphQlInputException(__('Please mention address id.'));
                }
                $this->updateAddress($data);
            }

            $this->customerRepository->save($customer);
        } catch (AlreadyExistsException $e) {
            throw new GraphQlAlreadyExistsException(
                __('A customer with the same email address already exists in an associated website.'),
                $e
            );
        }
        if (isset($data['address'])) {
            if (!isset($data['address']['id'])) {
                throw new GraphQlInputException(__('Please mention address id.'));
            }
            $this->updateAddress($data);
        }
    }
}
