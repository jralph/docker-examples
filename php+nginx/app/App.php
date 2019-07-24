<?php

namespace App;

use Monolog\ErrorHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Exception;

class App
{
    /**
     * @var Logger
     */
    private $logger;

    public function setupLogging(): void
    {
        $this->logger = new Logger('log');
        $errorLogHandler = new StreamHandler('php://stderr', Logger::WARNING);
        $errorLogHandler->setFormatter(new JsonFormatter());

        $this->logger->pushHandler($errorLogHandler);

        $errorHandler = new ErrorHandler($this->logger);
        $errorHandler->registerErrorHandler([], false);
        $errorHandler->registerExceptionHandler();
        $errorHandler->registerFatalHandler();
    }

    public function renderExamplePage(): string
    {
        if (isset($_GET['error'])) {
            throw new Exception("Received Error: \"{$_GET['error']}\"");
        }

        $datetime = date('Y-m-d H:i:s');

        return <<<EOF
        <img title="{$datetime}" src="img/docker.png" alt="docker logo">

        <p>{$datetime}</p>
        EOF;
    }
}
