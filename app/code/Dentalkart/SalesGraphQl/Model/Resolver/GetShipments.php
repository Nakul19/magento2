<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\SalesGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Dentalkart\SalesGraphQl\Model\Customer\CheckCustomerAccount;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Orders data reslover
 */
class GetShipments implements ResolverInterface
{

    /**
     * @var Builder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     *  @var ShipmentRepositoryInterface
    */
    private $shipmentRepositoryInterface;

    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @param CollectionFactoryInterface $collectionFactory
     * @param CheckCustomerAccount $checkCustomerAccount
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        CheckCustomerAccount $checkCustomerAccount,
        ShipmentRepositoryInterface $shipmentRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->checkCustomerAccount = $checkCustomerAccount;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Find shipments by criteria.
     *
     * @param $orderId
     * @return ShipmentInterface[]
    */
    public function getShipments($order_id){

        $filters = [
            $this->filterBuilder->setField(ShipmentInterface::ORDER_ID)->setValue($order_id)->create()
        ];

        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->shipmentRepositoryInterface->getList($searchCriteria);

        return $searchResults;
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
        $customerId = $context->getUserId();
        $this->checkCustomerAccount->execute($customerId, $context->getUserType());

        $order_id = 123152;

        $result = $this->getShipments($order_id);

        throw new GraphQlInputException(
            __(json_encode($result))
        );
        return $result;
    }
}
