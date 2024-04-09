import boto3

# Initialize an S3 client
s3 = boto3.client(
    's3',
    region_name="me-jeddah-1",
    aws_secret_access_key="asxmExdieq/JQ620xX8CeknZQNLnL6Y8SIasSOUcMgo=",
    aws_access_key_id="d60689c107626390494ba987659f4089419e5bcf",
    endpoint_url="https://axhxgarudf1b.compat.objectstorage.me-jeddah-1.oraclecloud.com"
)

keyname= 'new-video-proctoring-5.mp4'
# Specify the bucket name
bucket_name = 'proctoring-stg'
response = s3.get_object(Bucket=bucket_name, Key=keyname)
object_content = response['Body'].read()
print(object_content[:100])
# List all objects in the bucket
# for obj in s3.list_objects_v2(Bucket=bucket_name)['Contents']:
#     response = s3.get_object(Bucket=bucket_name, Key=obj['Key'])
    
#     # Read and print the object content
#     object_content = response['Body'].read().decode('utf-8')
#     if(obj['Key']=="Hello_QUIZ_admin-1711460276.webm"):
#         print(f"Content of object '{obj['Key']}':")
#         print(object_content)
