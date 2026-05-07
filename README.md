# Laravel Docker Setup

A containerized Laravel development environment using Docker and Docker Compose.

## Prerequisites

- [Docker Desktop](https://www.docker.com) installed and running
- Git

## Getting Started

### 1. Install Laravel

```bash
docker run --rm -v $(pwd)\src:/app composer create-project laravel/laravel . --prefer-dist
```

### 2. Database Configure Environment

Copy the example Docker environment file and update your database credentials in `src/.env`:

```bash
cp .env.docker src/.env
```

> Edit `src/.env` to match your database name, username, and password.

### 3. Build the Docker Image

```bash
docker compose build
```

---

## Running the Application

### Start

```bash
docker compose up -d
```

### Stop

```bash
docker compose down
```

---

## Artisan Commands

Run Laravel Artisan commands inside the container using:

```bash
docker compose exec php-fpm php artisan <command>
```

### Generate Application Key

> Required on first setup.

```bash
docker compose exec php-fpm php artisan key:generate
```

### Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

---

## Project Structure

```
intern
|── docker/
├── src/            # Laravel application source code
├── .env.docker     # Docker environment variables template
└── docker-compose.yml
└── Dockerfile
```

---

# Project Host 

http://localhost