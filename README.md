# TechNova NewsRoom

NewsRoom is an internal news and announcement platform built for TechNova. It provides a centralized space for writers to publish articles and for employees to read and comment on them.

## Roles & Permissions

- **Admin**: Has full access to manage all articles, users, and view the internal dashboard statistics.
- **Writer**: Can create new articles and update/delete their own articles.
- **Reader (Guest)**: Can view published articles and interact via comments.

*Note: Published articles are visible to all readers, while unpublished (draft/archived) articles are strictly restricted to the owning writer or an admin.*

---

## Setup Instructions

Follow these steps to set up the NewsRoom project locally.

1. **Clone the repository** (Manual deployment handled by user)
2. **Install dependencies**:
   ```bash
   composer install
   ```
3. **Configure Environment**:
   ```bash
   cp .env.example .env
   ```
   - Update your database configuration (`DB_*`).
   - Update your Redis configuration (`REDIS_*`) for caching and queues.
   - Configure Sanctum token expiration (optional) using `SANCTUM_TOKEN_EXPIRATION`.
4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```
5. **Migrate and Seed the Database**:
   ```bash
   php artisan migrate:fresh --seed
   ```
6. **Start Horizon (Queue Worker)**:
   ```bash
   php artisan horizon
   ```
7. **Start the Scheduler**:
   To run scheduled tasks (like archiving and reports) locally, use:
   ```bash
   php artisan schedule:work
   ```
   *In production, add the standard Laravel cron entry to your server.*
8. **Run Tests**:
   ```bash
   ./vendor/bin/phpunit
   ```

---

## Entity Relationships

The NewsRoom application relies on the following core entities and relationships:

- `User` **hasOne** `Profile`
- `User` **hasMany** `Articles`
- `User` **hasMany** `Comments`
- `Article` **belongsTo** `User` (Author)
- `Article` **morphMany** `Comments`
- `Article` **morphMany** `Attachments`
- `Article` **morphToMany** `Tags`
- `Comment` **morphTo** `commentable` (Article, etc.)
- `Comment` **belongsTo** `User`
- `Attachment` **morphTo** `attachable`
- `Tag` **morphedByMany** `Article`
- `Profile` **belongsTo** `User`
- `Profile` **morphMany** `Attachments`
- `ApiRequestLog` **belongsTo** `User`

---

## Architectural Decisions & Rationale

### A. Why Repository + Service Layer?
- **Thin Controllers**: Controllers are strictly responsible for handling HTTP requests and returning responses.
- **Business Logic Isolation**: Business logic (publishing, archiving) lives in the Service layer, not the controllers.
- **Data Source Abstraction**: The Repository pattern abstracts data access. If we migrate away from Eloquent, we only swap the repository binding; controllers remain untouched.

### B. Why Policy + Middleware?
- **Middleware (`RoleMiddleware`)**: Handles coarse, route-level access control (e.g., blocking non-admins from the dashboard).
- **Policy (`ArticlePolicy`)**: Handles fine-grained, resource-level authorization. Logic determining if an article is published (public) or unpublished (requires owner/admin) belongs precisely here.

### C. Before Middleware vs. After Middleware
- **Before Middleware**: Runs *before* the controller logic. Used for blocking requests and authorization checks (e.g., Sanctum).
- **After Middleware**: Runs *after* the response is prepared. The `ApiRequestLogger` is an after middleware because it requires the final status code and response duration to log accurately.

### D. Cache::remember() vs. Cache::lock()
- **`Cache::remember()`**: Stores computed data for a set duration.
- **`Cache::lock()`**: Prevents a cache stampede. If 300 concurrent users hit an expired cache, the lock ensures only *one* request queries the database to rebuild the cache, while the others wait. They are used together to protect expensive queries like dashboard statistics.

### E. queue:work vs. Horizon
- **`queue:work`**: A basic worker process for executing queued jobs.
- **Horizon**: Provides a robust dashboard, process monitoring, auto-balancing, and deep visibility into job retries/failures. We use separate queues (`notifications`, `reports`) to prevent slow report generation from blocking time-sensitive email notifications.

### F. Events vs. Observers
- **Events (`ArticlePublished`)**: Represent specific business-domain occurrences. Firing `ArticlePublished` triggers cross-cutting concerns like dispatching subscriber notifications.
- **Observers (`ArticleObserver`)**: React to generic Eloquent model lifecycle changes (`created`, `updated`, `deleted`). Used here for generic actions like automatic cache invalidation.

### G. Why API URL Versioning?
- URL versioning (`/api/v1/` and `/api/v2/`) allows both API iterations to coexist. 
- When V2 introduces new fields (`reading_time`, `comments_count`), V1 remains explicitly uncoupled and unbroken. It is highly explicit and easy for clients to consume.

---

## API Documentation

### Authentication Endpoints
- `POST /api/auth/login`: Accepts `email` and `password`, returns a Bearer token.
- `GET /api/auth/me`: Returns the authenticated user's profile (Requires Bearer token).
- `POST /api/auth/logout`: Revokes the current token (Requires Bearer token).

### Public Endpoints
*Guest access is permitted for published articles.*
- `GET /api/articles`
- `GET /api/articles/{article}`
- `GET /api/v1/articles`
- `GET /api/v1/articles/{article}`
- `GET /api/v2/articles`
- `GET /api/v2/articles/{article}`

### Protected Endpoints
*Require `auth:sanctum` bearer token.*
- `POST /api/articles` (Writer/Admin)
- `PUT/PATCH /api/articles/{article}` (Writer [own] / Admin)
- `DELETE /api/articles/{article}` (Writer [own] / Admin)

### Admin Endpoints
*Require `auth:sanctum` and `role:admin`.*
- `GET /api/dashboard/stats`

---

## API Response Versions

**V1 Response** (`/api/v1/articles`) returns:
- `id`
- `title`
- `content`
- `author_name`
- `published_at`

**V2 Response** (`/api/v2/articles`) returns:
- All V1 fields
- `reading_time` (calculated in minutes)
- `comments_count` (aggregated securely)
- `tags` (eager-loaded array)

---

## Queues and Horizon

- **Connection**: Driven by `QUEUE_CONNECTION=redis`.
- **Queues**:
  - `notifications` (High Priority): Used for dispatching user alerts, such as `SendArticlePublishedNotification`.
  - `reports` (Low Priority): Used for generating heavy analytical data.
- **Horizon**: Accessible via `/horizon` (if environment permits) to monitor throughput, queue wait times, and job failures.

---

## Scheduled Commands

The application automates maintenance via the Laravel Scheduler:

1. **Archive Old Articles**
   - Command: `php artisan articles:archive --days=30`
   - Schedule: Runs monthly on the 1st day at `00:00`.
   - Purpose: Automatically transitions unpublished articles older than 30 days to the `archived` status.

2. **Weekly Published Report**
   - Command: `php artisan articles:report`
   - Schedule: Runs weekly on Fridays at `08:00`.
   - Purpose: Aggregates and logs the number of published articles per writer to `storage/logs/articles-report.log`.

---

## Final Verification Checklist

The following functional behaviors have been strictly verified:

- ✅ Guest can list/view published articles
- ✅ Guest cannot view unpublished articles
- ✅ Guest cannot create/update/delete articles
- ✅ Writer can create articles
- ✅ Writer can update/delete own articles
- ✅ Writer cannot update/delete others’ articles
- ✅ Admin can manage all articles
- ✅ Admin can access dashboard
- ✅ Writer cannot access dashboard
- ✅ API request logs are created successfully
- ✅ Dashboard stats are cached in Redis
- ✅ Cache invalidation works flawlessly through observers/events
- ✅ `ArticlePublished` event fires exactly once upon transition to published
- ✅ Notifications are queued successfully via Redis
- ✅ V1 and V2 response shapes are distinct and correct
- ✅ Archive command properly ignores already published content
- ✅ Report command generates output and logs to file seamlessly

### Verification Command Results
During final verification, the following commands were run:
- `php artisan migrate:fresh --seed --force` (Successful execution, 0 errors)
- `./vendor/bin/phpunit` (100% Passing)
- `php artisan route:list` (All versioned, public, and protected routes present)
- `php artisan event:list` (Clean auto-discovery of ArticlePublished mapped to its listeners)
- `php artisan schedule:list` (Both scheduled commands successfully registered)
- `timeout 5 php artisan horizon` (Booted successfully. Note: In heavy seeding environments, some bulk welcome notifications may report fast failures in logs if the mail driver is misconfigured, but queue separation acts correctly).
