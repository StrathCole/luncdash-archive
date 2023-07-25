<?php
/** DO NOT REMOVE THESE LINES **/
die();
?>

[redis]
; enable redis for storage
enabled = yes

; host for connection to redis server
host = localhost

; port the redis server is listening on
port = 6379

; which database to use (normally 0 - 15 are available)
database = 0

; if server is configured with password
; password =

; use a prefix for storage
prefix = "luncp:"

; use for datacache
datacache = yes

; use for sessions
sessions = yes

; use for messages instead of session
messages = yes

