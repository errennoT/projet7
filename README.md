# Projet 7 - OpenClassrooms

La BDD est dans le dossier public <br/>
Les schémas UML sont dans le dossier uml <br/>

# Installer le projet

*Télécharger le dossier github <br/>
*Se positionner dans le dossier du projet <br/>
*Dans le terminal, taper "composer install" <br/>
*A la racine du projet, modifier le fichier .env pour régler la bdd <br/>
*Dans phpmyadmin importer la bdd <br/>
*Dans le terminal, taper "php bin/console doctrine:fixtures:load" <br/>

## Configurer JWT
$ mkdir -p config/jwt <br/>
$ openssl genrsa -out config/jwt/private.pem -aes256 4096 <br/>
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem <br/>

## Comment utiliser l'API ?
*La documentation technique se situe dans le dossier public
