<?php
/** DO NOT REMOVE THESE LINES **/
die();
?>


[smtp]
; smtp server to connect to, defaults to localhost
host = "mx.mailserver.com"

; port for smtp connection, e. g. 587 for submission or 465 for SSL, default is 587
port = 25

; username for authentication on SMTP
username = "not@enabled.com"

; password for authentication on SMTP
password = "xxxxxxxxxxx"

; secure connection mode, can be either "off", "tls" or "ssl"
; defaults to tls, be sure to match this setting with the port setting
security = off

; helo name for SMTP connections, should be the server name of the server that
; you will send the mails from. If empty it will be auto-detected.
helo = "lunc.notenabled.com"

; maximum email count to send during a single session. If more emails shall be
; sent the connection will be closed and re-opened
session_max_emails = 20


[smime]
; sign emails with SMIME certificate
enabled = no

; SMIME certificate file
; cert_file = /path/to/certfile.cert

; SMIME certificate bundle/chain file
; bundle_file = /path/to/certfile.bundle.cert

; SMIME certificate key
; key_file = /path/to/certfile.key

; SMIME certificate key password
; key_password =

; allow fallback to sending unsigned email
; allow_fallback = no

