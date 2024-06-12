
import requests
import json
import time

url = "https://qoahq.apps.beam.cloud"
payload = {"text": "Cybersecurity_First-quiz_mohamed17-1711965612.webm"}
headers = {
  "Accept": "*/*",
  "Accept-Encoding": "gzip, deflate",
  "Authorization": "Basic NWNmMTk2MGZhNmM0YTlhN2FmZGEzMzMyMGNhNDQyYWE6MmI5MDBjNjI4ODVkNGZiOTIzMTcyMTU2NWQxNGYyMGI=",
  "Connection": "keep-alive",
  "Content-Type": "application/json"
}

start_time = time.time()
response = requests.request("POST", url, headers=headers, data=json.dumps(payload))
print("--- %s seconds ---" % (time.time() - start_time))

if int(response.status_code) != 200:
    print(response.status_code)
    print(response.text)
    exit()

print(json.loads(response.text))


