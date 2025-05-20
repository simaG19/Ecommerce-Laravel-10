pipeline {
    agent any

    environment {
        APP_ENV = 'testing'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE = ':memory:'
    }

    stages {
        stage('Clone Repo') {
            steps {
                git 'https://github.com/simaG19/Ecommerce-Laravel-10.git'
            }
        }

        stage('Set Up PHP & Composer') {
            steps {
                sh '''
                    php -v
                    composer install --no-interaction --prefer-dist --optimize-autoloader
                    cp .env.example .env
                    php artisan key:generate
                '''
            }
        }

        stage('Run Backend Tests') {
            steps {
                sh 'php artisan migrate'
                sh 'php artisan test'
            }
        }

        stage('Build Frontend (Optional)') {
            steps {
                sh '''
                    npm install
                    npm run build
                '''
            }
        }
    }

    post {
        always {
            echo 'Pipeline finished.'
        }
        failure {
            echo 'Build failed.'
        }
    }
}
