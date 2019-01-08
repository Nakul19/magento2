<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dentalkart\TestGraphQl\Model\Resolver;


use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class GetNavigation implements ResolverInterface
{
    /**
     * @var CategoryInterface
    */
    private $categoryRepositoryInterface;

    /**
     * @var CategoryManagementInterface
     */
    private $categoryManagementInterface;

    private $categoryCollectionFactory;

    /**
     * @param CategoryManagementInterface $categoryManagementInterface
     */
    public function __construct(
        CategoryManagementInterface $categoryManagementInterface,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryManagementInterface = $categoryManagementInterface;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
    	$depth = $args['level'] ? $args['level'] : 2;
    	$id = $args['id'] ? $args['id'] : 2;

        $tree = $this->categoryManagementInterface->getTree($id, $depth);
        //$collection = $this->getCategoryCollection(2);

        // $categories = [];
        // foreach ($collection as $category) {
        //     $data['id'] = $category->getId();
        //     $data['name'] = $category->getName();
        //     $data['url_key'] = $category->getUrlKey();
        //     $data['children'] = $this->getCategoryCollection($data['id']);
        //     array_push($categories, $data);
        // }

        // throw new GraphQlInputException(__(json_encode($categories)));

        $result['navigation'] = $tree;
        return $result;
    }


    public function getCategoryCollection($categoryId)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', $categoryId);
        $collection->addAttributeToSelect(['name', 'url_key']);
        $collection->addIsActiveFilter();   
                
        return $collection;
    }

    // public function getData(string $categoryPath): array
    // {
    //     $breadcrumbsData = [];

    //     $pathCategoryIds = explode('/', $categoryPath);
    //     $parentCategoryIds = array_slice($pathCategoryIds, 2, -1);

    //     if (count($parentCategoryIds)) {
    //         $collection = $this->collectionFactory->create();
    //         $collection->addAttributeToSelect(['name', 'url_key']);
    //         $collection->addAttributeToFilter('entity_id', $parentCategoryIds);

    //         foreach ($collection as $category) {
    //             $breadcrumbsData[] = [
    //                 'category_id' => $category->getId(),
    //                 'category_name' => $category->getName(),
    //                 'category_level' => $category->getLevel(),
    //                 'category_url_key' => $category->getUrlKey(),
    //             ];
    //         }
    //     }
    //     return $breadcrumbsData;
    // }
}
