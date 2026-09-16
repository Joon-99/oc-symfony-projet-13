# Prerequisites

Before installing and running this project, make sure the following tools are installed on your machine:

- PHP 8.4+
- PHP extensions: bcmath, ctype, gd, iconv, pdo, pdo_pgsql
- Composer
- Docker + Docker Compose

You can check your local setup with the following script:

```bash
bash requirements.sh
```

# Setup

You can setup the project with the following script:

```bash
bash setup.sh
```

This will:
- create a minimal .env.local file
- generate the keys for Lexik
- install dependencies
- build the docker for the database (PostgreSQL)
- run the migrations
- load the fixtures
- start a local symfony server (http://localhost:8000)

# Tests

You can test an admin user with:

user: `admin@green-goodies.com`
password: `password`


You can test a regular user with: 

user: `alice.martin@green-goodies.com`
password: `password`

