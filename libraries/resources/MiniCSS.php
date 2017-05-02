<?php
/**
 * Simple script to combine and compress CSS files, to reduce the number of file request the server has to handle.
 * For more options/flexibility, see Minify : http://code.google.com/p/minify/
 *
 * @category	Functions
 * @package		Helpers
 * @subpackage	Framework
 * @update		$LastChangedBy: durandcedric $
 * @version		$LastChangedRevision: 19 $
 */
class MiniCSS {

	protected $_cache;
	protected $aFiles;

	/**
	 * Minify CSS
	 *
	 * @param string $css
	 *			CSS to be minified
	 * @return string
	 *
	 */
	public static function minify($css) {
		$MiniCSS = new MiniCSS($css);
		return $MiniCSS->min();
	}

	public function __construct($aFiles = array(), $cacheName = 'css') {
		if ($this->issetFiles($aFiles)) {
			// Enable compression
			if (extension_loaded('zilb')) {
				ini_set('zlib.output_compression', 'On');
			}

			// Load files
			error_reporting(0);
			$content = '';
			foreach ($this->aFiles as $file) {
				$content .= file_get_contents($file);
			}

			// Suppression des commentaires
			$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);

			// Remove tabs, spaces, newlines, etc...
			$content = str_replace(array("\r", "\n", "\t", '  ', '   '), '', $content);

			// Stockage du style dans la variable de classe
			$this->_cache = $content;
		}
	}

	public function min() {
		return $this->_cache;
	}

	private function issetFiles($aFiles = array()) {
		// If no file requested
		if (empty($aFiles)) {
			return false;
		} else {
			$this->aFiles = is_array($aFiles) ? $aFiles : array($aFiles);
			return true;
		}
	}

	/**
	 * Add file extension if needed
	 * @var string $file the file name
	 */
	function addExtension($file) {
		if (substr($file, -3) !== '.css') {
			$file .= '.css';
		}
		return $file;
	}

}
