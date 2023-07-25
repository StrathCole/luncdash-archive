<?php
/** DO NOT REMOVE THESE LINES **/
die();
?>

[global]
; path to include all .ini.php files from
include = "conf.d"

[site]
; unique hash for this installation, used for some (mostly deprecated)
; hashing functions
unique_hash = "xxxxxxxxxxxxxxxxxxxxxxx"


[rewrite]
; enable url rewriting
enabled = yes

; enable path mode, i. e. use /info/faq.html instead of /info.faq.html
path_mode = yes

; specifies if .html shall be added to urls
html_extension = yes


[logging]
; log priority (DEBUG, INFO, WARN, ERROR)
priority = INFO

; file name for log
logfile = "{WORKING_DIR}/app.log"


[cache]
; caching of templates
enabled = yes


[paths]
; template base path
template_path = "{WORKING_DIR}/tpl"

; web base url (defaults to /, only needed when used in subdirectory)
web_url = "/"

; template base url (defaults to {WEB_URL}/tpl) this has to match the setting
; "template_path" but has to be accessible from the web
; This url will be available through {THEME_PATH} variable in templates.
; Example:
; Document root of your website is in /var/www/myproject/web/
; Application is in /var/www/myproject/private/ (not accessible from the web)
; Template files are in /var/www/myproject/private/mytpl/
; Assets are in /var/www/myproject/web/assets/
; the settings for this would be:
; template_path = "/var/www/myproject/private/mytpl/"
; web_url = "/"
; theme_url = "/assets"
theme_url = "/assets"


[libraries]
; this section defines which libraries/modules shall be loaded on each call
; that means they must define a static method "init" and the full namespace
; must be provided here.
; Example:
; load = "YourProject\Hints\Helper,ForeignNamespace\Worker\Something"
; the class "Helper" would then be placed in the file
; app/yourproject/hints/Helper.inc.php
; whereas the class not in your project's namespace would go to
; app/foreignnamespace/worker/Something.inc.php
; The class "Helper" would look like this:
; namespace YourProject\Hints;
; class Helper {
;     ... some code
;     public static function init() {
;         // your code
;     }
;     ... further code
; }

; libraries to always load in early stage. Not all features may be available
; at this point of time
load_early =

; libraries to always load
load =

; libraries to load on CLI call
cli =

; libraries to load on WEB call
web =
