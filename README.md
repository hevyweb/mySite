# About me
My name is Dmytro Dzyuba. I'm a PHP developer since 2009. Almost all my carrier I've been working with symfony framework starting from version 1. I did a lot of verion migrations so I know about all key features of symfony. 
I decided to start this project and got it live to http://dmytrodzyuba.com. The main goal was to show my expertise in symfony. All my previous works are under NDA so I'm not able to post any thing.
The second goal is to get more and more knowledge of symfony's feature that are not used often. From my experience if some feature has been already set on the project and no need to update it, that breengs a black hole
in knowledge.

# About the project
I started this project approximately in August 2023. I has docker configs so you can easily download and install it locally. Few quick steps and you're done:
1. Clone the project to your local folder
```
git clone https://github.com/hevyweb/mySite.git .
```
2. Copy configs
```
cp ./.env.dist ./.env
```
3. Builde the project
```
docker-compose build
```
4. Boot containers
```
docker-compose up -d
```
5. Get to the main container and install dependencies
```
docker-compose exec fpma bash
composer install
yarn install
```
6. Build the front end
```
yarn encore dev
```
7. Run DB migrations to create MySQL tables (inside fpm container)
```
php bin/console doctrine:migrations:migrate
```

Optionally you can create an admin user by running a command:
```
php bin/console app:generate:user -u admin -p admin
```

# FAQ
If you have any questions or commens please send them via this contact form http://www.dmytrodzyuba.com/contact_me
