# Database test suite

This test suite tests the integrity of the database as a whole. It is run automatically
by test_db.sh in `--test` or `--ci` mode.

Each test consists of a file (named `example.sql`) which defines a view (called `schemacheck_example`).
The view should return a dataset including a column called `test_status` which is either `OK` or `FAIL`.
The test suite will call each view in turn and check for any rows where `test_status = 'FAIL'` - if any
are found, the test fails and the failing rows from the view are returned.

Each view is executed from within the context of the database it is testing, so calls to `DATABASE()` to
get the correct schema name can be used. References to other tables in databases (including `information_schema`)
must therefore be fully-qualified.

Tests here should not depend on specific data. They are intended to test the structure of the database,
not the content. As such, you will probably find that these tests rely *heavily* on the `information_schema`
database.