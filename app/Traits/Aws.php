<?php

namespace App\Traits;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;

trait Aws
{
    /**
     * S3 - Check Bucket Exists
     * @param string $bucket - Bucket Name
     * @return bool $response
     */
    public static function isBucketExist(string $bucket): bool {
        $credentials = new Credentials(env('AWS_ACCESS_KEY_ID', null), env('AWS_SECRET_ACCESS_KEY', null));

        $client = S3Client::factory([
            'region' => env('AWS_DEFAULT_REGION', 'ap-south-1'),
            'version' => '2006-03-01',
            'credentials' => $credentials
        ]);

        try {
            return $client->doesBucketExist($bucket);
        } catch (AwsException $e) {
            return false;
        }
    }


    /**
     * S3 - Upload File to S3
     * @param string $bucket - Bucket Name
     * @param $filePath
     * @param string $type
     * @return bool|array $response
     */
    public static function uploadFile(string $bucket, $filePath, $type = '') {
        $credentials = new Credentials(env('AWS_ACCESS_KEY_ID', null), env('AWS_SECRET_ACCESS_KEY', null));
        
        $s3 = new S3Client([
			'version'     => 'latest',
			'region'      => 'ap-south-1',
			'credentials' => $credentials
		]);
        dd($filePath);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        
		$s3FileName =  ($type) ? $type.'/'.time()."_".rand().".$ext" : time()."_".rand().".$ext";
        
        try {
            
            $result = $s3->putObject([
                'Bucket' => 'techizertech',
                'Key' => $s3FileName,
                'SourceFile' => $filePath,
                'ACL' => 'public-read'
			]);
            
			return [
				'success' => true,
				'url' => $result['ObjectURL'],
				'key' => $s3FileName,
				'message' => 'File Saved'
			];

        } catch (S3Exception $e) {
            return [
				'success' => false,
				'url' => '',
				'message' => 'Error while Saving'
			];
		}
    }


}
