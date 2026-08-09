# V1 to V2 Migration Status

**Update 2026-08-09**: V1 routes/controllers/services/models have been removed
(see #22). The table below is kept for historical reference on what did/didn't
have a V2 equivalent at removal time. The 6 "Not migrated" gaps that had no V2
equivalent are tracked as #37–#42. `IFSPSOAssistService`, `IFSService`,
`InputReference`, and the `PsoEnvironment` model/table are still present —
they back the scheduled rota-to-DSE cron task in `routes/console.php`, whose
fate is still undecided.

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

## Remaining V1 Dependencies (as of 2026-08-09)

`app/Traits/PSOAssist.php` and `app/Jobs/BookAppointments.php` (V1 load-test
job) were both removed — dead/replaced respectively (see #36). One dependency
remains:

- **`routes/console.php`** — the scheduled rota-to-DSE task instantiates
  `App\Services\V1\IFSPSOAssistService` directly, every 5 minutes. This is
  the sole reason `IFSPSOAssistService`, its parent `IFSService`,
  `App\Classes\V1\InputReference`, and the `PsoEnvironment` model/table are
  still present. Deciding this task's fate (keep as-is, port to V2, or drop)
  is what's left before V1 can be fully deleted.

## Summary

V1 controllers/routes/most-services/models have been removed (#22). Six
features had no V2 equivalent at removal time and are now gone, tracked as
follow-ups:

1. Activity SLA Delete (`DELETE /activity/{id}/sla`) — #37
2. Resource Create (`POST /resource`) — #38
3. Resource Relocate (`POST /resource/{id}/relocate`) — #39
4. Unavailability Delete (`DELETE /unavailability/{id}`) — #40
5. Region (`POST /region`) — #41
6. Cleanup (`DELETE /cleanup`) — #42

Activity Create and Commit were already migrated to V2 by the time of
removal (this doc just hadn't been updated).

Remaining before V1 is 100% gone: decide the scheduled rota-to-DSE task's
fate (see above).
