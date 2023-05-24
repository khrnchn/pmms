

<div align="center">
    <a href="https://imgbb.com/">
        <img src="https://i.ibb.co/xS6RZfh/petakom-logo.png" alt="petakom-logo" border="0">
    </a>
    <h3 align="center">Petakom Mart Management System/ PetaKoop</h3>
</div>

## Table of Contents

- [About](#about)
- [Getting Started](#getting_started)
- [Usage](#usage)
- [Contributing](../CONTRIBUTING.md)

## About <a name = "about"></a>

A Filament-based Petakom Mart Management System by our group for subject Software Engineering Practices. Developed using the Laravel framework.

## Getting Started <a name = "getting_started"></a>

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See [deployment](#deployment) for notes on how to deploy the project on a live system.

### Prerequisites

Have PHP, Composer, and node installed on your local machine. 

### Installing

1. Clone the repo.

2. Composer update.
```
composer update
```

3. Install node package manager.
```
npm install
```

4. Generate .env file.
```
cp .env.example .env
php artisan key:generate
```

5. Populate the database.
```
php artisan migrate --seed
```

6. Run the project locally.
```
php artisan serve
```

## Usage <a name = "usage"></a>

1. Login as different users (eg Admin/ Cashier/ Committee).
2. Create inventory, sales.
3. Manage employee's schedules.

## ✍️ Authors <a name = "authors"></a>

- [@ejjat0909](https://github.com/ejjat0909) - Izzat
- [@khrnchn](https://github.com/khrnchn) - Khairin
- [@AHisyam-coder](https://github.com/ahisyam-coder) - Hisyam

<p width="100%">
    <img width="23%" src="https://i.ibb.co/NSX1zSq/ijat.jpg"> 
    <img width="23%" src="https://i.ibb.co/cJkJgyN/kc.jpg"> 
    <img width="23%" src="https://i.ibb.co/t233xPJ/sye.jpg"> 
</p>
