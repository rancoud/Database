PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE test_select (
    id      INT          PRIMARY KEY  NOT NULL,
    name    VARCHAR (45) NOT NULL,
    rank    INT (1)      NOT NULL,
    comment TEXT
);
INSERT INTO test_select (id, name, rank, comment) VALUES (1, 'A', 0, NULL);
INSERT INTO test_select (id, name, rank, comment) VALUES (2, 'B', 10, 'yes');
INSERT INTO test_select (id, name, rank, comment) VALUES (3, 'C', 20, 'maybe');
INSERT INTO test_select (id, name, rank, comment) VALUES (4, 'D', 30, 'no');
INSERT INTO test_select (id, name, rank, comment) VALUES (5, 'E', 25, NULL);
INSERT INTO test_select (id, name, rank, comment) VALUES (6, 'F', 5, NULL);
COMMIT;
