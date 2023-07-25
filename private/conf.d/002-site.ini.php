<?php
/** DO NOT REMOVE THESE LINES **/
die();
?>

[site]
; notifications will go to this email address
admin_email = "none@none.com"

; this is the main host name of your website
hostname = "luncdash.com"

; current app version
version = "1.0.0"

; if set to yes redirect to main hostname will occur if a different one is called
enforce_hostname = no

; timezone for your site
timezone = "UTC"

; enable maintenance mode
maintenance = no

; enable development mode
environment = live

; the namespace that is used for all your project libs and modules
; all files for classes in that namespace are searched under app
; e. g. YourProject\Modules\TestModule will be in file
; app/yourproject/modules/TestModule.inc.php
; path names are always lowercase, class names are case-sensitive
namespace = LUNCDash

[cookies]
; host name for cookies, e. g. ".mywebsite.com" sets cookie that is valid for
; all subdomains of mywebsite.com whereas "mywebsite.com" does not.
; setting to empty will use HTTP_HOST as cookie domain
hostname = ".luncdash.com"

