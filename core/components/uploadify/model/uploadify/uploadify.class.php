<?php

class Uploadify
{
    /** @var modX $modx */
    public $modx;
    /** @var array $initialized */
    public $initialized = array();


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        if (empty($config) && !empty($_SESSION['UploadifyConfig'])) {
            $this->config = $_SESSION['UploadifyConfig'];
        } else {
            $corePath = $this->modx->getOption('uploadify.core_path', $config,
                $this->modx->getOption('core_path') . 'components/uploadify/');
            $assetsPath = $this->modx->getOption('uploadify.assets_path', $config,
                $this->modx->getOption('assets_path') . 'components/uploadify/');
            $assetsUrl = $this->modx->getOption('uploadify.assets_url', $config,
                $this->modx->getOption('assets_url') . 'components/uploadify/');
            //$connectorUrl = $assetsUrl.'connector.php';

            $this->config = array_merge(array(
                'assetsUrl' => $assetsUrl,
                'assetsPath' => $assetsPath,
                'cssUrl' => $assetsUrl . 'css/',
                'jsUrl' => $assetsUrl . 'js/',
                'imagesUrl' => $assetsUrl . 'images/',
                'actionUrl' => $assetsUrl . 'action.php',

                'modelPath' => $corePath . 'model/',
                'processorsPath' => $corePath . 'processors/',

                'maxFilesize' => 1048576,
                'fileExtensions' => 'jpg,jpeg,png',
                'imageExtensions' => 'jpg,jpeg,png',
                'imageMaxWidth' => 1280,
                'imageMaxHeight' => 720,
                'imageQuality' => 99,
                //'thumbMinWidth' => 720,
                //'thumbMinHeight' => 360,
                'thumbWidth' => 320,
                'thumbHeight' => 240,
                'thumbZC' => 'T',
                'thumbBG' => 'ffffff',
                'thumbFormat' => 'jpg',
                'thumbQuality' => 90,
                'source' => $this->modx->getOption('uf_source_default', null, 1, true),
                'listThumbSize' => '320x240,640x480',
                'listThumbZC' => '0,C',
                'listThumbBG' => 'ffffff,000000',
                'fromRetina' => isset($_SESSION['UploadifyConfig']['fromRetina'])
                    ? $_SESSION['UploadifyConfig']['fromRetina']
                    : false,

                'tplForm' => 'tpl.Uploadify.form',
                'tplImage' => 'tpl.Uploadify.image',
                'tplFile' => 'tpl.Uploadify.file',
                'tplAuth' => 'tpl.Uploadify.auth',
                'tplOption' => 'tpl.Uploadify.option',

                'authSnippet' => null,
                'host' => null,
            ), $config);

            $tmp = array('fileExtensions', 'imageExtensions', 'listThumbSize', 'listThumbZC', 'listThumbBG');
            foreach ($tmp as $v) {
                $this->config[$v] = array_map('trim', explode(',', $this->config[$v]));
            }
        }

        $tmp = array('ThumbSize', 'ThumbZC', 'ThumbBG');
        foreach ($tmp as $v) {
            $this->config['option' . $v] =
                isset($_SESSION['UploadifyConfig']['option' . $v])
                &&
                in_array($_SESSION['UploadifyConfig']['option' . $v], $this->config['list' . $v])
                    ? $_SESSION['UploadifyConfig']['option' . $v]
                    : $this->config['list' . $v][0];
        }

        if (!empty($this->config['optionThumbSize'])) {
            $tmp = array_map('trim', explode('x', $this->config['optionThumbSize']));
            if (!empty($tmp[0]) && !empty($tmp[1])) {
                $this->config['thumbWidth'] = $tmp[0];
                $this->config['thumbHeight'] = $tmp[1];
            }
        }
        if (isset($this->config['optionThumbZC']) && $this->config['optionThumbZC'] != '') {
            $this->config['thumbZC'] = $this->config['optionThumbZC'];
        }
        if (isset($this->config['optionThumbBG']) && $this->config['optionThumbBG'] != '') {
            $this->config['thumbBG'] = $this->config['optionThumbBG'];
        }

        $_SESSION['UploadifyConfig'] = $this->config;

