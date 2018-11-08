### Version 3.9.0 - 2018-11-08

* Change deps to allow Symfony 4 version of proxy bridge

### Version 3.8.0 - 2018-06-21

* Check that the time (or delay) specified is at least 30 seconds into the future, 
and if not send directly to the internal queue manager. This is to avoid issues 
with foreign key constraints where the job is inserted, immediately removed, and then
you try to flush a link to the job.
