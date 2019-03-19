<?php

/**
 * @author Mykola Chomenko <mykola.chomenko@dipcom.cz>
 */

namespace Chomenko\CacheCleaner\DI;

use Chomenko\CacheCleaner\CleanerFactory;
use Chomenko\CacheCleaner\Console\CacheCommand;
use Nette;
use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\Compiler;

class CacheCleanerExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = self::getConfiguration($this->name, $this->compiler);
		$builder->addDefinition($this->prefix('cleanerFactory'))
			->setFactory(CleanerFactory::class)
			->addSetup('@' . CleanerFactory::class . "::setConfig", [$config]);

		$builder->addDefinition($this->prefix('command'))
			->setFactory(CacheCommand::class)
			->setInject(TRUE)
			->addTag("kdyby.console");
	}

	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$ini = $class->getMethod("initialize");
		$name = $this->prefix('cleanerFactory');
		$body = '$this->getService("' . $name . '")::initialize();' . "\n";
		$body .= $ini->getBody();
		$ini->setBody($body);
	}

	/**
	 * @param string $name
	 * @param Compiler $compiler
	 * @return array
	 */
	private static function getConfiguration(string $name, Compiler $compiler): array
	{
		$config = [];
		$configuration = $compiler->getConfig();
		$parameters = $configuration["parameters"];
		$tempDir = $parameters["tempDir"];

		$search = [];
		$replace = [];
		foreach ($parameters as $parameterName => $value) {
			if (is_string($value)) {
				$search[] = "%" . $parameterName . "%";
				$replace[] = $value;
			}
		}

		if (array_key_exists($name, $configuration)) {
			$config = $configuration[$name];
			if(isset($config["dirs"]) && is_array($config["dirs"])) {
				foreach ($config["dirs"] as $key => $dir) {
					$config["dirs"][$key] = str_replace($search, $replace, $dir);
				}
			}
		}

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
		return $config;
	}

	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) {
			$name = "CacheCleaner";
			CleanerFactory::setConfig(self::getConfiguration($name, $compiler));
			CleanerFactory::initialize();
			$compiler->addExtension('CacheCleaner', new CacheCleanerExtension());
		};
	}

}
