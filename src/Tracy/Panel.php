<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 * Created: 23.05.2018 21:34
 */

namespace Chomenko\CacheCleaner\Tracy;

use Chomenko\CacheCleaner\Cleaner;
use Nette\Utils\Html;
use Tracy\IBarPanel;
use Latte;

class Panel implements IBarPanel
{

	/**
	 * @var Cleaner
	 */
	private $cleaner;

	/**
	 * @var array
	 */
	private $_data;

	/**
	 * @param Cleaner $cleaner
	 */
	public function __construct(Cleaner $cleaner)
	{
		$this->cleaner = $cleaner;
	}

	/**
	 * @return Html
	 */
	private function getIconHtml()
	{
		$path = __DIR__ . '/icon.png';
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$data = file_get_contents(__DIR__ . '/icon.png');
		$src = 'data:image/' . $type . ';base64,' . base64_encode($data);
		return Html::el("img")
			->setAttribute("src", $src)
			->setAttribute('weight', 16)
			->setAttribute('height', 16);
	}

	/**
	 * @return string
	 */
	public function getTab()
	{
		["files" => $files, "size" => $size] = $this->cleaner->getFileList();

		$this->_data = [
			'files' => $files,
			'size' => $size,
			'url' => $this->cleaner->createCleanUrl(),
		];
		return Html::el()->addHtml($this->getIconHtml())->addText(Latte\Runtime\Filters::bytes($size));
	}

	/**
	 * @return string
	 */
	public function getPanel()
	{
		$latte = new Latte\Engine;
		return $latte->renderToString(__DIR__ . '/panel.latte', $this->_data);
	}

}