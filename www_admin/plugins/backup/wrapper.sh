#!/bin/bash
cd "$(dirname "$0")"
bash backup.sh "$@" >backup.log 2>&1 &
echo $! | tee backup.pid
