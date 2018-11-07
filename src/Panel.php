<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 * Created: 23.05.2018 21:34
 */

namespace Chomenko\CacheCleaner;

use Nette\Utils\Finder;
use Nette\Utils\Html;
use Tracy\IBarPanel;
use Latte;

class Panel implements IBarPanel{


    /**
     * @var string
     */
    private $temp_dir;

    /**
     * @var array
     */
    private $ignore_files;

    /**
     * @param string $temp_dir
     * @param array $ignore_files
     */
    public function __construct($temp_dir, $ignore_files = array())
    {
        $this->temp_dir = $temp_dir;
        $this->ignore_files = $ignore_files;
    }


    /**
     * @param array $files
     * @param float $size
     * @param null $parent
     */
    private function scan(&$files = array(), &$size, $parent = null)
    {

        if(!$parent){
            $parent = $this->temp_dir;
        }

        /** @var \SplFileInfo $file */
        foreach (Finder::findFiles('*')->in($parent) as $file){
            if(array_search($file->getFilename(), $this->ignore_files) === false) {
                $files[] = array(
                    'file' => $file->getRealPath(),
                    'size' => $file->getSize()
                );
                $size += $file->getSize();
            }
        }

        foreach(Finder::findDirectories('*')->in($parent) as $dir){
           $this->scan($files, $size, $dir->getRealPath());
        }

    }

    /**
     * @return Html
     */
    private function getIconHtml()
    {
        $path = __DIR__.'/icon.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents(__DIR__.'/icon.png');
        $src = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return Html::el("img")
            ->setAttribute("src", $src)
            ->setAttribute('weight', 16)
            ->setAttribute('height', 16);
    }

    /**
     * @param array $files
     */
    private function cleanCache(array $files)
    {
        if(isset($_GET['panel_action']) && $_GET['panel_action'] === "clean_cache"){
            foreach ($files as $file){
                unlink($file['file']);
            }
            header("location:/");
            exit;
        }
    }

    /**
     * @var array
     */
    private $_data = array();

    /**
     * @return string
     */
    public function getTab()
    {
        $this->scan($files, $size);
        $this->cleanCache($files);
        $this->_data = array(
            'files' => $files,
            'size' => $size
        );
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