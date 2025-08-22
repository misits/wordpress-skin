#!/bin/bash
# WP-Skin Production Deployment Script
# This script creates a production-ready version of your theme

set -e  # Exit on any error

THEME_NAME=$(basename "$PWD")
BUILD_DIR="../${THEME_NAME}-production"

echo "🚀 Starting production build for theme: $THEME_NAME"

# Check if resources directory exists
if [ ! -d "resources" ]; then
    echo "❌ No resources directory found. Make sure you're in the theme root directory."
    exit 1
fi

# Build assets
echo "📦 Building production assets..."
cd resources/

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    echo "📥 Installing dependencies..."
    npm install
fi

# Build for production
echo "🏗️  Building CSS and JS..."
npm run build

cd ../

# Create build directory
echo "📁 Creating production build directory..."
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# Copy essential theme files
echo "📋 Copying theme files..."
cp -r lib/ "$BUILD_DIR/" 2>/dev/null || true
cp functions.php "$BUILD_DIR/" 2>/dev/null || true
cp skin.php "$BUILD_DIR/" 2>/dev/null || true
cp index.php "$BUILD_DIR/" 2>/dev/null || true
cp style.css "$BUILD_DIR/" 2>/dev/null || true
cp *.php "$BUILD_DIR/" 2>/dev/null || true

# Copy only production resources
echo "📦 Copying production assets..."
mkdir -p "$BUILD_DIR/resources"

# Copy built assets
cp -r resources/assets/ "$BUILD_DIR/resources/" 2>/dev/null || true
cp -r resources/components/ "$BUILD_DIR/resources/" 2>/dev/null || true
cp -r resources/layouts/ "$BUILD_DIR/resources/" 2>/dev/null || true
cp -r resources/views/ "$BUILD_DIR/resources/" 2>/dev/null || true
cp -r resources/dist/ "$BUILD_DIR/resources/" 2>/dev/null || true

# Set proper permissions
echo "🔐 Setting file permissions..."
find "$BUILD_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || true
find "$BUILD_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || true

# Show size comparison
echo "📊 Size comparison:"
echo "   Development: $(du -sh . | cut -f1)"
echo "   Production:  $(du -sh "$BUILD_DIR" | cut -f1)"

echo ""
echo "✅ Production build complete!"
echo "📁 Build location: $BUILD_DIR"
echo ""
echo "🔺 Upload these directories to your production server:"
echo "   - lib/wp-skin/ (WP-Skin core)"
echo "   - resources/assets/ (compiled CSS/JS)"
echo "   - resources/components/ (PHP components)"
echo "   - resources/layouts/ (PHP layouts)"
echo "   - resources/views/ (PHP views)"
echo "   - resources/dist/ (built modern JS assets)"
echo "   - *.php files (theme files)"
echo ""
echo "🚫 DO NOT upload:"
echo "   - resources/src/ (source files)"
echo "   - resources/node_modules/ (dependencies)"
echo "   - resources/package*.json (build configs)"
echo "   - resources/vite.config.js (build tools)"
echo ""
echo "🎯 Ready for production deployment!"