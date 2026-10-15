<?php
/**
 * HelloAloysius: the hooks.
 *
 * MediaWiki shouts ("I'm about to show a page!", "An edit was saved!").
 * This class listens. extension.json says which shouts we care about.
 */

use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\User\UserEditTracker;

class HelloHooks {

	private const SESSION_KEY = 'HelloAloysiusJustSaved';

	private UserEditTracker $userEditTracker;

	public function __construct( UserEditTracker $userEditTracker ) {
		$this->userEditTracker = $userEditTracker;
	}

	/**
	 * Shout #1: "An edit was just saved."
	 * We remember it in the session, so the NEXT page can celebrate.
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		if ( $editResult->isNullEdit() ) {
			return;
		}
		$session = RequestContext::getMain()->getRequest()->getSession();
		$session->set( self::SESSION_KEY, [
			'isNew' => (bool)( $flags & EDIT_NEW ),
		] );
	}

	/**
	 * Shout #2: "I'm about to display a page."
	 * Runs on every page view.
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$user = $out->getUser();

		// 1. Did this person just save an edit? Then celebrate.
		$session = $out->getRequest()->getSession();
		$saved = $session->get( self::SESSION_KEY );
		if ( $saved ) {
			$session->remove( self::SESSION_KEY );
			$saved['count'] = $user->isRegistered()
				? $this->userEditTracker->getUserEditCount( $user )
				: 0;
			$out->addJsConfigVars( 'wgHelloJustSaved', $saved );
			$out->addModules( 'ext.helloAloysius' );
		}

		// 2. Only on normal article views: greeting chip, reading time, progress bar.
		$title = $out->getTitle();
		if ( !$out->isArticle() || !$title || !$title->isContentPage() || !$title->exists() ) {
			return;
		}

		$text = preg_replace( '#<(style|script)\b.*?</\1>#is', '', $out->getHTML() );
		$text = trim( html_entity_decode( strip_tags( $text ) ) );
		$words = $text === '' ? 0 : count( preg_split( '/\s+/u', $text ) );
		$minutes = max( 1, (int)round( $words / 200 ) );

		$name = $user->isRegistered() ? $user->getName() : 'Aloysius';
		$lang = $out->getLanguage();

		$chip = Html::rawElement( 'div', [ 'class' => 'hello-chip' ],
			Html::element( 'span', [ 'class' => 'hello-chip-wave' ], '👋' ) .
			Html::element( 'span', [ 'class' => 'hello-chip-hi' ],
				$out->msg( 'helloaloysius-greeting', $name )->text() ) .
			Html::element( 'span', [ 'class' => 'hello-chip-dot' ], '·' ) .
			Html::element( 'span', [], '⏱ ' .
				$out->msg( 'helloaloysius-readtime' )->numParams( $minutes )->text() ) .
			Html::element( 'span', [ 'class' => 'hello-chip-dot' ], '·' ) .
			Html::element( 'span', [],
				$out->msg( 'helloaloysius-words' )->numParams( $words )->text() )
		);

		$out->prependHTML( $chip );
		$out->addModules( 'ext.helloAloysius' );
	}
}
