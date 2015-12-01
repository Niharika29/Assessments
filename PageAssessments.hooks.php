<?php
/**
 * Hooks for PageAssessments extension
 *
 * @file
 * @ingroup Extensions
 */

class PageAssessmentsHooks {

	/**
	 * Register the parser function hook
	 * @param $parser Parser
	 * @return bool
	 */
	public static function onParserFirstCallInit ( &$parser ) {
		$parser->setFunctionHook( 'assessment', 'PageAssessmentsBody::execute' );
	}

	public static function onLoadExtensionSchemaUpdates ( DatabaseUpdater $updater = null ) {
		$dbDir = __DIR__ . '/db';
		$updater->addExtensionUpdate( array( 'addtable', 'page_assessments', "$dbDir/addReviewsTable.sql", true ) );
		return true;
	}

}
