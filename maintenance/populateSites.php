<?php

namespace Wikimedia\Sites;

use Http;
use Maintenance;
use MWException;
use SiteSQLStore;
use Wikimedia\Sites\SiteMatrixParser;
use Wikimedia\Sites\SitesBuilder;

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) : __DIR__ . '/../../..';

require_once $basePath . '/maintenance/Maintenance.php';

/**
 * Maintenance script for populating the SiteStore from another wiki that runs the
 * SiteMatrix extension.
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 * @author Katie Filbert < aude.wiki@gmail.com >
 */
class PopulateSites extends Maintenance {

	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Populate the site store from another wiki that runs the SiteMatrix extension' );

		$this->addOption( 'strip-protocols', "Strip http/https from URLs to make them protocol relative." );
		$this->addOption( 'force-protocol', "Force a specific protocol for all URLs (like http/https).", false, true );
		$this->addOption( 'load-from', "Full URL to the API of the wiki to fetch the site info from. "
				. "Default is https://meta.wikimedia.org/w/api.php", false, true );
		$this->addOption( 'script-path', 'Script path to use for wikis in the site matrix. '
				. ' (e.g. "/w/$1")', false, true );
		$this->addOption( 'article-path', 'Article path for wikis in the site matrix. '
				. ' (e.g. "/wiki/$1")', false, true );
		$this->addOption( 'site-group', 'Site group that this wiki is a member of.  Used to populate '
				. ' local interwiki identifiers in the site identifiers table.  If not set and --wiki'
				. ' is set, the script will try to determine which site group the wiki is part of'
				. ' and populate interwiki ids for sites in that group.', false, true );
		$this->addOption( 'no-expand-group', 'Do not expand site group codes in site matrix. '
				. ' By default, "wiki" is expanded to "wikipedia".' );
	}

	public function execute() {
		$stripProtocols = (bool)$this->getOption( 'strip-protocols', false );
		$forceProtocol = $this->getOption( 'force-protocol', null );
		$url = $this->getOption( 'load-from', 'https://meta.wikimedia.org/w/api.php' );
		$scriptPath = $this->getOption( 'script-path', '/w/$1' );
		$articlePath = $this->getOption( 'article-path', '/wiki/$1' );
		$expandGroup = !$this->getOption( 'no-expand-group', false );
		$siteGroup = $this->getOption( 'site-group' );
		$wikiId = $this->getOption( 'wiki' );

		if ( $stripProtocols && is_string( $forceProtocol ) ) {
			$this->error( "You can't use both strip-protocols and force-protocol", 1 );
		}

		$protocol = true;
		if ( $stripProtocols ) {
			$protocol = false;
		} elseif ( is_string( $forceProtocol ) ) {
			$protocol = $forceProtocol;
		}

		// @todo make it configurable, such as from a config file.
		$validGroups = array( 'wikipedia', 'wikivoyage', 'wikiquote', 'wiktionary',
			'wikibooks', 'wikisource', 'wikiversity', 'wikinews' );

		try {
			$json = $this->getSiteMatrixData( $url );

			$siteMatrixParser = new SiteMatrixParser( $scriptPath, $articlePath,
				$protocol, $expandGroup );

			$sites = $siteMatrixParser->sitesFromJson( $json );

			$store = SiteSQLStore::newInstance();
			$sitesBuilder = new SitesBuilder( $store, $validGroups );
			$sitesBuilder->buildStore( $sites, $siteGroup, $wikiId );

		} catch ( MWException $e ) {
			$this->output( $e->getMessage() );
		}

		$this->output( "done.\n" );
	}

	/**
	 * @param string $url
	 *
	 * @throws MWException
	 * @return string
	 */
	protected function getSiteMatrixData( $url ) {
		$url .= '?action=sitematrix&format=json';

		$json = Http::get( $url );

		if ( !$json ) {
			throw new MWException( "Got no data from $url\n" );
		}

		return $json;
	}

}

$maintClass = PopulateSites::class;
require_once RUN_MAINTENANCE_IF_MAIN;