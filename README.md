# DRM Catholic

Diocesan Relationship Manager (DRM) for Catholic Dioceses and Archdioceses in the United States.

## Overview

DRM Catholic is a comprehensive relationship management system designed specifically for Catholic dioceses and archdioceses in the United States. It provides tools to manage:

- **Dioceses & Archdioceses**: Track all diocesan information including location, leadership, and statistics
  - Support for tracking external dioceses (contacts from other dioceses)
  - Ecclesiastical province tracking with metropolitan archdiocese
- **Regions**: Manage regions within a diocese (groupings of deaneries or parishes)
  - Regional/Episcopal vicar tracking
- **Deaneries**: Manage groups of parishes for coordination and support
  - Dean (vicar forane) tracking
- **Parishes**: Manage parish data within each diocese
  - Support for different canonical statuses (parish, quasi-parish, personal parish, national parish)
  - **Sacred Site Designations**: Cathedrals, Basilicas (major/minor), Shrines (national/diocesan), Pilgrimage Sites
- **Missions**: Track mission churches, chapels, and other worship sites
  - Mission churches, chapels, oratories
  - Campus, hospital, prison, military chapels
  - Shrines and pilgrimage sites (non-parish sacred sites)
- **Schools**: Track Catholic schools (elementary, middle, high school)
  - Principal and assistant principal tracking
  - Teachers and faculty management
  - Counselors and support staff
  - Enrollment and accreditation
- **Religious Houses**: Track monasteries, convents, and other religious communities
  - Monasteries, abbeys, and priories
  - Convents and motherhouses
  - Friaries (Franciscan and mendicant communities)
  - Hermitages and retreat houses
  - Formation houses and provincial houses
- **Organizations**: Manage Catholic organizations at all levels:
  - **Diocesan**: Catholic Charities, hospitals, healthcare systems, SVDP, retreat centers
  - **National**: USCCB (United States Conference of Catholic Bishops) and committees
  - **International**: Roman Curia, Pontifical councils, Vatican dicasteries
  - Religious congregations, seminaries, Catholic universities
  - **St. Vincent de Paul Society (SVDP) Hierarchy**:
    - National Council, Regional Councils, District Councils
    - Diocesan/Archdiocesan Councils, Parish Conferences
    - Executive Director, Presidents at each level, Spiritual Advisors
  - **Catholic Charities**: Executive Director, Directors of Programs, Program Directors
- **Apostolates**: Track all ministry groups and lay apostolates doing ministry in the name of the Church
  - Youth ministry, campus ministry, pro-life
  - Evangelization, catechesis, liturgical ministries
  - Hispanic, African-American, Asian ministries
  - Knights of Columbus, ladies auxiliaries, and more
- **Diocesan Offices**: Manage administrative departments (chancery, tribunal, education, vocations, etc.)
- **Contacts**: Maintain comprehensive contact information for:
  - Clergy (bishops, auxiliary bishops, priests, deacons)
  - Religious (members of religious orders)
  - Lay leaders (principals, directors, associate directors, coordinators)
  - Organization leadership (executives, board members)
  - External contacts from other dioceses
- **Positions**: Track multiple roles held by contacts across organizations:
  - USCCB committee positions
  - Roman Curia appointments
  - Positions in other dioceses
  - Leadership roles across multiple entities (metropolitan, regional vicar, dean)

## Features

- RESTful API for diocesan data management
- Support for all 195+ dioceses and archdioceses in the US
- **Cross-diocesan contact tracking** - Track contacts from other dioceses
- **Multiple position support** - A contact can hold positions in USCCB, Roman Curia, and other organizations
- **Organization scope levels** - Parish, diocesan, provincial, national, and international organizations
- **Complete territorial hierarchy** - Province → Diocese → Region → Deanery → Parish → Mission
- **Metropolitan structure** - Track ecclesiastical provinces and metropolitan archbishops
- **Religious houses management** - Track monasteries, convents, friaries, hermitages
- Parish and deanery management
- School management with superintendent and principal tracking
- Organization management for hospitals, Catholic Charities, SVDP, USCCB, Roman Curia, etc.
- Apostolate tracking for all Church ministries
- Comprehensive contact management for anyone doing ministry in the name of the Church
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

