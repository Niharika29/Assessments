-- Add article assessments log table

CREATE TABLE IF NOT EXISTS /*_*/page_assessments_log (
	pa_page_id          INT(10) NOT NULL,
	pa_project          VARCHAR(128) DEFAULT NULL,
	pa_class            VARCHAR(20) DEFAULT NULL,
	pa_importance       VARCHAR(20) DEFAULT NULL,
	pa_page_revision    INT(10) NOT NULL,
	pa_user_id          INT(10) DEFAULT NULL,
	pa_timestamp        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)/*$wgDBTableOptions*/;

CREATE INDEX /*i*/pa_project_log ON /*_*/ page_assessments (pa_project);
CREATE UNIQUE INDEX /*i*/pa_page_project_log ON /*_*/ page_assessments (pa_page_id, pa_project);
