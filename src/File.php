<?php

namespace Floxim\FileStorage;

class File {

    protected $body;
    protected $meta = [];

    public static function createFromBody($body, $meta)
    {
        $file = new static();
        $file->body = (string) $body;
        if (!isset($meta['ContentType']) && isset($meta['Key'])) {
            $meta['ContentType'] = Base::getContentTypeByUrl($meta['Key']);
        }
        $file->meta = $meta;
        return $file;
    }

    /**
     * @param $path
     * @param array $meta
     * @return \Floxim\FileStorage\File
     */
    public static function createFromPath($path, $meta = [])
    {
        $file = new static();
        $file->body = file_get_contents($path);
        $file->meta = array_merge(
            [
                'Key' => $path,
                'ContentType' => Base::getContentTypeByUrl($path)
            ],
            $meta
        );
        return $file;
    }

    public function __toString()
    {
        return $this->body;
    }

    public function getContentType()
    {
        return $this->getMeta('ContentType');
    }

    public function getKey()
    {
        return $this->getMeta('Key');
    }

    public function getMeta($key, $default = null)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : $default;
    }

    public function dump()
    {
        header("Content-type: ".$this->getContentType());
        echo $this;
    }

    public function saveToDir($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $key = $this->getKey();
        $targetPath = $dir . '/'.md5(rand(100000, 999999).time());
        if (preg_match("~\.[^/]+~", $key, $ext)) {
            $targetPath .= $ext[0];
        }
        file_put_contents($targetPath, (string) $this);
        return $targetPath;
    }
}