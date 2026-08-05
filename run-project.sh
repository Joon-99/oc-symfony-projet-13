#!/usr/bin/bash

docker compose --env-file .env.local up -d
symfony proxy:start
symfony serve -d
