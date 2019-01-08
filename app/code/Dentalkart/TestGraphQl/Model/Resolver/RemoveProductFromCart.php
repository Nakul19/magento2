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
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Dentalkart\TestGraphQl\Model\Cart\GetCartForUser;

/**
 * Order sales field resolver, used for GraphQL request processing
 */
class RemoveProductFromCart implements ResolverInterface
{

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var UserContextInterface
    */
    private $userContext;

    /**
     * @var CartItemRepositoryInterface
    */
    private $cartItemRepositoryInterface;


    private $cartItemInterface;

    public function __construct(
        UserContextInterface $userContext,
        CartItemRepositoryInterface $cartItemRepositoryInterface,
        CartItemInterface $cartItemInterface,
        GetCartForUser $getCartForUser
    ) {
        $this->userContext = $userContext;
        $this->cartItemRepositoryInterface = $cartItemRepositoryInterface;
        $this->cartItemInterface = $cartItemInterface;
        $this->getCartForUser= $getCartForUser;
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
        if (!isset($args['cartId'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (!isset($args['itemId'])) {
            throw new GraphQlInputException(__('Required parameter "itemId" is missing'));
        }

        $maskedCartId = $args['cartId'];
        $itemId = $args['itemId'];

        return $this->removeFromCart($maskedCartId, $itemId);
    }


    /**
     * Removes the specified item from the specified cart.
     *
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be removed.
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item or cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The item could not be removed.
     */
    public function removeFromCart($maskedCartId, $itemId){
        try {
            $currentUserId = $this->userContext->getUserId();
            $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId);
            $cartId = $cart->getId();
            $item = $this->cartItemRepositoryInterface->deleteById($cartId, $itemId);
            
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $item;
    }

}