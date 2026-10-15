# Your own Aloysius Wiki

<walkthrough-tutorial-duration duration="20"></walkthrough-tutorial-duration>

In 2 minutes you will have your own copy of MediaWiki, the software behind Wikipedia, with three extensions built at the St Aloysius workshop. Then you will change it and watch it change.

Click **Start** to begin.

## Start your wiki

Run this in the terminal at the bottom of the screen. Click the copy button, paste it into the terminal, and press Enter.

```sh
bash start.sh
```

The first time takes about a minute. Wait until you see **✅ Your wiki is running!**

## Open your wiki

Click the **Web Preview** button <walkthrough-web-preview-icon></walkthrough-web-preview-icon> at the top right of this window, then **Preview on port 8080**.

Your wiki opens in a new tab. Keep this tab open too.

Log in with:
* Username: `Admin`
* Password: `aloysius2026`

## Try the extensions

On your wiki's Main Page, click:
* **Special:Hello**: a page built entirely by PHP code
* **Special:WikiPulse**: every edit on every Wikipedia, live
* **Countdown demo**: a brand-new wikitext tag

Then open **Mangaluru**, click **Edit**, add a sentence, and save. Watch the bottom-right corner.

## Change your wiki's name

<walkthrough-editor-open-file filePath="workshop/Workshop.php">Open workshop/Workshop.php</walkthrough-editor-open-file>

Find this line and put your own name in it:

```
$wgSitename = "Aloysius Wiki";
```

The file saves automatically. Refresh your wiki tab: the name at the top left has changed.

While you're in this file, try `$wgDefaultSkin = 'monobook';` and refresh. That's Wikipedia from the 2000s.

## Change your logo

In the file list on the left, open the `workshop` folder. Right-click `logo.png`, delete it, then drag in any picture from your computer and name it `logo.png`.

Refresh your wiki. Your picture is the logo now.

## Change what an extension says

<walkthrough-editor-open-file filePath="extensions/HelloAloysius/i18n/en.json">Open extensions/HelloAloysius/i18n/en.json</walkthrough-editor-open-file>

Change `"Edit saved!"` to anything you like. Then edit a page on your wiki again and save. The card says your new text.

This file is how MediaWiki speaks 300+ languages. Open `kn.json` next to it to see the Kannada version, then visit any page with `?uselang=kn` at the end of the address.

## Read the code

Every extension is just a folder. Open `extensions/Countdown` and read the files in this order:

1. `extension.json`: the label on the box. Who am I, where's my code, when do I run.
2. `includes/CountdownHooks.php`: the PHP that turns `<countdown>` into a box.
3. `modules/ext.countdown.js`: the JavaScript that makes it tick.
4. `modules/ext.countdown.css`: the colours. Try changing them!

## Done!

<walkthrough-conclusion-trophy></walkthrough-conclusion-trophy>

You ran MediaWiki, used three extensions, and changed both settings and extension code.

**Next: write your first user script.** Run this to open Tutorial 2:

```sh
cloudshell launch-tutorial user-script.md
```

* Start again later: run `bash start.sh`
* Start fresh: run `bash reset.sh` then `bash start.sh`
* Next step: [How to become a MediaWiki hacker](https://www.mediawiki.org/wiki/How_to_become_a_MediaWiki_hacker)
