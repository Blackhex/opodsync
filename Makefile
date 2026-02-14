.PHONY : server

server:
	php -S 0.0.0.0:8080 -t server server/index.php
