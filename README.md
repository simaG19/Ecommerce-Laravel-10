🛒 Laravel 10 E-Commerce Website with Bootstrap 5
A modern, responsive eCommerce platform built with Laravel 10 and Bootstrap 5. This project offers a robust foundation for developing scalable online stores, featuring an intuitive user interface and comprehensive backend management.

🚀 Features
Frontend
Responsive design powered by Bootstrap 5

User registration and authentication

Product browsing with detailed views

Shopping cart and wishlist functionalities

Secure checkout process with payment integration

Order tracking and history
YouTube
GitHub

Admin Panel
Dashboard with key metrics

Product and category management

Order management and status updates

User role and permission controls

Promotional tools like coupons and discounts
ItSolutionStuff
+2
DEV Community
+2
GitHub
+2
GitHub
+1
GitHub
+1

🛠️ Installation Guide
Prerequisites
Ensure you have the following installed:

PHP >= 8.1

Composer

Node.js and npm

MySQL or another supported database
GitHub
Medium

Steps
Clone the Repository

bash
Copy
Edit
git clone https://github.com/yourusername/laravel-ecommerce.git
cd laravel-ecommerce
Install PHP Dependencies

bash
Copy
Edit
composer install
Install Node.js Dependencies

bash
Copy
Edit
npm install
Configure Environment Variables

bash
Copy
Edit
cp .env.example .env
php artisan key:generate
Update the .env file with your database credentials and other necessary configurations.

Run Migrations and Seed Database

bash
Copy
Edit
php artisan migrate --seed
Compile Assets

bash
Copy
Edit
npm run build
Start the Development Server

bash
Copy
Edit
php artisan serve
Access the application at http://localhost:8000.
