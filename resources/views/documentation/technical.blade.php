<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <title>Structure du projet crfpe (solaris-crfpe.fr)</title>
  </head>
  <body>
    <div class="container pt-5">
        <h1>Notre Stack docker (Laravel v8.21.0 (PHP v7.4.13))</h1>
        <ul>
            <li>Nginx</li>
            <li>Php</li>
            <li>MySql</li>
            <li>PhpMyadmin</li>
            <li>certbot (ssl)</li>
        </ul>
        <h1>Configuration du projet : </h1>
        <ul>
            <li>Cloner le projet git : <strong>https://gitlab.com/dev-connect-team/crfpeprojectdockerlaravelprod.git</strong></li>
            <li>Dupliquer le fichier nginx/<strong>default.conf.dist</strong> et renomer le en <strong>default.conf</strong> (Configuration nginx vhost, certificat ssl etc ...)</li>
            <li>Dupliquer le fichier src/<strong>.env.example</strong> et renomer <strong>.env</strong> (Pour mettre votre configs local)</li>
            <li>Commenter la ligne (27 au 30) dans le fichier src/app/Providers/AppServiceProvider.php</li>
            <li>docker-compose up -d --build</li>
            <li>docker-compose run --rm composer install</li>
            <li>docker-compose run --rm artisan key:generate (Générer clé laravel)</li>
            <li>docker-compose run --rm artisan migrate</li>
            <li>Décommenter la ligne (27 au 30) dans le fichier src/app/Providers/AppServiceProvider.php</li>
            <li>docker-compose run --rm npm install (installer les dépendance js package json)</li>
            <li>docker-compose run --rm npm run dev or (watch or production)  (compilation)</li>
            <li>Accéder à votre URL vhost : ex http://crfpe.local/</li>
        </ul>
        <h1>Structure du projet crfpe</h1>
        <ul>
            <li><i class="far fa-folder-open"></i> solaris-crfpe.fr/</li>
            <ol>
                <li><i class="far fa-folder-open"></i> nginx/</li>
                <ol>
                    <li><i class="far fa-folder-open"></i> certbot/</li>
                    <li>default.conf</li>
                </ol>
            </ol>
            <ol>
                <li><i class="far fa-folder-open"></i> php/</li>
                <ol>
                    <li>www.conf</li>
                </ol>
            </ol>
            <ol>
                <li><i class="far fa-folder-open"></i> src/</li>
                <ol>
                    <li>laravel app files</li>
                </ol>
            </ol>
            <ol>
                <li>composer.dockerfile</li>
            </ol>
            <ol>
                <li>docker-compose.yml</li>
            </ol>
            <ol>
                <li>init-letsencrypt.sh</li>
            </ol>
            <ol>
                <li>nginx.dockerfile</li>
            </ol>
            <ol>
                <li>php.dockerfile</li>
            </ol>
        </ul>
        <h1>les commandes les plus importantes :</h1>
        <ul>
            <li>docker-compose build</li>
            <li>docker-compose up -d</li>
            <li>docker-compose ps</li>
            <li>docker-compose stop</li>
            <li>docker-compose down</li>
            <li>docker-compose run --rm composer update</li>
            <li>docker-compose run --rm artisan migrate</li>
            <li>docker-compose run --rm composer create-project laravel/laravel src</li>
            <li>docker-compose run --rm artisan make:model Course -m</li>
            <li>docker-compose run --rm artisan make:factory CourseFactory --model=Course</li>
            <li>docker-compose run --rm artisan migrate --seed Or docker-compose run --rm artisan db:seed</li>
            <li>Changement type clonne BD : docker-compose run --rm composer require doctrine/dbal</li>
            <li>docker-compose run --rm artisan make:migration alter_pf_formations_change_nb_hours_to_decimal --table=pf_formations</li>
        </ul>
        <h1>Nginx Let's Encrypt (certbot)</h1>
        <p>https://github.com/wmnnd/nginx-certbot</p>
        <p>init-letsencrypt.sh récupère et assure le renouvellement d’un certificat Let’s Encrypt pour un ou plusieurs domaines dans une configuration docker-compose avec nginx.</p>
        <p><strong>Installation :</strong></p>
        <p>1/ Add domains and email addresses to <strong>init-letsencrypt.sh</strong></p>
        <p>2/ Replace all occurrences of <strong>example.org</strong> with primary domain (the first one you added to init-letsencrypt.sh) in <strong>nginx/default.conf</strong></p>
        <p>3/ Run the init script: <strong>./init-letsencrypt.sh</strong></p>
        
        <h1>Définitions</h1>
        <p><strong>Docker Compose</strong> va vous permettre d'orchestrer vos conteneurs, et ainsi de simplifier vos déploiements sur de multiples environnements. Docker Compose est un outil écrit en Python qui permet de décrire, dans un fichier YAML, plusieurs conteneurs comme un ensemble de services. </p>
        <p><strong>Stack</strong> un ensemble de conteneurs Docker lancés via un seul et unique fichier Docker Compose.</p>

        
    </div>  
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
  </body>
</html>