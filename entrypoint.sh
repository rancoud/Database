#!/bin/sh
while ! mysqladmin ping -h"$MYSQL_HOST" -P"3306" --silent; do
  sleep 1
done

exec composer ci