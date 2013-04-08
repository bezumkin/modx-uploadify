<?php
class uFile extends xPDOSimpleObject {
	public $file;
	/* @var modPhpThumb $phpThumb */
	public $phpThumb;
	/* @var modMediaSource $mediaSource */
	public $mediaSource;


	public function initializeMediaSource($ctx = 'web') {
		if ($this->mediaSource) {return true;}
		else if  ($this->mediaSource = $this->xpdo->getObject('sources.modMediaSource', $this->get('source'))) {
			$this->mediaSource->set('ctx', $ctx);
			$this->mediaSource->initialize();
			return true;
		}
		else {
			return false;
		}
	}


	public function Resize(array $options) {
		if ($this->get('type') != 'image') {return false;}

		$this->initializeMediaSource();
		$this->file = $this->mediaSource->getObjectContents($this->get('path').$this->get('file'));
		$raw = $this->phpThumb($options);

		$file = $this->mediaSource->updateObject(
			$this->get('path') . $this->get('file')
			,$raw
		);

		$this->set('size', strlen($raw));
		$this->save();

		return $file ? $this->mediaSource->getObjectUrl($this->get('path').$this->get('file')) : false;
	}


	public function makeThumbnail(array $options) {
		if ($this->get('type') != 'image') {return false;}

		$this->initializeMediaSource();
		$this->file = $this->mediaSource->getObjectContents($this->get('path').$this->get('file'));
		$raw = $this->phpThumb($options);

		$new_file = $this->getThumbName($options);
		$file = $this->mediaSource->createObject(
			$this->get('path')
			,$new_file
			,$raw
		);

		$url = $this->mediaSource->getObjectUrl($this->get('path').$new_file);
		$thumb = $this->xpdo->newObject('uFile');
		$thumb->fromArray(array_merge(
			$this->toArray()
			,array(
				'file' => $new_file
				,'size' => strlen($raw)
				,'parent' => $this->id
				,'url' => $url
			))
		);
		$thumb->save();

		return $file ? $url : false;
	}


	public function getThumbName(array $options = array()) {
		$tmp = explode('.', $this->get('file'));
		$ext = !empty($options['f']) ? $options['f'] : $tmp[1];
		return $tmp[0] . 's.' . $ext;
	}


	public function phpThumb($options = array()) {
		require_once MODX_CORE_PATH . 'model/phpthumb/modphpthumb.class.php';
		$phpThumb = new modPhpThumb($this->xpdo);
		$phpThumb->initialize();

		$tmp = tempnam(MODX_BASE_PATH, 'uf_');
		file_put_contents($tmp, $this->file['content']);
		$phpThumb->setSourceFilename($tmp);

		foreach ($options as $k => $v) {
			$phpThumb->setParameter($k, $v);
		}

		if ($phpThumb->GenerateThumbnail() && $phpThumb->RenderOutput()) {
			@unlink($phpThumb->sourceFilename);
			@unlink($tmp);
			return $phpThumb->outputImageData;
		}
		else {
			$this->xpdo->log(modX::LOG_LEVEL_ERROR, 'Could not resize "'.$this->get('url').'". '.print_r($phpThumb->debugmessages,1));
			return false;
		}
	}


	public function createFile($raw) {
		$this->initializeMediaSource();

		$this->mediaSource->createContainer($this->get('path'), '/');
		$file = $this->mediaSource->createObject(
			$this->get('path')
			,$this->get('file')
			,$raw
		);

		return $file ? $this->mediaSource->getObjectUrl($this->get('path').$this->get('file')) : false;
	}


	public function remove(array $ancestors= array ()) {
		$this->initializeMediaSource();
		$this->mediaSource->removeObject($this->get('path').$this->get('file'));

		return parent::remove($ancestors);
	}


}