dev:
	docker compose -p starters-dev up --build -d
	docker image prune -af

staging:
	@export APP_ENV=staging && \
	export DB_USER=staginguser && \
	export DB_PASSWORD=stagingpass && \
	export DB_NAME=starters_staging && \
	export PORT=5433 && \
	export WS_PORT=9092 && \
	docker compose -p starters-staging up --build -d
	docker image prune -af

prod:
	@export APP_ENV=prod && \
	export DB_USER=produser && \
	export DB_PASSWORD=prodpass && \
	export DB_NAME=starters_prod && \
	export PORT=5434 && \
	export WS_PORT=9093 && \
	docker compose -p starters-prod up --build -d
	docker image prune -af

stop:
	@if [ "$(v)" = "v" ]; then \
		docker compose down -v; \
	else \
		docker compose down; \
	fi

