<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\CacheCleaner;

use Chomenko\CacheCleaner\Tracy\Panel;
use Nette\Http\RequestFactory;
use Tracy;

class CleanerFactory
{

	/**
	 * @var Panel
	 */
	private static $panel;

	/**
	 * @var Cleaner
	 */
	private static $cleaner;

	/**
	 * @var array
	 */
	private static $configure = [];

	/**
	 * @param array $configure
	 */
	public static function setConfig(array $configure)
	{
		self::$configure = $configure;
	}

	/**
	 * @return Cleaner
	 */
	public static function getCleaner(): Cleaner
	{
		if (self::$cleaner) {
			return self::$cleaner;
		}
		$requestFactory = new RequestFactory();
		$request = $requestFactory->createHttpRequest();
		$config = new Config(self::$configure);
		return self::$cleaner = new Cleaner($config, $request);
	}

	/**
	 * @return Panel
	 */
	public static function getPanel(): Panel
	{
		if (self::$panel) {
			return self::$panel;
		}
		self::$panel = new Panel(self::getCleaner());
		Tracy\Debugger::getBar()->addPanel(self::$panel);
		return self::$panel;
	}

	public static function initialize()
	{
		$cleaner = self::getCleaner();
		$cleaner->actionClean();
		self::getPanel();
	}

}
