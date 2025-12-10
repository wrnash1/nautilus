#!/bin/bash
# Nautilus Development Environment - Quick Start Script
# Works with both Docker and Podman

set -e

COMPOSE_CMD=""

# Detect if using Podman or Docker
if command -v podman-compose &> /dev/null; then
    COMPOSE_CMD="podman-compose"
    echo "✓ Using Podman Compose"
elif command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
    echo "✓ Using Docker Compose"
elif command -v docker &> /dev/null && docker compose version &> /dev/null; then
    COMPOSE_CMD="docker compose"
    echo "✓ Using Docker Compose (plugin)"
else
    echo "❌ Error: Neither podman-compose nor docker-compose found!"
    echo ""
    echo "Please install one of:"
    echo "  - Podman: sudo dnf install podman podman-compose"
    echo "  - Docker: sudo dnf install docker docker-compose"
    exit 1
fi

echo ""
echo "=========================================="
echo "  Nautilus Development Environment"
echo "=========================================="
echo ""

# Parse command line arguments
case "${1:-up}" in
    up|start)
        echo "🚀 Starting Nautilus development environment..."
        echo ""
        $COMPOSE_CMD up -d

        # Fix file ownership for git/development (Podman user namespace issue)
        echo "🔧 Fixing file permissions for development..."
        podman unshare chown -R 0:0 .

        echo ""
        echo "✓ Containers started!"
        echo ""
        echo "Access points:"
        echo "  • Nautilus Installer: http://localhost:8080/install.php"
        echo "  • Nautilus App:       http://localhost:8080/"
        echo "  • phpMyAdmin:         http://localhost:8081/"
        echo ""
        echo "Database credentials for installer:"
        echo "  Host:     database"
        echo "  Port:     3306"
        echo "  Database: nautilus"
        echo "  Username: root"
        echo "  Password: Frogman09!"
        echo ""
        ;;

    down|stop)
        echo "🛑 Stopping Nautilus development environment..."
        $COMPOSE_CMD down
        echo "✓ Containers stopped!"
        ;;

    restart)
        echo "🔄 Restarting Nautilus development environment..."
        $COMPOSE_CMD down
        $COMPOSE_CMD up -d
        echo "✓ Containers restarted!"
        ;;

    reset|fresh)
        echo "🗑️  Destroying all data and starting fresh..."
        echo ""
        read -p "This will DELETE the database and all data. Continue? [y/N] " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            $COMPOSE_CMD down -v
            rm -f .env .installed
            echo "✓ All data destroyed!"
            echo ""
            echo "Starting fresh environment..."
            $COMPOSE_CMD up -d
            echo ""
            echo "✓ Fresh install ready!"
            echo "  Visit: http://localhost:8080/install.php"
        else
            echo "Cancelled."
        fi
        ;;

    logs)
        echo "📋 Showing container logs (Ctrl+C to exit)..."
        $COMPOSE_CMD logs -f
        ;;

    shell|bash)
        echo "🐚 Opening shell in web container..."
        if [[ "$COMPOSE_CMD" == "podman-compose" ]]; then
            podman exec -it nautilus-web /bin/bash
        else
            docker exec -it nautilus-web /bin/bash
        fi
        ;;

    db|mysql)
        echo "🗄️  Connecting to MariaDB..."
        if [[ "$COMPOSE_CMD" == "podman-compose" ]]; then
            podman exec -it nautilus-db mysql -u root -pFrogman09! nautilus
        else
            docker exec -it nautilus-db mysql -u root -pFrogman09! nautilus
        fi
        ;;

    status|ps)
        echo "📊 Container status:"
        $COMPOSE_CMD ps
        ;;

    build)
        echo "🔨 Rebuilding containers..."
        $COMPOSE_CMD build --no-cache
        echo "✓ Build complete!"
        ;;

    help|*)
        echo "Nautilus Development Environment - Quick Start"
        echo ""
        echo "Usage: ./start-dev.sh [command]"
        echo ""
        echo "Commands:"
        echo "  up, start    Start the development environment (default)"
        echo "  down, stop   Stop all containers"
        echo "  restart      Restart all containers"
        echo "  reset, fresh Destroy all data and start fresh (for clean install testing)"
        echo "  logs         Show container logs"
        echo "  shell, bash  Open shell in web container"
        echo "  db, mysql    Connect to MariaDB database"
        echo "  status, ps   Show container status"
        echo "  build        Rebuild containers from scratch"
        echo "  help         Show this help message"
        echo ""
        echo "Examples:"
        echo "  ./start-dev.sh              # Start development environment"
        echo "  ./start-dev.sh reset        # Fresh install for testing"
        echo "  ./start-dev.sh logs         # View logs"
        echo "  ./start-dev.sh db           # Access database"
        ;;
esac
