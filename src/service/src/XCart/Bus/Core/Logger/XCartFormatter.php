<?php
/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Core\Logger;

use Monolog\Formatter\NormalizerFormatter;

class XCartFormatter extends NormalizerFormatter
{
    /**
     * @todo: to global scope
     * @var string
     */
    private $runtimeId;

    /**
     * @param string $dateFormat
     */
    public function __construct($dateFormat = null)
    {
        parent::__construct($dateFormat);

        $this->runtimeId = hash('md4', uniqid('runtime', true));
    }

    /**
     * Formats a log record.
     *
     * @param  array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $output = $record['message'] . PHP_EOL;

        if ($record['context']) {
            $output .= PHP_EOL;
            $output .= 'Context:' . PHP_EOL;
            foreach ((array) $record['context'] as $key => $value) {
                $output .= $key . ' => ' . $this->convertToString($value) . PHP_EOL;
            }
        }

        if ($record['extra']) {
            $output .= PHP_EOL;
            $output .= 'Extra:' . PHP_EOL;
            foreach ((array) $record['extra'] as $key => $value) {
                $output .= $key . ' => ' . $this->convertToString($value) . PHP_EOL;
            }
        }

        $parts = [
            'Time: ' . $record['datetime']->format($this->dateFormat),
            'Channel: ' . $record['channel'] . '.' . $record['level_name'],
            'Runtime id: ' . $this->runtimeId,
            'Server API: ' . \PHP_SAPI . '; IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'n/a'),
        ];

        if ($_SERVER !== null) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $parts[] = 'Request method: ' . $_SERVER['REQUEST_METHOD'];
            }

            if (isset($_SERVER['REQUEST_URI'])) {
                $parts[] = 'URI: ' . $_SERVER['REQUEST_URI'];
            }
        }

        return $output . PHP_EOL . implode(';' . PHP_EOL, $parts) . ';' . PHP_EOL . PHP_EOL;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function convertToString($data): string
    {
        if (null === $data || is_scalar($data)) {
            return (string) $data;
        }

        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
