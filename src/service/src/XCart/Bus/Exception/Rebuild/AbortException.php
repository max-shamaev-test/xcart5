<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Exception\Rebuild;

use GuzzleHttp\Exception\ParseException;
use XCart\Bus\Exception\RebuildException;

class AbortException extends RebuildException
{
    /**
     * @param string $transitionId
     * @param string $message
     *
     * @return RebuildException
     */
    public static function fromDownloadStepWrongResponse($transitionId, $message)
    {
        return (new self('Package download error'))
            ->setData([
                sprintf('Entity %s, %s', $transitionId, $message),
            ]);
    }

    /**
     * @param string $transitionId
     *
     * @return RebuildException
     */
    public static function fromDownloadStepEmptyResponse($transitionId)
    {
        return (new self('Package download error'))
            ->setData([
                sprintf('Entity %s, empty pack file', $transitionId),
            ]);
    }

    /**
     * @param string $transitionId
     * @param string $path
     *
     * @return RebuildException
     */
    public static function fromUnpackStepExtractionError($transitionId, $path)
    {
        return (new self('Package extraction error'))
            ->setData([
                sprintf('Entry %s, cannot extract (%s)', $transitionId, $path),
            ]);
    }

    /**
     * @param string $transitionId
     * @param string $path
     *
     * @return RebuildException
     */
    public static function fromUnpackStepMissingPackage($transitionId, $path)
    {
        return (new self('Package extraction error'))
            ->setData([
                sprintf('Entry %s, file (%s) not found', $transitionId, $path),
            ]);
    }

    /**
     * @param string $transitionId
     *
     * @return RebuildException
     */
    public static function fromCheckStepInvalidResponse($transitionId)
    {
        return (new self('Hash checking error'))
            ->setData([
                sprintf('Entry %s, invalid response', $transitionId),
            ]);
    }

    /**
     * @param string $transitionId
     *
     * @return RebuildException
     */
    public static function fromCheckStepWrongResponse($transitionId, $message)
    {
        return (new self('Hash checking error'))
            ->setData([
                sprintf('Entry %s, %s', $transitionId, $message),
            ]);
    }

    /**
     * @param string $path
     *
     * @return RebuildException
     */
    public static function fromCheckStepWrongHashFile($path)
    {
        return (new self('Hash checking error'))
            ->setData([
                sprintf('Cannot read hashes (%s)', $path),
            ]);
    }

    /**
     * @param string[] $errors
     *
     * @return RebuildException
     */
    public static function fromUpdateModulesListStepUpdateError($errors)
    {
        return (new self('Failed to update modules list'))
            ->setData($errors);
    }

    /**
     * @param ParseException $exception
     *
     * @return RebuildException
     */
    public static function fromUpdateModulesListStepWrongResponse(ParseException $exception)
    {
        return (new self(
            'Failed to update modules list',
            $exception->getCode(),
            $exception
        ))
            ->setDescription(
                $exception->getMessage() .
                '<br>Content:<br>' .
                $exception->getResponse()->getBody()
            );
    }

    /**
     * @param ParseException $exception
     *
     * @return RebuildException
     */
    public static function fromXCartStepWrongResponse(ParseException $exception)
    {
        return (new self(
            'Error thrown from X-Cart',
            $exception->getCode(),
            $exception
        ))
            ->setDescription(
                $exception->getMessage() .
                '<br>Content:<br>' .
                $exception->getResponse()->getBody()
            );
    }

    /**
     * @return RebuildException
     */
    public static function fromXCartStepEmptyResponse()
    {
        return new self('Empty response from X-Cart');
    }

    /**
     * @return RebuildException
     */
    public static function fromXCartStepErrorResponse($name, $error)
    {
        return (new self('X-Cart rebuild step failed'))
            ->setDescription("Step {$name} failed with the following errors:")
            ->setData($error);
    }
}
