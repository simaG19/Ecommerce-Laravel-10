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
                sh '''
                  composer install --no-interaction --prefer-dist --optimize-autoloader

                  cp .env.example .env
                  php artisan key:generate
                '''
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
