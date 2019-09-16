<?php
/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Core\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Silex\Application;
use XCart\SilexAnnotations\Annotations\Service;

abstract class ALogger
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @param Application $app
     *
     * @return LoggerInterface
     *
     * @Service\Constructor
     * @codeCoverageIgnore
     */
    public static function serviceConstructor(
        Application $app
    ) {
        $self = new static($app['config']['log.path']);

        /** @var Logger|LoggerInterface $log */
        $log = new $app['monolog.logger.class']($self->getName());

        foreach ($self->getHandlers() as $handler) {
            if ($handler) {
                $log->pushHandler($handler);
            }
        }

        return $log;
    }

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return HandlerInterface[]
     */
    protected function getHandlers()
    {
        return [
            $this->getDefaultHandler(),
        ];
    }

    /**
     * @return PHPFileHandler
     */
    protected function getDefaultHandler()
    {
        try {
            $handler = new PHPFileHandler($this->getFilePath(), $this->getLevel());
            $handler->setFormatter($this->getFormatter());

            return $handler;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return FormatterInterface
     */
    protected function getFormatter()
    {
        return new LineFormatter();
    }

    /**
     * @return string
     */
    protected function getName()
    {
        $parts = explode('\\', static::class);

        return 'service-' . strtolower(array_pop($parts));
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return $this->path . date('/Y/m/') . $this->getName() . '.log.' . date('Y-m-d') . '.php';
    }

    /**
     * @return string
     */
    protected function getLevel()
    {
        return LogLevel::DEBUG;
    }
}
