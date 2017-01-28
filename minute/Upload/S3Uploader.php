<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 7/18/2016
 * Time: 1:12 PM
 */
namespace Minute\Upload {

    use Illuminate\Support\Str;
    use Minute\Aws\Client;
    use Minute\Config\Config;
    use Minute\Error\AwsError;
    use Minute\Event\UserUploadEvent;
    use Minute\Mime\MimeUtils;

    class S3Uploader {
        /**
         * @var Client
         */
        private $client;
        /**
         * @var Config
         */
        private $config;
        /**
         * @var MimeUtils
         */
        private $mimeUtils;

        /**
         * S3Uploader constructor.
         *
         * @param Client $client
         * @param Config $config
         * @param MimeUtils $mimeUtils
         */
        public function __construct(Client $client, Config $config, MimeUtils $mimeUtils) {
            $this->client    = $client;
            $this->config    = $config;
            $this->mimeUtils = $mimeUtils;
        }

        public function upload(UserUploadEvent $event) {
            $uploader = $this->client->getS3Client();
            $uploads  = $this->config->get(Client::AWS_KEY . '/uploads');
            $defaults = ['image' => ['jpg', 'jpeg', 'png', 'gif'], 'video' => ['mp4', 'avi', 'mov', 'mkv'], 'sound' => ['mp3', 'wav', 'ogg'], 'other' => ['swf', 'bin']];
            $allowed  = $this->config->get('private/extensions', $defaults);
            $bucket   = $uploads['upload_bucket'] ?? sprintf('www.%s', $this->config->getPublicVars('domain'));
            $user_id  = $event->getUserId();

            if (!empty($user_id) || !empty($uploads['anonymous_uploads'])) {
                $data       = $event->getFileData();
                $remotePath = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '', ltrim($event->getRemotePath() ?: basename($event->getLocalPath()), '.'));

                if (!empty($data)) {
                    if (strpos($remotePath, '/') !== false) {
                        return ltrim($remotePath, '/');
                    } else {
                        $ext = strtolower(pathinfo($remotePath, PATHINFO_EXTENSION));

                        foreach ($allowed as $type => $extensions) {
                            if (in_array($ext, $extensions)) {
                                $folder = $type;
                                break;
                            }
                        }

                        $remotePath = sprintf('users/%s/%s/%s', $user_id ?: 'anonymous', $folder ?? 'image', !empty($folder) ? $remotePath : "$remotePath.png");
                    }

                    try {
                        if ($url = $uploader->getObjectUrl($bucket, $remotePath)) {
                            if ($response = $uploader->headObject(['Bucket' => $bucket, 'Key' => $remotePath])) {
                                if ($response['ContentLength'] === strlen($data)) {
                                    if ($cdn = $uploads['cdn_cname'] ?? null) {
                                        $url = sprintf('http%s://%s/%s', !empty($uploads['https_only']) ? 's' : '', $uploads['cdn_host'] ?? $uploads['cdn_cname'], $remotePath);
                                    }

                                    return $event->setUrl($url);
                                } else {
                                    $remotePath = sprintf('%s/%s.%s', pathinfo($remotePath, PATHINFO_DIRNAME), Str::random(16), pathinfo($remotePath, PATHINFO_EXTENSION));
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                    }

                    if ($cache = $uploads['cache'] ?? 30 * 86400) {
                        $headers = ['params' => [
                            'CacheControl' => "public, max-age=$cache",
                            //'Expires' => gmdate("D, d M Y H:i:s T", strtotime("+$cache seconds")),
                        ]];
                    }

                    if ($mime = $this->mimeUtils->getMimeType($remotePath)) {
                        $headers['params']['ContentType'] = $mime;
                    }

                    if ($url = $uploader->upload($bucket, $remotePath, $data, 'public-read', $headers ?? [])) {
                        if ($cdn = $uploads['cdn_cname'] ?? null) {
                            $theUrl = sprintf('http%s://%s/%s', !empty($uploads['https_only']) ? 's' : '', $uploads['cdn_host'] ?? $uploads['cdn_cname'], $remotePath);
                        } else {
                            $theUrl = $url->get('ObjectURL');
                        }

                        return $event->setUrl($theUrl);
                    }
                }
            } else {
                throw new AwsError("Anonymous uploads are not enabled");
            }

            throw new AwsError("Upload failed, S3 configuration error");
        }
    }
}