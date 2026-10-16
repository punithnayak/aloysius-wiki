# Build your own extension: Rangoli 🌸

<walkthrough-tutorial-duration duration="25"></walkthrough-tutorial-duration>

You will build a real MediaWiki extension from an empty folder: a moving rainbow banner on every article, with a **Celebrate ✨** button that makes flowers fly across the screen.

It takes 5 small files. Every extension in Wikipedia, even Echo and VisualEditor, started exactly like this.

Click **Start** to begin.

## Make sure your wiki is running

Run this in the terminal at the bottom. If your wiki is already running, it just says so.

```sh
bash start.sh
```

Then open your wiki: **Web Preview** <walkthrough-web-preview-icon></walkthrough-web-preview-icon> → **Preview on port 8080**. Log in as `Admin` / `aloysius2026`.

## Step 1: Make the folder

An extension is just a folder. Make yours, with three small folders inside:

```sh
mkdir -p extensions/Rangoli/includes extensions/Rangoli/i18n extensions/Rangoli/modules
```

* `includes/` will hold your **PHP code**
* `i18n/` will hold your **words**
* `modules/` will hold your **CSS and JavaScript**

## Step 2: The label on the box

Every extension starts with `extension.json`. MediaWiki reads it first to learn what is inside. Create it and open it:

```sh
touch extensions/Rangoli/extension.json && cloudshell edit extensions/Rangoli/extension.json
```

Paste this in, then put **your own name** after `"author"`:

```json
{
	"name": "Rangoli",
	"author": "Your Name",
	"version": "1.0.0",
	"descriptionmsg": "rangoli-desc",
	"license-name": "GPL-2.0-or-later",
	"type": "other",
	"AutoloadClasses": {
		"RangoliHooks": "includes/RangoliHooks.php"
	},
	"HookHandlers": {
		"main": { "class": "RangoliHooks" }
	},
	"Hooks": {
		"BeforePageDisplay": "main"
	},
	"MessagesDirs": {
		"Rangoli": [ "i18n" ]
	},
	"ResourceFileModulePaths": {
		"localBasePath": "modules",
		"remoteExtPath": "Rangoli/modules"
	},
	"ResourceModules": {
		"ext.rangoli": {
			"styles": [ "rangoli.css" ],
			"packageFiles": [ "rangoli.js" ]
		}
	},
	"manifest_version": 2
}
```

It answers four questions:
* **Who am I?** `name`, `author`
* **Where is my code?** `AutoloadClasses`: the class `RangoliHooks` lives in `includes/RangoliHooks.php`
* **When do I run?** `Hooks`: when the `BeforePageDisplay` bell rings, call `main`
* **What do I send to the browser?** `ResourceModules`: one CSS file and one JavaScript file

## Step 3: The PHP that listens

```sh
touch extensions/Rangoli/includes/RangoliHooks.php && cloudshell edit extensions/Rangoli/includes/RangoliHooks.php
```

Paste this:

```php
<?php
/**
 * Rangoli: a colourful banner on every article.
 * MediaWiki rings the BeforePageDisplay bell, and we add our banner.
 */

use MediaWiki\Html\Html;

class RangoliHooks {

	public function onBeforePageDisplay( $out, $skin ): void {
		// Only on normal article pages, not on edit or special pages
		if ( !$out->isArticle() ) {
			return;
		}

		$name = $out->getUser()->isRegistered() ? $out->getUser()->getName() : 'friend';

		$banner = Html::rawElement( 'div', [ 'class' => 'rangoli' ],
			Html::element( 'span', [ 'class' => 'rangoli-flower' ], '🌸' ) .
			Html::element( 'span', [ 'class' => 'rangoli-text' ],
				$out->msg( 'rangoli-hello', $name )->text() ) .
			Html::element( 'button', [ 'class' => 'rangoli-button', 'type' => 'button' ],
				$out->msg( 'rangoli-button' )->text() )
		);

		$out->prependHTML( $banner );
		$out->addModules( 'ext.rangoli' );
	}
}
```

MediaWiki calls `onBeforePageDisplay` every time it shows a page. Your function:
1. Skips edit pages and special pages
2. Builds the banner, with your username in it
3. Puts it at the top of the page with `prependHTML`
4. Asks for your CSS and JavaScript with `addModules`

## Step 4: The words

```sh
touch extensions/Rangoli/i18n/en.json && cloudshell edit extensions/Rangoli/i18n/en.json
```

```json
{
	"@metadata": {
		"authors": [ "Your Name" ]
	},
	"rangoli-desc": "Adds a colourful banner to every article.",
	"rangoli-hello": "Namaskara, $1! This banner comes from YOUR extension.",
	"rangoli-button": "Celebrate ✨"
}
```

Your code never says "Namaskara" directly. It asks for the message `rangoli-hello`, and `$1` is replaced with the username. That's how one extension can speak 300 languages.

## Step 5: The colours

```sh
touch extensions/Rangoli/modules/rangoli.css && cloudshell edit extensions/Rangoli/modules/rangoli.css
```

