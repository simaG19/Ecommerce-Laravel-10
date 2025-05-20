pipeline {
  agent any

  stages {
    stage('Build') {
      steps {
        // use the PAT credential to authenticate the git clone
        git branch: 'main',
            url: 'https://github.com/simaG19/Ecommerce-Laravel-10.git',
            credentialsId: 'github-pat'

        sh 'php composer.phar install'
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
