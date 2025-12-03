# GestionSocios API Documentation

## Overview
The GestionSocios API provides programmatic access to the application's data. It follows RESTful principles and uses JSON for data exchange.

## Base URL
All API requests should be made to:
`http://your-domain.com/api/v1/`

## Authentication
The API uses JWT (JSON Web Tokens) for authentication.
Include the token in the `Authorization` header of your requests:
`Authorization: Bearer <your_token>`

### Get Token
**POST** `/auth`
Request Body:
```json
{
    "email": "admin@example.com",
    "password": "your_password"
}
```
Response:
```json
{
    "token": "eyJhbGciOiJIUzI1NiIsIn...",
    "user": {
        "id": 1,
        "email": "admin@example.com",
        "name": "Admin",
        "role": "admin"
    }
}
```

## Resources

### Members
- **GET** `/members`: List all members
- **GET** `/members/{id}`: Get a specific member
- **POST** `/members`: Create a new member
- **PUT** `/members/{id}`: Update a member
- **DELETE** `/members/{id}`: Delete a member

### Events
- **GET** `/events`: List all events
- **GET** `/events/{id}`: Get a specific event
- **POST** `/events`: Create a new event
- **PUT** `/events/{id}`: Update an event
- **DELETE** `/events/{id}`: Delete an event

### Donations
- **GET** `/donations`: List all donations
- **GET** `/donations/{id}`: Get a specific donation
- **POST** `/donations`: Create a new donation
- **PUT** `/donations/{id}`: Update a donation
- **DELETE** `/donations/{id}`: Delete a donation

### Suppliers
- **GET** `/suppliers`: List all suppliers
- **GET** `/suppliers/{id}`: Get a specific supplier

### Expenses
- **GET** `/expenses`: List all expenses
- **GET** `/expenses/{id}`: Get a specific expense

### Tasks
- **GET** `/tasks`: List all tasks
- **GET** `/tasks/{id}`: Get a specific task

## Error Handling
Errors are returned with an appropriate HTTP status code and a JSON object containing an error message:
```json
{
    "error": "Description of the error"
}
```
