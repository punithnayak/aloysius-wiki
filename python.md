# Talk to Wikipedia with Python 🐍

<walkthrough-tutorial-duration duration="15"></walkthrough-tutorial-duration>

Every page on Wikipedia comes from the **Action API**, and Python can ask it questions too. You will run a script that finds out everything about any Wikimedia editor: where they edit, their latest edits, even what time of day they like to edit.

Click **Start** to begin.

## Step 1: The 15-line version

<walkthrough-editor-open-file filePath="python/recent_edits.py">Open python/recent_edits.py</walkthrough-editor-open-file>

It asks English Wikipedia for its last 10 edits. Run it:

```sh
python3 python/recent_edits.py
```

Run it again. Different list? Those are real people editing Wikipedia right now.

If Python says `No module named requests`, run `pip3 install --user requests` and try again.

## Step 2: Look up an editor

<walkthrough-editor-open-file filePath="python/wikiuser.py">Open python/wikiuser.py</walkthrough-editor-open-file>

This bigger script asks the API about any person, live. Start it:

```sh
python3 python/wikiuser.py
```

It asks for a **username**. Type `Punith.nyk` (your workshop host) and press Enter. When it asks **Which wiki?**, just press Enter to use the wiki where they edit most.

You'll see:
* **🌍 Their global profile:** total edits on every Wikimedia wiki
* **📊 Where they edit:** a bar chart of their busiest wikis
* **✏️ Their latest edits:** green for text added, red for removed
* **🕒 Their habits:** busiest hours and days, and favourite pages

Then it asks for the next username. Keep going, and press Enter on an empty line to stop.

## Step 3: A very busy editor

Type this username next: `TheSandDoctor`. When it asks **Which wiki?**, type `enwiki`.

This editor has made almost 100,000 edits on nearly 700 wikis.

You can also skip the questions and give everything at once:

```sh
python3 python/wikiuser.py TheSandDoctor --wiki enwiki
```

Have a Wikimedia account? Look yourself up!

## Step 4: The raw JSON

Everything the script found is saved in a file. Open it:

```sh
cloudshell edit user_Punith.nyk.json
```

This is exactly what the API sends back. The script just picks out the interesting parts and draws the charts.

## Step 5: Your own wiki

Your Cloud Shell wiki has the same API. Look up yourself as Admin, and you'll see the pages you edited in the other tutorials:

```sh
python3 python/wikiuser.py Admin --api http://localhost:8080/api.php
```

Not running? Start it first with `bash start.sh`.

Same code, any MediaWiki. That's the point of an API.

## How it works

The script asks only **3 questions**. You can open each one in your browser:

1. **Who are you, everywhere?** `meta=globaluserinfo`
   [Try it for Punith.nyk](https://meta.wikimedia.org/w/api.php?action=query&meta=globaluserinfo&guiuser=Punith.nyk&guiprop=editcount|merged&format=json&formatversion=2)
2. **Who are you on this wiki?** `list=users`
3. **What did you edit?** `list=usercontribs`
   [Try it for Punith.nyk](https://www.mediawiki.org/w/api.php?action=query&list=usercontribs&ucuser=Punith.nyk&uclimit=10&format=json&formatversion=2)

Everything else, like the charts, the habits and the favourite pages, is ordinary Python: `Counter`, `datetime` and a `for` loop.

## Make it yours

Edit `python/wikiuser.py`, save, and run it again:

1. **Show more edits:** find `show_latest(edits)` and change it to `show_latest(edits, 20)`
2. **Busiest month:** in `show_habits`, count `t.strftime("%b %Y")` the same way as the weekdays
3. **Namespaces:** count `e["ns"]` to see how many edits are articles (0), user pages (2) or files (6)
4. **Kannada Wikipedia:** run Step 1 again with `kn.wikipedia.org` in the URL

## Done!

<walkthrough-conclusion-trophy></walkthrough-conclusion-trophy>

You used the same API that Wikipedia's own tools, bots and apps use.

* Every API module, with examples: [Special:ApiSandbox](https://en.wikipedia.org/wiki/Special:ApiSandbox)
* The full manual: [API:Main page](https://www.mediawiki.org/wiki/API:Main_page)
* Please always send a `User-Agent`, like the script does, so Wikimedia knows who is asking
