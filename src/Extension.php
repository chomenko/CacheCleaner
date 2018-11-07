<?php

/**
 * @author Mykola Chomenko <mykola.chomenko@dipcom.cz>
 */

namespace Chomenko\CacheCleaner;

use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\Compiler;

class Extension extends CompilerExtension{

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $ignore_files = array('.gitignore');

        $builder->addDefinition($this->prefix('panel'))
            ->setFactory(Panel::class, array(
                'temp_dir' => $builder->parameters['tempDir'],
                'ignore_files' => $ignore_files
            ));

        $builder->getDefinition('tracy.bar')
            ->addSetup('$service->addPanel($this->getService(?));',array($this->prefix('panel')));
    }

    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Compiler $compiler){
            $compiler->addExtension('CacheCleaner', new Extension());
        };
    }
}
