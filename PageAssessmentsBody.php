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
				return;
			case 'change':
				break;
			default:
				$newRecord = true;
				break;
		}

		$pageNamespace = $pageObj->getNamespace();
		$pageId = $pageObj->getArticleID();
		$revisionId = $pageObj->getLatestRevID();

		// Compile the array to be inserted to the DB
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
		$values['pa_user_id'] = $parser->getRevisionUser();
		PageAssessmentsBody::insertLogRecord( $values );
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
		$dbw->insert( 'page_assessments', $values, __METHOD__ );
		return true;
	}


	/**
	 * Insert to the logging table
	 * @param array $values Values to be entered to the DB
	 * @return bool True/False on query success/fail
	 */
	public function insertLogRecord ( $values ) {
		$logValues = array(
			'pa_page_id' => $values['pa_page_id'],
			'pa_user_id' => $values['pa_user'],
			'pa_page_revision' => $values['pa_page_revision'],
			'pa_project' => $values['pa_project'],
			'pa_class' => $values['pa_class'],
			'pa_importance' => $values['pa_importance'],
			'pa_timestamp' => $values['pa_timestamp']
		);
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert( 'page_assessments_log', $logValues, __METHOD__ );
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
		}
		return 'noexist'; // New record
	}

}
