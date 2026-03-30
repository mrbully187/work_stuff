#!/bin/bash

echo "Starting Gantry System..."

python3 -u /var/www/html/python/gantryPosDB.py &
python3 -u /var/www/html/python/gantryLogger.py

wait
