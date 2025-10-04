up:
	docker compose --env-file=./backend/.env --env-file=.env up -d
down:
	docker compose --env-file=./backend/.env --env-file=.env down
seed: 
	docker exec -it task-master-app php artisan db:seed
test: 
	docker exec -it task-master-app php artisan test
	
fix-log: 
	docker exec -it task-master-app chown -R www-data:www-data storage
