<?php

/**
 * @author Mykola Chomenko <mykola.chomenko@dipcom.cz>
 */

namespace Chomenko\CacheCleaner\DI;

use Chomenko\CacheCleaner\Cleaner;
use Chomenko\CacheCleaner\Config;
use Chomenko\CacheCleaner\Console\CacheCommand;
use Chomenko\CacheCleaner\Tracy\Panel;
use Nette\Application\Application;
use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\Compiler;

class CacheCleanerExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$tempDir = $builder->parameters["tempDir"];
		$config = $this->getConfig();

		if (!isset($config["dirs"])) {
			$config["dirs"] = [
				$tempDir,
			];
		}

		if (!isset($config["ignoreFiles"])) {
			$config["ignoreFiles"] = [
				'.gitignore',
			];
		}

		$builder->addDefinition($this->prefix('config'))
			->setFactory(Config::class, [
				"parameters" => $config,
			]);

		$builder->addDefinition($this->prefix('cleaner'))
			->setFactory(Cleaner::class);

		$builder->addDefinition($this->prefix('panel'))
			->setFactory(Panel::class);

		$builder->addDefinition($this->prefix('command'))
			->setFactory(CacheCommand::class)
			->setInject(TRUE)
			->addTag("kdyby.console");

		$builder->getDefinition('tracy.bar')
			->addSetup('$service->addPanel($this->getService(?));', [
				$this->prefix('panel'),
			]);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$application = $builder->getDefinitionByType(Application::class);
		$cleaner = $builder->getDefinitionByType(Cleaner::class);
		$application->addSetup('?->actionClean(?)', [$cleaner, $application]);
	}

	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('CacheCleaner', new Extension());
		};
	}

}
