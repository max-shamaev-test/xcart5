<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Executor\Step;

use XCart\Bus\Exception\Rebuild\HoldException;
use XCart\Bus\Exception\RebuildException;
use XCart\Bus\Rebuild\Executor\StepState;

abstract class AUpgradeNote extends ANote
{
    /**
     * @todo: multi-language
     *
     * @param array $transition
     *
     * @return array
     */
    protected function getNotesListByTransition($transition): array
    {
        $type = $this->getType();

        if ($transition['id'] === 'CDev-Core') {
            $notesDir = 'upgrade/';

        } elseif ($transition['id'] === 'XC-Service') {
            $notesDir = 'upgrade/';

        } else {
            [$author, $name] = explode('-', $transition['id']);

            $notesDir = sprintf('classes/XLite/Module/%s/%s/hooks/upgrade/', $author, $name);
        }

        return array_filter($transition['new_files'], static function ($file) use ($type, $transition, $notesDir) {
            if (!preg_match('/^' . preg_quote($notesDir, '/') . '.*' . $type . '\.txt$/', $file)) {
                return false;
            }

            $relativePath = dirname(str_replace($notesDir, '', $file));
            $version      = str_replace(['/', '\\'], '.', $relativePath);

            return version_compare($version, $transition['version_after'], '<=')
                && version_compare($version, $transition['version_before'], '>');
        });
    }

    /**
     * @param StepState $state
     *
     * @throws RebuildException
     */
    protected function reportNotes($state): void
    {
        if (!empty($state->data)) {
            throw HoldException::fromAUpgradeNoteStepNote($this->getType(), $state);
        }
    }
}
