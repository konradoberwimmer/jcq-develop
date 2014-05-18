ALTER TABLE jcq_questionscales ADD layout SMALLINT NULL;
ALTER TABLE jcq_questionscales ADD relpos SMALLINT NULL;

UPDATE jcq_questionscales JOIN jcq_question ON jcq_questionscales.questionID = jcq_question.ID SET jcq_questionscales.layout=2 WHERE jcq_question.questtype=1 AND jcq_questionscales.layout IS NULL;
UPDATE jcq_questionscales JOIN jcq_question ON jcq_questionscales.questionID = jcq_question.ID SET jcq_questionscales.layout=1 WHERE jcq_question.questtype=4 AND jcq_questionscales.layout IS NULL;
UPDATE jcq_questionscales JOIN jcq_question ON jcq_questionscales.questionID = jcq_question.ID SET jcq_questionscales.layout=1 WHERE jcq_question.questtype=5 AND jcq_questionscales.layout IS NULL;
UPDATE jcq_questionscales JOIN jcq_question ON jcq_questionscales.questionID = jcq_question.ID SET jcq_questionscales.layout=3 WHERE jcq_question.questtype=6 AND jcq_questionscales.layout IS NULL;
UPDATE jcq_questionscales JOIN jcq_question ON jcq_questionscales.questionID = jcq_question.ID SET jcq_questionscales.relpos=1 WHERE jcq_question.questtype=6 AND jcq_questionscales.relpos IS NULL;
