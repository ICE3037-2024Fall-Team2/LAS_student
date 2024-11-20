<?php
//require 'vendor/autoload.php'; // Ensure Composer's autoload is loaded
require __DIR__ . '/../vendor/autoload.php'; // 引用共享的 autoload.php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function uploadToS3($localFilePath, $bucketName, $s3Key)
{
    $awsKey = getenv('AWS_ACCESS_KEY_ID');
    $awsSecret = getenv('AWS_SECRET_ACCESS_KEY');
    $region = getenv('AWS_DEFAULT_REGION');

    if (!$awsKey || !$awsSecret || !$region) {
        throw new Exception("AWS credentials are not set in the environment.");
    }

    $s3 = new S3Client([
        'region' => $region,
        'version' => 'latest',
        'credentials' => [
            'key' => $awsKey,
            'secret' => $awsSecret,
        ],
    ]);

    try {
        // Upload the file to S3
        $result = $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $s3Key,
            'SourceFile' => $localFilePath,
            'ACL' => 'private', // Ensure the file is private
        ]);

        // Return the S3 Key (not the full URL)
        return $s3Key;
    } catch (AwsException $e) {
        throw new Exception("Error uploading file to S3: " . $e->getMessage());
    }
}
