# symfony4-user

http://localhost:83/



Cron job path http://localhost:83/cronupdate set on server that run on every minute intervel to check post pending status. If the post is not appoved by admin within 5 minutes then it reject the post and send email to user.

Admin login details
malwinder.d4d@gmail.com
password : Admin@123
 
Run  these migration to setup database on mysql

$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:migrate

