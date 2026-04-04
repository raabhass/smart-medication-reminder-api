# Smart Medication Reminder — Laravel API Backend Handoff

This document is designed to be given directly to Claude Code so it can generate a Laravel API backend for the existing React frontend.

---
## 1) Project context

The existing repository is a frontend-only React prototype for a **Smart Medication Reminder** web application.

### Current frontend screens / flows
1. **Login**
   - Basic login form
   - No real authentication request yet
   - Clicking login currently just moves the page state to dashboard

2. **Dashboard**
   - Summary cards:
     - Total Patients
     - Missed Doses
     - Active Alerts
     - Upcoming Refills
   - Recent alerts table

3. **Patients**
   - Patient management table
   - Existing UI columns:
     - Name
     - Age
     - Role
     - Status
   - “Add Patient” button exists in UI

4. **Medication Scheduling**
   - Form fields visible in UI:
     - Patient Name
     - Medication Name
     - Dosage
     - Frequency
     - Time
     - Instructions
   - “Save Schedule” button exists

5. **Alerts**
   - Table columns:
     - Patient
     - Alert Type
     - Time

6. **Medication History**
   - Table columns:
     - Patient
     - Medication
     - Time
     - Status

### Important implementation reality
The current frontend uses:
- local React state
- hardcoded arrays for patients, alerts, and history
- no real routing
- no API client logic
- no real form submission logic
- no persistence

So this backend should treat the current React app as a **UI prototype** that now needs:
- authentication
- persistent storage
- CRUD endpoints
- dashboard aggregation
- medication schedule management
- alert generation / retrieval
- medication history retrieval
- dose event recording

---
## 2) Recommended backend stack for Laravel

Use:
- **Laravel API project**
- **MySQL** (or PostgreSQL if preferred locally; schema below works for either)
- **Laravel Sanctum** for API authentication
- **Eloquent ORM**
- **Form Request validation**
- **API Resources** for JSON response shaping
- **Seeder + Factory** support for demo data
- **REST-style JSON API**

### Suggested Laravel folders / architecture
- app/Models
- app/Http/Controllers/Api
- app/Http/Requests
- app/Http/Resources
- routes/api.php
- database/migrations
- database/seeders

### Suggested main controllers
- AuthController
- DashboardController
- PatientController
- MedicationScheduleController
- DoseEventController
- AlertController

---
## 3) Functional scope to implement first

Implement the smallest backend that makes the current React UI truly functional.

### Minimum functional features
1. User can log in
2. Frontend can fetch current user
3. Frontend can fetch dashboard summary
4. Frontend can list patients
5. Frontend can view a single patient
6. Frontend can create a medication schedule for a patient
7. Frontend can fetch medication schedules for a patient
8. Frontend can record a dose event
9. Frontend can fetch medication history
10. Frontend can fetch alerts

### Nice-to-have immediately after minimum
1. Create patient
2. Update patient
3. Update medication schedule
4. Acknowledge alert
5. Filter history by date/status
6. Search patients

---
## 4) Minimum API list

Base prefix:
- `/api`

Authentication should use Sanctum bearer token auth after login.

### Auth
#### POST `/api/auth/login`
Authenticate user and return token.

**Request**
```json
{
  "email": "caregiver@example.com",
  "password": "secret123"
}
```

**Response**
```json
{
  "message": "Login successful",
  "token": "plain-text-token-or-sanctum-token",
  "user": {
    "id": 1,
    "name": "Primary Caregiver",
    "email": "caregiver@example.com",
    "role": "caregiver"
  }
}
```

#### GET `/api/auth/me`
Return current authenticated user.

**Response**
```json
{
  "id": 1,
  "name": "Primary Caregiver",
  "email": "caregiver@example.com",
  "role": "caregiver"
}
```

#### POST `/api/auth/logout`
Revoke current token.

**Response**
```json
{
  "message": "Logged out successfully"
}
```

---

### Dashboard
#### GET `/api/dashboard/summary`
Return aggregated summary for current UI dashboard.

**Response**
```json
{
  "total_patients": 12,
  "missed_doses_today": 3,
  "active_alerts": 5,
  "upcoming_refills": 2,
  "recent_alerts": [
    {
      "id": 10,
      "patient_id": 3,
      "patient_name": "Mary Smith",
      "type": "missed_dose",
      "message": "Missed morning dose",
      "alert_time": "2026-03-28 09:00:00",
      "is_acknowledged": false
    }
  ]
}
```

