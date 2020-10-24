<?php declare(strict_types = 1);

namespace BrandEmbassyTest\Slim\Sample;

use BrandEmbassy\Slim\Controller\Controller;
use BrandEmbassy\Slim\Request\RequestInterface;
use BrandEmbassy\Slim\Response\ResponseInterface;

final class UsersController extends Controller
{
    public function getUsers(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withJson(['users' => []]);
    }


    public function createUser(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withJson(['status' => 'created'], 201);
    }
}
