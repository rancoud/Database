#!/bin/sh

DB_TIMEOUT=${DB_TIMEOUT:-45}

# Check MySQL up
MYSQL_CMD="mysql -h ${MYSQL_HOST} -P ${MYSQL_PORT:-3306} -u ${MYSQL_USER:-root}"
echo "Waiting ${DB_TIMEOUT}s for MySQL database to be ready..."
counter=1
while ! ${MYSQL_CMD} -e "show databases;" > /dev/null 2>&1; do
  sleep 1
  counter=$((counter + 1))
  if [ ${counter} -gt "${DB_TIMEOUT}" ]; then
    >&2 echo "ERROR: Failed to connect to MySQL database on $MYSQL_HOST"
    exit 1
  fi;
done
echo "MySQL database ready!"

# Check PostgreSQL up
POSTGRES_CMD="psql --host=${POSTGRES_HOST} --port=${POSTGRES_PORT:-5432} --username=${DB_USER:-postgres} -lqt"
echo "Waiting ${DB_TIMEOUT}s for database to be ready..."
counter=1
while ${POSTGRES_CMD} | cut -d \| -f 1 | grep -qw "${POSTGRES_DATABASE}" > /dev/null 2>&1; [ $? -ne 0 ]; do
  sleep 1
  counter=$((counter + 1))
  if [ ${counter} -gt "${DB_TIMEOUT}" ]; then
    >&2 echo "ERROR: Failed to connect to PostgreSQL database on $POSTGRES_HOST"
    exit 1
  fi;
done
echo "PostgreSQL database ready!"

exec "$@"
