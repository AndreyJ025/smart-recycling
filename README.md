# EcoLens - Smart Recycling Platform

EcoLens is a web application designed to facilitate recycling by connecting users with recycling centers, tracking recycled materials, and rewarding eco-friendly behavior.

## Getting Started with Docker

This guide will help you set up the EcoLens platform on your development machine using Docker, which ensures consistency across different development environments.

### Prerequisites

- [Docker](https://www.docker.com/products/docker-desktop) (version 20.10.0 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 1.29.0 or higher)
- Git (optional, for cloning the repository)

### Installation

1. **Clone the repository** (skip if you already have the codebase):

```bash
git clone https://github.com/yourusername/smart-recycling.git
cd smart-recycling
```

2. **Start the Docker environment**:

```bash
docker-compose up -d
```

This command starts three services:
- `web`: PHP/Apache server (access at http://localhost:8080)
- `db`: MySQL database server
- `phpmyadmin`: Database administration tool (access at http://localhost:8081)

3. **Wait for initialization**:

The first startup may take a few minutes as Docker downloads images, builds containers, and initializes the database.

### Accessing the Application

- **Main Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (use username `root` and password `root_password`)

### Default Admin Account

- **Email**: admin@gmail.com
- **Password**: 12345

### Project Structure

- `/` - Root project directory
- `/docker` - Docker configuration files
- `/auth` - Authentication-related files
- `/admin` - Admin dashboard and management
- `/centers` - Recycling centers functionality
- `/users` - User profiles and management
- `/uploads` - Storage for uploaded files

### Development Workflow

1. Make changes to PHP files in the project directory
2. Changes are immediately available in the running application (no rebuild needed)
3. For database changes, use phpMyAdmin or SQL scripts

### Configuration

#### Environment Variables

Docker Compose sets these environment variables for connecting to the database:

- `DB_HOST`: db
- `DB_USER`: root
- `DB_PASSWORD`: root_password
- `DB_NAME`: smart_recycling

#### API Keys

If you need to modify API keys, update these files:

- Google Maps API: `/web/.env`
- SerpAPI: `/config/serpapi-config.php`
- SERPERAPI: `/config/serper-config.php`

### Troubleshooting

#### Database Connection Issues

If you encounter database connection problems:

1. Ensure all containers are running:
```bash
docker-compose ps
```

2. Check the logs for errors:
```bash
docker-compose logs db
docker-compose logs web
```

3. Try resetting the database:
```bash
docker-compose down
docker volume rm smart-recycling_mysql_data
docker-compose up -d
```

#### Permission Issues

If you encounter permission problems with file uploads:

```bash
docker-compose exec web chmod -R 775 /var/www/html/uploads
docker-compose exec web chown -R www-data:www-data /var/www/html/uploads
```

### Stopping the Environment

```bash
docker-compose down   # Stops containers but preserves data
docker-compose down -v   # Stops containers and removes volumes (data will be lost)
```

### Updating the Application

```bash
git pull   # If you cloned from a repository
docker-compose down
docker-compose up -d --build
```
