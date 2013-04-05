<?php

class Uploadify {


	function __construct(modX &$modx,array $config = array()) {
		$this->modx =& $modx;

		if (empty($config) && !empty($_SESSION['UploadifyConfig'])) {
			$this->config = $_SESSION['UploadifyConfig'];
		}
		else {
			$corePath = $this->modx->getOption('uploadify.core_path',$config,$this->modx->getOption('core_path').'components/uploadify/');
			$assetsPath = $this->modx->getOption('uploadify.assets_path',$config,$this->modx->getOption('assets_path').'components/uploadify/');
			$assetsUrl = $this->modx->getOption('uploadify.assets_url',$config,$this->modx->getOption('assets_url').'components/uploadify/');
			//$connectorUrl = $assetsUrl.'connector.php';

			$this->config = array_merge(array(
				'assetsUrl' => $assetsUrl
				,'assetsPath' => $assetsPath
				,'cssUrl' => $assetsUrl.'css/'
				,'jsUrl' => $assetsUrl.'js/'
				,'imagesUrl' => $assetsUrl.'images/'

				//,'connectorUrl' => $connectorUrl

				//,'corePath' => $corePath
				,'modelPath' => $corePath.'model/'
				//,'chunksPath' => $corePath.'elements/chunks/'
				//,'templatesPath' => $corePath.'elements/templates/'
				//,'chunkSuffix' => '.chunk.tpl'
				//,'snippetsPath' => $corePath.'elements/snippets/'
				//,'processorsPath' => $corePath.'processors/'

				,'maxFilesize' => 1048576
				,'fileExtensions' => 'jpg,jpeg,png'
				,'imageExtensions' => 'jpg,jpeg,png'
				,'imageMaxWidth' => 1280
				,'imageMaxHeight' => 720
				,'thumbMinWidth' => 720
				,'thumbMinHeight' => 360
				,'thumbWidth' => 300
				,'thumbHeight' => 176
				,'thumbZC' => 'T'
				,'filesPath' => str_replace(MODX_BASE_PATH, '', $assetsPath) . 'files/'
				,'filesUrl' => $assetsUrl . 'files/'

				,'tplForm' => 'tpl.Uploadify.form'
				,'tplImage' => 'tpl.Uploadify.image'
				,'tplFile' => 'tpl.Uploadify.file'
				,'tplAuth' => 'tpl.Uploadify.auth'

				,'authSnippet' => null
				,'host' => null
				,'uploadiFive' => false

			),$config);

			$this->config['fileExtensions'] = explode(',', $this->config['fileExtensions']);
			$this->config['imageExtensions'] = explode(',', $this->config['imageExtensions']);
			$this->config['filesPath'] = str_replace('//', '/', MODX_BASE_PATH . $this->config['filesPath'] . '/');
			$this->config['filesUrl'] = str_replace('//', '/', $this->config['filesUrl'] . '/');

			$_SESSION['UploadifyConfig'] = $this->config;
		}

		$this->modx->addPackage('uploadify',$this->config['modelPath']);
		$this->modx->lexicon->load('uploadify:default');
	}


	/* Verify and set setting of file processing
	 *
	 * @var string $key Key of setting
	 * @var mixed $value Value of setting
	 * @return boolean
	 * */
	public function setSetting($key, $value) {
		$_SESSION['UploadifyConfig'][$key] = $value;

		return true;
	}

