import boto3
import base64

# Initialize an S3 client


# Specify the bucket name
bucket_name = 'proctoring-stg'

# Path to the video file
video_file_path = './hello_QUIZ_admin-1711512071.webm.mp4'

# Read the video file
with open(video_file_path, 'rb') as file:
    video_content = file.read()

# Encode the video content to base64
video_base64 = base64.b64encode(video_content).decode('utf-8')

print(video_base64[:100])

keyname = 'new-video-proctoring-5.mp4'

# Upload the base64-encoded content to S3
s3.put_object(Bucket=bucket_name, Key=keyname , Body=video_base64)
