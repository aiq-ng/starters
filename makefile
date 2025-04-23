dev:
	docker compose --env-file profiles/dev/.env.app -p starters-dev up --build -d

staging:
	docker compose --env-file profiles/staging/.env.app -p starters-staging up --build -d

prod:
	docker compose --env-file profiles/prod/.env.app -p starters-prod up --build -d

stop:
	@if [ -z "$(env)" ]; then \
		echo "❌ Please pass env=dev|staging|prod to stop the correct environment"; \
		exit 1; \
	fi
	@if [ "$(v)" = "v" ]; then \
		docker compose -p starters-$(env) down -v; \
	else \
		docker compose -p starters-$(env) down; \
	fi

