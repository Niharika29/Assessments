<?php
/**
 * PageAssessments extension body
 *
 * @file
 * @ingroup Extensions
 */

class PageAssessmentsBody {

	/**
	 * Driver function
	 */
	public function execute ( &$parser, $project = '', $class = '', $importance = '' ) {
		$newRecord = false;
		// Title class object for the Main page of this Talk page
		$pageObj = $parser->getTitle()->getSubjectPage();
		$pageTitle = $pageObj->getText();
		$exists = PageAssessmentsBody::checkIfExists( $pageTitle, $project, $class, $importance );
		switch ( $exists ) {
			case 'nochange':
				return; // Yay, no need for the API call
			case 'change':
				break;
			default:
				$newRecord = true;
				break;
		}

		$pageNamespace = $pageObj->getNamespace();
		$pageId = $pageObj->getArticleID();
		$revisionId = $pageObj->getLatestRevID();

		$values = array(
			'pa_page_id' => $pageId,
			'pa_page_name' => $pageTitle,
			'pa_page_namespace' => $pageNamespace,
			'pa_project' => $project,
			'pa_class' => $class,
			'pa_importance' => $importance,
			'pa_page_revision' => $revisionId
		);
		if ( $newRecord ) {
			PageAssessmentsBody::insertRecord( $values );
		} else {
			PageAssessmentsBody::updateRecord( $values );
		}
		return;
	}


	/**
	 * Update record in DB with new values
	 * @param array $values New values to be entered into the DB
	 * @return bool True/False on query success/fail
	 */
	public function updateRecord ( $values ) {
		$dbw = wfGetDB( DB_MASTER );
		$conds =  array(
			'pa_page_name' => $values['pa_page_name'],
			'pa_project'  => $values['pa_project']
		);
		$dbw->update( 'page_assessments', $values, $conds, __METHOD__ );
		return true;
	}


	/**
	 * Insert a new record in DB
	 * @param array $values New values to be entered into the DB
	 * @return bool True/False on query success/fail
	 */
	public function insertRecord ( $values ) {
		$dbw = wfGetDB( DB_MASTER );
		try {
			$dbw->insert( 'page_assessments', $values, __METHOD__ );
		} catch ( Exception $error ) {
			return false;
		}
		return true;
	}


	/**
	 * Check if the record already exists and is changed
	 * @param string $pageTitle Title of the page
	 * @param string $project Name of the Wikiproject associated
	 * @param string $class Class attribute of the review
	 * @param string $importance Importance attribute of the review
	 * @return string nochange|change|noexist No changes/Changes to existing record/New record
	 */
	public function checkIfExists ( $pageTitle, $project, $class, $importance ) {
		$dbw = wfGetDB( DB_SLAVE ); // Read only query
		$res = $dbw->select(
			'page_assessments',
			'*',
			'pa_page_name = "' . $pageTitle . '" AND pa_project = "' . $project . '"'
		);
		if ( $res ) {
			foreach ( $res as $row ) {
				if ( $row->class == $class && $row->importance == $importance ) {
					return 'nochange'; // Record is same as new
				} elseif ( $row->class != $class || $row->importance != $importance ) {
					return 'change';   // Record has changed
				}
			}
		} else {
			return 'noexist'; // New record
		}
	}

}
