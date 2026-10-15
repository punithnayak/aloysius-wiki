<?php
/**
 * Special:Hello, a page that only code can write.
 * Nobody can edit it. MediaWiki runs execute() every time someone opens it.
 */

use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\SiteStats\SiteStats;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\Utils\MWTimestamp;

class SpecialHello extends SpecialPage {

	public function __construct() {
		parent::__construct( 'Hello' );
	}

	public function execute( $par ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$user = $this->getUser();
		$out->addModuleStyles( 'ext.helloAloysius.special.styles' );
		$out->addModules( 'ext.helloAloysius.special' );

		$name = $user->isRegistered() ? $user->getName() : 'Aloysius';

		// 1. The hero banner
		$hero = Html::rawElement( 'div', [ 'class' => 'hello-hero' ],
			Html::element( 'div', [ 'class' => 'hello-wave' ], '👋' ) .
			Html::element( 'h2', [], $this->msg( 'helloaloysius-greeting', $name )->text() ) .
			Html::element( 'p', [], $this->msg( 'helloaloysius-subtitle' )->text() ) .
			Html::element( 'a', [
				'class' => 'hello-btn',
				'href' => SpecialPage::getTitleFor( 'Randompage' )->getLocalURL(),
			], '🎲 ' . $this->msg( 'helloaloysius-random' )->text() )
		);

		// 2. Live numbers, read from the database
		$services = MediaWikiServices::getInstance();
		$stats = [
			[ '📄', 'helloaloysius-stat-articles', SiteStats::articles() ],
			[ '✏️', 'helloaloysius-stat-edits', SiteStats::edits() ],
			[ '👥', 'helloaloysius-stat-users', SiteStats::users() ],
		];
		if ( $user->isRegistered() ) {
			$stats[] = [ '⭐', 'helloaloysius-stat-youredits',
				$services->getUserEditTracker()->getUserEditCount( $user ) ];
		}
		$cards = '';
		foreach ( $stats as $i => [ $icon, $msg, $num ] ) {
			$cards .= Html::rawElement( 'div', [ 'class' => 'hello-card', 'style' => "--i:$i" ],
				Html::element( 'div', [ 'class' => 'hello-card-icon' ], $icon ) .
				Html::element( 'div', [ 'class' => 'hello-num', 'data-target' => (int)$num ],
					$this->getLanguage()->formatNum( (int)$num ) ) .
				Html::element( 'div', [ 'class' => 'hello-label' ], $this->msg( $msg )->text() )
			);
		}
		$statsHtml = Html::rawElement( 'div', [ 'class' => 'hello-stats' ], $cards );

		// 3. The latest edits, straight from the recentchanges table
		$dbr = $services->getConnectionProvider()->getReplicaDatabase();
		$rows = $dbr->newSelectQueryBuilder()
			->select( [ 'rc_namespace', 'rc_title', 'rc_timestamp', 'rc_source', 'actor_name' ] )
			->from( 'recentchanges' )
			->join( 'actor', null, 'actor_id = rc_actor' )
			->where( [ 'rc_source' => [ 'mw.edit', 'mw.new' ] ] )
			->orderBy( 'rc_timestamp', 'DESC' )
			->limit( 6 )
			->caller( __METHOD__ )
			->fetchResultSet();

		$linkRenderer = $this->getLinkRenderer();
		$items = '';
		foreach ( $rows as $row ) {
			$page = Title::makeTitle( (int)$row->rc_namespace, $row->rc_title );
			$who = $row->actor_name;
			$when = $this->getLanguage()->getHumanTimestamp(
				new MWTimestamp( $row->rc_timestamp ), null, $user );
			$verb = $row->rc_source === 'mw.new' ? '✨' : '✏️';
			$items .= Html::rawElement( 'li', [],
				Html::element( 'span', [ 'class' => 'hello-avatar' ], mb_substr( $who, 0, 1 ) ) .
				Html::rawElement( 'span', [ 'class' => 'hello-feed-text' ],
					Html::element( 'b', [], $who ) . ' ' . $verb . ' ' .
					$linkRenderer->makeLink( $page ) ) .
				Html::element( 'span', [ 'class' => 'hello-when' ], $when )
			);
		}
		if ( $items === '' ) {
			$items = Html::element( 'li', [], $this->msg( 'helloaloysius-recent-empty' )->text() );
		}
		$feed = Html::rawElement( 'div', [ 'class' => 'hello-feed' ],
			Html::element( 'h3', [], $this->msg( 'helloaloysius-recent-title' )->text() ) .
			Html::rawElement( 'ol', [], $items )
		);

		// 4. A small note for the workshop
		$note = Html::rawElement( 'p', [ 'class' => 'hello-footnote' ],
			$this->msg( 'helloaloysius-footnote' )->escaped() );

		$out->addHTML( Html::rawElement( 'div', [ 'class' => 'hello-dash' ],
			$hero . $statsHtml . $feed . $note ) );
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
