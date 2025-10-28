inspector:
	open http://localhost:5173
	npx -y @modelcontextprotocol/inspector

start:
	symfony server:start -d --no-tls

log:
	symfony server:log

stop:
	symfony server:stop

restart: stop start log

ci: codestyle phpstan test

codestyle:
	symfony php vendor/bin/php-cs-fixer fix

phpstan:
	symfony php vendor/bin/phpstan analyse

test:
	symfony php bin/phpunit
