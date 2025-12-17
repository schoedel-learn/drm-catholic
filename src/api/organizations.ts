import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Organization } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/organizations
 * Get all organizations (Catholic Charities, hospitals, SVDP, etc.)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, type } = req.query;

  let organizations = dataStore.getAllOrganizations();

  if (dioceseId && typeof dioceseId === 'string') {
    organizations = dataStore.getOrganizationsByDiocese(dioceseId);
  }

  if (type && typeof type === 'string') {
    organizations = organizations.filter((o) => o.type === type);
  }

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
  const { name, dioceseId, type, address, phone, email, website, description } = req.body;

  if (!name || !dioceseId || !type) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, dioceseId, type',
    });
    return;
  }

  // Verify diocese exists
  const diocese = dataStore.getDioceseById(dioceseId);
  if (!diocese) {
    res.status(400).json({
      success: false,
      error: 'Diocese not found',
    });
    return;
  }

  const organization: Organization = {
    id: generateId(),
    name,
    dioceseId,
    type,
    address,
    phone,
    email,
    website,
    description,
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

export default router;
