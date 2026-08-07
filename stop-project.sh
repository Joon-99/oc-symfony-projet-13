#!/usr/bin/bash

symfony server:stop
symfony proxy:stop
docker compose --env-file .env.local down
