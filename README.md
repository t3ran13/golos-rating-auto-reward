### Example of App based on t3ran13/golos-php-event-listener PHP event listener


## RedisManager

DB structure LIB:
- DB0
    - app:processes:last_id
    - app:processes:{id}:last_update_datetime
    - app:processes:{id}:status
    - app:processes:{id}:mode
    - app:processes:{id}:pid
    - app:processes:{id}:handler
    - app:processes:{id}:data:last_block
    
    - app:listeners:last_id
    - app:listeners:{id}:last_update_datetime
    - app:listeners:{id}:status
    - app:listeners:{id}:mode
    - app:listeners:{id}:pid
    - app:listeners:{id}:handler
    - app:listeners:{id}:data:last_block
    - app:listeners:{id}:conditions:{n}:key
    - app:listeners:{id}:conditions:{n}:value
    
    - app:events:{listener_id}:{block_n}:{trx_n_in_block}

DB structure MyApp:
- DB0
    - myapp:rewards-post-list //list of json
    - myapp:rewards-users-list //list of json
    - myapp:rewards-users-stop-list //list of json
    
    

### for run docker-compose for cron in win
```bash
$Env:COMPOSE_CONVERT_WINDOWS_PATHS=1
docker-compose down && Docker-compose up -d
```
    
    