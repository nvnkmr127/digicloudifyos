# DigiCloudify OS

Laravel 11 + Livewire 3 monolith for agency operations, integrations, and daily insights.

## Tech Stack

- PHP 8.2+
- Laravel 11
- Livewire 3 (server-rendered UI)
- Vite 5 (assets)
- Tailwind CSS

## Quick Start

Install dependencies:

```bash
composer install
npm install
```

Create an environment file and app key:

```bash
cp .env.example .env
php artisan key:generate
```

Run the app (two terminals):

```bash
php artisan serve
```

```bash
npm run dev
```

Production assets:

```bash
npm run build
```
