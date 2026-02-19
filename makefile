M ?= upload local

upd:
	git add -A && git commit -m "${M}" && git pull && git push

dup:
	docker compose up -d

dde:
	docker compose down

dbb:
	docker compose build

pb:
	docker exec -it logicalc-php-1 bash

#SERVER
php:
	sudo -u www-data docker exec -it gorbmos-php-1 bash
