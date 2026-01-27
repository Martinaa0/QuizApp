# API Documentation

Complete API reference for Quiz App.

## Base URL
```
http://localhost:8000/api
```

## Authentication

Most endpoints require authentication. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {your_token_here}
```

Tokens are obtained from `/login` or `/register` endpoints.

---

## Public Endpoints

### Authentication

#### Register User
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "user_type": "student" // optional: admin, teacher, student
}
```

**Response:**
```json
{
  "message": "User registered successfully",
  "user": { ... },
  "token": "1|..."
}
```

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "user": { ... },
  "token": "1|..."
}
```

---

### Quizzes

#### List Quizzes
```http
GET /quizzes?category=Science&difficulty=medium&search=math&is_active=1
```

**Query Parameters:**
- `category` (optional) - Filter by category
- `difficulty` (optional) - Filter by difficulty (easy, medium, hard)
- `search` (optional) - Search in title
- `is_active` (optional) - Filter by active status (0 or 1)

**Response:**
```json
[
  {
    "id": 1,
    "title": "Math Quiz",
    "description": "...",
    "category": "Science",
    "difficulty": "medium",
    "duration": 30,
    "is_active": true,
    "image": "quizzes/image.jpg",
    "created_by": 1,
    "creator": { ... },
    "questions": [ ... ]
  }
]
```

#### Get Quiz
```http
GET /quizzes/{id}
```

**Response:**
```json
{
  "id": 1,
  "title": "Math Quiz",
  "description": "...",
  "category": "Science",
  "difficulty": "medium",
  "duration": 30,
  "is_active": true,
  "image": "quizzes/image.jpg",
  "created_by": 1,
  "creator": { ... },
  "questions": [
    {
      "id": 1,
      "text": "What is 2+2?",
      "type": "multiple_choice",
      "points": 1,
      "order": 0,
      "options": [ ... ]
    }
  ]
}
```

---

### Questions

#### List Questions
```http
GET /questions?quiz_id=1&type=multiple_choice
```

**Query Parameters:**
- `quiz_id` (optional) - Filter by quiz
- `type` (optional) - Filter by type (multiple_choice, true_false, short_answer)

#### Get Question
```http
GET /questions/{id}
```

---

### Options

#### List Options
```http
GET /options?question_id=1
```

#### Get Option
```http
GET /options/{id}
```

---

### External API

#### Get Premade Quizzes
```http
GET /external/premade-quizzes?category=9&difficulty=medium
```

**Response:**
```json
{
  "success": true,
  "quizzes": [
    {
      "id": "trivia_general_10",
      "title": "General Knowledge Quiz",
      "description": "...",
      "category": "General Knowledge",
      "difficulty": "medium",
      "question_count": 10,
      "duration": 10,
      "is_external": true
    }
  ]
}
```

#### Get Premade Quiz Questions
```http
GET /external/premade-quizzes/{quizId}
```

---

## Protected Endpoints

### User

#### Logout
```http
POST /logout
Authorization: Bearer {token}
```

#### Get Authenticated User
```http
GET /user
Authorization: Bearer {token}
```

---

### Quizzes

#### Create Quiz
```http
POST /quizzes
Authorization: Bearer {token}
Content-Type: multipart/form-data

title: "New Quiz"
description: "Quiz description"
category: "Science"
difficulty: "medium"
duration: 30
is_active: true
image: [file] // optional, max 2MB
```

#### Update Quiz
```http
PUT /quizzes/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

// Same as create, all fields optional
```

#### Delete Quiz
```http
DELETE /quizzes/{id}
Authorization: Bearer {token}
```

---

### Questions

#### Create Question
```http
POST /questions
Authorization: Bearer {token}
Content-Type: application/json

{
  "quiz_id": 1,
  "text": "What is 2+2?",
  "type": "multiple_choice",
  "points": 1,
  "order": 0
}
```

#### Update Question
```http
PUT /questions/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "text": "Updated question",
  "points": 2
}
```

#### Delete Question
```http
DELETE /questions/{id}
Authorization: Bearer {token}
```

#### Reorder Questions
```http
POST /quizzes/{id}/questions/reorder
Authorization: Bearer {token}
Content-Type: application/json

{
  "order": [
    { "id": 1, "order": 0 },
    { "id": 2, "order": 1 },
    { "id": 3, "order": 2 }
  ]
}
```

---

### Options

#### Create Option
```http
POST /options
Authorization: Bearer {token}
Content-Type: application/json

{
  "question_id": 1,
  "text": "4",
  "is_correct": true,
  "order": 0
}
```

#### Update Option
```http
PUT /options/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "text": "Updated option",
  "is_correct": false
}
```

#### Delete Option
```http
DELETE /options/{id}
Authorization: Bearer {token}
```

---

### Quiz Attempts

#### Start Quiz
```http
POST /quizzes/{id}/start
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Quiz attempt started successfully",
  "attempt": {
    "id": 1,
    "user_id": 1,
    "quiz_id": 1,
    "started_at": "2024-01-01 12:00:00",
    "status": "in_progress"
  }
}
```

#### Submit Answer
```http
POST /attempts/answer
Authorization: Bearer {token}
Content-Type: application/json

{
  "attempt_id": 1,
  "question_id": 1,
  "option_id": 1, // for multiple_choice/true_false
  "answer_text": "answer" // for short_answer
}
```

**Response:**
```json
{
  "message": "Answer submitted successfully",
  "is_correct": true,
  "points_earned": 1
}
```

#### Submit Quiz
```http
POST /attempts/{id}/submit
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Quiz submitted successfully",
  "attempt": {
    "id": 1,
    "score": 8,
    "total_points": 10,
    "percentage": 80.00,
    "completed_at": "2024-01-01 12:30:00",
    "status": "completed",
    "user_answers": [ ... ]
  }
}
```

#### Get Results
```http
GET /attempts/{id}/results
Authorization: Bearer {token}
```

#### Get User Attempts
```http
GET /quizzes/{id}/attempts
Authorization: Bearer {token}
```

---

### File Upload

#### Upload Image
```http
POST /upload/image
Authorization: Bearer {token}
Content-Type: multipart/form-data

image: [file] // max 2MB, formats: jpeg, png, jpg, gif, webp
```

**Response:**
```json
{
  "url": "/storage/quizzes/image.jpg",
  "path": "quizzes/image.jpg"
}
```

#### Upload PDF
```http
POST /upload/pdf
Authorization: Bearer {token}
Content-Type: multipart/form-data

pdf: [file] // max 5MB
```

---

## Admin Endpoints

### User Management

#### List Users
```http
GET /admin/users?search=john&user_type=student&page=1
Authorization: Bearer {admin_token}
```

#### Create User
```http
POST /admin/users
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "John Doe",
  "username": "johndoe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "user_type": "student"
}
```

#### Get User
```http
GET /admin/users/{id}
Authorization: Bearer {admin_token}
```

#### Update User
```http
PUT /admin/users/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Updated Name",
  "user_type": "teacher"
}
```

#### Delete User
```http
DELETE /admin/users/{id}
Authorization: Bearer {admin_token}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized. You can only edit your own quizzes."
}
```

### 404 Not Found
```json
{
  "message": "Quiz not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Rate Limiting

API endpoints are rate-limited. Check response headers:
- `X-RateLimit-Limit` - Maximum requests
- `X-RateLimit-Remaining` - Remaining requests

---

## Notes

- All dates are in UTC format
- File uploads are stored in `storage/app/public`
- External quizzes are fetched from Open Trivia Database API
- Quiz attempts are automatically submitted when timer expires
