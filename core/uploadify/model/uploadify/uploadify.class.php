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
			$connectorUrl = $assetsUrl.'connector.php';

			$this->config = array_merge(array(
				'assetsUrl' => $assetsUrl
				,'assetsPath' => $assetsPath
				,'cssUrl' => $assetsUrl.'css/'
				,'jsUrl' => $assetsUrl.'js/'
				,'imagesUrl' => $assetsUrl.'images/'

				,'connectorUrl' => $connectorUrl

				,'corePath' => $corePath
				,'modelPath' => $corePath.'model/'
				,'chunksPath' => $corePath.'elements/chunks/'
				,'templatesPath' => $corePath.'elements/templates/'
				,'chunkSuffix' => '.chunk.tpl'
				,'snippetsPath' => $corePath.'elements/snippets/'
				,'processorsPath' => $corePath.'processors/'

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
	
	
	public function getForm() {
		if (!$this->checkAuth()) {
			return $this->modx->getChunk($this->config['tplAuth']);
		}
	
		$this->modx->regClientStartupScript('	<script type="text/javascript">
			UploadifyConfig = {
				jsUrl: "'.$this->config['jsUrl'].'web/"
				,cssUrl: "'.$this->config['cssUrl'].'web/"
			};
			if(typeof jQuery == "undefined") {
				document.write("<script type=\"text/javascript\" src=\"'.$this->config['jsUrl'].'web/lib/jquery-1.9.0.min.js\"><\/script>");
			}
		</script>');
		$this->modx->regClientScript($this->config['jsUrl'].'web/uploadify.js');
		$this->modx->regClientCSS($this->config['cssUrl'].'web/uploadify.css');
		
		$timestamp = time();
		$placeholders = array(
			'timestamp' => $timestamp
			,'hash' => $this->getHash($timestamp)
		);
		
		return $this->modx->getChunk($this->config['tplForm'], $placeholders);
	}
	
	
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
				die;
			}
		}
		// Save file
		else {
			if ($file = $this->saveFile($data)) {
				$arr = array(
					'file' => $this->config['filesUrl'] . $file['path'] . $file['file']
				);
				return $this->success($this->modx->getChunk($this->config['tplFile'], $arr));
				die;
			}
		}
		
		return $this->failure('uploadify_err');
	}
	
	
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
						$entry = $this->modx->newObject('ufUserFile', array(
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
	
	
	public function saveFile($data) {
		if (is_uploaded_file($data['tmp_name'])) {
			$hash = md5($data['tmp_name'] . rand());
			$path = $hash[0] .'/'. $hash[1] . '/' . $hash[2] . '/';
			$fullPath = $this->config['filesPath'] . $path;
			$filename = $hash . '.' . $data['extension'];
			
			if ($this->checkPath($fullPath)) {
				move_uploaded_file($data['tmp_name'], $fullPath . $filename);
				if (file_exists($path . $newname)) {
					$arr = array(
						'path' => $path
						,'file' => $filename
					);
					
					if ($size = filesize($fullPath . $filename)) {
						$entry = $this->modx->newObject('ufUserFile', array(
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
	
	
	public function checkPath($fullPath) {
		$fullPath = explode('/', str_replace(MODX_BASE_PATH, '', $fullPath));
		
		$tmp = array();
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
	
	
	function checkAuth() {
		if (!empty($this->config['authSnippet'])) {
			return $this->modx->runSnippet($this->config['authSnippet']);
		}
		else {
			return true;
		}
	}
	
	
	public function getHash($timestamp) {
		return md5(md5($this->modx->site_id . $timestamp).$this->modx->site_id);
	}
	
	
	public function failure($message) {
		return $this->response($this->modx->lexicon($message), array(), false);
	}
	
	
	public function success($data) {
		return $this->response('', $data, true);
	}
	
	
	public function response($message = '', $data = '', $success = true) {
		$response = array(
			'success' => $success
			,'message' => $message
			,'data' => $data
		);
		
		return json_encode($response);
	}
	
	
	public function phpThumb($src, $dst = '', $params = array()) {
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