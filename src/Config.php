<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\CacheCleaner;

class Config
{

	/**
	 * @var array
	 */
	protected $dirs = [];

	/**
	 * @var array
	 */
	protected $ignoreFiles = [];

	/**
	 * @param array $parameters
	 */
	public function __construct(array $parameters = [])
	{
		foreach ($parameters as $name => $value) {
			if (property_exists($this, $name)) {
				$this->{$name} = $value;
			}
		}
	}

	/**
	 * @return array
	 */
	public function getDirs(): array
	{
		return $this->dirs;
	}

	/**
	 * @param string $dir
	 */
	public function addDir(string $dir)
	{
		$this->dirs[] = $dir;
	}

	/**
	 * @return array
	 */
	public function getIgnoreFiles(): array
	{
		return $this->ignoreFiles;
	}

	/**
	 * @param string $ignoreFiles
	 * @return $this
	 */
	public function addIgnoreFiles(string $ignoreFiles)
	{
		$this->ignoreFiles[] = $ignoreFiles;
		return $this;
	}

}
