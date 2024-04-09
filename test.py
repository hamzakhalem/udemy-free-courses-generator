
import requests
import json
import time



start_time = time.time()
response = requests.request("POST", url, headers=headers, data=json.dumps(payload))
print("--- %s seconds ---" % (time.time() - start_time))

if int(response.status_code) != 200:
    print(response.status_code)
    print(response.text)
    exit()

print(json.loads(response.text))


