<?php
namespace Dentalkart\TestGraphQl\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {   
        // $filterableAttributes = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);
        // $attributes = $filterableAttributes->getList();
        return $this->resultPageFactory->create();
    }

}
