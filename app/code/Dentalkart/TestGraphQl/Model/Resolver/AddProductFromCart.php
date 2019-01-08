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

/**
 * Order sales field resolver, used for GraphQL request processing
 */
class AddProductFromCart implements ResolverInterface
{
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
        CartItemInterface $cartItemInterface
    ) {
        $this->userContext = $userContext;
        $this->cartItemRepositoryInterface = $cartItemRepositoryInterface;
        $this->cartItemInterface = $cartItemInterface;
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
		foreach ($args['cartItem'] as $key => $value) {
			$this->cartItemInterface[$key] = $value;
		}
        return $this->addToCart($this->cartItemInterface);
    }


    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem The item.
     * @return \Magento\Quote\Api\Data\CartItemInterface Item.
     * @throws GraphQlNoSuchEntityException
     */
    private function addToCart($cartItem)
    {
        try {
            $item = $this->cartItemRepositoryInterface->save($cartItem);

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $item;
    }
}