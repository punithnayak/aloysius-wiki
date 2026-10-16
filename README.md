# Aloysius Wiki 🎉

Your own copy of **MediaWiki**, the software that runs Wikipedia, with three extensions built at the **St Aloysius MediaWiki workshop**. Change the title, the logo or the code, and watch your wiki change.

## Tutorial 1: your own wiki, in your browser (Google sign-in, nothing to install)

[![Open in Cloud Shell](https://gstatic.com/cloudssh/images/open-btn.svg)](https://shell.cloud.google.com/cloudshell/editor?cloudshell_git_repo=https://github.com/punithnayak/aloysius-wiki&cloudshell_tutorial=tutorial.md&show=ide%2Cterminal)

1. Click the button and sign in with your Google account. Trust the repo when asked.
2. Follow the steps on the right. You'll run `bash start.sh`, then click **Web Preview → Preview on port 8080**.
3. Log in as **Admin** / **aloysius2026**.

**Tutorial 2: your first user script.** Rainbow headings, your own menu links and a Party mode button, written on a wiki page:

[![Open Tutorial 2 in Cloud Shell](https://gstatic.com/cloudssh/images/open-btn.svg)](https://shell.cloud.google.com/cloudshell/editor?cloudshell_git_repo=https://github.com/punithnayak/aloysius-wiki&cloudshell_tutorial=user-script.md&show=ide%2Cterminal)

**Tutorial 3: build your own extension.** Make a colourful extension called Rangoli from an empty folder:

[![Open Tutorial 3 in Cloud Shell](https://gstatic.com/cloudssh/images/open-btn.svg)](https://shell.cloud.google.com/cloudshell/editor?cloudshell_git_repo=https://github.com/punithnayak/aloysius-wiki&cloudshell_tutorial=build-extension.md&show=ide%2Cterminal)

**Tutorial 4: talk to Wikipedia with Python.** Type any username and get their edits, favourite pages and busiest hours from the API:

[![Open Tutorial 4 in Cloud Shell](https://gstatic.com/cloudssh/images/open-btn.svg)](https://shell.cloud.google.com/cloudshell/editor?cloudshell_git_repo=https://github.com/punithnayak/aloysius-wiki&cloudshell_tutorial=python.md&show=ide%2Cterminal)

Google Cloud Shell is free with a personal Google account. Some college accounts block it; use your personal Gmail if so.

**Have a GitHub account instead?** Click **Code → Codespaces → Create codespace**. The wiki starts by itself; open the **Ports** tab and click the globe next to 8080.

**Have Docker on your laptop?** Run `bash start.sh` and open http://localhost:8080

## What's inside

| Extension | Try it | What it teaches |
|---|---|---|
| **HelloAloysius** | Edit any page and save. Then open `Special:Hello` | Hooks (`BeforePageDisplay`, `PageSaveComplete`) and special pages |
| **WikiPulse** | Open `Special:WikiPulse` | A special page that shows live data from Wikimedia EventStreams |
| **Countdown** | Open `Countdown demo` | A new wikitext tag, `<countdown to="...">` |

## Things to change

| I want to change... | Open this file | Do this |
|---|---|---|
| The wiki's name | `workshop/Workshop.php` | Change `$wgSitename` |
| The logo | `workshop/logo.png` | Replace it with your own picture |
| The look | `workshop/Workshop.php` | Set `$wgDefaultSkin` to `'monobook'` or `'timeless'` |
| The "Edit saved!" card | `extensions/HelloAloysius/i18n/en.json` | Change `helloaloysius-saved-title` |
| Countdown colours | `extensions/Countdown/modules/ext.countdown.css` | Change the colours in `.cd-sunset` |
| Switch an extension off | `workshop/Workshop.php` | Put `//` in front of its `wfLoadExtension` line |

Save the file and refresh your wiki. No restart needed.

## Files

```
start.sh          starts your wiki (run it again any time)
reset.sh          deletes your wiki's pages so you can start fresh
workshop/
  Workshop.php    YOUR settings: name, logo, skin, extensions
  logo.png        your logo
  pages/          the demo pages added on first start
python/
  wikiuser.py     Tutorial 4: everything about a Wikimedia editor
  recent_edits.py the 15-line version
solutions/
  userscript/     the finished Tutorial 2 user script
  Rangoli/        the finished Tutorial 3 extension
extensions/
  HelloAloysius/  greeting chip, save celebration, Special:Hello
  WikiPulse/      Special:WikiPulse
  Countdown/      the <countdown> tag
data/             your wiki's database (created by start.sh)
```

## If something goes wrong

* **The wiki shows an error after you changed a file:** read the message, it names the file and line. Undo your last change and refresh.
* **Web Preview shows "connection refused":** run `bash start.sh` again.
* **You want to start over:** `bash reset.sh` then `bash start.sh`.

Made for the St Aloysius MediaWiki workshop, October 2026. Learn more: [How to become a MediaWiki hacker](https://www.mediawiki.org/wiki/How_to_become_a_MediaWiki_hacker)
