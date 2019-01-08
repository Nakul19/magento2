<?php

declare(strict_types=1);

namespace Dentalkart\TestGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

/**
 * Order sales field resolver, used for GraphQL request processing
 */
class Cart implements ResolverInterface
{
    /**
     * @var UserContextInterface
    */
    private $userContext;

    /**
     * @var CartRepositoryInterface
    */
    private $cartRepository;

    /**
     * @var CartTotalRepositoryInterface
    */
    private $cartTotalRepository;

    /**
     * @var CartManagementInterface
    */
    private $cartManagementInterface;


    /**
     * @var QuoteIdToMaskedQuoteIdInterface
    */
    private $quoteIdToMaskedQuoteIdInterface;


    public function __construct(
        UserContextInterface $userContext,
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteIdInterface
    ) {
        $this->userContext = $userContext;
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->quoteIdToMaskedQuoteIdInterface = $quoteIdToMaskedQuoteIdInterface;
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
        $cartData = $this->getCartData();

        return $cartData;
    }


    /**
     * @param int $orderId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getCartData()
    {
        try {
            $customerId = $this->userContext->getUserId();
            $cart = $this->cartRepository->getForCustomer($customerId);
            $cartId = $cart->getId();
            $maskedCartId = $this->quoteIdToMaskedQuoteIdInterface->execute((int)$cartId);

            $cart['id'] = $maskedCartId;
            $cart['totals'] = $this->cartTotalRepository->get($cartId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $cart;
    }
}