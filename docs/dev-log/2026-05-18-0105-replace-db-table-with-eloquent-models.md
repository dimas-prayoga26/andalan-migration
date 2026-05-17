# Replace DB::table with Eloquent Models (Attendance + LeaveRequest)

## Date
2026-05-18

## Changes
- Removed all `DB::table(...)` usages from:
  - `app/Http/Controllers/AttendanceController.php`
  - `app/Http/Controllers/LeaveRequestController.php`
- Replaced table access with Eloquent model queries.

## AttendanceController
- `employee_profiles` lookup now uses `EmployeeProfile` model:
  - staff profile name lookup in `datatable()`
  - bulk profile name map in `datatable()`

## LeaveRequestController
- `leave_types` query now uses `LeaveType` model.
- `meta_data_leave_companies` query now uses `MetaDataLeaveCompany` model.
- `leave_balances` summary query now uses `LeaveBalance` model + relation `leaveType`.
- `employee_deployments` join date lookup now uses `EmployeeDeployment` model.
- `leave_requests` annual usage query now uses `LeaveRequest` model + `whereHas('leaveType')`.
- `employee_profiles` lookups now use `EmployeeProfile` model.

## New Models Added
- `app/Models/LeaveBalance.php`
- `app/Models/MetaDataLeaveCompany.php`

## Validation
- `php -l` passed for updated controllers and new models.
- `vendor/bin/pint --dirty --format agent` passed.
