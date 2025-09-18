#!/bin/sh
set -e

cd /app

if [ -f "package.json" ]; then

    npm install --legacy-peer-deps --loglevel=error
    # npm install --legacy-peer-deps --quiet

    npm run dev -- --host 0.0.0.0 

fi
