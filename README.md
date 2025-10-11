# PHP Mini Accounting API

A minimal accounting API project implemented in vanilla PHP.

This project focuses on adhering to strong **Separation of Concerns** principles by utilizing the **MVC (Model-View-Controller)** architecture augmented with the **Service/Repository** pattern.

This structure is designed to enhance code readability, maintainability, and testability by mitigating "Fat Model" and "Fat Controller" anti-patterns.

Our architecture is explicitly designed to promote high cohesion and low coupling through the following layers:

    Controller: Handles incoming HTTP requests, validates basic input, and delegates business logic execution to the Service Layer. Controllers are kept lean (“Thin Controllers”).
    Service Layer: Orchestrates the business rules and workflows. It acts as a coordinator between the Controller and the Repository layer. This layer houses the core business logic, preventing “Fat Models.”
    Repository Layer: Manages all data access operations (CRUD) for a specific entity. It abstracts the underlying database technology, allowing the Service layer to interact with data via a consistent interface.
    Model (Entity): A plain PHP class representing the data structure, free of any business or persistence logic.

Dependency Management: The Container Pattern

To ensure explicit control over dependencies and to facilitate testing, this project utilizes a Custom Dependency Injection (DI) Container.

    Decoupling: Classes (Controllers, Services, Repositories) do not manually instantiate their dependencies (e.g., new UserRepository()). Instead, they declare their required dependencies in their constructors (Type-Hinting).
    Resolution: The DI Container is responsible for automatically resolving and injecting the correct, instantiated dependencies into any class upon request. This adheres to the SOLID principles, specifically the Dependency Inversion Principle (DIP).
    Benefit: This approach drastically simplifies testing by allowing easy substitution of real dependencies with Mocks or Stubs during unit tests, leading to cleaner, more robust, and easily verifiable code.