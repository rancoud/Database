CREATE TABLE test_select(
  id  SERIAL PRIMARY KEY,
  name           character varying(255)      NOT NULL,
  rank           INT       NOT NULL,
  comment        TEXT
);