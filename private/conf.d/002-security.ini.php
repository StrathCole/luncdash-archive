<?php
/** DO NOT REMOVE THESE LINES **/
die();
?>

[security]
; use CSRF for forms
csrf = yes

; hash mode for passwords should be crypt, sha just for legacy compatibility
hash_mode = crypt

; umask for newly created dirs and files, default is 0027 -> 0750
umask = 0027

; chmod for log file, config cache and some other files.
; default should be "secure" which is 0600
; possible values are "secure" (0600), "lax" (0660), "open" (0666)
chmod_rule = lax