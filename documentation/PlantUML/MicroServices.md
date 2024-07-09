# Project Overview

This project is a microservices-based application designed for user management, including authentication, authorization, and profile management. It leverages Docker containers for each service to ensure modularity and ease of deployment. The main components of this system include the Gateway Service, Authentication Service, Authorization Service, Profile Service, and Kafka for asynchronous communication between services.

## Requirements

- **Docker**: To run the microservices in isolated containers.
- **Docker Compose**: To manage multi-container Docker applications.
- **Git**: To clone the repositories.
- **Postman**: For API testing and documentation.

## Setup Instructions

### 1. Clone the repositories

Start by cloning the following repositories:

```sh
git clone git@github.com:miloskec/gateway.git
git clone git@github.com:miloskec/authentication.git
git clone git@github.com:miloskec/authorization.git
git clone git@github.com:miloskec/profile.git
git clone git@github.com:miloskec/kafka.git
```

### 2. Run the setup script

Navigate to the gateway directory and execute the setup script to automatically set up the microservices:

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

3. **Fetching Profile**:
    - User sends a profile fetch request to the Gateway.
    - Gateway applies JWT authentication and authorization checks.
    - Upon successful authorization, Gateway forwards the request to the Profile Service.

4. **Background Jobs**:
    - Authentication Service runs jobs for sending email notifications and producing Kafka messages.
    - Authorization and Profile Services consume Kafka messages to update their respective data.

This setup ensures that each microservice operates independently while communicating effectively through Kafka, providing a robust and scalable architecture.