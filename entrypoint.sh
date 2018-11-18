#!/usr/bin/env bash

case $1 in

start)
    php -S 0.0.0.0:8000 -t /var/www/app/web
;;

*)
    echo "Usage: $0 {start}"
    exit 1
;;

esac




