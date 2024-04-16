# Blog
___
[![Quality check](https://github.com/hevyweb/mySite/actions/workflows/index.yml/badge.svg?branch=main)](https://github.com/hevyweb/mySite/actions/workflows/index.yml)
## About me
___
My name is Dmytro Dzyuba. I live in Ukraine. I'm a PHP developer since 2009. Almost all my carrier I've been working 
with [symfony framework](https://symfony.com/) starting from version 1. I did a lot of version migrations, so I know 
about all key features of symfony. I decided to start this project and got it live to http://dmytrodzyuba.com. 
The main goal was to show my expertise in symfony. All my previous works are under NDA. I'm not able to post anything.
The second goal is to get more and more knowledge of symfony's feature that are not used often. From my experience 
if some feature has been already set on the project and no need to update it, that brings a white spot in knowledge.
## About the project
___
I was fired from my previous company in June 2023. A war in Ukraine made a lot of western companies leave the Ukrainian 
market to avoid risks. The IT market shrunk and degraded. It was a really hard time to find a decent job. I started 
this project approximately i June and first result was in August 2023. Basically this project is a blog, where I can 
post my articles in multi-language. Also, I added a contact form and a page where I can post my career path.
## Installation
___
This project has docker configs. Before installing it you need:
- [Docker](https://www.docker.com/)
- [Git](https://git-scm.com/)

You can easily download and install it locally. A few quick steps and you're done:
1. Clone the project to your local folder
```
git clone https://github.com/hevyweb/mySite.git ./
```
2. Copy default configs. You can update them to use your own settings.
```
cp ./.env.dist ./.env
```
3. Build the project containers. Potentially you can skip this step and proceed to the next one, but it helps you to
debug issues during building the containers. 
```
docker-compose build
```
4. Boot containers. I added `-d` option in the end. But on the first run if you're not confident I recommend to run this
command without `-d`, just to be able to track down some issues, that might occure.
```
docker-compose up -d
```
5. Get to the main container and install dependencies
```
docker-compose exec fpm bash
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
8. Optionally you can create an admin user
```
php bin/console app:generate:user -u admin -p admin
```
## Quality check
___
In this project I added 3 quality check tools:
### PHP-CS-FIXER
[PHP-CS-FIXER](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) is a tool that allows to check weather code style follows
modern standards. Those standards are described in different [PSRs](https://www.php-fig.org/psr/). This tool can 
automatically fix invalid code style too.
To be able to inspect the code I added a command to composer:
```
composer csfixer
```
### Psalm
Since this is a symfony project I added a [Psalm-plugin for symfony](https://github.com/psalm/psalm-plugin-symfony).
It has a dependency on base Psalm repo. This plugin allows to use some symfony's features without extra configuration. 
For example, I can call an instance of the Doctrine repository by entity name and psalm can resolver appropriate 
class without errors. However, from my point of view this plugin has a few critical issues,
that were posted since 2022 and haven't been resolved yet. So I had to use `@psalm-suppress` a lot. 
To be able to inspect the code I added a command to composer:
```
composer psalm
```
### PHPStan
[PHPStan](https://github.com/phpstan/phpstan) is a cool tool that works pretty much the same as Psalm. From my point of 
view it is a bit better, because It doesn't have those issues like psalm have. But I decided to keep them both just for
comparison. To be able to inspect the code I added a command to composer:
```
composer phpStan
```
### Deptrac
To be added in the future

### Testing
To be added in the future

## FAQ
___
If you have any questions or comments please send them via this contact form http://www.dmytrodzyuba.com/contact_me
