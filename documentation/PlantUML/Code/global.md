# Microservices-Based Application Documentation

## Global - packages

```plantuml
@startuml
allowmixing

actor User

package "Gateway Service" {
  component GatewayController
  component JWTMiddleware
  component AuthorizeProfileAccessMiddleware
  note right of GatewayController
    Microservice
  end note
}

package "Authentication Service" {
  component AuthController
  component AuthService
  component AuthResource
  component EmailService
  component KafkaProducer
  component AuthJobs
  note right of AuthController
    Microservice
  end note
}

package "Authorization Service" {
  component AutzController
  component AutzService
  component AutzResource
  component KafkaConsumerAuth
  note right of AutzController
    Microservice
  end note
}

package "Profile Service" {
  component ProfileController
  component ProfileService
  component ProfileResource
  component KafkaConsumerProfile
  component ProfileJobs
  note right of ProfileController
    Microservice
  end note
}

User --> GatewayController : Register, Login, Logout, Fetch Profile

GatewayController --> JWTMiddleware : Fetch Profile
JWTMiddleware --> AuthController : Verify JWT for Fetch Profile
JWTMiddleware --> AuthorizeProfileAccessMiddleware : Fetch Profile

AuthorizeProfileAccessMiddleware --> AutzController : Verify Authorization for Fetch Profile
AuthorizeProfileAccessMiddleware --> GatewayController : Authorization Result

GatewayController --> AuthController : Register, Login, Logout
GatewayController --> ProfileController : Fetch Profile Data - Done only if user is authorized

AuthController --> AuthService
AuthService --> AuthResource
AuthService --> EmailService : Send Welcome Email
AuthService --> KafkaProducer : Produce User Created Event

KafkaProducer --> KafkaConsumerAuth : User Created Event
KafkaConsumerAuth --> AutzService : Handle User Created Event
AutzService --> AutzResource : Update Authorization Data

KafkaProducer --> KafkaConsumerProfile : User Created Event
KafkaConsumerProfile --> ProfileService : Handle User Created Event
ProfileService --> ProfileResource : Update Profile Data

AuthService --> AuthJobs : Auth Job Handling
ProfileService --> ProfileJobs : Profile Job Handling

@enduml
```