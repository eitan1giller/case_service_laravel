# Laravel + React Prototype Skeleton

This is a minimal skeleton demonstrating a PHP Laravel-style backend and a React frontend for the case-management prototype. It is not a full Laravel install — it provides example route/controller files and a small React app to show the API contract.

Backend (Laravel-style)

- `backend/routes/web.php` - example routes
- `backend/app/Http/Controllers/CaseController.php` - example controller
- `backend/composer.json` - placeholder for dependencies

Frontend (React + Vite)

- `frontend/package.json` - deps and scripts
- `frontend/src` - simple React app that calls the backend API

To run the frontend demo (after installing Node.js):

```bash
cd case_service_laravel/frontend
npm install
npm run dev
```

To build a real Laravel backend, run `composer create-project laravel/laravel backend` and copy the controller/routes into that project.
