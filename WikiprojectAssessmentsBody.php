<?php
/**
 * WikiprojectAssessments extension body
 *
 * @file
 * @ingroup Extensions
 */

class WikiprojectAssessmentsBody {

	/**
	 * Driver function
	 */
	public function execute ( &$parser, $project = '', $class = '', $importance = '' ) {
		// TODO: Send parameters off for validation
		$newRecord = false;
		$pageTitle = $parser->getVariableValue( 'rootpagename' );
		$exists = WikiprojectAssessmentsBody::checkIfExists( $pageTitle, $project, $class, $importance );
		switch ( $exists ) {
			case 'nochange':
				return; // Yay, no need for the API call
			case 'change':
				break;
			default:
				$newRecord = true;
				break;
		}
		$apiRes = WikiprojectAssessmentsBody::makeAPIRequest( $pageTitle );
		$apiParams = WikiprojectAssessmentsBody::extractParams( $apiRes );
		$values = array(
			'pageId' => $apiParams['pageid'],
			'pageName' => $pageTitle,
			'namespace' => $apiParams['namespace'],
			'project' => $project,
			'class' => $class,
			'importance' => $importance,
			'pageRevision' => $apiParams['revisionid']
		);
		if ( $newRecord ) {
			WikiprojectAssessmentsBody::insertRecord( $values );
		} else {
			WikiprojectAssessmentsBody::updateRecord( $values );
		}
		return;
	}


	/**
	 * Make an API request to obtain page ID, last revision ID and namespace
	 * @param string $pageTitle Title of the page to query upon
	 * @return array $data Data returned by the API call
	 */
	public function makeAPIRequest ( $pageTitle ) {
		$params = new DerivativeRequest (
			new WebRequest(), // $this->getRequest() is preferred except that $this is null?!?
			array(
				'action' => 'query',
				'prop'   => 'revisions',
				'rvprop' => 'ids',
				'rvlimit'=> 1,
				'titles' => $pageTitle,
				'formatversion' => 2 // Doesn't work, why?
			),
			true
		);
		$api = new ApiMain( $params );
		try {
			$api->execute();
			$transforms = array( 'strip' => 'all' ); // Remove unnecesary metadata
			$data = $api->getResult()->getResultData( null, $transforms );
			return $data;
		} catch ( UsageException $e ) {
			// TODO: Add a logging mechanism for errors
		}
	}


	/**
	 * Extract useful stuff from the API returned data
	 * @param array $apiRes Data from the API call
	 * @return array Values namespace, pageid and revisionid
	 */
	public function extractParams ( $apiRes ) {
		$apiRes = $apiRes['query']['pages'];
		$pageId = array_keys( $apiRes )[0]; // Ugly hack to get around default API return format tantrums
		$namespace = $apiRes[$pageId]['ns'];
		$revisionId = $apiRes[$pageId]['revisions'][0]['revid'];
		return array(
			'namespace' => $namespace ? $namespace : '0',
			'pageid'    => $pageId ? $pageId : '0',
			'revisionid'=> $revisionId ? $revisionId : '0'
		);
	}


	/**
	 * User provided input values
	 * Validate for strings
	 */
	public function validateInput ( ) {

	}


	/**
	 * Update record in DB with new values
	 * @param array $values New values to be entered into the DB
	 * @return bool True/False on query success/fail
	 */
	public function updateRecord ( $values ) {
		$dbw = wfGetDB( DB_MASTER );
		$conds =  array(
			'pageName' => $values['pageName'],
			'project'  => $values['project']
		);
		$dbw->update( 'articleAssessments', $values, $conds, __METHOD__ );
		return true;
	}


	/**
	 * Insert a new record in DB
	 * @param array $values New values to be entered into the DB
	 * @return bool True/False on query success/fail
	 */
	public function insertRecord ( $values ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert( 'articleAssessments', $values, __METHOD__ );
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
			'articleAssessments',
			'*',
			'pageName = "' . $pageTitle . '" AND project = "' . $project . '"'
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
