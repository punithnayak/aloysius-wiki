# Your first user script 🪩

<walkthrough-tutorial-duration duration="15"></walkthrough-tutorial-duration>

A **user script** is CSS and JavaScript that you write on a wiki page. It changes the wiki **only for you**, and anyone with an account can make one, even on real Wikipedia.

You will add rainbow headings, your own menu links, and a **Party mode** button. No files and no PHP: everything happens in your browser.

Click **Start** to begin.

## Open your wiki

If your wiki isn't running, start it:

```sh
bash start.sh
```

Open it with **Web Preview** <walkthrough-web-preview-icon></walkthrough-web-preview-icon> → **Preview on port 8080**, and log in as `Admin` / `aloysius2026`.

## Step 1: Your own CSS

On your wiki, type this in the **search box** at the top and press Enter:

```
User:Admin/common.css
```

The page doesn't exist yet. Click **Create** (the tab at the top, or the red link), paste this in, and click **Save page**:

```css
/* 1. Rainbow headings */
.mw-page-title-main,
.mw-body-content h2 {
	display: inline-block;
	background: linear-gradient( 90deg, #ff4e50, #f9a826, #2ec4b6, #7b2ff7 );
	-webkit-background-clip: text;
	background-clip: text;
	color: transparent;
	font-weight: 800;
}

/* 2. Links that glow when you point at them */
.mw-body-content a:hover {
	color: #ff4e50;
	text-shadow: 0 0 8px rgba( 255, 78, 80, 0.6 );
}

/* 3. Party mode: the whole page cycles through every colour */
body.party {
	animation: party 2s linear infinite;
}
@keyframes party {
	to { filter: hue-rotate( 360deg ); }
}
```

Now open **Mangaluru**. The headings are a rainbow, and links glow when you point at them.

Nothing changed? Press **Ctrl + Shift + R** to reload without the old copy.

## Step 2: Your own JavaScript

Search for this page the same way and click **Create**:

```
User:Admin/common.js
```

Paste this in and click **Save page**:

```js
// 1. Say hello every time a page opens
mw.notify( 'Hello, ' + mw.config.get( 'wgUserName' ) + '! Your script is running 🎉' );

mw.loader.using( 'mediawiki.util' ).then( function () {

	// 2. Your own link in the Tools menu
	mw.util.addPortletLink( 'p-tb', mw.util.getUrl( 'Special:Random' ), '🎲 Surprise me', 't-surprise' );

	// 3. Party mode: a link that switches the disco on and off
	var party = mw.util.addPortletLink( 'p-tb', '#', '🪩 Party mode', 't-party' );
	party.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		document.body.classList.toggle( 'party' );
	} );

	// 4. Page facts: ask MediaWiki about this page
	var facts = mw.util.addPortletLink( 'p-tb', '#', '📊 Page facts', 't-facts' );
	facts.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		mw.notify(
			'Page: ' + mw.config.get( 'wgPageName' ) +
			' · Revision: ' + mw.config.get( 'wgRevisionId' ) +
			' · Your edits: ' + mw.config.get( 'wgUserEditCount' )
		);
	} );
} );
```

Open **Mangaluru** again:
* A hello message pops up in the top right
* The **Tools** menu on the right has 3 new links at the bottom
* Click **🪩 Party mode**. Click it again to stop the party
* Click **📊 Page facts**

## What the code does

Your script talks to MediaWiki through `mw`, a toolbox that is ready on every page:

* `mw.notify( '...' )` shows a small pop-up message
* `mw.config.get( 'wgUserName' )` asks MediaWiki for facts: your name, the page name, the revision number, your edit count
* `mw.util.addPortletLink( 'p-tb', ... )` adds a link to the Tools menu (`p-tb` is the Tools menu's ID)
* `mw.loader.using( 'mediawiki.util' )` waits until the `mw.util` part of the toolbox has loaded

And Party mode is just CSS: clicking the link adds the class `party` to the page, and `common.css` says what a party looks like.

## Make it yours

Edit your pages again, save, and reload:

1. **New greeting:** change the text inside `mw.notify(...)`
2. **Faster party:** in `common.css`, change `2s` to `0.3s`
3. **Your favourite page:** copy the "Surprise me" line and point it at a page you like: `mw.util.getUrl( 'Mangaluru' )`
4. **More facts:** add `mw.config.get( 'wgUserLanguage' )` to Page facts. [All the facts you can ask for](https://www.mediawiki.org/wiki/Manual:Interface/JavaScript#mw.config)

## Try it on real Wikipedia

Log in to **test.wikipedia.org** with your Wikimedia account and open:

```
https://test.wikipedia.org/wiki/Special:MyPage/common.js
```

Paste the same script and save. It runs there too, on a real Wikimedia wiki, and still only for you.

## Stuck?

* **Nothing happens:** press **F12**, open **Console**, and read the red line. It names the line with the mistake.
* **The error points at squashed code:** add `?debug=true` to the end of the address.
* **Compare with the finished version** in the `solutions/userscript` folder.

## Done!

<walkthrough-conclusion-trophy></walkthrough-conclusion-trophy>

You changed MediaWiki with CSS and JavaScript, without touching the server.

| | User script | Extension |
|---|---|---|
| Lives in | a wiki page | files on the server |
| Who sees it | only you | everyone |
| Who can add it | anyone with an account | a server admin |
| Languages | CSS + JavaScript | CSS + JavaScript + PHP |

**Next: build a real extension** that everyone sees. Run this to open Tutorial 3:

```sh
cloudshell launch-tutorial build-extension.md
```
