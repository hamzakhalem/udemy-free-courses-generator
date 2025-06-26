from pytube import YouTube
from moviepy.video.io.VideoFileClip import VideoFileClip
import os

# === CONFIG ===
video_url = 'https://www.youtube.com/watch?v=6vLLK_kbfrI'  # Replace with your URL
download_path = './downloads'
shorts_path = './shorts'
segment_duration = 120  # in seconds

# === DOWNLOAD VIDEO ===
yt = YouTube(video_url)
stream = yt.streams.filter(file_extension='mp4', progressive=True).order_by('resolution').desc().first()

if not os.path.exists(download_path):
    os.makedirs(download_path)

video_filepath = stream.download(output_path=download_path)
print(f"Downloaded: {video_filepath}")

# === CUT INTO SHORTS ===
if not os.path.exists(shorts_path):
    os.makedirs(shorts_path)

video = VideoFileClip(video_filepath)
total_duration = int(video.duration)

for start_time in range(0, total_duration, segment_duration):
    end_time = min(start_time + segment_duration, total_duration)
    short_clip = video.subclip(start_time, end_time)
    short_filename = os.path.join(shorts_path, f"short_{start_time}_{end_time}.mp4")
    short_clip.write_videofile(short_filename, codec="libx264", audio_codec="aac")
    print(f"Saved short: {short_filename}")

video.close()
