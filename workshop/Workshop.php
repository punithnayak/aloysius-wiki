<?php
/**
 * ============================================================
 *  YOUR WIKI'S SETTINGS: change things here, save, refresh!
 * ============================================================
 * This file is loaded at the end of LocalSettings.php.
 * Lines starting with // are comments: MediaWiki ignores them.
 */

// ✏️ 1. The name of your wiki (top left, and in the browser tab)
$wgSitename = "Aloysius Wiki";

// ✏️ 2. Your logo. Replace workshop/logo.png with your own picture,
//       or put the address of any image here.
$wgLogos = [
	'1x' => "$wgScriptPath/workshop/logo.png",
	'icon' => "$wgScriptPath/workshop/logo.png",
];

// ✏️ 3. The look (skin). Try: 'vector-2022', 'vector', 'monobook', 'timeless'
$wgDefaultSkin = 'vector-2022';

// ✏️ 4. The three workshop extensions. Put // in front of a line to switch one off.
wfLoadExtension( 'HelloAloysius' );   // greeting chip, save celebration, Special:Hello
wfLoadExtension( 'WikiPulse' );       // Special:WikiPulse: every Wikipedia edit, live
wfLoadExtension( 'Countdown' );       // new wikitext tag: <countdown to="...">
// wfLoadExtension( 'Rangoli' );     // ✏️ Tutorial 2: remove the // once you have built it

// ✏️ 5. Who can edit? Remove the // to make only logged-in users edit.
// $wgGroupPermissions['*']['edit'] = false;

// ------------------------------------------------------------
// Workshop helpers. You don't need to change anything below.
// ------------------------------------------------------------

// Let everyone run their own common.js and common.css
$wgAllowUserJs = true;
$wgAllowUserCss = true;

// Show real error messages, so you can learn from them
$wgShowExceptionDetails = true;

// Work behind Cloud Shell, Codespaces or a tunnel. start.sh writes the
// public address to data/server.txt; otherwise use the browser's address.
$wgServer = \MediaWiki\Request\WebRequest::detectServer();
$workshopServerFile = '/var/www/data/server.txt';
$workshopServer = is_readable( $workshopServerFile ) ? trim( file_get_contents( $workshopServerFile ) ) : '';
$workshopHost = trim( explode( ',', $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '' )[0] );
if ( $workshopHost === '' ) {
	$workshopHost = $_SERVER['HTTP_HOST'] ?? '';
}
if ( $workshopServer !== '' ) {
	$wgServer = $workshopServer;
} elseif ( preg_match( '#^([a-z0-9.-]+\.(cloudshell\.dev|app\.github\.dev|gitpod\.io|trycloudflare\.com))(:\d+)?$#i', $workshopHost, $m ) ) {
	$wgServer = 'https://' . $m[1];
}
