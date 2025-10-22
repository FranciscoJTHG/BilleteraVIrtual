# Agent Guidelines for BilleteraVirtual

## Build, Lint & Test Commands

### SOAP Service (PHP/Symfony)
```bash
# Run all tests
cd soap-service && php bin/phpunit

# Run single test
cd soap-service && php bin/phpunit tests/Integration/Service/RegistroClienteTest.php::App\\Tests\\Integration\\Service\\RegistroClienteTest::testRegistroClienteExitoso

# Run migrations
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate
```

### REST Service (Node.js)
```bash
# Start dev server
cd rest-service && npm run dev

# No built-in tests currently - use curl or Postman (docs/Epayco-Wallet.postman_collection.json)
```

### Docker
```bash
# Full stack
docker-compose up -d

# Run migrations (required on first startup)
docker exec -it epayco-soap php bin/console doctrine:migrations:migrate --no-interaction
```

## Code Style & Conventions

### PHP/Symfony (SOAP Service)
- **Namespace**: `App\ControllerName`, `App\Service\ServiceName`, `App\Entity\EntityName`
- **Attributes**: Use PHP 8 attributes (`#[Route]`, `#[ORM\Entity]`, `#[Assert\...]`)
- **Imports**: Use full `use` statements at top; use `as` for conflicts
- **Naming**: camelCase for properties/methods, PascalCase for classes
- **Type Hints**: Always add return types and parameter types (e.g., `: string`, `: array`)
- **Validation**: Use Symfony Validator constraints as attributes on DTOs and Entities
- **Error Handling**: Catch specific exceptions (`\Doctrine\DBAL\Exception`, `\Exception`); return error arrays with `cod_error` and `message_error` fields
- **Formatting**: 4 spaces, trailing commas in arrays, no comments

### Node.js/Express (REST Service)
- **Structure**: Controllers → Services → Routes
- **Naming**: camelCase for functions/variables, validate inputs with schemas
- **Error Handling**: Try-catch blocks returning structured error responses

### Database (Doctrine)
- **Entities**: Use entity attributes for mapping; declare types explicitly
- **Repositories**: Extend `ServiceEntityRepository`; use custom query methods
- **Migrations**: Auto-generate with `doctrine:migrations:diff`, name with version timestamp
