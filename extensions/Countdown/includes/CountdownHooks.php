<?php
/**
 * Countdown: adds a new wikitext tag.
 *
 *   <countdown to="2026-10-17 09:30 +05:30">Day 2 starts</countdown>
 *
 * The parser (slide 8) meets <countdown>, and instead of printing it,
 * calls our render() function and puts our HTML in the page.
 */

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class CountdownHooks {

	/** Shout: "The parser is starting. Any new tags?" */
	public function onParserFirstCallInit( $parser ) {
		$parser->setHook( 'countdown', [ self::class, 'render' ] );
	}

	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		$to = trim( $args['to'] ?? '' );
		$zone = MediaWikiServices::getInstance()->getMainConfig()->get( 'Localtimezone' ) ?: 'UTC';
		try {
			$target = new DateTime( $to, new DateTimeZone( $zone ) );
		} catch ( Exception $e ) {
			$target = null;
		}
		if ( $to === '' || !$target ) {
			return Html::element( 'strong', [ 'class' => 'error' ],
				wfMessage( 'countdown-error' )->inContentLanguage()->text() );
		}

		$out = $parser->getOutput();
		$out->addModules( [ 'ext.countdown' ] );
		$out->addModuleStyles( [ 'ext.countdown.styles' ] );

		// The label can contain wikitext, like '''bold''' or [[links]].
		$label = $input === null || trim( $input ) === ''
			? ''
			: $parser->recursiveTagParse( trim( $input ), $frame );

		$left = max( 0, $target->getTimestamp() - time() );
		$parts = [
			'days' => intdiv( $left, 86400 ),
			'hours' => intdiv( $left % 86400, 3600 ),
			'minutes' => intdiv( $left % 3600, 60 ),
			'seconds' => $left % 60,
		];

		$units = '';
		foreach ( $parts as $unit => $value ) {
			$units .= Html::rawElement( 'div', [ 'class' => 'cd-unit' ],
				Html::element( 'span', [ 'class' => 'cd-num', 'data-unit' => $unit ],
					str_pad( (string)$value, 2, '0', STR_PAD_LEFT ) ) .
				Html::element( 'span', [ 'class' => 'cd-label' ],
					wfMessage( 'countdown-' . $unit )->inContentLanguage()->text() )
			);
		}

		$theme = in_array( $args['theme'] ?? '', [ 'sunset', 'ocean', 'night' ], true )
			? $args['theme'] : 'sunset';

		return Html::rawElement( 'div', [
				'class' => "cd cd-$theme",
				'data-to' => $target->getTimestamp() * 1000,
				'data-done' => $args['done'] ?? wfMessage( 'countdown-done' )->inContentLanguage()->text(),
			],
			( $label !== '' ? Html::rawElement( 'div', [ 'class' => 'cd-title' ], $label ) : '' ) .
			Html::rawElement( 'div', [ 'class' => 'cd-units' ], $units ) .
			Html::element( 'div', [ 'class' => 'cd-when' ],
				$target->format( 'D, j M Y · H:i T' ) )
		);
	}
}
