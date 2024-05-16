<?php

namespace BrandEmbassy\Slim\SwooleBridge;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Slim\App;

class BridgeManager
{
    private App $app;

    private RequestTransformer $requestTransformer;

    private ResponseMerger $responseMerger;


    public function __construct(
        App $app,
        RequestTransformer $requestTransformer,
        ResponseMerger $responseMerger,
    ) {
        $this->app = $app;
        $this->requestTransformer = $requestTransformer;
        $this->responseMerger = $responseMerger;
    }

    public function process(
        Request $swooleRequest,
        Response $swooleResponse
    ): Response {
        $response = $this->app->getContainer()->get('response');

        $slimRequest = $this->requestTransformer->toSlim($swooleRequest);
        $slimResponse = $this->app->process($slimRequest, $response);

        return $this->responseMerger->mergeToSwoole($slimResponse, $swooleResponse);
    }
}
