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
