# V1 to V2 Migration Status

## Route Coverage

| Feature             | V1 Route                                     | V2 Route                             | Status                                |
|---------------------|----------------------------------------------|--------------------------------------|---------------------------------------|
| Health Check        | -                                            | `POST /health-check`                 | V2 only (new)                         |
| Commit              | `POST /commit`                               | - (stub controller exists, no route) | **Not migrated**                      |
| Travel Analyzer     | `POST /travelanalyzer`                       | `POST /travelanalyzer`               | Migrated                              |
| Travel Broadcast    | `POST /travelanalyzerservice`                | `POST /travelanalyzerservice`        | Migrated                              |
| Travel Show         | -                                            | `GET /travelanalyzer/{id}`           | V2 only (new)                         |
| Activity Status     | `PATCH /activity/{id}/{status}`              | `PATCH /activity/{id}/status`        | Migrated (cleaner URL)                |
| Activity Delete     | `DELETE /activity` + `DELETE /activity/{id}` | `DELETE /activity`                   | Migrated (consolidated)               |
| Activity Create     | `POST /activity`                             | -                                    | **Not migrated**                      |
| Activity SLA Delete | `DELETE /activity/{id}/sla`                  | -                                    | **Not migrated**                      |
| Load                | `POST /load`                                 | `POST /load`                         | Migrated                              |
| Rota to DSE         | `PATCH /rotatodse`                           | `PATCH /rota`                        | Migrated (renamed)                    |
| Usage               | `GET /usage`                                 | `GET /usage`                         | Migrated                              |
| Delete              | `DELETE /delete`                             | `DELETE /delete`                     | Migrated                              |
| Cleanup             | `DELETE /cleanup`                            | -                                    | **Not migrated**                      |
| Appointment (all)   | full CRUD                                    | full CRUD                            | Migrated                              |
| Exception           | `POST /exception`                            | `POST /exception`                    | Migrated                              |
| Resource List/Show  | `GET /resource`                              | `GET /resource`                      | Migrated                              |
| Resource Event      | `POST /resource/{id}/event`                  | `POST /resource/{id}/event`          | Migrated                              |
| Resource Shift      | `PATCH /resource/{id}/shift`                 | `PATCH /resource/{id}/shift`         | Migrated                              |
| Resource Relocate   | `POST /resource/{id}/relocate`               | -                                    | **Not migrated**                      |
| Resource Create     | `POST /resource`                             | -                                    | **Not migrated**                      |
| Unavailability      | `POST`, `PATCH`, `DELETE`                    | `POST`, `PATCH` (no delete)          | **Partially migrated**                |
| Region              | `POST /region`                               | -                                    | **Not migrated**                      |
| Load Test           | `POST /loadtest`                             | -                                    | **Not migrated** (likely intentional) |

## Remaining V1 Dependencies

These files outside V1 controllers/routes still reference V1 services:

- **`app/Console/Kernel.php`** — Scheduled rota task uses `V1\IFSPSOAssistService`
- **`app/Traits/PSOAssist.php`** — `sendRotaToDSE()` uses `V1\IFSPSOAssistService`
- **`app/Jobs/BookAppointments.php`** — Load test job uses `V1\PSOLoadTestService`

These need to be updated to use V2 services (or removed) before V1 can be fully deleted.

## Code Issues

- **Misplaced service class:** `app/Http/Controllers/Api/V2/ScheduleExceptionService.php` is a service sitting inside the Controllers directory. Should be moved to `app/Services/V2/`.
- **V2 CommitController** (`app/Http/Controllers/Api/V2/CommitController.php`) is a stub with design notes but no implementation or route.

## Summary

Most core features are migrated to V2. The remaining gaps to close before V1 can be removed:

1. Activity Create (`POST /activity`)
2. Activity SLA Delete (`DELETE /activity/{id}/sla`)
3. Resource Create (`POST /resource`)
4. Resource Relocate (`POST /resource/{id}/relocate`)
5. Unavailability Delete (`DELETE /unavailability/{id}`)
6. Region (`POST /region`)
7. Cleanup (`DELETE /cleanup`)
8. Commit (needs full implementation, route, and shared encryption flow)
9. Migrate or remove the V1 references in Kernel, PSOAssist trait, and BookAppointments job
10. Move `ScheduleExceptionService.php` out of Controllers into Services
