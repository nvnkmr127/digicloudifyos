#!/bin/bash

echo "🚀 DigiCloudify OS - Laravel Livewire Setup"
echo "=========================================="
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    echo "   Visit: https://getcomposer.org/download/"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install Node.js first."
    echo "   Visit: https://nodejs.org/"
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

echo "✅ Prerequisites check passed"
echo ""

# Step 1: Install PHP dependencies
echo "📦 Step 1/7: Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Composer install failed"
    exit 1
fi

echo "✅ PHP dependencies installed"
echo ""

# Step 2: Install Node dependencies
echo "📦 Step 2/7: Installing Node dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ npm install failed"
    exit 1
fi

echo "✅ Node dependencies installed"
echo ""

# Step 3: Setup environment
echo "⚙️  Step 3/7: Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "ℹ️  .env file already exists"
fi
echo ""

# Step 4: Generate application key
echo "🔑 Step 4/7: Generating application key..."
php artisan key:generate --ansi

if [ $? -ne 0 ]; then
    echo "❌ Key generation failed"
    exit 1
fi

echo "✅ Application key generated"
echo ""

# Step 5: Install Laravel Breeze
echo "🔐 Step 5/7: Installing Laravel Breeze for authentication..."
composer require laravel/breeze --dev --no-interaction

if [ $? -ne 0 ]; then
    echo "❌ Breeze installation failed"
    exit 1
fi

php artisan breeze:install blade --no-interaction

echo "✅ Laravel Breeze installed"
echo ""

# Step 6: Run migrations (ask first)
echo "💾 Step 6/7: Database migrations"
echo "⚠️  Please configure your database in .env file first"
echo "   Then run: php artisan migrate"
echo ""

# Step 7: Build assets
echo "🎨 Step 7/7: Building frontend assets..."
echo "   Run 'npm run dev' to start development server"
echo "   Or run 'npm run build' for production"
echo ""

echo "✅ Setup Complete!"
echo ""
echo "📝 Next Steps:"
echo "   1. Edit .env file and configure your database"
echo "   2. Run: php artisan migrate"
echo "   3. Run: npm run dev (in a separate terminal)"
echo "   4. Run: php artisan serve"
echo "   5. Visit: http://localhost:8000"
echo ""
echo "📚 Documentation:"
echo "   - README.md - Setup and features guide"
echo "   - CONVERSION_GUIDE.md - React to Livewire patterns"
echo "   - FINAL_STATUS.md - Complete implementation status"
echo ""
echo "🎉 Happy coding!"