```css
/* The banner: a moving rainbow */
.rangoli {
	display: flex;
	align-items: center;
	gap: 14px;
	margin: 0 0 18px;
	padding: 16px 20px;
	border-radius: 18px;
	color: #fff;
	font-size: 1.1em;
	font-weight: 700;
	background: linear-gradient( 90deg, #ff4e50, #f9a826, #2ec4b6, #7b2ff7, #ff4e50 );
	background-size: 300% 100%;
	box-shadow: 0 10px 30px rgba( 123, 47, 247, 0.35 );
	animation: rangoli-flow 8s linear infinite;
}

/* The flower spins slowly */
.rangoli-flower {
	font-size: 1.8em;
	animation: rangoli-spin 6s linear infinite;
}

.rangoli-text {
	flex: 1;
	text-shadow: 0 1px 3px rgba( 0, 0, 0, 0.3 );
}

/* The button */
.rangoli-button {
	padding: 8px 16px;
	border: 0;
	border-radius: 999px;
	font: inherit;
	color: #7b2ff7;
	background: #fff;
	cursor: pointer;
	transition: transform 0.2s;
}
.rangoli-button:hover {
	transform: scale( 1.1 ) rotate( -3deg );
}

/* Each flying flower */
.rangoli-burst {
	position: fixed;
	z-index: 1000;
	font-size: 26px;
	pointer-events: none;
	animation: rangoli-fly 1.5s ease-out forwards;
}

@keyframes rangoli-flow { to { background-position: 300% 0; } }
@keyframes rangoli-spin { to { transform: rotate( 360deg ); } }
@keyframes rangoli-fly {
	to {
		opacity: 0;
		transform: translate( var( --x ), var( --y ) ) rotate( 360deg );
	}
}
```

The rainbow is 4 colours in `linear-gradient`. The animation slides it along forever.

## Step 6: The magic button

```sh
touch extensions/Rangoli/modules/rangoli.js && cloudshell edit extensions/Rangoli/modules/rangoli.js
```

```js
/**
 * Rangoli: when you click the button, flowers fly everywhere.
 */
document.querySelectorAll( '.rangoli-button' ).forEach( function ( button ) {
	button.addEventListener( 'click', function () {
		var flowers = [ '🌸', '✨', '🌼', '🎉', '💜', '🧡' ];
		var box = button.getBoundingClientRect();

		for ( var i = 0; i < 30; i++ ) {
			var flower = document.createElement( 'span' );
			flower.className = 'rangoli-burst';
			flower.textContent = flowers[ i % flowers.length ];
			flower.style.left = box.left + box.width / 2 + 'px';
			flower.style.top = box.top + 'px';
			flower.style.setProperty( '--x', ( Math.random() - 0.5 ) * 600 + 'px' );
			flower.style.setProperty( '--y', ( Math.random() * 400 ) + 'px' );
			document.body.appendChild( flower );
			setTimeout( flower.remove.bind( flower ), 1600 );
		}
	} );
} );
```

This runs in the reader's browser. When the button is clicked, it creates 30 flowers and sends each one flying in a random direction.

## Step 7: Switch it on

<walkthrough-editor-open-file filePath="workshop/Workshop.php">Open workshop/Workshop.php</walkthrough-editor-open-file>

Find this line:

```
// wfLoadExtension( 'Rangoli' );
```

Delete the `// ` at the start, so it reads `wfLoadExtension( 'Rangoli' );`

Now open **Mangaluru** on your wiki and refresh. 🌈 Your banner is there. Click **Celebrate ✨**.

## Step 8: Check it's official

Open **Special:Version** on your wiki and scroll to **Installed extensions**. Rangoli is there, with your name as the author.

Your extension is now part of MediaWiki, exactly like Echo and Cite.

## Make it yours

Try these. Save the file, then refresh your wiki.

1. **New colours:** in `rangoli.css`, change the 4 colours in `linear-gradient`. Try `#00c9ff, #92fe9d, #f9f871, #ff6f91`.
2. **New words:** in `en.json`, change `rangoli-hello` and `rangoli-button`.
3. **More flowers:** in `rangoli.js`, change `30` to `100`.
4. **New emoji:** change `🌸` in the PHP file, and the list of flowers in `rangoli.js`.
5. **Speak Kannada:** create the Kannada words file:

```sh
touch extensions/Rangoli/i18n/kn.json && cloudshell edit extensions/Rangoli/i18n/kn.json
```

```json
{
	"@metadata": {
		"authors": [ "Your Name" ]
	},
	"rangoli-hello": "ನಮಸ್ಕಾರ, $1! ಈ ಬ್ಯಾನರ್ ನಿಮ್ಮದೇ ಎಕ್ಸ್‌ಟೆನ್ಷನ್‌ನಿಂದ ಬಂದಿದೆ.",
	"rangoli-button": "ಸಂಭ್ರಮಿಸಿ ✨"
}
```

Then open any article with `?uselang=kn` at the end of the address.

## Stuck?

**The page shows an error:** read it. It names the file and the line. The usual mistakes are a missing comma in a `.json` file, or a missing `;` in the PHP file.

**Compare with the finished version** in the `solutions/Rangoli` folder.

**Or copy the finished version** and carry on from there:

```sh
rm -rf extensions/Rangoli && cp -r solutions/Rangoli extensions/
```

## Done!

<walkthrough-conclusion-trophy></walkthrough-conclusion-trophy>

You built a MediaWiki extension from an empty folder: a manifest, a hook, messages, CSS and JavaScript.

That is the same shape as every extension on Wikipedia. Tomorrow we learn how to send real code to Wikimedia.

**Bonus: talk to Wikipedia with Python.** Run this to open Tutorial 4:

```sh
cloudshell launch-tutorial python.md
```

* Learn more: [Manual: Developing extensions](https://www.mediawiki.org/wiki/Manual:Developing_extensions)
* Every hook you can listen to: [Manual: Hooks](https://www.mediawiki.org/wiki/Manual:Hooks)
