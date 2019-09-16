<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Rebuild\Executor;

use Silex\Application;
use XCart\Bus\Rebuild\Executor\Script\ScriptInterface;
use XCart\Bus\Rebuild\Executor\Step\StepInterface;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 */
class ScriptFactory
{
    /**
     * @var string[]|ScriptInterface[]
     */
    private $scripts = [];

    /**
     * @var array
     */
    private $steps = [];

    /**
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string                 $name
     * @param string|ScriptInterface $script
     */
    public function addScript($name, $script)
    {
        $this->scripts[$name] = $script;
    }

    /**
     * @param string        $scriptName
     * @param StepInterface $step
     * @param int           $weight
     */
    public function addStep($scriptName, $step, $weight)
    {
        $this->steps[$scriptName][] = [
            'weight' => $weight,
            'step'   => $step,
        ];
    }

    /**
     * @param string $name
     *
     * @return ScriptInterface|null
     */
    public function createScript($name)
    {
        $script = $this->getScript($name);
        if ($script) {
            $script->setSteps($this->getScriptSteps($name));

            return $script;
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return null|ScriptInterface
     */
    private function getScript($name)
    {
        if (isset($this->scripts[$name])) {
            $script = $this->scripts[$name];

            return $this->app[$script] ?? new $script;
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return StepInterface[]
     */
    private function getScriptSteps($name)
    {
        $steps = $this->steps[$name] ?? [];

        usort($steps, function ($a, $b) {
            $a = (int) $a['weight'];
            $b = (int) $b['weight'];

            if ($a === $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });

        return array_map(function ($item) {
            $step = $item['step'];

            return $this->app[$step] ?? new $step;
        }, $steps);
    }
}
