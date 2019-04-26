# symfony4-user

http://localhost:83/



Cron job path http://localhost:83/cronupdate set on server that run on every minute intervel to check post pending status. If the post is not appoved by admin within 5 minutes then it reject the post and send email to user.

Admin login details
malwinder.d4d@gmail.com
password : Admin@123
 
User login
malwinder.d5d@gmail.com
Pass: Admin@123
 
Api List
Login api POST
http://localhost:83/api/login
email=abc@example.com
password=abc

Create Content POST (Authorization)
http://localhost:83/api/new
title = abc
description =  testdesc
content = test content
email = abc@example.com

List all content on admin side GET (Authorization)
http://localhost:83/api/listall

Approve Content on admin side GET (Authorization)
http://localhost:83/api/approve/{id}

Show individual Content on admin side GET (Authorization)
http://localhost:83/api/show/{id}

List approvded content GET 
http://localhost:83/api/list

 
 
Run  these migration to setup database on mysql

$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate

