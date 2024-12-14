<?php
//require 'vendor/autoload.php'; // Ensure Composer's autoload is loaded
require __DIR__ . '/../vendor/autoload.php'; 

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function generatePresignedUrl($bucketName, $key, $expiration = '+60 minutes')
{
    $awsKey = getenv('AWS_ACCESS_KEY_ID');
    $awsSecret = getenv('AWS_SECRET_ACCESS_KEY');
    $region = getenv('AWS_DEFAULT_REGION');

    // If credentials or region are not set, throw an error
    if (!$awsKey || !$awsSecret || !$region) {
        throw new Exception("AWS credentials are not set in the environment.");
    }

    // Create an S3 client with the given credentials and region
    $s3 = new S3Client([
        'region' => $region,
        'version' => 'latest',
        'credentials' => [
            'key' => $awsKey,
            'secret' => $awsSecret,
        ],
    ]);

    try {
        // Create a command to get an object from S3
        $command = $s3->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key'    => $key,
        ]);

        // Create a presigned request (temporary URL) for this command
        $request = $s3->createPresignedRequest($command, $expiration);

        // Return the presigned URL as a string
        return (string)$request->getUri();
    } catch (AwsException $e) {
        throw new Exception("Error generating presigned URL: " . $e->getMessage());
    }
}
