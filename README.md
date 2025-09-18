# Test Opportunity LWSA

** Momorepo back-end/front-end  

** Laravel 12 ( Breeze + Sanctum ) + Vue.js (  Material Partner V3  )

## Build e Start  - ** Docker CONFIG somente para ambiente Development - otimmizado os dockerfile para macOS**

## Ajustar variaveis de ambiente em .env na pasta raiz principalmente SMTP
*** Caso ache interessante pode alterar algumas configs dos arquivos na pasta .docker/conf.d

## no prompt na pasta raiz executar:

mkdir ./.docker/volumes/mysql && docker compose up -d --build


Aguardar o processamento, depende do PC e internet de 4 `a 15 min.

Acessar ambientes conforme host e porta que definiu no .env

*** não me preocupei com muitos detalhes de front (rs)


## Usuario de admin
###    admin@test.io
###    password
** pode alterar no arquivo de seeder ./back-end/database/seeders/AdminUserSeeder.php


### Para testar envio dos relatorios diarios:
docker compose exec backend bash -lc "php artisan sales:send-daily-summaries"

### para executar os teste back-end: 
docker compose exec backend bash -lc "php artisan test" 

### para executar os teste front-end:

docker compose exec --user node app npm run test:unit --prefix /app
