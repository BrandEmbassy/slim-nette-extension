<?php

namespace BrandEmbassy\Slim\SwooleBridge;

use BrandEmbassy\Slim\Request\Request;
use BrandEmbassy\Slim\Request\RequestInterface;
use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigRequestCookies;
use OpenSwoole\Http\Request as SwooleRequest;
use Slim\Http;
use Slim\Http\UploadedFile;

class RequestTransformer
{
    private const DEFAULT_SCHEMA = 'https';

    public function toSlim(SwooleRequest $request): RequestInterface
    {
        $slimRequest = Request::createFromEnvironment(
            new Http\Environment([
                    'SERVER_PROTOCOL' => $request->server['server_protocol'],
                    'REQUEST_METHOD' => $request->server['request_method'],
                    'REQUEST_SCHEME' => static::DEFAULT_SCHEMA,
                    'REQUEST_URI' => $request->server['request_uri'],
                    'QUERY_STRING' => $request->server['query_string'] ?? '',
                    'SERVER_PORT' => $request->server['server_port'],
                    'REMOTE_ADDR' => $request->server['remote_addr'],
                    'REQUEST_TIME' => $request->server['request_time'],
                    'REQUEST_TIME_FLOAT' => $request->server['request_time_float']
                ]
            )
        );

        $slimRequest = $this->copyHeaders($request, $slimRequest);

        if ($this->isMultiPartFormData($request) || $this->isXWwwFormUrlEncoded($request)) {
            $slimRequest = $this->handlePostData($request, $slimRequest);
        }

        if ($this->isMultiPartFormData($request)) {
            $slimRequest = $this->handleUploadedFiles($request, $slimRequest);
        }

        $slimRequest = $this->copyCookies($request, $slimRequest);

        return $this->copyBody($request, $slimRequest);
    }

    private function copyCookies(SwooleRequest $request, RequestInterface $slimRequest): RequestInterface
    {
        if (empty($request->cookie)) {
            return $slimRequest;
        }

        foreach ($request->cookie as $name => $value) {
            $cookie = Cookie::create($name, $value);
            $slimRequest = FigRequestCookies::set($slimRequest, $cookie);
        }

        return $slimRequest;
    }

    private function copyBody(SwooleRequest $request, RequestInterface $slimRequest): RequestInterface
    {
        if (empty($request->rawContent())) {
            return $slimRequest;
        }

        $body = $slimRequest->getBody();
        $body->write($request->rawContent());
        $body->rewind();

        return $slimRequest->withBody($body);
    }

    private function copyHeaders(SwooleRequest $request, RequestInterface $slimRequest): RequestInterface
    {
        foreach ($request->header as $key => $val) {
            $slimRequest = $slimRequest->withHeader($key, $val);
        }

        return $slimRequest;
    }

    private function isMultiPartFormData(SwooleRequest $request): bool
    {
        return !(!isset($request->header['content-type'])
            || false === stripos($request->header['content-type'], 'multipart/form-data'));
    }

    private function isXWwwFormUrlEncoded(SwooleRequest $request): bool
    {
        return !(!isset($request->header['content-type'])
            || false === stripos($request->header['content-type'], 'application/x-www-form-urlencoded'));
    }

    private function handleUploadedFiles(
        SwooleRequest $request,
        RequestInterface $slimRequest
    ): RequestInterface {
        if (empty($request->files) || !is_array($request->files)) {
            return $slimRequest;
        }

        $uploadedFiles = [];

        foreach ($request->files as $key => $file) {
            $uploadedFiles[$key] = new UploadedFile(
                $file['tmp_name'],
                $file['name'],
                $file['type'],
                $file['size'],
                $file['error']
            );
        }

        return $slimRequest->withUploadedFiles($uploadedFiles);
    }

    private function handlePostData(
        SwooleRequest $swooleRequest,
        RequestInterface $slimRequest
    ): RequestInterface {
        if (empty($swooleRequest->post) || !is_array($swooleRequest->post)) {
            return $slimRequest;
        }

        return $slimRequest->withParsedBody($swooleRequest->post);
    }
}
