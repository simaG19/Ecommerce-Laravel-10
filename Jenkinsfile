pipeline {
    agent any

    // If you’ve configured Jenkins Global Tools for PHP, Composer, NodeJS, uncomment and adjust:
    // tools {
    //     php      'PHP 8.1'      // name in Jenkins → points to PHP 8.1 install
    //     composer 'Composer 2.5' // name in Jenkins → points to Composer
    //     nodejs   'NodeJS 16'    // name in Jenkins → points to Node.js & npm
    // }

    environment {
        APP_ENV       = 'testing'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE   = ':memory:'
    }

    stages {
        stage('Checkout code') {
            steps {
                // explicit main-branch checkout
                git branch: 'main',
                    url:   'https://github.com/simaG19/Ecommerce-Laravel-10.git'
            }
        }

        stage('Install PHP dependencies') {
            steps {
                // OS essentail tool
                sh 'sudo apt-get -y install software-properties-common apt-transport-https git gnupg sudo nano wget curl zip unzip tcl inetutils-ping net-tools'

                // PHP & its required extensions
                sh 'sudo add-apt-repository ppa:ondrej/php'
                sh 'sudo apt-get install -y php8.2 php8.2-fpm php8.2-bcmath php8.2-curl php8.2-imagick php8.2-intl php-json php8.2-mbstring php8.2-mysql php8.2-xml php8.2-zip'

                // Composer installation to build and run project
                sh '''sudo php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"'''
                sh '''sudo php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"'''
                sh 'sudo php composer-setup.php'
                sh '''sudo php -r "unlink('composer-setup.php');"'''
                sh 'sudo mv composer.phar /usr/local/bin/composer'
            }
        }

        stage('Run migrations & tests') {
            steps {
                sh '''
                  # migrate and run your PHPUnit / Pest test suite
                  php artisan migrate --force
                  php artisan test
                '''
            }
        }

        stage('Build frontend assets') {
            when {
                expression { fileExists('package.json') }
            }
            steps {
                sh '''
                  # show Node & npm
                  node --version
                  npm --version

                  # install & build
                  npm ci
                  npm run build
                '''
            }
        }
    }

    post {
        success {
            echo '✅ All done—build and tests passed!'
        }
        failure {
            echo '❌ Something failed. Check the logs above.'
        }
        always {
            echo '🏁 Pipeline finished.'
        }
    }
}