	/* Loads uploading form to frontend and registers scripts
	 *
	 * @return string html Processed form
	 * */
	public function getForm() {
		if (!$this->checkAuth()) {
			return $this->modx->getChunk($this->config['tplAuth']);
		}

		$this->modx->regClientStartupScript('	<script type="text/javascript">
			UploadifyConfig = {
				jsUrl: "'.$this->config['jsUrl'].'web/"
				,cssUrl: "'.$this->config['cssUrl'].'web/"
				,uploadiFive: '.$this->config['uploadiFive'].'
			};
		</script>');
		$this->modx->regClientScript($this->config['jsUrl'].'web/uploadify.js');

		if ($this->config['uploadiFive']) {
			$this->modx->regClientCSS($this->config['cssUrl'].'web/uploadifive.css');
		}
		else {
			$this->modx->regClientCSS($this->config['cssUrl'].'web/uploadify.css');
		}

		$timestamp = time();
		$placeholders = array(
			'timestamp' => $timestamp
			,'hash' => $this->getHash($timestamp)
			,'assetsUrl' => $this->config['assetsUrl']
			,'maxFilesize' => $this->config['maxFilesize']
		);

		return $this->modx->getChunk($this->config['tplForm'], $placeholders);
	}


	/* Main processing method
	 *
	 * @var array $data Array with data of uploading file
	 * @return json $response
	 * */
	public function uploadFile($data) {
		if (!$this->checkAuth()) {
			return 'Wrong auth';
		}

		$fileParts = pathinfo($data['name']);
		$data['extension'] = strtolower($fileParts['extension']);

		// Main verifications
		if (!$file = is_uploaded_file($data['tmp_name'])) {
			return $this->failure('uploadify_err_nofile');
		}
		else if (!in_array($data['extension'], $this->config['fileExtensions'])) {
			return $this->failure('uploadify_err_extension');
		}
		else if ($data['size'] > $this->config['maxFilesize']) {
			return $this->failure('uploadify_err_size');
		}

		// Save image
		if (in_array($data['extension'], $this->config['imageExtensions'])) {
			if ($file = $this->saveImage($data)) {
				$arr = array(
					'image' => $this->config['filesUrl'] . $file['path'] . $file['image']
					,'thumb' => !empty($file['thumb']) ? $this->config['filesUrl'] . $file['path'] . $file['thumb'] : ''
				);
				return $this->success($this->modx->getChunk($this->config['tplImage'], $arr));
			}
		}
		// Save file
		else {
			if ($file = $this->saveFile($data)) {
				$arr = array(
					'file' => $this->config['filesUrl'] . $file['path'] . $file['file']
				);
				return $this->success($this->modx->getChunk($this->config['tplFile'], $arr));
			}
		}

		return $this->failure('uploadify_err');
	}


	/* Converts and saves uploaded image
	 *
	 * @var array $data Array with data of uploading file
	 * @return mixed
	 * */
	public function saveImage($data) {
		if (is_uploaded_file($data['tmp_name'])) {
			$hash = md5($data['tmp_name'] . rand());
			$path = $hash[0] .'/'. $hash[1] . '/' . $hash[2] . '/';
			$fullPath = $this->config['filesPath'] . $path;
			$filename = $hash . '.' . $data['extension'];
			$thumbname = $hash . '_thumb.' . $data['extension'];

			if ($this->checkPath($fullPath)) {
				move_uploaded_file($data['tmp_name'], $fullPath . $filename);

				if (file_exists($fullPath . $filename)) {
					$dimension = getimagesize($fullPath . $filename);

					// Generate image
					$params = array(
						'w' => $dimension[0] > $this->config['imageMaxWidth'] ? $this->config['imageMaxWidth'] : $dimension[0]
						,'h' => $dimension[1] > $this->config['imageMaxHeight'] ? $this->config['imageMaxHeight'] : $dimension[1]
						,'bg' => 'ffffff'
						,'q' => 95
						,'zc' => 0
						,'f' => substr($data['extension'], 1)
					);
					$this->phpThumb($fullPath . $filename, $fullPath . $filename, $params);
					$arr = array(
						'path' => $path
						,'image' => $filename
					);
					if ($size = filesize($fullPath . $filename)) {
						$entry = $this->modx->newObject('uFile', array(
							'uid' => !empty($_SESSION['uid']) ? $_SESSION['uid'] : $this->modx->user->id
							,'timestamp' => time()
							,'path' => $fullPath
							,'url' => $this->config['filesUrl'] . $path
							,'file' => $filename
							,'size' => $size
							,'ip' => $_SERVER['REMOTE_ADDR']
							,'host' => $this->config['host']
						));
						$entry->save();
					}

					// Generate thumb, if needed
					if ($dimension[0] > $this->config['thumbMinWidth'] || $dimension[1] > $this->config['thumbMinHeight']) {
						$params = array(
							'w' => $this->config['thumbWidth']
							,'h' => $this->config['thumbHeight']
							,'bg' => 'ffffff'
							,'q' => 95
							,'zc' => $this->config['thumbZC']
							,'f' => substr($data['extension'], 1)
						);
						$this->phpThumb($fullPath . $filename, $fullPath . $thumbname, $params);
						$arr['thumb'] = $thumbname;
					}

					return $arr;
				}
			}
		}
		return false;
	}


	/* Saves uploaded non-image file
	 *
	 * @var array $data Array with data of uploading file
	 * @return mixed
	 * */
	public function saveFile($data) {
		if (is_uploaded_file($data['tmp_name'])) {
			$hash = md5($data['tmp_name'] . rand());
			$path = $hash[0] .'/'. $hash[1] . '/' . $hash[2] . '/';
			$fullPath = $this->config['filesPath'] . $path;
			$filename = $hash . '.' . $data['extension'];

			if ($this->checkPath($fullPath)) {
				move_uploaded_file($data['tmp_name'], $fullPath . $filename);
				if (file_exists($fullPath . $filename)) {
					$arr = array(
						'path' => $path
						,'file' => $filename
					);

					if ($size = filesize($fullPath . $filename)) {
						$entry = $this->modx->newObject('uFile', array(
							'uid' => !empty($_SESSION['uid']) ? $_SESSION['uid'] : $this->modx->user->id
							,'timestamp' => time()
							,'path' => $fullPath
							,'url' => $this->config['filesUrl'] . $path
							,'file' => $filename
							,'size' => $size
							,'ip' => $_SERVER['REMOTE_ADDR']
							,'host' => $this->config['host']
						));
						$entry->save();
					}

					return $arr;
				}
				else {
					return $this->failure('uploadify_err_save');
				}
			}
			else {
				return $this->failure('uploadify_err_save');
			}
		}
		return false;
	}


	/* Checks and recursively create path for upload
	 *
	 * @var string $fullPath Full path for checking
	 * @return boolean
	 * */
	public function checkPath($fullPath) {
		$fullPath = explode('/', str_replace(MODX_BASE_PATH, '', $fullPath));

		$path = '';
		foreach ($fullPath as $v) {
			if ($v == '') {continue;}

			$path .= '/' . $v;
			if (!file_exists(MODX_BASE_PATH . $path)) {
				if (!mkdir(MODX_BASE_PATH . $path)) {
					return false;
				}
			}
		}
		return true;
	}


	/* Runs snippet that checks permission of user to upload files
	 *
	 * @return boolean
	 * */
	function checkAuth() {
		if (!empty($this->config['authSnippet'])) {
			return $this->modx->runSnippet($this->config['authSnippet']);
		}
		else {
			return true;
		}
	}


	/* Returns unique hash based on site id
	 *
	 * @return string $hash Unique hash
	 * */
	public function getHash($timestamp) {
		return md5(md5($this->modx->site_id . $timestamp).$this->modx->site_id);
	}


	/* Shorthand for failure response
	 *
	 * @var sting $message Error message
	 * */
	public function failure($message) {
		return $this->response($this->modx->lexicon($message), array(), false);
	}


	/* Shorthand for success response
	 *
	 * @var mixed $data Info for end user
	 * */
	public function success($data) {
		return $this->response('', $data, true);
	}


	/* General method for returning result of work
	 *
	 * @var string $message Optional message to end user
	 * @var mixed $data Any additional data for end user
	 * @return boolean $success Type of response
	 * */
	public function response($message = '', $data = array(), $success = true) {
		$response = array(
			'success' => $success
			,'message' => $message
			,'data' => $data
		);

		return json_encode($response);
	}


	/* Process image with specified parameters
	 *
	 * @var string $src Source file
	 * @var string $dst Destination file
	 * @var array $params Array with processing parameters
	 * @return boolean
	 * */
	public function phpThumb($src, $dst, $params = array()) {
		if (empty($dst)) {$dst = $src;}

		require_once MODX_CORE_PATH.'model/phpthumb/phpthumb.class.php';
		$phpThumb = new phpThumb();
		$phpThumb->setSourceFilename($src);
		foreach ($params as $k => $v) {
			$phpThumb->setParameter($k, $v);
		}

		if ($phpThumb->GenerateThumbnail()) {
			if ($phpThumb->RenderToFile($dst)) {
				return true;
			}
		}
		$this->modx->log(modX::LOG_LEVEL_ERROR, print_r($phpThumb->debugmessages, true));
		return false;
	}

}