### Regions
- `GET /api/v1/regions` - List all regions
- `GET /api/v1/regions?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/regions/:id` - Get region by ID
- `POST /api/v1/regions` - Create new region
- `PUT /api/v1/regions/:id` - Update region
- `DELETE /api/v1/regions/:id` - Delete region
- `GET /api/v1/regions/:id/deaneries` - Get deaneries in region
- `GET /api/v1/regions/:id/contacts` - Get contacts in region (e.g., regional vicar)

### Deaneries
- `GET /api/v1/deaneries` - List all deaneries
- `GET /api/v1/deaneries?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/deaneries/:id` - Get deanery by ID
- `POST /api/v1/deaneries` - Create new deanery
- `PUT /api/v1/deaneries/:id` - Update deanery
- `DELETE /api/v1/deaneries/:id` - Delete deanery
- `GET /api/v1/deaneries/:id/parishes` - Get parishes in deanery

### Parishes
- `GET /api/v1/parishes` - List all parishes
- `GET /api/v1/parishes?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/parishes/:id` - Get parish by ID
- `POST /api/v1/parishes` - Create new parish
- `PUT /api/v1/parishes/:id` - Update parish
- `DELETE /api/v1/parishes/:id` - Delete parish
- `GET /api/v1/parishes/:id/contacts` - Get contacts in parish

### Missions
- `GET /api/v1/missions` - List all missions
- `GET /api/v1/missions?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/missions?parishId=xxx` - Filter by parent parish
- `GET /api/v1/missions?missionType=chapel` - Filter by type (mission_church, chapel, oratory, campus_chapel, etc.)
- `GET /api/v1/missions/:id` - Get mission by ID
- `POST /api/v1/missions` - Create new mission
- `PUT /api/v1/missions/:id` - Update mission
- `DELETE /api/v1/missions/:id` - Delete mission
- `GET /api/v1/missions/:id/contacts` - Get contacts at mission

### Schools
- `GET /api/v1/schools` - List all schools
- `GET /api/v1/schools?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/schools?parishId=xxx` - Filter by sponsoring parish
- `GET /api/v1/schools?type=high_school` - Filter by type (elementary, middle, high_school, k8, k12, preschool)
- `GET /api/v1/schools/:id` - Get school by ID
- `POST /api/v1/schools` - Create new school
- `PUT /api/v1/schools/:id` - Update school
- `DELETE /api/v1/schools/:id` - Delete school
- `GET /api/v1/schools/:id/contacts` - Get contacts at school

### Religious Houses
- `GET /api/v1/religious-houses` - List all religious houses (monasteries, convents, friaries, etc.)
- `GET /api/v1/religious-houses?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/religious-houses?type=monastery` - Filter by type (monastery, abbey, priory, convent, friary, hermitage, etc.)
- `GET /api/v1/religious-houses?religiousOrder=Benedictines` - Filter by religious order
- `GET /api/v1/religious-houses/:id` - Get religious house by ID
- `POST /api/v1/religious-houses` - Create new religious house
- `PUT /api/v1/religious-houses/:id` - Update religious house
- `DELETE /api/v1/religious-houses/:id` - Delete religious house
- `GET /api/v1/religious-houses/:id/contacts` - Get community members/contacts at religious house

