#!/bin/bash
echo "--- START S3 BUCKET CREATION ---"
awslocal s3 mb s3://my-test-bucket
echo "--- END S3 BUCKET CREATION ---"