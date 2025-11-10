# Project Management System<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



A comprehensive web-based project management application built with Laravel 12, featuring Kanban boards, task management, time tracking, and team collaboration tools.<p align="center">

<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

![License](https://img.shields.io/badge/License-MIT-green.svg)<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

</p>

## 🚀 Features

## About Laravel

### Project Management

- **Kanban Board System** - Visual task management with drag-and-drop functionalityLaravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- **Multiple Projects** - One active project per user to maintain focus

- **Project Archiving** - Archive completed projects and automatically free team members- [Simple, fast routing engine](https://laravel.com/docs/routing).

- **Project Roles** - Project Admin, Team Lead, Developer, and Designer roles- [Powerful dependency injection container](https://laravel.com/docs/container).

- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.

### Task Management- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).

- **Task Cards** - Create, assign, and track tasks across different board columns- Database agnostic [schema migrations](https://laravel.com/docs/migrations).

- **Subtasks** - Break down tasks into manageable subtasks- [Robust background job processing](https://laravel.com/docs/queues).

- **Task Priority** - Set priorities (Critical, High, Medium, Low)- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

- **Task Approval** - Team Lead approval system for completed tasks

- **Task Assignment** - Assign tasks to specific team membersLaravel is accessible, powerful, and provides tools required for large, robust applications.



### Team Collaboration## Learning Laravel

- **Team Management** - Add and manage team members with specific roles

- **Blocker System** - Report and track blockers with helper assignmentLaravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all mo`dern web application frameworks, making it a breeze to get started with the framework.

- **Real-time Notifications** - Get notified about task updates, blockers, and team activities

- **Comments** - Add comments and updates to blockers and tasksYou may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.



### Time TrackingIf you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

- **Time Logs** - Track time spent on tasks and subtasks

- **Work Sessions** - Start/stop time tracking for active work## Laravel Sponsors

- **Time Reports** - View detailed time logs and activity history

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Reports & Analytics

- **Project Reports** - Generate comprehensive project status reports### Premium Partners

- **Task Statistics** - View task completion rates and progress

- **Time Analytics** - Analyze time spent across tasks and projects- **[Vehikl](https://vehikl.com)**

- **[Tighten Co.](https://tighten.co)**

## 📋 Requirements- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**

- **[64 Robots](https://64robots.com)**

- PHP >= 8.2- **[Curotec](https://www.curotec.com/services/technologies/laravel)**

- Composer- **[DevSquad](https://devsquad.com/hire-laravel-developers)**

- MySQL/MariaDB- **[Redberry](https://redberry.international/laravel-development)**

- Node.js & NPM (for asset compilation)- **[Active Logic](https://activelogic.com)**



## 🔧 Installation## Contributing



1. **Clone the repository**Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

```bash

git clone <repository-url>## Code of Conduct

cd pm

```In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).



2. **Install PHP dependencies**## Security Vulnerabilities

```bash

composer installIf you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

```

## License

3. **Install NPM dependencies**

```bashThe Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

npm install

```## Additional setup for social login and password reset



4. **Environment setup**If you plan to enable Google OAuth login and email password resets, add the following environment variables to your environment (for example in a local `.env` file):

```bash

cp .env.example .env```

php artisan key:generateGOOGLE_CLIENT_ID=your-google-client-id

```GOOGLE_CLIENT_SECRET=your-google-client-secret

GOOGLE_REDIRECT=https://your-app.test/auth/google/callback

5. **Database configuration**```



Edit `.env` file and configure your database:After adding env values, run the migration to add the `google_id` column:

```env

DB_CONNECTION=mysql```bash

DB_HOST=127.0.0.1php artisan migrate

DB_PORT=3306```

DB_DATABASE=manajemen_proyek

DB_USERNAME=rootMake sure your mail settings are configured in the environment so password reset emails can be sent (MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS).

DB_PASSWORD=
```

6. **Run migrations**
```bash
php artisan migrate
```

7. **Seed database (optional)**
```bash
php artisan db:seed
```

8. **Build assets**
```bash
npm run build
```

9. **Start development server**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 👥 User Roles

### System Roles
- **Admin** - Full system access and management
- **Team Lead** - Manage teams and approve tasks
- **Developer** - Work on assigned tasks
- **Designer** - Work on design-related tasks

### Project Roles
- **Project Admin** - Full project control (automatically assigned to project creator)
- **Team Lead** - Manage team members and approve tasks (one per project)
- **Developer** - Execute development tasks
- **Designer** - Execute design tasks

## 🎯 Key Workflows

### Creating a Project
1. Only users with Project Admin privileges can create projects
2. Creator automatically becomes Project Admin
3. Users can only create a new project if they're not in another active project

### Task Management
1. Create boards (columns) for your project workflow
2. Add task cards to boards
3. Assign tasks to team members
4. Move tasks between columns via drag-and-drop
5. Team Lead approves completed tasks

### Blocker System
1. Report a blocker when stuck on a task
2. Team Lead/Project Admin assigns a helper
3. Helper works on resolving the blocker
4. Update status as work progresses
5. Reporter gets notified when resolved

### Time Tracking
1. Start time log when beginning work on a task
2. Add notes about work performed
3. Stop time log when taking a break or finishing
4. View all time logs in the time tracking section

## 🔐 Security Features

- Authentication and authorization
- Role-based access control (RBAC)
- CSRF protection
- SQL injection prevention
- XSS protection

## 🛠️ Technology Stack

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade Templates
- **Database:** MySQL/MariaDB
- **CSS Framework:** Bootstrap 5.3 + Tailwind CSS 4.0
- **Build Tool:** Vite 6
- **Icons:** Font Awesome 6, Bootstrap Icons
- **JavaScript:** Vanilla JS, Axios
- **Authentication:** Laravel Sanctum, Laravel Socialite (Google OAuth)

## 📁 Project Structure

```
pm/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/               # Eloquent models
│   └── Providers/            # Service providers
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/             # Database seeders
├── public/                   # Public assets
├── resources/
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── views/               # Blade templates
├── routes/
│   └── web.php              # Web routes
└── storage/                 # Application storage
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is licensed under the MIT License.

## 👨‍💻 Developer

Developed as part of UKK (Uji Kompetensi Keahlian) project.

## 📧 Support

For support and questions, please open an issue in the repository.

---

**Note:** This project is actively maintained and regularly updated with new features and improvements.
