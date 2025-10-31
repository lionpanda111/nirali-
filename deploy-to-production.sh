#!/bin/bash

# Deployment Script for Nirali Makeup Studio
# Run this script to prepare the site for production

echo "🚀 Starting deployment preparation..."
echo "----------------------------------"

# 1. Backup current config if exists
if [ -f "includes/config.php" ]; then
    echo "🔧 Backing up current config.php..."
    cp includes/config.php includes/config.backup.$(date +%Y%m%d).php
fi

# 2. Copy production config
if [ -f "includes/config.production.php" ]; then
    echo "⚙️  Activating production configuration..."
    cp includes/config.production.php includes/config.php
else
    echo "❌ Error: Production configuration file not found!"
    exit 1
fi

# 3. Create necessary directories
echo "📁 Creating required directories..."
mkdir -p logs uploads/cache

# 4. Set proper permissions
echo "🔒 Setting file permissions..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 777 logs uploads
chmod 755 .htaccess

# 5. Remove development files
echo "🧹 Cleaning up development files..."
rm -rf .git .github .vscode .idea *.sublime-*
rm -f *.log *.sql
find . -name "*.bak" -delete
find . -name "*.backup" -delete
find . -name "*.tmp" -delete

# 6. Optimize images (requires optipng and jpegoptim)
echo "🖼️  Optimizing images..."
if command -v optipng &> /dev/null; then
    find . -name "*.png" -exec optipng -o7 {} \;
fi

if command -v jpegoptim &> /dev/null; then
    find . -name "*.jpg" -exec jpegoptim --strip-all {} \;
fi

# 7. Generate sitemap
echo "🗺️  Generating sitemap..."
# Add your sitemap generation command here

# 8. Clear any cache
echo "🧼 Clearing cache..."
rm -rf cache/*

# 9. Verify installation
echo "✅ Verifying installation..."
if [ -f "includes/config.php" ] && [ -d "admin" ] && [ -d "assets" ]; then
    echo "🎉 Deployment preparation completed successfully!"
    echo "Next steps:"
    echo "1. Upload files to your hosting"
    echo "2. Import your database"
    echo "3. Update database credentials in includes/config.php"
    echo "4. Test the site thoroughly"
else
    echo "⚠️  Warning: Some verification steps failed. Please check the output above."
fi

echo "----------------------------------"
echo "🚀 Ready for deployment!"
