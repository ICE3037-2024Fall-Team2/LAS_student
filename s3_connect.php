<?php
//require 'vendor/autoload.php'; // Ensure Composer's autoload is loaded
require __DIR__ . '/../vendor/autoload.php'; // 引用共享的 autoload.php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function generatePresignedUrl($bucketName, $key, $expiration = '+60 minutes')
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
        // 创建 S3 命令
        $command = $s3->getCommand('GetObject', [
            'Bucket' => $bucketName,
            'Key'    => $key,
        ]);

        // 创建预签名请求
        $request = $s3->createPresignedRequest($command, $expiration);

        // 返回预签名 URL
        return (string)$request->getUri();
    } catch (AwsException $e) {
        throw new Exception("Error generating presigned URL: " . $e->getMessage());
    }
}
