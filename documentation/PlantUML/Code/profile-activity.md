# Microservices-Based Application Documentation

## Profile Activity

```plantuml
@startuml
start
:User Request to Fetch Profile;
:GatewayController receives request;
:GatewayController forwards request to JWTMiddleware;
:JWTMiddleware verifies JWT;
if (JWT valid?) then (yes)
    :JWTMiddleware forwards request to AuthorizeProfileAccessMiddleware;
    :AuthorizeProfileAccessMiddleware verifies user authorization;
    if (User authorized?) then (yes)
        :AuthorizeProfileAccessMiddleware forwards request back to GatewayController;
        :GatewayController forwards request to ProfileController;
        :ProfileController fetches user profile data;
        :ProfileService processes profile logic;
        :ProfileResource formats profile data;
        :ProfileService sends formatted profile data back to GatewayController;
        :GatewayController sends profile data to User;
    else (no)
        :AuthorizeProfileAccessMiddleware returns authorization error;
    endif
else (no)
    :JWTMiddleware returns authentication error;
endif
stop
@enduml
```