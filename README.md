
# Automated DpIP

A simple Laravel application to fetch Dp IPs data and insert into database.


Implemented Features:
 - Jobs
 - Schedules


## Project Structure

For building this app, I used two model `IP` and `SyncStatus`.

 - `IP` store the IPs we fetch from the DPIP database.
 - `SyncStatus` store the status and details of sync process.

When the process start, each step store in the `SyncStatus` table, so that we can monitor the process status. For scheduling I used [Laravel Task Scheduling](https://laravel.com/docs/7.x/scheduling) .
