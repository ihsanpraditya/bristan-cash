# Variables
CORE_ZIP=laravel_core.zip
PUBLIC_ZIP=laravel_public.zip
NYATU_ZIP=laravel_nyatu.zip
DB_PROD=production.sqlite
DB_DEV=database/database.sqlite

.PHONY: all build zip-pisah zip-nyatu zip-toshare restore-dev clean help

# Default command run when you type 'make'
all: clean build zip-pisah

help:
	@echo "Available commands:"
	@echo "  make            - Clean, build assets, generate seeded prod DB, package split ZIPs (core & public)"
	@echo "  make zip-pisah  - Package core and public into separate ZIPs (laravel_core.zip & laravel_public.zip)"
	@echo "  make zip-nyatu  - Package everything into a single ZIP (laravel_nyatu.zip)"
	@echo "  make clean      - Remove generated ZIP files and temporary build exports (SAFE: never deletes dev DB)"
	@echo "  make restore-dev- Reinstall local composer dev dependencies (Pest, Pint, etc.)"

build:
	@echo "🧹 Removing dev hot-reload flag if present..."
	rm -f public/hot

	@echo "🎨 Compiling Tailwind & Vite assets for production..."
	bun run build

	@echo "🧾 Preparing clean production database with initial seeders..."
	@touch $(DB_DEV)
	@if [ -f $(DB_DEV) ]; then cp $(DB_DEV) database/dev.sqlite; fi
	php artisan migrate:fresh --seed --force
	cp $(DB_DEV) $(DB_PROD)

	@echo "📦 Installing production PHP dependencies (no-dev)..."
	composer install --no-dev --optimize-autoloader

	@echo "🧼 Clearing application and configuration caches..."
	php artisan optimize:clear

zip-pisah:
	@echo "🤐 Packaging core application (laravel_core.zip)..."
	zip -r $(CORE_ZIP) . \
		-x "public/*" \
		-x "node_modules/*" \
		-x "storage/framework/cache/data/*" \
		-x "storage/framework/sessions/*" \
		-x "storage/framework/views/*" \
		-x "storage/logs/*" \
		-x "tests/*" \
		-x ".git/*" \
		-x ".env" \
		-x "Makefile" \
		-x ".agents/*" \
		-x ".claude/*" \
		-x ".cursor/*" \
		-x ".gemini/*" \
		-x "*.zip" \
		-x "database/*"

	@echo "📂 Packaging public folder contents (laravel_public.zip)..."
	cd public && zip -r ../$(PUBLIC_ZIP) . -x "hot" -x "*.DS_Store" -x "index.php" && cd ..

	@echo "🔄 Restoring development SQLite database..."
	@if [ -f database/dev.sqlite ]; then mv -f database/dev.sqlite $(DB_DEV); fi

	@echo "🔄 Restoring development Composer dependencies..."
	composer install

	@echo ""
	@echo "=========================================================================="
	@echo "🚀 SIAP DEPLOY KE SHARED HOSTING (FTP / cPanel File Manager):"
	@echo "=========================================================================="
	@echo "1. Upload & Ekstrak '$(CORE_ZIP)' ke folder DILUAR public_html:"
	@echo "   Contoh path: /home/username/laravel_core/"
	@echo ""
	@echo "2. Upload & Ekstrak '$(PUBLIC_ZIP)' langsung di dalam folder web publik:"
	@echo "   Contoh path: /home/username/public_html/"
	@echo "   (public/index.php sudah otomatis mendeteksi folder ../laravel_core)"
	@echo ""
	@echo "3. Siapkan file .env di dalam /home/username/laravel_core/:"
	@echo "   Salin dari .env.production.example lalu sesuaikan APP_URL & APP_KEY."
	@echo ""
	@echo "4. Pastikan folder berikut memiliki izin tulis (chmod 775 atau 777):"
	@echo "   - /home/username/laravel_core/storage/"
	@echo "   - /home/username/laravel_core/bootstrap/cache/"
	@echo "   - /home/username/laravel_core/database/ (dan file database.sqlite)"
	@echo "=========================================================================="

zip-nyatu:
	@echo "🤐 Packaging single application (laravel_nyatu.zip)..."
	zip -r $(NYATU_ZIP) . \
		-x "node_modules/*" \
		-x "storage/framework/cache/data/*" \
		-x "storage/framework/sessions/*" \
		-x "storage/framework/views/*" \
		-x "storage/logs/*" \
		-x "tests/*" \
		-x ".git/*" \
		-x ".env" \
		-x "Makefile" \
		-x ".agents/*" \
		-x ".claude/*" \
		-x ".cursor/*" \
		-x ".gemini/*" \
		-x "*.zip" \
		-x "public/hot" \
		-x "database/*"

	@echo "🔄 Restoring development SQLite database..."
	@if [ -f database/dev.sqlite ]; then mv -f database/dev.sqlite $(DB_DEV); fi

	@echo "🔄 Restoring development Composer dependencies..."
	composer install

	@echo "🚀 Done! Upload $(NYATU_ZIP) directly inside your web root."

zip-toshare:
	zip -r to_share.zip . -x "to_share.zip" -x "to_share/*" -x "tests/*" -x "node_modules/*" -x "vendor/*" -x "storage/app/*" -x "storage/framework/cache/data/*" -x "storage/framework/sessions/*" -x "storage/framework/views/*" -x "storage/logs/*" -x ".git/*" -x ".env" -x "Makefile"
	unzip -o to_share.zip -d to_share

restore-dev:
	composer install

clean:
	@echo "🧹 Cleaning up old deployment zip files and temporary exports..."
	rm -f $(CORE_ZIP)
	rm -f $(PUBLIC_ZIP)
	rm -f $(NYATU_ZIP)
	rm -f $(DB_PROD)
	rm -f database/dev.sqlite
	rm -rf to_share to_share.zip