### Organizations
- `GET /api/v1/organizations` - List all organizations
- `GET /api/v1/organizations?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/organizations?type=hospital` - Filter by type (catholic_charities, hospital, usccb, roman_curia, etc.)
- `GET /api/v1/organizations?scope=national` - Filter by scope (parish, diocesan, provincial, national, international)
- `GET /api/v1/organizations/national` - Get all national organizations (e.g., USCCB)
- `GET /api/v1/organizations/international` - Get all international organizations (e.g., Roman Curia)
- `GET /api/v1/organizations/:id` - Get organization by ID
- `POST /api/v1/organizations` - Create new organization
- `PUT /api/v1/organizations/:id` - Update organization
- `DELETE /api/v1/organizations/:id` - Delete organization
- `GET /api/v1/organizations/:id/contacts` - Get leadership/contacts at organization
- `GET /api/v1/organizations/:id/positions` - Get all positions held within organization

### Apostolates
- `GET /api/v1/apostolates` - List all apostolates
- `GET /api/v1/apostolates?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/apostolates?parishId=xxx` - Filter by parish
- `GET /api/v1/apostolates?type=youth_ministry` - Filter by type
- `GET /api/v1/apostolates/:id` - Get apostolate by ID
- `POST /api/v1/apostolates` - Create new apostolate
- `PUT /api/v1/apostolates/:id` - Update apostolate
- `DELETE /api/v1/apostolates/:id` - Delete apostolate
- `GET /api/v1/apostolates/:id/contacts` - Get contacts involved in apostolate

### Diocesan Offices
- `GET /api/v1/offices` - List all diocesan offices
- `GET /api/v1/offices?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/offices?type=chancery` - Filter by type (chancery, tribunal, education, vocations, etc.)
- `GET /api/v1/offices/:id` - Get office by ID
- `POST /api/v1/offices` - Create new office
- `PUT /api/v1/offices/:id` - Update office
- `DELETE /api/v1/offices/:id` - Delete office
- `GET /api/v1/offices/:id/contacts` - Get staff at office

### Contacts
- `GET /api/v1/contacts` - List all contacts
- `GET /api/v1/contacts?dioceseId=xxx` - Filter by diocese
- `GET /api/v1/contacts?parishId=xxx` - Filter by parish
- `GET /api/v1/contacts?schoolId=xxx` - Filter by school
- `GET /api/v1/contacts?organizationId=xxx` - Filter by organization
- `GET /api/v1/contacts?role=bishop` - Filter by role
- `GET /api/v1/contacts/:id` - Get contact by ID
- `POST /api/v1/contacts` - Create new contact
- `PUT /api/v1/contacts/:id` - Update contact
- `DELETE /api/v1/contacts/:id` - Delete contact

### Positions (Multiple Roles per Contact)
- `GET /api/v1/positions` - List all contact positions
- `GET /api/v1/positions?contactId=xxx` - Get all positions for a contact
- `GET /api/v1/positions?dioceseId=xxx` - Get positions in a diocese
- `GET /api/v1/positions?organizationId=xxx` - Get positions in an organization (e.g., USCCB)
- `GET /api/v1/positions/:id` - Get position by ID
- `POST /api/v1/positions` - Create new position (e.g., add USCCB committee role)
- `PUT /api/v1/positions/:id` - Update position
- `DELETE /api/v1/positions/:id` - Delete position

## Data Model

### Entity Types

| Entity | Description |
|--------|-------------|
| Diocese | Diocese or archdiocese with bishop, location, statistics (supports external dioceses) |
| Province | Ecclesiastical province grouping dioceses under a metropolitan archbishop |
| Region | Group of deaneries or parishes within a diocese under a regional vicar |
| Deanery | Group of parishes for coordination under a dean (vicar forane) |
| Parish | Local church community with pastor and mass schedules |
| Mission | Mission church, chapel, oratory, or other worship site |
| School | Catholic school (preschool through high school) |
| ReligiousHouse | Monastery, convent, friary, hermitage, or other religious community |
| Organization | Catholic organizations with scope (diocesan, national, international) |
| Apostolate | Ministry group (youth, pro-life, evangelization, etc.) |
| DiocesanOffice | Administrative department (chancery, tribunal, etc.) |
| Contact | Person associated with any entity (supports external contacts) |
| ContactPosition | Additional roles held by a contact (USCCB, Roman Curia positions) |

