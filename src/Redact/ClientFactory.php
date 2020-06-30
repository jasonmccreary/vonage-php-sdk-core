<?php
declare(strict_types=1);

namespace Nexmo\Redact;

use Nexmo\Client\APIResource;
use Nexmo\Client\APIExceptionHandler;
use Psr\Container\ContainerInterface;

/**
 * @todo Finish this Namespace
 */
class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var APIResource $api */
        $api = $container->get(APIResource::class);
        $api
            ->setBaseUri('/v1/redact/transaction')
            ->setCollectionName('')
        ;

        // This API has a slightly different format for the error message, so override
        $exceptionHandler = $api->getExceptionErrorHandler();
        if ($exceptionHandler instanceof APIExceptionHandler) {
            $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
        }
        $api->setExceptionErrorHandler($exceptionHandler);

        return new Client($api);
    }
}
