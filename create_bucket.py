import boto3

s3 = boto3.client(
    's3',
    region_name="me-jeddah-1",
    aws_secret_access_key="asxmExdieq/JQ620xX8CeknZQNLnL6Y8SIasSOUcMgo=",
    aws_access_key_id="d60689c107626390494ba987659f4089419e5bcf",
    endpoint_url="https://axhxgarudf1b.compat.objectstorage.me-jeddah-1.oraclecloud.com"
)

bucket_name = 'medad-lms-stg'

# Create the bucket
s3.create_bucket(Bucket=bucket_name)

print(f"Bucket '{bucket_name}' created successfully.")
