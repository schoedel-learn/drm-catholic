# DRM Catholic

Diocesan Relationship Manager (DRM) for Catholic Dioceses and Archdioceses in the United States.

## Overview

DRM Catholic is a comprehensive relationship management system designed specifically for Catholic dioceses and archdioceses in the United States. It provides tools to manage:

- **Dioceses & Archdioceses**: Track all diocesan information including location, leadership, and statistics
- **Parishes**: Manage parish data within each diocese
- **Contacts**: Maintain contact information for clergy and staff

## Features

- RESTful API for diocesan data management
- Support for all 195+ dioceses and archdioceses in the US
- Parish management within dioceses
- Contact management for clergy and staff
- Extensible architecture for future features

## Getting Started

### Prerequisites

- Node.js >= 18.0.0
- npm >= 9.0.0

### Installation

```bash
# Clone the repository
git clone https://github.com/schoedel-learn/drm-catholic.git
cd drm-catholic

# Install dependencies
npm install

# Build the project
npm run build

# Start the development server
npm run dev
```

### Running in Production

```bash
npm run build
npm start
```

## API Endpoints

### Health Check
- `GET /health` - Service health status

### Dioceses
- `GET /api/v1/dioceses` - List all dioceses
- `GET /api/v1/dioceses?state=TX` - Filter by state
- `GET /api/v1/dioceses?type=archdiocese` - Filter by type
- `GET /api/v1/dioceses/:id` - Get diocese by ID
- `POST /api/v1/dioceses` - Create new diocese
- `PUT /api/v1/dioceses/:id` - Update diocese
- `DELETE /api/v1/dioceses/:id` - Delete diocese
- `GET /api/v1/dioceses/:id/parishes` - Get parishes in diocese
- `GET /api/v1/dioceses/:id/contacts` - Get contacts in diocese

### Parishes
- `GET /api/v1/parishes` - List all parishes
- `GET /api/v1/parishes?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/parishes/:id` - Get parish by ID
- `POST /api/v1/parishes` - Create new parish
- `PUT /api/v1/parishes/:id` - Update parish
- `DELETE /api/v1/parishes/:id` - Delete parish
- `GET /api/v1/parishes/:id/contacts` - Get contacts in parish

### Contacts
- `GET /api/v1/contacts` - List all contacts
- `GET /api/v1/contacts?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/contacts?parishId=xxx` - Filter by parish
- `GET /api/v1/contacts?role=bishop` - Filter by role
- `GET /api/v1/contacts/:id` - Get contact by ID
- `POST /api/v1/contacts` - Create new contact
- `PUT /api/v1/contacts/:id` - Update contact
- `DELETE /api/v1/contacts/:id` - Delete contact

## Development

### Scripts

```bash
npm run dev          # Start development server with hot reload
npm run build        # Build for production
npm start            # Start production server
npm run lint         # Run ESLint
npm test             # Run tests
npm run test:watch   # Run tests in watch mode
npm run test:coverage # Run tests with coverage report
```

### Project Structure

```
drm-catholic/
├── src/
│   ├── api/           # API route handlers
│   │   ├── dioceses.ts
│   │   ├── parishes.ts
│   │   └── contacts.ts
│   ├── config/        # Application configuration
│   ├── models/        # Data models and interfaces
│   ├── services/      # Business logic and data access
│   ├── utils/         # Utility functions
│   ├── __tests__/     # Test files
│   └── index.ts       # Application entry point
├── .github/
│   └── workflows/     # GitHub Actions CI/CD
├── package.json
├── tsconfig.json
├── jest.config.js
└── .eslintrc.js
```

## Branch Protection

The `main` branch is protected with the following rules:
- Require pull request reviews before merging
- Require status checks to pass before merging
- Require branches to be up to date before merging
- Include administrators in these restrictions

To configure branch protection:
1. Go to repository Settings > Branches
2. Add a branch protection rule for `main`
3. Enable the desired protections

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Roadmap

- [ ] Database integration (PostgreSQL)
- [ ] User authentication and authorization
- [ ] Web dashboard interface
- [ ] Import/export functionality for diocesan data
- [ ] Integration with USCCB data sources
- [ ] Reporting and analytics
- [ ] Multi-tenant support for individual dioceses
