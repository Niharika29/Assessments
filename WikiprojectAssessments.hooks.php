<?php
/**
 * Hooks for WikiprojectAssessments extension
 *
 * @file
 * @ingroup Extensions
 */

class WikiprojectAssessmentsHooks {

	/**
	 * Register the parser function hook
	 * @param $parser Parser
	 * @return bool
	 */
	public static function onParserFirstCallInit ( &$parser ) {
		$parser->setFunctionHook( 'review', 'WikiprojectAssessmentsBody::execute' );
	}

	public static function onLoadExtensionSchemaUpdates ( DatabaseUpdater $updater = null ) {
		$dbDir = __DIR__ . '/db';
		$updater->addExtensionUpdate( array( 'addtable', 'articleAssessments', "$dbDir/addReviewsTable.sql", true ) );
		return true;
	}

}