---

### Patients
#### GET `/api/patients`
Return patient list.

**Optional query params**
- `search`
- `status`
- `page`
- `per_page`

**Response**
```json
{
  "data": [
    {
      "id": 1,
      "full_name": "John Doe",
      "age": 78,
      "role_label": "Patient",
      "status": "stable"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

#### GET `/api/patients/{id}`
Return one patient with basic details.

**Response**
```json
{
  "id": 1,
  "full_name": "John Doe",
  "age": 78,
  "gender": null,
  "status": "stable",
  "notes": null,
  "created_by_user_id": 1,
  "created_at": "2026-03-28T10:00:00Z",
  "updated_at": "2026-03-28T10:00:00Z"
}
```

#### POST `/api/patients`
Create patient.

**Request**
```json
{
  "full_name": "John Doe",
  "age": 78,
  "gender": "male",
  "status": "stable",
  "notes": "Needs morning reminder"
}
```

#### PATCH `/api/patients/{id}`
Update patient.

#### DELETE `/api/patients/{id}`
Soft-delete patient or hard delete depending on preference.
Recommended: soft delete.

---

### Medication schedules
#### GET `/api/patients/{id}/medication-schedules`
List schedules for one patient.

**Response**
```json
{
  "data": [
    {
      "id": 1,
      "patient_id": 1,
      "medication_name": "Aspirin",
      "dosage": "75 mg",
      "frequency": "daily",
      "scheduled_time": "08:00:00",
      "instructions": "After breakfast",
      "start_date": "2026-03-28",
      "end_date": null,
      "is_active": true
    }
  ]
}
```

#### POST `/api/patients/{id}/medication-schedules`
Create a schedule for a patient.

**Request**
```json
{
  "medication_name": "Aspirin",
  "dosage": "75 mg",
  "frequency": "daily",
  "scheduled_time": "08:00:00",
  "instructions": "After breakfast",
  "start_date": "2026-03-28",
  "end_date": null,
  "is_active": true
}
```

#### PATCH `/api/medication-schedules/{id}`
Update schedule.

#### DELETE `/api/medication-schedules/{id}`
Delete or deactivate schedule.
Recommended approach: set `is_active = false` rather than hard delete.

---

### Dose events / medication history
#### POST `/api/dose-events`
Record a dose event. This drives the medication history table.

**Request**
```json
{
  "patient_id": 1,
  "medication_schedule_id": 1,
  "status": "taken",
  "event_time": "2026-03-28 08:02:00",
  "notes": "Taken with breakfast"
}
```

**Response**
```json
{
  "id": 100,
  "patient_id": 1,
  "medication_schedule_id": 1,
  "status": "taken",
  "event_time": "2026-03-28 08:02:00",
  "notes": "Taken with breakfast"
}
```

#### GET `/api/medication-history`
List medication history for table view.

**Optional query params**
- `patient_id`
- `status`
- `date_from`
- `date_to`
- `page`
- `per_page`

**Response**
```json
{
  "data": [
    {
      "id": 100,
      "patient_id": 1,
      "patient_name": "John Doe",
      "medication_schedule_id": 1,
      "medication_name": "Aspirin",
      "event_time": "2026-03-28 08:02:00",
      "status": "taken"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

### Alerts
#### GET `/api/alerts`
Return alert list.

**Optional query params**
- `patient_id`
- `type`
- `is_acknowledged`
- `page`
- `per_page`

**Response**
```json
{
  "data": [
    {
      "id": 10,
      "patient_id": 2,
      "patient_name": "Mary Smith",
      "type": "missed_dose",
      "message": "Missed morning dose",
      "alert_time": "2026-03-28 09:00:00",
      "is_acknowledged": false
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

#### PATCH `/api/alerts/{id}/acknowledge`
Mark alert as acknowledged.

**Response**
```json
{
  "message": "Alert acknowledged"
}
```

---
## 5) Database design

This schema is based on the minimum API list and the existing React screens.

### Core entities
1. users
2. patients
3. medication_schedules
4. dose_events
5. alerts

### Entity relationship summary
- One **user** creates many **patients**
- One **patient** has many **medication schedules**
- One **medication schedule** has many **dose events**
- One **patient** has many **alerts**
- One **patient** has many **dose events** (directly useful for reporting)
- Optional: one **user** can acknowledge many alerts

---

## 6) Tables and columns

### 6.1 users
Purpose:
- Caregiver/admin accounts who log into the system

Suggested columns:
- `id` bigint primary key
- `name` string
- `email` string unique
- `password` string
- `role` string default `caregiver`
- `created_at`
- `updated_at`

Laravel notes:
- Use default `users` table migration and add `role`

Example roles:
- `caregiver`
- `admin`

---

### 6.2 patients
Purpose:
- Stores patient records shown in patient table and referenced by schedules, alerts, and dose history

Suggested columns:
- `id` bigint primary key
- `created_by_user_id` foreign key -> users.id
- `full_name` string
- `age` unsigned integer
- `gender` string nullable
- `status` string default `stable`
- `notes` text nullable
- `deleted_at` nullable soft delete
- `created_at`
- `updated_at`

Recommended status values:
- `stable`
- `needs_attention`

Important note:
- The React UI shows a “Role” column, but every record there is effectively a patient.
- Do **not** store a separate patient role column unless needed for future UX.
- The frontend can simply display `"Patient"` as a static label.

Indexes:
- index on `created_by_user_id`
- index on `status`
- index on `full_name`

---

### 6.3 medication_schedules
Purpose:
- Stores recurring or active medication instructions per patient

Suggested columns:
- `id` bigint primary key
- `patient_id` foreign key -> patients.id
- `medication_name` string
- `dosage` string
- `frequency` string
- `scheduled_time` time
- `instructions` text nullable
- `start_date` date
- `end_date` date nullable
- `is_active` boolean default true
- `created_at`
- `updated_at`

Recommended frequency values:
- `daily`
- `twice_daily`
- `weekly`
- `custom`

Notes:
- UI currently has a single “Time” input, so start with one `scheduled_time`
- If the product later needs multiple times per day, create a separate `medication_schedule_times` table later
- For current frontend, one time field is enough

Indexes:
- index on `patient_id`
- index on `is_active`
- compound index on (`patient_id`, `is_active`)

---

### 6.4 dose_events
Purpose:
- Stores actual medication events such as taken or missed
- This table powers medication history and dashboard missed-dose counts

Suggested columns:
- `id` bigint primary key
- `patient_id` foreign key -> patients.id
- `medication_schedule_id` foreign key -> medication_schedules.id
- `status` string
- `event_time` datetime
- `notes` text nullable
- `created_at`
- `updated_at`

Recommended status values:
- `taken`
- `missed`
- `skipped`

Indexes:
- index on `patient_id`
- index on `medication_schedule_id`
- index on `status`
- index on `event_time`

Important design decision:
- Keep both `patient_id` and `medication_schedule_id` on this table even though patient can be inferred from schedule
- This makes dashboard and reporting queries simpler and faster

---

### 6.5 alerts
Purpose:
- Stores generated or manual alerts shown in alerts table and dashboard recent alerts

Suggested columns:
- `id` bigint primary key
- `patient_id` foreign key -> patients.id
- `type` string
- `message` text
- `alert_time` datetime
- `is_acknowledged` boolean default false
- `acknowledged_by_user_id` foreign key nullable -> users.id
- `acknowledged_at` datetime nullable
- `created_at`
- `updated_at`

Recommended type values:
- `missed_dose`
- `refill_due`
- `needs_attention`

Indexes:
- index on `patient_id`
- index on `type`
- index on `is_acknowledged`
- index on `alert_time`

---

## 7) Relationship definitions for Eloquent

### User model
- hasMany(Patient::class, 'created_by_user_id')
- hasMany(Alert::class, 'acknowledged_by_user_id')

### Patient model
- belongsTo(User::class, 'created_by_user_id')
- hasMany(MedicationSchedule::class)
- hasMany(DoseEvent::class)
- hasMany(Alert::class)

### MedicationSchedule model
- belongsTo(Patient::class)
- hasMany(DoseEvent::class)

### DoseEvent model
- belongsTo(Patient::class)
- belongsTo(MedicationSchedule::class)

### Alert model
- belongsTo(Patient::class)
- belongsTo(User::class, 'acknowledged_by_user_id')

---

## 8) Suggested migration structure

Order:
1. create_users_table
2. create_patients_table
3. create_medication_schedules_table
4. create_dose_events_table
5. create_alerts_table

### Migration notes
- Use foreign keys with constrained deletes where appropriate
- Patients should probably use soft deletes
- If patient is soft-deleted, related schedules/history should remain for audit unless product requirements say otherwise
- Prefer `cascadeOnDelete()` only where safe
- For medical history, preserving records is often better than deleting

Recommended delete rules:
- deleting user: restrict or preserve patients
- deleting patient: ideally soft delete only
- deleting schedule: do not delete dose events automatically if history matters

---

## 9) Example migration blueprint

### patients
```php
Schema::create('patients', function (Blueprint $table) {
    $table->id();
    $table->foreignId('created_by_user_id')->constrained('users');
    $table->string('full_name');
    $table->unsignedInteger('age');
    $table->string('gender')->nullable();
    $table->string('status')->default('stable');
    $table->text('notes')->nullable();
    $table->softDeletes();
    $table->timestamps();

    $table->index('status');
    $table->index('full_name');
});
```

### medication_schedules
```php
Schema::create('medication_schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')->constrained('patients');
    $table->string('medication_name');
    $table->string('dosage');
    $table->string('frequency');
    $table->time('scheduled_time');
    $table->text('instructions')->nullable();
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['patient_id', 'is_active']);
});
```

### dose_events
```php
Schema::create('dose_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')->constrained('patients');
    $table->foreignId('medication_schedule_id')->constrained('medication_schedules');
    $table->string('status');
    $table->dateTime('event_time');
    $table->text('notes')->nullable();
    $table->timestamps();

    $table->index('status');
    $table->index('event_time');
});
```

### alerts
```php
Schema::create('alerts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')->constrained('patients');
    $table->string('type');
    $table->text('message');
    $table->dateTime('alert_time');
    $table->boolean('is_acknowledged')->default(false);
    $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users');
    $table->dateTime('acknowledged_at')->nullable();
    $table->timestamps();

    $table->index('type');
    $table->index('is_acknowledged');
    $table->index('alert_time');
});
```

---

## 10) Validation rules to implement

### Login
- email: required, email
- password: required, string

### Create patient
- full_name: required, string, max 255
- age: required, integer, min 0, max 130
- gender: nullable, in male,female,other
- status: required, in stable,needs_attention
- notes: nullable, string

### Create medication schedule
- medication_name: required, string, max 255
- dosage: required, string, max 100
- frequency: required, string, max 50
- scheduled_time: required, date_format:H:i:s or H:i
- instructions: nullable, string
- start_date: required, date
- end_date: nullable, date, after_or_equal:start_date
- is_active: boolean

### Create dose event
- patient_id: required, exists:patients,id
- medication_schedule_id: required, exists:medication_schedules,id
- status: required, in taken,missed,skipped
- event_time: required, date
- notes: nullable, string

### Acknowledge alert
- ensure alert exists
- ensure authenticated user exists
- set `is_acknowledged = true`
- set `acknowledged_by_user_id`
- set `acknowledged_at = now()`

---

## 11) Business logic notes

### Dashboard summary logic
`GET /api/dashboard/summary` should calculate:
- `total_patients` = count of patients not soft deleted
- `missed_doses_today` = count of dose_events where status = missed and event_time is today
- `active_alerts` = count of alerts where is_acknowledged = false
- `upcoming_refills` = for now, can be placeholder count or derived later if refill tracking is added
- `recent_alerts` = latest alerts ordered by alert_time desc limit maybe 5

### Medication history logic
History table in frontend is basically `dose_events` joined with:
- patients
- medication_schedules

So return:
- patient_name
- medication_name
- event_time
- status

### Alerts logic
At minimum:
- alerts can be seeded or inserted manually
- later automation can create alerts from missed dose logic
- current frontend only needs to display them

### Refill due logic
The current React UI displays “refill due” in alerts, but there is no explicit refill model in current UI.
For minimum version:
- allow alert type `refill_due`
- no full refill tracking table needed yet

---
## 12) Seeder plan

Create seeders for:
1. one caregiver user
2. 3 patients matching existing UI feel
3. 3 medication schedules
4. 3 dose events
5. 2 alerts

### Example seed data
#### user
- name: Primary Caregiver
- email: caregiver@example.com
- password: password

#### patients
- John Doe, 78, stable
- Mary Smith, 82, needs_attention
- Robert Brown, 75, stable

#### schedules
- John Doe -> Aspirin -> 8:00 AM
- Mary Smith -> Metformin -> 9:00 AM
- Robert Brown -> Vitamin D -> 1:00 PM

#### dose events
- John Doe -> Aspirin -> taken
- Mary Smith -> Metformin -> missed
- Robert Brown -> Vitamin D -> taken

#### alerts
- Mary Smith -> missed_dose
- John Doe -> refill_due

---
## 13) Suggested API route file sketch

```php
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DoseEventController;
use App\Http\Controllers\Api\MedicationScheduleController;
use App\Http\Controllers\Api\PatientController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
    Route::patch('/patients/{patient}', [PatientController::class, 'update']);
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);

    Route::get('/patients/{patient}/medication-schedules', [MedicationScheduleController::class, 'indexByPatient']);
    Route::post('/patients/{patient}/medication-schedules', [MedicationScheduleController::class, 'store']);
    Route::patch('/medication-schedules/{medicationSchedule}', [MedicationScheduleController::class, 'update']);
    Route::delete('/medication-schedules/{medicationSchedule}', [MedicationScheduleController::class, 'destroy']);

    Route::post('/dose-events', [DoseEventController::class, 'store']);
    Route::get('/medication-history', [DoseEventController::class, 'history']);

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::patch('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
});
```

---
## 14) Suggested controller responsibilities

### AuthController
Methods:
- login
- me
- logout

### DashboardController
Methods:
- summary

### PatientController
Methods:
- index
- store
- show
- update
- destroy

### MedicationScheduleController
Methods:
- indexByPatient
- store
- update
- destroy

### DoseEventController
Methods:
- store
- history

### AlertController
Methods:
- index
- acknowledge

---
## 15) Suggested API Resources

Create:
- UserResource
- PatientResource
- MedicationScheduleResource
- DoseEventHistoryResource
- AlertResource
- DashboardSummaryResource

This will keep the frontend JSON consistent and make Claude Code generate cleaner Laravel code.

---
## 16) Suggested frontend integration mapping

### Login screen
Replace fake login action with:
- POST `/api/auth/login`
- save token in frontend
- use token for subsequent requests

### Dashboard screen
Use:
- GET `/api/dashboard/summary`

### Patients screen
Use:
- GET `/api/patients`

### Add Patient flow
Use:
- POST `/api/patients`

### Medication form
Use:
- POST `/api/patients/{id}/medication-schedules`

### Alerts table
Use:
- GET `/api/alerts`

### History table
Use:
- GET `/api/medication-history`

---
## 17) Immediate assumptions Claude Code should follow

1. Build a **Laravel API only**, not Blade views
2. Use **Sanctum token auth**
3. Build migrations, models, controllers, form requests, seeders, and API resources
4. Return JSON only
5. Use pagination where list endpoints exist
6. Use soft deletes for patients
7. Keep code simple and minimum-first
8. Keep schema open for later extension
9. Use REST naming exactly as listed above unless there is a strong Laravel reason to slightly improve it
10. Do not over-engineer refill tracking yet

---
## 18) Future extension ideas (do not block MVP)
These should NOT be required for first pass, but schema should allow them later:
- reminder notifications via email/SMS/push
- refill inventory tracking
- multiple medication times per day
- caregiver notes timeline
- patient contact info and emergency contact
- audit log
- role permissions
- recurring alert generation jobs
- report export

---
## 19) Final instruction for Claude Code

Generate a Laravel API backend for this React medication reminder prototype with the following priorities:

1. working auth
2. migrations and seeders
3. patient CRUD
4. medication scheduling
5. dose event recording
6. medication history endpoint
7. alerts endpoint
8. dashboard summary endpoint

Use clean Laravel conventions, JSON API responses, validation, and seed data that matches the existing frontend demo content.
