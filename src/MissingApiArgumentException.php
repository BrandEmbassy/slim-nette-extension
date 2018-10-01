<?php declare(strict_types = 1);

namespace BrandEmbassy\Slim;

use Exception;

final class MissingApiArgumentException extends Exception implements RequestException
{

}
