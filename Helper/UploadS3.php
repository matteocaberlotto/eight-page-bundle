<?php

namespace Eight\PageBundle\Helper;

use Psr\Log\LoggerInterface;

/**
 * Helper for s3 file upload
 */
class UploadS3
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        try {
            $this->s3 = new \Aws\S3\S3Client([
                'region'  => $_SERVER['AWS_DEFAULT_REGION'],
                'version' => 'latest',
                'credentials' => [
                    'key'    => $_SERVER['AWS_ACCESS_KEY_ID'],
                    'secret' => $_SERVER['AWS_SECRET_ACCESS_KEY'],
                ],
            ]);
        } catch(\Exception $e) {
            $this->logger->error('EightPage connection to S3 failed:  ' . $e->getMessage());
        }
    }

    public function upload($source, $filepath)
    {
        try {
            $this->logger->info('Uploading ' . $source . ' as ' . mime_content_type($source));
            $result = $this->s3->putObject([
                'Bucket' => $_SERVER['EIGHT_S3_BUCKET'],
                'Key'    => ($_SERVER['EIGHT_S3_PATH'] ? $_SERVER['EIGHT_S3_PATH'] . DIRECTORY_SEPARATOR : '') . $filepath,
                'SourceFile' => $source,
                'ACL'        => 'public-read',
                'ContentType' => mime_content_type($source),
            ]);
        } catch(\Exception $e) {
            $this->logger->error('EightPage upload to S3 failed:  ' . $e->getMessage());
        }
    }
}
