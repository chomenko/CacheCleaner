<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\CacheCleaner;

use Nette\Application\Application;
use Nette\Http\Request;
use Nette\Http\Url;
use Nette\Utils\Finder;
use Tracy\Debugger;

class Cleaner
{

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @param Config $config
	 * @param Request $request
	 */
	public function __construct(Config $config, Request $request)
	{
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 * @return array
	 */
	public function getFileList(): array
	{
		$files = [];
		$size = 0;
		foreach ($this->config->getDirs() as $dir) {
			$this->scan($dir, $files, $size);
		}
		return [
			"files" => $files,
			"size" => $size
		];
	}

	/**
	 * @param string $dir
	 * @param array $files
	 * @param int $size
	 */
	private function scan($dir, &$files = [], &$size)
	{
		if (!is_dir($dir)) {
			return;
		}

		/** @var \SplFileInfo $file */
		foreach (Finder::findFiles('*')->in($dir) as $file) {
			if (array_search($file->getFilename(), $this->config->getIgnoreFiles()) === FALSE) {
				$files[] = [
					'file' => $file->getRealPath(),
					'size' => $file->getSize(),
				];
				$size += $file->getSize();
			}
		}

		foreach (Finder::findDirectories('*')->in($dir) as $childDir) {
			$this->scan($childDir->getRealPath(), $files, $size);
		}
	}

	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function createCleanUrl(): Url
	{
		$url = clone $this->request->getUrl();
		$url->setQueryParameter("panel_action", "clean_cache");
		return $url;
	}

	public function clean()
	{
		["files" => $files, "size" => $size] = $this->getFileList();
		foreach ($files as $file) {
			@unlink($file['file']);
		}
	}

	/**
	 * @param Application $app
	 */
	public function actionClean(Application $app)
	{
		if (Debugger::$productionMode) {
			return;
		}

		$url = clone $this->request->getUrl();
		$clean = $url->getQueryParameter("panel_action");

		if ($clean !== "clean_cache") {
			return;
		}

		$this->clean();
		$parameters = $url->getQueryParameters();
		if (isset($parameters["panel_action"])) {
			unset($parameters["panel_action"]);
		}

		$url->setQuery($parameters);
		header("location:$url");
		exit;
	}

}
