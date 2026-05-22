# Digital Archives & Mapping System

## Requirements
Make sure the following are installed on your device before proceeding:
- [Docker](https://www.docker.com/products/docker-desktop)
- [Git](https://git-scm.com/)

---

## Installation & Setup


### Step 1: Copy the Environment File
```bash
cp .env.example .env
```

### Step 2: Start Docker
```bash
docker compose up -d
```
> Wait for Docker to finish pulling and building the containers before proceeding.

### Step 3: Generate Application Key
```bash
docker compose exec laravel.test php artisan key:generate
```

### Step 4: Run Migrations
```bash
docker compose exec laravel.test php artisan migrate
```

### Step 5: Open the System in Browser
```
http://localhost
```

---

## Notes
- Do **not** push your `.env` file to GitHub.
- The `.env.example` file is provided as a template — fill in values as needed.
- If `laravel.test` does not work in Steps 3 and 4, check your `docker-compose.yml` for the correct service name.

---

## Stopping the System
```bash
docker compose down
```

## Restarting the System
```bash
docker compose up -d
```
