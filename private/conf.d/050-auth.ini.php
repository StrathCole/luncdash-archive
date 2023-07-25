<?php
/** DO NOT REMOVE THESE LINES **/
die();
?>

[sessions]
; enable sessions
enabled = no

; only use cookies for session ids, don't put it into url
cookie_only = yes

; life time of session in seconds
lifetime = 5400

; if yes use session storage for messages to persist through url calls
use_for_messages = yes

; check session against user ip, can be either no, yes or "partly"
; partly means checking only the first octets instead of full ip address
check_ip_address = partly

[auth]
; is authentication enabled (login / user and group rights)
enabled = yes

; login method EMAIL or USERNAME or BOTH
login_by = USERNAME
