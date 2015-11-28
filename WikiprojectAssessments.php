<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'WikiprojectAssessments' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['WikiprojectAssessments'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['WikiprojectAssessmentsAlias'] = __DIR__ . '/WikiprojectAssessments.i18n.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for WikiprojectAssessments extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the WikiprojectAssessments extension requires MediaWiki 1.25+' );
}
