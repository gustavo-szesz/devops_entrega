# README for Auth API Project

## Overview
This project is a simple PHP application that demonstrates how to handle URL rewriting and query parameters using the `$_GET` superglobal.

## Project Structure
```
auth-api
├── api
│   ├── index.php        # Entry point of the PHP application
│   └── .htaccess       # URL rewriting configuration
├── Dockerfile           # Instructions to build the Docker image
└── README.md            # Documentation for the project
```

## Setup Instructions

### Prerequisites
- Docker installed on your machine.

### Building the Docker Image
1. Navigate to the project directory:
   ```
   cd path/to/auth-api
   ```
2. Build the Docker image:
   ```
   docker build -t auth-api .
   ```

### Running the Application
1. Run the Docker container:
   ```
   docker run -p 8080:80 auth-api
   ```
2. Access the application in your web browser at:
   ```
   http://localhost:8080
   ```

## Usage
You can test the application by appending query parameters to the URL. For example:
```
http://localhost:8080?name=John&age=30
```
This will print the contents of the `$_GET` superglobal, showing the parameters passed in the URL.

## License
This project is open-source and available for modification and distribution.