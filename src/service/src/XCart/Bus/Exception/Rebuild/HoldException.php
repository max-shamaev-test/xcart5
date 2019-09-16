<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Exception\Rebuild;

use XCart\Bus\Exception\RebuildException;
use XCart\Bus\Rebuild\Executor\Script\ScriptInterface;
use XCart\Bus\Rebuild\Executor\StepState;

class HoldException extends RebuildException
{
    /**
     * @param StepState $state
     *
     * @return RebuildException
     */
    public static function fromCheckStepModifiedFilesPresent($state)
    {
        $data = $state->data;

        return (new self('File modification detected'))
            ->setType('file-modification-dialog')
            ->setDescription('file_modification_description')
            ->setData($data['modified'])
            ->addPrompt(ScriptInterface::PROMPT_RELEASE)
            ->setStepState($state);
    }

    /**
     * @param string    $type
     * @param StepState $state
     *
     * @return RebuildException
     */
    public static function fromAUpgradeNoteStepNote($type, $state)
    {
        return (new self('Upgrade note'))
            ->setType('note-' . $type)
            ->setData($state->data)
            ->addPrompt(ScriptInterface::PROMPT_RELEASE)
            ->setStepState($state);
    }

    /**
     * @param StepState $state
     *
     * @return RebuildException
     */
    public static function fromReloadPageStepReload($state)
    {
        return (new self('Reload page'))
            ->setType('reload-page')
            ->setStepState($state);
    }
}
