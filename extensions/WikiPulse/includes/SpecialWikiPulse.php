<?php
/**
 * Special:WikiPulse: every edit on every Wikimedia wiki, live.
 *
 * PHP only draws the empty dashboard. The browser then connects to
 * Wikimedia's public EventStreams service and fills it in real time.
 */

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

class SpecialWikiPulse extends SpecialPage {

	public function __construct() {
		parent::__construct( 'WikiPulse' );
	}

	public function execute( $par ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModuleStyles( 'ext.wikiPulse.styles' );
		$out->addModules( 'ext.wikiPulse' );

		$stat = function ( $id, $msg ) {
			return Html::rawElement( 'div', [ 'class' => 'wp-stat' ],
				Html::element( 'div', [ 'class' => 'wp-stat-num', 'id' => $id ], '0' ) .
				Html::element( 'div', [ 'class' => 'wp-stat-label' ], $this->msg( $msg )->text() )
			);
		};

		$filters = '';
		$choices = [
			'all' => 'wikipulse-filter-all',
			'enwiki' => 'wikipulse-filter-en',
			'knwiki' => 'wikipulse-filter-kn',
			'tcywiki' => 'wikipulse-filter-tcy',
			'wikidatawiki' => 'wikipulse-filter-wikidata',
		];
		foreach ( $choices as $wiki => $msg ) {
			$filters .= Html::element( 'button', [
				'type' => 'button',
				'class' => 'wp-chip' . ( $wiki === 'all' ? ' is-on' : '' ),
				'data-wiki' => $wiki,
			], $this->msg( $msg )->text() );
		}
		$filters .= Html::rawElement( 'label', [ 'class' => 'wp-bots' ],
			Html::element( 'input', [ 'type' => 'checkbox', 'id' => 'wp-hidebots', 'checked' => true ] ) .
			' ' . $this->msg( 'wikipulse-hidebots' )->escaped()
		);

		$html =
			Html::rawElement( 'div', [ 'class' => 'wp-head' ],
				Html::rawElement( 'div', [],
					Html::rawElement( 'div', [ 'class' => 'wp-status', 'id' => 'wp-status' ],
						Html::element( 'span', [ 'class' => 'wp-dot' ] ) .
						Html::element( 'span', [ 'id' => 'wp-status-text' ],
							$this->msg( 'wikipulse-connecting' )->text() )
					) .
					Html::element( 'h2', [ 'class' => 'wp-title' ], $this->msg( 'wikipulse-title' )->text() ) .
					Html::element( 'p', [ 'class' => 'wp-sub' ], $this->msg( 'wikipulse-subtitle' )->text() )
				) .
				Html::rawElement( 'div', [ 'class' => 'wp-stats' ],
					$stat( 'wp-rate', 'wikipulse-stat-rate' ) .
					$stat( 'wp-total', 'wikipulse-stat-total' ) .
					$stat( 'wp-wikis', 'wikipulse-stat-wikis' ) .
					$stat( 'wp-bytes', 'wikipulse-stat-bytes' )
				)
			) .
			Html::rawElement( 'div', [ 'class' => 'wp-filters' ], $filters ) .
			Html::rawElement( 'div', [ 'class' => 'wp-grid' ],
				Html::rawElement( 'div', [ 'class' => 'wp-stage' ],
					Html::element( 'canvas', [ 'id' => 'wp-canvas' ] ) .
					Html::rawElement( 'div', [ 'class' => 'wp-legend' ],
						Html::element( 'span', [ 'class' => 'wp-add' ], '● ' . $this->msg( 'wikipulse-legend-add' )->text() ) .
						Html::element( 'span', [ 'class' => 'wp-del' ], '● ' . $this->msg( 'wikipulse-legend-del' )->text() ) .
						Html::element( 'span', [ 'class' => 'wp-new' ], '● ' . $this->msg( 'wikipulse-legend-new' )->text() )
					)
				) .
				Html::rawElement( 'div', [ 'class' => 'wp-side' ],
					Html::element( 'h3', [], $this->msg( 'wikipulse-feed' )->text() ) .
					Html::element( 'ol', [ 'class' => 'wp-feed', 'id' => 'wp-feed' ] ) .
					Html::element( 'h3', [], $this->msg( 'wikipulse-top' )->text() ) .
					Html::element( 'div', [ 'class' => 'wp-top', 'id' => 'wp-top' ] )
				)
			) .
			Html::rawElement( 'p', [ 'class' => 'wp-foot' ], $this->msg( 'wikipulse-footnote' )->parse() );

		$out->addHTML( Html::rawElement( 'div', [ 'class' => 'wp' ], $html ) );
	}

	protected function getGroupName() {
		return 'changes';
	}
}
