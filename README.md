# Quiz App - Full Stack Web Application

Modern quiz application built with Laravel (PHP) backend and Vue.js frontend. Create, manage, and take quizzes with rich features including drag-and-drop question ordering, external quiz integration, and comprehensive admin panel.

## 🚀 Features

### Core Functionality
- **Quiz Management**: Create, edit, delete, and organize quizzes
- **Question Types**: Multiple choice, True/False, and Short answer questions
- **Rich Text Editor**: TinyMCE integration for formatted questions and descriptions
- **Image Upload**: Upload quiz cover images
- **Question Ordering**: Drag-and-drop functionality to reorder questions (jQuery UI)

### User Features
- **Authentication**: Secure login/registration with Laravel Sanctum
- **Role-Based Access**: Admin, Teacher, and Student roles
- **Quiz Taking**: Interactive quiz interface with timer and progress tracking
- **Results**: Detailed results with print functionality
- **External Quizzes**: Access to pre-made quizzes from Open Trivia Database API

### Admin Features
- **User Management**: Full CRUD operations for user management
- **Quiz Management**: Manage all quizzes regardless of creator
- **Statistics**: View user statistics and quiz attempts

### Technical Features
- **RESTful API**: Complete API with authentication
- **AJAX**: Asynchronous operations without page refresh
- **Responsive Design**: Bootstrap 5 with Bootswatch Minty theme
- **Search & Filters**: Advanced filtering by category, difficulty, and status
- **Print Support**: Print-friendly results page

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **Laravel Sanctum** - API Authentication
- **SQLite/MySQL** - Database
- **PHP 8.2+**

### Frontend
- **Vue.js 3** - Progressive JavaScript Framework
- **Vue Router** - Client-side routing
- **Axios** - HTTP client
- **Bootstrap 5** - CSS Framework
- **Bootswatch Minty** - Theme
- **TinyMCE** - Rich text editor
- **jQuery UI** - Drag-and-drop functionality
- **Vite** - Build tool

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 20.19.0+ or 22.12.0+
- npm or yarn
- SQLite (default) or MySQL

## 🔧 Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd QuizApp
```

### 2. Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database file (for SQLite)
touch database/database.sqlite

# Or configure MySQL in .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=quiz_app
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Create storage link
php artisan storage:link

# (Optional) Create test user
php create_test_user.php
```

### 3. Frontend Setup

```bash
cd ../frontend

# Install dependencies
npm install

# Create .env file (optional, defaults to localhost:8000)
# VITE_API_BASE_URL=http://localhost:8000
```

### 4. Run the Application

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```
Backend will run on `http://localhost:8000`

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```
Frontend will run on `http://localhost:5173`

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
Most endpoints require authentication via Bearer token:
```
Authorization: Bearer {token}
```

### Public Endpoints

#### Authentication
- `POST /register` - Register new user
- `POST /login` - Login user

#### Quizzes
- `GET /quizzes` - List all quizzes (with filters: ?category=, ?difficulty=, ?search=, ?is_active=)
- `GET /quizzes/{id}` - Get quiz details

#### Questions
- `GET /questions` - List questions (with filters: ?quiz_id=, ?type=)
- `GET /questions/{id}` - Get question details

#### Options
- `GET /options` - List options (with filters: ?question_id=)
- `GET /options/{id}` - Get option details

#### External API
- `GET /external/premade-quizzes` - Get list of premade quizzes
- `GET /external/premade-quizzes/{quizId}` - Get premade quiz with questions

### Protected Endpoints (Require Authentication)

#### User
- `POST /logout` - Logout user
- `GET /user` - Get authenticated user

#### Quizzes
- `POST /quizzes` - Create quiz
- `PUT /quizzes/{id}` - Update quiz
- `DELETE /quizzes/{id}` - Delete quiz

#### Questions
- `POST /questions` - Create question
- `PUT /questions/{id}` - Update question
- `DELETE /questions/{id}` - Delete question
- `POST /quizzes/{id}/questions/reorder` - Reorder questions

#### Options
- `POST /options` - Create option
- `PUT /options/{id}` - Update option
- `DELETE /options/{id}` - Delete option

#### Quiz Attempts
- `POST /quizzes/{id}/start` - Start quiz attempt
- `POST /attempts/answer` - Submit answer
- `POST /attempts/{id}/submit` - Submit quiz
- `GET /attempts/{id}/results` - Get quiz results
- `GET /quizzes/{id}/attempts` - Get user's attempts for quiz

#### File Upload
- `POST /upload/image` - Upload image (max 2MB)
- `POST /upload/pdf` - Upload PDF (max 5MB)

### Admin Endpoints (Require Admin Role)

#### User Management
- `GET /admin/users` - List all users
- `POST /admin/users` - Create user
- `GET /admin/users/{id}` - Get user details
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete user

## 🗂️ Project Structure

```
QuizApp/
├── backend/                 # Laravel backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/ # API controllers
│   │   │   ├── Middleware/  # Custom middleware
│   │   │   └── Requests/    # Form requests
│   │   └── Models/          # Eloquent models
│   ├── database/
│   │   └── migrations/      # Database migrations
│   ├── routes/
│   │   └── api.php         # API routes
│   └── storage/            # File storage
│
└── frontend/               # Vue.js frontend
    ├── src/
    │   ├── components/     # Vue components
    │   ├── views/          # Page views
    │   ├── router/         # Vue Router config
    │   ├── services/       # API service
    │   └── assets/         # Styles and assets
    └── public/             # Static files
```

## 🎯 User Roles

### Student
- View and take quizzes
- View own quiz results
- Cannot create or edit quizzes

### Teacher
- All student permissions
- Create and edit own quizzes
- Manage questions and options
- Cannot access admin panel

### Admin
- All teacher permissions
- Manage all quizzes (edit/delete any quiz)
- User management (CRUD operations)
- Access admin panel

## 🔐 Default Test User

After running `php create_test_user.php`:
- **Email**: test@example.com
- **Password**: password123
- **Role**: Student

## 🚀 Deployment

### Backend Deployment

1. Set environment variables in `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=your_host
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. Run migrations:
   ```bash
   php artisan migrate --force
   ```

3. Optimize for production:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. Set up web server (Apache/Nginx) to point to `backend/public`

### Frontend Deployment

1. Build for production:
   ```bash
   cd frontend
   npm run build
   ```

2. Set `VITE_API_BASE_URL` in `.env.production` to your backend URL

3. Deploy `dist/` folder to your web server

4. Configure web server to serve `index.html` for all routes (SPA routing)

## 🧪 Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## 📝 Environment Variables

### Backend (.env)
```env
APP_NAME="Quiz App"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,localhost:5173,127.0.0.1,127.0.0.1:8000
```

### Frontend (.env)
```env
VITE_API_BASE_URL=http://localhost:8000
```

## 🐛 Troubleshooting

### CORS Issues
- Ensure `SANCTUM_STATEFUL_DOMAINS` includes your frontend URL
- Check CORS middleware configuration

### Storage Issues
- Run `php artisan storage:link` to create symbolic link
- Check `storage/app/public` permissions

### Database Issues
- Ensure database file exists (SQLite) or connection is correct (MySQL)
- Run `php artisan migrate:fresh` to reset database

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👥 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

For support, please open an issue in the repository.

---

**Built with ❤️ using Laravel and Vue.js**
