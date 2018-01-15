<?php

namespace Floxim\FileStorage;

class AmazonS3 extends Base {

    protected $client = null;
    protected $defaultBucket = null;

    public function __construct($options)
    {
        $this->client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $options['region'],
            'credentials' => [
                'key' => $options['key'],
                'secret' => $options['secret']
            ]
            /*,
            'debug' => [
                'logfn' => function ($msg) {
                    // fx::debug($msg);
                }
            ]
            */
        ]);
        if (isset($options['bucket'])) {
            $this->defaultBucket = $options['bucket'];
        }
    }

    public function saveFile ($url, $target_location = null)
    {

    }

    /**
     * @param $url
     * @param array $options
     * @return bool|\Floxim\FileStorage\File
     */
    public function getFile ($url, $options = [])
    {
        $bucket = isset($options['bucket']) ? $options['bucket'] : $this->defaultBucket;
        $key = self::normalizeUrl($url);
        try {
            $o = $this->client->getObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);
            return self::responseToFile($o, $key);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected static function responseToFile($o, $url)
    {
        return File::createFromBody($o['Body'], [
            'ContentType' => $o['ContentType'],
            'Key' => $url
        ]);
    }

    /**
     * @param $url
     * @param $file \Floxim\FileStorage\File
     * @param array $meta
     * @return mixed
     */
    public function putFile ($url, $file, $meta = [], $options = [])
    {
        $key = self::normalizeUrl($url);
        $putParams = [
            'Bucket' => isset($options['bucket']) ? $options['bucket'] : $this->defaultBucket,
            'Key' => $key,
            'ContentType' => $file->getContentType()
        ];
        $putParams['Body'] = (string) $file;
        return $this->client->putObject($putParams);
    }

    public function putFileBody ($url, $body, $meta = [])
    {

    }

    protected static function normalizeUrl ($url)
    {
        $url = ltrim($url, '/');
        $url = preg_replace("~\?.*$~", '', $url);
        return $url;
    }
}