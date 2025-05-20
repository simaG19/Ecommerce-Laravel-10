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
                // explicitly checkout the `main` branch
                git branch: 'main',
                    url: 'https://github.com/simaG19/Ecommerce-Laravel-10.git'
            }
        }

        stage('Prepare') {
            steps {
                sh '''
                  # ensure correct PHP & Composer are on PATH
                  php -v
                  composer install --no-interaction --prefer-dist --optimize-autoloader

                  # set up environment
                  cp .env.example .env
                  php artisan key:generate
                '''
            }
        }

        stage('Migrate & Test') {
            steps {
                sh '''
                  # migrate in-memory DB and run your test suite
                  php artisan migrate
                  php artisan test
                '''
            }
        }

        stage('Frontend Build (if any)') {
            when {
                expression { fileExists('package.json') }
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
            echo '👍 Build succeeded!'
        }
        failure {
            echo '🚨 Build failed!'
        }
        always {
            echo '🏁 Pipeline complete.'
        }
    }
}
