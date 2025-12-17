import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Organization } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/organizations
 * Get all organizations (Catholic Charities, hospitals, SVDP, USCCB, Roman Curia, etc.)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, type, scope } = req.query;

  let organizations = dataStore.getAllOrganizations();

  if (dioceseId && typeof dioceseId === 'string') {
    organizations = dataStore.getOrganizationsByDiocese(dioceseId);
  }

  if (type && typeof type === 'string') {
    organizations = organizations.filter((o) => o.type === type);
  }

  if (scope && typeof scope === 'string') {
    organizations = organizations.filter((o) => o.scope === scope);
  }

  res.json({
    success: true,
    data: organizations,
    count: organizations.length,
  });
});

/**
 * GET /api/v1/organizations/national
 * Get all national organizations (e.g., USCCB)
 */
router.get('/national', (_req: Request, res: Response) => {
  const organizations = dataStore.getNationalOrganizations();

  res.json({
    success: true,
    data: organizations,
    count: organizations.length,
  });
});

/**
 * GET /api/v1/organizations/international
 * Get all international organizations (e.g., Roman Curia)
 */
router.get('/international', (_req: Request, res: Response) => {
  const organizations = dataStore.getInternationalOrganizations();

  res.json({
    success: true,
    data: organizations,
    count: organizations.length,
  });
});

/**
 * GET /api/v1/organizations/:id
 * Get a specific organization by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const organization = dataStore.getOrganizationById(req.params.id);

  if (!organization) {
    res.status(404).json({
      success: false,
      error: 'Organization not found',
    });
    return;
  }

  res.json({
    success: true,
    data: organization,
  });
});

/**
 * POST /api/v1/organizations
 * Create a new organization
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, type, scope, address, phone, email, website, description, parentOrganizationId } = req.body;

  if (!name || !type || !scope) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, type, scope',
    });
    return;
  }

  // For diocesan/parish scope, require dioceseId
  if ((scope === 'diocesan' || scope === 'parish') && !dioceseId) {
    res.status(400).json({
      success: false,
      error: 'dioceseId is required for diocesan or parish scope organizations',
    });
    return;
  }

  // Verify diocese exists if provided
  if (dioceseId) {
    const diocese = dataStore.getDioceseById(dioceseId);
    if (!diocese) {
      res.status(400).json({
        success: false,
        error: 'Diocese not found',
      });
      return;
    }
  }

  const organization: Organization = {
    id: generateId(),
    name,
    dioceseId,
    type,
    scope,
    address,
    phone,
    email,
    website,
    description,
    parentOrganizationId,
  };

  const created = dataStore.createOrganization(organization);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/organizations/:id
 * Update an existing organization
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateOrganization(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Organization not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/organizations/:id
 * Delete an organization
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteOrganization(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Organization not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Organization deleted successfully',
  });
});

/**
 * GET /api/v1/organizations/:id/contacts
 * Get leadership and key contacts at an organization
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const organization = dataStore.getOrganizationById(req.params.id);

  if (!organization) {
    res.status(404).json({
      success: false,
      error: 'Organization not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByOrganization(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

/**
 * GET /api/v1/organizations/:id/positions
 * Get all positions held within an organization (for USCCB, Roman Curia tracking)
 */
router.get('/:id/positions', (req: Request, res: Response) => {
  const organization = dataStore.getOrganizationById(req.params.id);

  if (!organization) {
    res.status(404).json({
      success: false,
      error: 'Organization not found',
    });
    return;
  }

  const positions = dataStore.getPositionsByOrganization(req.params.id);

  res.json({
    success: true,
    data: positions,
    count: positions.length,
  });
});

export default router;
