<?php
//require 'vendor/autoload.php'; // Ensure Composer's autoload is loaded
require __DIR__ . '/../vendor/autoload.php'; 

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function uploadToS3($localFilePath, $bucketName, $s3Key, $type)
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
        'debug' => false,
    ]);

    try {
        //error_log("Bucket: $bucketName, Key: $s3Key, File: $localFilePath");

        $result = $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $s3Key,
            'SourceFile' => $localFilePath,
            'ACL' => 'private',
        ]);

        error_log("S3 Upload Success: " . json_encode($result));

        if ($type == 'user'){
            return $s3Key;
        } elseif ($type == 'lab'){
            return $result['ObjectURL'];
        }

    } catch (AwsException $e) {
        error_log("AWS SDK Error: " . $e->getAwsErrorMessage());
        error_log("AWS SDK Trace: " . $e->getTraceAsString());
        throw new Exception("Error uploading to S3: " . $e->getMessage());
    }
}
