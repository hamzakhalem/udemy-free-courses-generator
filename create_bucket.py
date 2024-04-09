import boto3

bucket_name = 'medad-lms-stg'

# Create the bucket
s3.create_bucket(Bucket=bucket_name)

print(f"Bucket '{bucket_name}' created successfully.")
