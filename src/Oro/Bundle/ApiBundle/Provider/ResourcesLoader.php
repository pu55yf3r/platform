<?php

namespace Oro\Bundle\ApiBundle\Provider;

use Oro\Bundle\ApiBundle\Processor\CollectResources\CollectResourcesContext;
use Oro\Bundle\ApiBundle\Processor\CollectResourcesProcessor;
use Oro\Bundle\ApiBundle\Request\ApiResource;
use Oro\Bundle\ApiBundle\Request\RequestType;

class ResourcesLoader
{
    /** @var CollectResourcesProcessor */
    protected $processor;

    /**
     * @param CollectResourcesProcessor $processor
     */
    public function __construct(CollectResourcesProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Gets all resources available through a given Data API version.
     *
     * @param string      $version     The Data API version
     * @param RequestType $requestType The request type, for example "rest", "soap", etc.
     *
     * @return ApiResource[]
     */
    public function getResources($version, RequestType $requestType)
    {
        /** @var CollectResourcesContext $context */
        $context = $this->processor->createContext();
        $context->setVersion($version);
        $context->getRequestType()->set($requestType->toArray());

        $this->processor->process($context);

        return array_values($context->getResult()->toArray());
    }
}
