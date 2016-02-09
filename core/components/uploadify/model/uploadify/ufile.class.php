<?php

class uFile extends xPDOSimpleObject
{
    /** @var modPhpThumb $phpThumb */
    public $phpThumb;
    /** @var modMediaSource $mediaSource */
    public $mediaSource;


    /**
     * @param string $ctx
     *
     * @return bool
     */
    public function prepareSource($ctx = 'web')
    {
        if ($this->mediaSource) {
            $this->mediaSource->errors = array();

            return $this->mediaSource;
        } elseif ($this->mediaSource = $this->xpdo->getObject('sources.modMediaSource', $this->get('source'))) {
            $this->mediaSource->set('ctx', $ctx);
            $this->mediaSource->initialize();

            return $this->mediaSource;
        }

        return false;
    }


    /**
     * @param array $options
     * @param null $raw
     *
     * @return bool|string
     */
    public function Resize(array $options, $raw = null)
    {
        if ($this->get('type') != 'image') {
            return false;
        } elseif (!$raw = $this->_phpThumb($options, $raw)) {
            return false;
        }

        $filename = $this->get('path') . $this->get('file');
        if ($this->prepareSource()) {
            $this->mediaSource->updateObject($filename, $raw);
            if (empty($this->mediaSource->errors['file'])) {
                $this->set('size', strlen($raw));
                $this->save();

                return $this->mediaSource->getObjectUrl($filename);
            } else {
                $this->xpdo->log(modX::LOG_LEVEL_ERROR,
                    "Could not update file {$filename}: " . $this->mediaSource->errors['file']);
            }
        }

        return false;
    }


    /**
     * @param array $options
     * @param null $raw
     *
     * @return bool|string
     */
    public function makeThumbnail(array $options, $raw = null)
    {
        if ($this->get('type') != 'image') {
            return false;
        } elseif (!$raw = $this->_phpThumb($options, $raw)) {
            return false;
        }

        $new_file = $this->getThumbName($options);
        if ($this->prepareSource()) {
            $this->mediaSource->createObject($this->get('path'), $new_file, $raw);
            if (empty($this->mediaSource->errors['file'])) {
                $url = $this->mediaSource->getObjectUrl($this->get('path') . $new_file);
                $thumb = $this->xpdo->newObject('uFile');
                $thumb->fromArray(array_merge(
                        $this->toArray(),
                        array(
                            'file' => $new_file,
                            'size' => strlen($raw),
                            'parent' => $this->get('id'),
                            'url' => $url,
                        )
                    )
                );
                $thumb->save();

                return $url;
            } else {
                $this->xpdo->log(modX::LOG_LEVEL_ERROR,
                    "Could not create file {$new_file}: " . $this->mediaSource->errors['file']);
            }
        }

        return false;
    }


    /**
     * @param array $options
     * @param null $raw
     *
     * @return bool|null
     */
    protected function _phpThumb(array $options = array(), $raw = null)
    {
        if ($this->get('type') != 'image') {
            return false;
        } elseif (!class_exists('modPhpThumb')) {
            /** @noinspection PhpIncludeInspection */
            require MODX_CORE_PATH . 'model/phpthumb/modphpthumb.class.php';
        }
        if (empty($raw)) {
            $prepare = $this->prepareSource();
            if ($prepare !== true) {
                return $prepare;
            }

            $filename = $this->get('path') . $this->get('file');
            $info = $this->mediaSource->getObjectContents($filename);
            if (!is_array($info)) {
                return "Could not retrieve contents of file {$filename} from media source.";
            } elseif (!empty($this->mediaSource->errors['file'])) {
                return "Could not retrieve file {$filename} from media source: " . $this->mediaSource->errors['file'];
            }
            $raw = $info['content'];
        }

        /** @noinspection PhpParamsInspection */
        $phpThumb = new modPhpThumb($this->xpdo);
        $phpThumb->initialize();

        $tmp = tempnam(MODX_BASE_PATH, 'uf_');
        file_put_contents($tmp, $raw);
        $phpThumb->setSourceFilename($tmp);

        foreach ($options as $k => $v) {
            $phpThumb->setParameter($k, $v);
        }

        if ($phpThumb->GenerateThumbnail()) {
            imageinterlace($phpThumb->gdimg_output, true);
            if ($phpThumb->RenderOutput()) {
                @unlink($phpThumb->sourceFilename);
                @unlink($tmp);
                $this->xpdo->log(modX::LOG_LEVEL_INFO,
                    '[Uploadify] phpThumb messages for "' . $this->get('url') . '". ' .
                    print_r($phpThumb->debugmessages, 1)
                );

                return $phpThumb->outputImageData;
            }
        }
        @unlink($phpThumb->sourceFilename);
        @unlink($tmp);
        $this->xpdo->log(modX::LOG_LEVEL_ERROR,
            '[Uploadify] Could not resize "' . $this->get('url') . '". ' . print_r($phpThumb->debugmessages, 1));

        return false;
    }


    /**
     * @param array $options
     *
     * @return string
     */
    public function getThumbName(array $options = array())
    {
        $tmp = explode('.', $this->get('file'));
        $ext = !empty($options['f'])
            ? $options['f']
            : $tmp[1];

        return $tmp[0] . 's.' . $ext;
    }


    /**
     * @param $raw
     *
     * @return bool|string
     */
    public function createFile($raw)
    {
        if ($this->prepareSource()) {
            $this->mediaSource->createContainer($this->get('path'), '/');

            if ($file = $this->mediaSource->createObject($this->get('path'), $this->get('file'), $raw)) {
                return $this->mediaSource->getObjectUrl($this->get('path') . $this->get('file'));
            }
        }

        return false;
    }


    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = array())
    {
        $filename = $this->get('path') . $this->get('file');
        if ($this->prepareSource()) {
            $this->mediaSource->removeObject($filename);
            if (!empty($this->mediaSource->errors['file'])) {
                $this->xpdo->log(xPDO::LOG_LEVEL_ERROR,
                    '[Uploadify] Could not remove file at "' . $filename . '": ' . $this->mediaSource->errors['file']
                );
            }
        }

        $children = $this->xpdo->getIterator('uFile', array('parent' => $this->get('id')));
        /** @var uFile $child */
        foreach ($children as $child) {
            $child->remove();
        }

        return parent::remove($ancestors);
    }

}