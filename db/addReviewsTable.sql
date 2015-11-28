-- Add article assessments table

CREATE TABLE IF NOT EXISTS articleAssessments (
	pageId          INT(20) NOT NULL,
	pageName        VARCHAR(255) NOT NULL,
	namespace       INT(20) NOT NULL,
	project         VARCHAR(128) DEFAULT NULL,
	class           VARCHAR(20) DEFAULT NULL,
	importance      VARCHAR(20) DEFAULT NULL,
	pageRevision    INT(20) NOT NULL
);

