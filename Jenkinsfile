pipeline {
    agent any

    environment {
        APP_ENV       = 'testing'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE   = ':memory:'
    }

    stages {
        stage('Checkout') {
            steps {
                // explicitly check out main
                git branch: 'main',
                    url:    'https://github.com/simaG19/Ecommerce-Laravel-10.git'
            }
        }

        stage('PHP / Composer') {
            // use the official Composer image (bundles PHP & Composer)
            agent {
                docker {
                    image 'composer:2.5-php8.1'
                    // persist composer cache across builds
                    args  '-v $HOME/.composer/cache:/root/.composer/cache'
                }
            }
            steps {
                sh '''
                  composer install --no-interaction --prefer-dist --optimize-autoloader
                  cp .env.example .env
                  php artisan key:generate
                '''
            }
        }

        stage('Migrate & Test') {
            agent {
                docker {
                    image 'composer:2.5-php8.1'
                    args  '-v $HOME/.composer/cache:/root/.composer/cache'
                }
            }
            steps {
                sh '''
                  php artisan migrate --force
                  php artisan test
                '''
            }
        }

        stage('Frontend Build') {
            when {
                expression { fileExists('package.json') }
            }
            agent {
                docker {
                    image 'node:16'
                    args  '-v $HOME/.npm:/root/.npm'
                }
            }
            steps {
                sh '''
                  npm ci
                  npm run build
                '''
            }
        }
    }

    post {
        success {
            echo '✅ Build succeeded!'
        }
        failure {
            echo '❌ Build failed!'
        }
        always {
            echo '🏁 Pipeline complete.'
        }
    }
}
