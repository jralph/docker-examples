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

    /**
     * @throws Exception
     */
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

    /**
     * @return string
     * @throws Exception
     */
    public function renderExamplePage(): string
    {
        if (isset($_GET['error'])) {
            throw new Exception("Received Error: \"{$_GET['error']}\"");
        }

        if (isset($_GET['phpinfo'])) {
            phpinfo();
            return '';
        }

        $datetime = date('Y-m-d H:i:s');

        header("Cache-Control: public, max-age: 3600, s-maxage: 3600");

        return <<<EOF
        <img title="{$datetime}" src="img/docker.png" alt="docker logo">
        <p>{$datetime}</p>
        EOF;
    }
}
