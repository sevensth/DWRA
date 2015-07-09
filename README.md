DWRA: DreamingWish.com Random Avatar service

DWRA is a simple random avatar service based on wp_identicon using PHP server script.

Usage:

Nginx:

In your server secion, add this line:

rewrite ^(.*)$ /index.php?$query_string last;

Note directories 'identicon' and 'static' are cache directories, make sure PHP has read/write permision to them.
