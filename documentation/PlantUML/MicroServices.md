# Project Overview

This project is a microservices-based application designed for user management, including authentication and authorization functionalities. It uses Docker containers for each service to ensure modularity and easy of deployment. The main components of this architecture include the Gateway Service, Authentication Service, Authorization Service, Profile Service, and Kafka for asynchronous communication between services. Additionally, a DataDog agent is implemented as a dedicated service that collects logs from all services and forwards them to DataDog (which offers several advantages for microservices architectures, such as centralized logging, real-time performance monitoring, and detailed observability).

## Requirements

- **Docker**: To run the microservices in isolated containers.
- **Docker Compose**: To manage multi-container Docker applications.
- **Git**: To clone the repositories.
- **Postman**: For API testing and documentation.

## Setup Instructions

### NOTE
**If all your project branches are set to 'production-prepare', you may follow the instructions provided at this [link](https://github.com/miloskec/gateway/blob/production-prepare/README.md) and bypass the instructions given below.**

### 1. Clone the repositories

Start by cloning the following repositories:

```sh
git clone git@github.com:miloskec/gateway.git
git clone git@github.com:miloskec/authentication.git
git clone git@github.com:miloskec/authorization.git
git clone git@github.com:miloskec/profile.git
git clone git@github.com:miloskec/kafka.git
```


### 2. Prepare the development environment and install dependencies
Each microservice requires a local development environment capable of running PHP 8.3 and its dependencies. It's recommended to add the ppa:ondrej/xxxx repository to your system to ensure you have the latest PHP version and extensions like Memcache available. Install PHP, Apache2, MySQL 8, and other necessary components.
Something like:  
```sh
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php8.3 apache2 mysql-server
``` 
Navigate to each service directory and execute composer install. To avoid issues with missing PHP extensions that are not critical for local development, such as ext-rdkafka, you can bypass the platform requirements:

```sh
composer install --ignore-platform-req=ext-rdkafka
```
Ensure you copy .env.example to .env in each service and configure the necessary settings, such as database connections and API keys. For the Authentication service, include your mailer credentials, and for services integrated with DataDog, include your DataDog API key which can be obtained from your DataDog account.


### 3. Run the setup script
After setting up each microservice, navigate to the gateway directory and run the setup script to initialize and link all the services together. This script will use Docker Compose to orchestrate the containers and ensure all services are communicating effectively. 

```sh
cd gateway
./setup_microservices.sh
```

The setup script performs the following tasks:

- Creates a Docker network if it doesn't exist.
- Brings up the Docker containers for each service.
- Runs database migrations and seeds the necessary data.
- Starts background jobs for processing tasks like sending email notifications and consuming Kafka messages.

## Services and Their Roles

1. **Gateway Service**: Acts as the entry point for all client requests. It handles routing and applies necessary middlewares like JWT authentication and authorization checks.
2. **Authentication Service**: Manages user registration, login, and JWT token verification. It also handles background jobs for sending registration email notifications and producing Kafka messages for user creation events.
3. **Authorization Service**: Verifies user roles and permissions. It consumes Kafka messages to update its data when users are created or updated.
4. **Profile Service**: Manages user profiles. It consumes Kafka messages to update profile data when users are created or updated.

## Communication Between Services

- **Gateway as a Proxy**: The Gateway Service acts like a proxy, forwarding client requests to the appropriate microservices using REST API calls. These calls are internal to the Docker network, ensuring secure and efficient communication.
- **Kafka for Asynchronous Actions**: Some actions, such as user creation, are handled asynchronously using Kafka. When a user is created, the Authentication Service produces a Kafka message, which is then consumed by the Authorization and Profile Services to update their data accordingly.

## Request Handling and Validation

- **Request Validation**: Each service has its own Laravel request objects for validation. For example, during user registration, the Gateway Service validates the email format, while the Authentication Service checks if the email is already in use after receiving the request.
- **Services and Tiny Controllers**: Each project follows a service-oriented architecture with thin controllers. The Guzzle package is used for making HTTP requests between services.
- **Error Handling**: Errors are handled uniformly across all services, with a consistent response structure.
- **Logging**: Every Guzzle request and response is logged and stored in specific JSON log files for each service, ensuring traceability and debugging capabilities.

## Error Handling 

- **Centralized approach**: The microservices architecture ensures robust error management by standardizing the error responses across all services. Errors are captured and handled through a custom exception handler defined in App/Exceptions/Handler.php. This centralized approach aids in maintaining consistency and simplifies management.
- **Standardized Error Response**: All microservices utilize a consistent error response format, making it easier for clients and services to understand and handle errors. The standardized error response structure is as follows:

```json
{
    "status": "<HTTP_STATUS_CODE>",
    "source": "<MICRO_SERVICE_SOURCE_OF_ERROR>",
    "message": "<ERROR_MESSAGE>",
    "error": {
        "type": "<EXCEPTION_TYPE>",
        "details": "<ERROR_DETAILS_ARRAY>"
    }
}
```

- **Custom Exception Handler**: The App/Exceptions/Handler.php is equipped to handle various types of exceptions, ensuring that each error is processed effectively. This handler allows for the addition of specific exception types, which can be managed and modified as needed, without affecting the broader service functionality.
- **Sensitive Data Handling**: Special attention is given to ensure that sensitive data is not exposed in error messages. Before any data is logged or included in an error response, it is filtered to remove sensitive information, thereby safeguarding user privacy and security.

## Logging 

- **Monolog**: Logging is managed through Laravel Monolog, with each service configured to log detailed operational data. These logs provide valuable insights into the system's behavior and are essential for troubleshooting and monitoring.
- **DataDog Integration**: A dedicated DataDog agent, running as an independent microservice within its own Docker container, handles all logging operations. This setup allows for efficient log management without imposing overhead on other services.
- **Log Collection and Forwarding**: Logs from each service are collected by the DataDog agent and forwarded to the DataDog server. This includes logs generated by Laravel Monolog and request/response logs from Guzzle transactions. The DataDog platform provides a centralized view of logs, enabling detailed analysis and real-time monitoring.
- **Security and Compliance**: All logs are pre-processed to filter out sensitive information before they are sent to DataDog. This ensures compliance with data protection regulations and maintains the integrity and security of the data.
- **Activity Logger**: An Activity Logger has been implemented to record all updates to specific models within the database. This logger is manually enabled for models where change tracking is required. It captures details such as which fields were changed, when the change occurred, and who made the change. This functionality is crucial for auditing and provides a historical record of modifications, enhancing transparency and accountability within the system.

### Log Uses 

Logs are utilized for:

- **Debugging**: Identifying and resolving issues within the services.
- **Monitoring**: Observing the system’s performance and health.
- **Audit Trails**: Providing a history of operations for security audits and compliance.

## Diagrams

### Register Sequence Diagram
![Register Sequence Diagram](https://github.com/miloskec/gateway/blob/basic/documentation/PlantUML/Images/register-complete-sequence-diagram.png)

### Token Verify Sequence Diagram
![Token Verify Sequence Diagram](https://github.com/miloskec/gateway/blob/basic/documentation/PlantUML/Images/token-verify-complete-sequence-diagram.png)

### Login Sequence Diagram
![Login Sequence Diagram](https://github.com/miloskec/gateway/blob/basic/documentation/PlantUML/Images/login-complete-sequence-diagram.png)

### Profile Sequence Diagram
![Profile Sequence Diagram](https://github.com/miloskec/gateway/blob/basic/documentation/PlantUML/Images/profile-complete-sequence-diagram.png)

### Fetch Profile Activity Diagram
![Fetch Profile Activity Diagram](https://github.com/miloskec/gateway/blob/basic/documentation/PlantUML/Images/fetch-profile-activity-diagram.png)

### Global Package Diagram
![Global Package Diagram](https://github.com/miloskec/gateway/blob/basic/documentation/PlantUML/Images/global-package-diagram.png)

## Postman Collection

The API endpoints are documented and can be tested using the Postman collection available at the following URL: [Postman Collection](https://documenter.getpostman.com/view/9220824/2sA3dxEXTW).

## How It Works

1. **User Registration**:
    - User sends a registration request to the Gateway.
    - Gateway forwards the request to the Authentication Service.
    - Authentication Service processes the registration, sends a welcome email, and produces a Kafka message.

2. **User Login**:
    - User sends a login request to the Gateway.
    - Gateway forwards the request to the Authentication Service for JWT token generation.
    - If the user successfully logs in, the JWT token is stored in the cache to prevent unnecessary communication with the Authentication Service for subsequent requests until the token expires or is invalidated (e.g., upon issuing a refresh token).

3. **Fetching Profile**:
    - User sends a profile fetch request to the Gateway.
    - Gateway applies JWT authentication and authorization checks.
    - Upon successful authorization, Gateway forwards the request to the Profile Service.
    - Additionally, as part of the security measures, each service behind the Gateway also performs its own identity verification by validating the JWT token using the same JWT_SECRET used by the Authentication Service that initially issued the token. This provides an added layer of protection within the gateway system, ensuring that each service independently confirms the user’s identity.

4. **Background Jobs**:
    - Authentication Service runs jobs for sending email notifications and producing Kafka messages.
    - Authorization and Profile Services consume Kafka messages to update their respective data.

This setup ensures that each microservice operates independently while communicating effectively through Kafka, providing a robust and scalable architecture.