### Territorial Hierarchy

```
Province (Metropolitan Archbishop)
└── Diocese (Diocesan Bishop)
    └── Region (Regional/Episcopal Vicar) [optional]
        └── Deanery (Dean/Vicar Forane)
            └── Parish (Pastor)
                └── Mission (Chaplain) [if under a parish]
```

### Religious House Types

| Type | Description |
|------|-------------|
| monastery | Monks (Benedictines, Cistercians, Trappists, etc.) |
| abbey | Monastery headed by an abbot |
| priory | Monastery headed by a prior |
| convent | Community of religious women (nuns or sisters) |
| friary | Franciscan or mendicant community |
| hermitage | Small community or individual hermit dwellings |
| motherhouse | Headquarters of a religious congregation |
| provincial_house | Provincial headquarters of a religious order |
| formation_house | House for novices/those in formation |
| retreat_house | Religious community focused on retreats |

### Mission Types

| Type | Description |
|------|-------------|
| mission_church | Mission church (worship site) |
| chapel | Chapel |
| oratory | Public or semi-public oratory |
| campus_chapel | University/college chapel |
| hospital_chapel | Hospital chapel |
| prison_chapel | Prison/correctional facility chapel |
| military_chapel | Military chapel |
| shrine | Shrine or pilgrimage site |
| monastery_chapel | Monastery or convent chapel |

### Organization Types

| Type | Description |
|------|-------------|
| catholic_charities | Catholic Charities organizations |
| hospital | Catholic hospitals |
| healthcare_system | Healthcare networks |
| svdp | St. Vincent de Paul conferences |
| usccb | USCCB (United States Conference of Catholic Bishops) |
| usccb_committee | USCCB committees and subcommittees |
| roman_curia | Roman Curia dicasteries |
| pontifical_council | Pontifical councils |
| religious_congregation | Religious orders/congregations |
| seminary | Seminaries |
| catholic_university | Catholic universities |

### Organization Scopes

| Scope | Description |
|-------|-------------|
| parish | Parish-level organization |
| diocesan | Diocese-level organization |
| provincial | Province-level organization |
| national | National organization (e.g., USCCB) |
| international | International organization (e.g., Roman Curia) |

### Contact Roles

Contacts can have various roles including:
- **Church Leadership**: pope, cardinal, archbishop, metropolitan, bishop, auxiliary_bishop, coadjutor_bishop, bishop_emeritus
- **Diocesan**: vicar_general, chancellor, vice_chancellor, episcopal_vicar, regional_vicar, judicial_vicar
- **Deanery/Region**: dean, vicar_forane
- **Parish**: pastor, parochial_vicar, parochial_administrator, deacon, pastoral_associate
- **Mission**: chaplain, mission_administrator
- **School**: superintendent, associate_superintendent, principal, assistant_principal, teacher, counselor, school_nurse, librarian, athletic_director
- **Religious House**: abbot, abbess, prior, prioress, guardian, mother_superior, novice_director, formation_director, vocation_director
- **Seminary**: rector, vice_rector, spiritual_director, academic_dean
- **Organization**: executive_director, president, vice_president, ceo, cfo, coo, administrator
- **Ministry**: director, associate_director, assistant_director, coordinator, associate_coordinator
- **USCCB**: usccb_president, usccb_vice_president, usccb_committee_chair, usccb_committee_member
- **Roman Curia**: prefect, secretary, undersecretary, nuncio, apostolic_nuncio
- **General**: staff, volunteer, board_member, board_chair, trustee, consultant

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
│   │   ├── deaneries.ts
│   │   ├── parishes.ts
│   │   ├── schools.ts
│   │   ├── organizations.ts
│   │   ├── apostolates.ts
│   │   ├── offices.ts
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
- [ ] Ministry certification tracking
- [ ] Safe environment training compliance
- [ ] Event and calendar management