        $this->modx->addPackage('uploadify', $this->config['modelPath']);
        $this->modx->lexicon->load('uploadify:default');
    }


    /**
     * Initializes component into different contexts.
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties Properties for initialization.
     *
     * @return bool
     */
    public function initialize($ctx = 'web', $scriptProperties = array())
    {
        $this->config = array_merge($this->config, $scriptProperties);
        $this->config['ctx'] = $ctx;
        if (!empty($this->initialized[$ctx])) {
            return true;
        }
        switch ($ctx) {
            case 'mgr':
                break;
            default:
                if (!MODX_API_MODE) {
                    $config = $this->makePlaceholders($this->config);
                    if ($css = $this->modx->getOption('uf_frontend_css')) {
                        $this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
                    }
                    if ($js = $this->modx->getOption('uf_frontend_js')) {
                        $params = $this->modx->toJSON(array(
                            'jsUrl' => $this->config['jsUrl'] . 'web/',
                            'cssUrl' => $this->config['cssUrl'] . 'web/',
                            'actionUrl' => $this->config['actionUrl'],
                            'uploadiFive' => $this->config['uploadiFive'],
                        ));
                        $this->modx->regClientStartupScript(trim('
                            <script type="text/javascript">UploadifyConfig = ' . $params . ';</script>
                        '), true);
                        $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
                    }
                }
                $this->initialized[$ctx] = true;
        }

        return true;
    }


    /**
     * Verify and set setting of file processing
     *
     * @param string $key Key of setting
     * @param mixed $value Value of setting
     *
     * @return boolean
     * */
    public function setOption($key, $value)
    {
        if ($key == 'fromRetina') {
            $_SESSION['UploadifyConfig']['fromRetina'] = (bool)$value;
        } else {
            if (array_key_exists('list' . $key, $this->config) && in_array($value, $this->config['list' . $key])) {
                $_SESSION['UploadifyConfig']['option' . $key] = $value;
            } else {
                return $this->failure($this->modx->lexicon('uf_err_option'));
            }
        }

        return $this->success();
    }


    /**
     * Loads uploading form to frontend and registers scripts
     *
     * @return string html Processed form
     * */
    public function getForm()
    {
        if (!$this->checkAuth()) {
            return $this->modx->getChunk($this->config['tplAuth']);
        }
        $this->initialize($this->modx->context->key);
        $extensions = array();
        foreach ($this->config['fileExtensions'] as $v) {
            $extensions[] = '*.' . $v;
        }

        $tmp = array('ThumbSize', 'ThumbZC', 'ThumbBG');
        $listThumbSize = $listThumbZC = $listThumbBG = '';
        foreach ($tmp as $v) {
            foreach ($this->config['list' . $v] as $v2) {
                $arr = array(
                    'name' => $this->modx->lexicon('uf_frontend_option_' . $v2),
                    'value' => $v2,
                    'selected' => !empty($this->config['option' . $v]) && $this->config['option' . $v] == $v2
                        ? 'selected'
                        : '',
                );
                ${'list' . $v} .= $this->modx->getChunk($this->config['tplOption'], $arr);
            }
        }

        $timestamp = time();
        $placeholders = array(
            'timestamp' => $timestamp,
            'hash' => $this->getHash($timestamp),
            'assetsUrl' => $this->config['assetsUrl'],
            'actionUrl' => $this->config['actionUrl'],
            'maxFilesize' => round($this->config['maxFilesize'] / 1024 / 1024, 2) . 'Mb',
            'fileExtensions' => implode('; ', $extensions),
            'listThumbSize' => str_replace('uf_frontend_option_', '', $listThumbSize),
            'listThumbZC' => str_replace('uf_frontend_option_', '', $listThumbZC),
            'listThumbBG' => str_replace('uf_frontend_option_', '', $listThumbBG),
            'fromRetina' => $this->config['fromRetina'],
        );

        return $this->modx->getChunk($this->config['tplForm'], $placeholders);
    }


    /**
     * Main processing method
     *
     * @param $data
     *
     * @return string
     */
    public function uploadFile($data)
    {
        if (!$this->checkAuth()) {
            return 'Wrong auth';
        }

        $fileParts = pathinfo($data['name']);
        $data['extension'] = strtolower($fileParts['extension']);

        // Main verifications
        if (!$file = is_uploaded_file($data['tmp_name'])) {
            return $this->failure('uf_err_nofile');
        } else {
            if (!in_array($data['extension'], $this->config['fileExtensions'])) {
                return $this->failure('uf_err_extension');
            } else {
                if ($data['size'] > $this->config['maxFilesize']) {
                    return $this->failure('uf_err_size');
                }
            }
        }

        // Save image
        $base_url = $this->modx->getOption('url_scheme', null, 'http://', true);
        $base_url .= $this->modx->getOption('http_host');
        if (in_array($data['extension'], $this->config['imageExtensions'])) {
            if ($arr = $this->saveImage($data)) {
                $arr['image'] = strpos($arr['url'], '://') === false
                    ? $base_url . '/' . ltrim($arr['url'], '/')
                    : $arr['url'];
                $arr['thumb'] = !empty($arr['thumb'])
                    ? (strpos($arr['thumb'], '://') === false
                        ? $base_url . '/' . ltrim($arr['thumb'], '/')
                        : $arr['thumb']
                    )
                    : '';

                return $this->success($this->modx->getChunk($this->config['tplImage'], $arr));
            }
        } else {
            // Save file
            if ($arr = $this->saveFile($data)) {
                $arr['file'] = strpos($arr['url'], '://') === false
                    ? $base_url . '/' . ltrim($arr['url'], '/')
                    : $arr['url'];

                return $this->success($this->modx->getChunk($this->config['tplFile'], $arr));
            }
        }

        return $this->failure('uf_err_unknown');
    }


    /**
     * Converts and saves uploaded image
     *
     * @param $data
     *
     * @return array|bool|string
     */
    public function saveImage($data)
    {
        if (!is_uploaded_file($data['tmp_name'])) {
            return false;
        }
        $raw = file_get_contents($data['tmp_name']);
        $hash = md5($data['tmp_name'] . rand());
        $path = $hash[0] . '/' . $hash[1] . '/' . $hash[2] . '/';
        $filename = $hash . '.' . $data['extension'];

        /** @var modProcessorResponse $response */
        $response = $this->runProcessor('web/file/create', array(
            'source' => $this->config['source'],
            'name' => @$data['name'],
            'description' => @$data['description'],
            'path' => $path,
            'file' => $filename,
            'type' => 'image',
            'raw' => $raw,
        ));
        if ($response->isError()) {
            return $this->failure($response->getAllErrors());
        }

        /** @var uFile $file */
        $file = $this->modx->getObject('uFile', $response->response['object']['id']);
        $arr = $file->toArray();

        $dimension = getimagesize($data['tmp_name']);

        if ($dimension[0] > $this->config['thumbWidth'] || $dimension[1] > $this->config['thumbHeight']) {
            $options = array(
                'w' => !$this->config['thumbZC'] && $dimension[0] < $this->config['thumbWidth']
                    ? $dimension[0]
                    : $this->config['thumbWidth'],
                'h' => !$this->config['thumbZC'] && $dimension[1] < $this->config['thumbHeight']
                    ? $dimension[1]
                    : $this->config['thumbHeight'],
                'bg' => $this->config['thumbBG'],
                'q' => $this->config['thumbQuality'],
                'zc' => $this->config['thumbZC'],
                'f' => $this->config['thumbFormat'],
            );
            $arr['thumb'] = $file->makeThumbnail($options, $raw);
        }

        $options = array(
            'q' => $this->config['imageQuality'],
            'f' => !empty($dimension['mime']) && preg_match('#^image/(.*)#', $dimension['mime'], $matches)
                ? $matches[1]
                : 'jpg',
        );

        // We can`t resize animated GIF
        if ($options['f'] == 'gif' && preg_match_all('#\x00\x21\xF9\x04.{4}\x00\x2C#s', $raw, $matches)) {
            if (count($matches[0]) > 1) {
                return $arr;
            }
        }

        // Images from retina display can be reduced twice
        if ($this->config['fromRetina']) {
            $dimension[0] = $options['w'] = floor($dimension[0] / 2);
            $dimension[1] = $options['h'] = floor($dimension[1] / 2);
        }

        // Resizing image
        if ($dimension[0] > $this->config['imageMaxWidth'] && $dimension[1] > $this->config['imageMaxHeight']) {
            if ($dimension[0] > $dimension[1]) {
                $tmp = round($dimension[0] / $dimension[1], 2);
                $tmp2 = round($dimension[1] / $tmp);
                if ($tmp2 > $this->config['imageMaxHeight']) {
                    $options['h'] = $this->config['imageMaxHeight'];
                } else {
                    $options['w'] = $this->config['imageMaxWidth'];
                }
            } else {
                $tmp = round($dimension[1] / $dimension[0], 2);
                $tmp2 = round($dimension[0] / $tmp);
                if ($tmp2 > $this->config['imageMaxWidth']) {
                    $options['w'] = $this->config['imageMaxWidth'];
                } else {
                    $options['h'] = $this->config['imageMaxHeight'];
                }
            }
        } else {
            if ($dimension[0] > $this->config['imageMaxWidth']) {
                $options['w'] = $this->config['imageMaxWidth'];
            } else {
                if ($dimension[1] > $this->config['imageMaxHeight']) {
                    $options['h'] = $this->config['imageMaxHeight'];
                } else {
                    $options['w'] = $dimension[0];
                    $options['h'] = $dimension[1];
                }
            }
        }
        $file->Resize($options, $raw);

        return $arr;
    }


    /**
     * Saves uploaded non-image file
     *
     * @param $data
     *
     * @return array|bool|string
     */
    public function saveFile($data)
    {
        if (is_uploaded_file($data['tmp_name'])) {
            $hash = md5($data['tmp_name'] . rand());
            $path = $hash[0] . '/' . $hash[1] . '/' . $hash[2] . '/';
            $filename = $hash . '.' . $data['extension'];

            /** @var modProcessorResponse $response */
            $response = $this->runProcessor('web/file/create', array(
                'source' => $this->config['source'],
                'name' => @$data['name'],
                'description' => @$data['description'],
                'path' => $path,
                'file' => $filename,
                'type' => 'file',
                'raw' => file_get_contents($data['tmp_name']),
            ));
            if ($response->isError()) {
                return $this->failure($response->getAllErrors());
            }

            /** @var uFile $file */
            $file = $this->modx->getObject('uFile', $response->response['object']['id']);

            return $file->toArray();

        }

        return false;
    }


    /**
     * Runs snippet that checks permission of user to upload files
     *
     * @return bool|string
     */
    function checkAuth()
    {
        return !empty($this->config['authSnippet'])
            ? $this->modx->runSnippet($this->config['authSnippet'], $this->config)
            : true;
    }


    /**
     * Returns unique hash based on site id
     *
     * @param $timestamp
     *
     * @return string
     */
    public function getHash($timestamp)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return md5(md5($this->modx->site_id . $timestamp) . $this->modx->site_id);
    }


    /**
     * @param $message
     *
     * @return string
     */
    public function failure($message)
    {
        return $this->response($this->modx->lexicon($message), array(), false);
    }


    /**
     * Shorthand for success response
     *
     * @param array $data
     *
     * @return string
     */
    public function success($data = array())
    {
        return $this->response('', $data, true);
    }


    /**
     * General method for returning result of work
     *
     * @param string $message
     * @param array $data
     * @param bool $success
     *
     * @return string
     */
    public function response($message = '', $data = array(), $success = true)
    {
        $response = array(
            'success' => $success,
            'message' => $message,
            'data' => $data,
        );

        return json_encode($response);
    }


    /**
     * Method for transform array to placeholders
     *
     * @param array $array
     * @param string $prefix
     *
     * @return array
     */
    public function makePlaceholders(array $array = array(), $prefix = '')
    {
        $result = array(
            'pl' => array(),
            'vl' => array(),
        );
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result = array_merge_recursive($result, $this->makePlaceholders($v, $k . '.'));
            } else {
                $result['pl'][$prefix . $k] = '[[+' . $prefix . $k . ']]';
                $result['vl'][$prefix . $k] = $v;
            }
        }

        return $result;
    }


    /**
     * Shorthand for the call of processor
     *
     * @access public
     *
     * @param string $action Path to processor
     * @param array $data Data to be transmitted to the processor
     *
     * @return mixed The result of the processor
     */
    public function runProcessor($action = '', $data = array())
    {
        if (empty($action)) {
            return false;
        }

        return $this->modx->runProcessor($action, $data, array('processors_path' => $this->config['processorsPath']));
    }

}