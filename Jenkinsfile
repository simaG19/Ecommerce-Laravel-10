pipeline {
    agent any

    stages {
        stage('Build') {
            steps {
                git 'https://github.com/Kennibravo/jenkins-laravel.git'
                sh 'composer install'
                sh 'cp .env.example .env'
                sh 'php artisan key:generate'
            }
        }
        stage('Test') {
            steps {
                sh './vendor/bin/phpunit'
            }
        }
    }
}
