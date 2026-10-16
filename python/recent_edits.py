# The 15-line version: the last 10 edits on Wikipedia.
import requests

URL = "https://en.wikipedia.org/w/api.php"
params = {
    "action": "query",
    "list": "recentchanges",
    "rclimit": 10,
    "format": "json",
}
headers = {"User-Agent": "AloysiusWorkshop/1.0"}

data = requests.get(URL, params=params, headers=headers).json()
for change in data["query"]["recentchanges"]:
    print(change["type"], "->", change["title"])
