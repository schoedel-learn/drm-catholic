import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { DiocesanOffice } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/offices
 * Get all diocesan offices/departments
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, type } = req.query;

  let offices = dataStore.getAllOffices();

  if (dioceseId && typeof dioceseId === 'string') {
    offices = dataStore.getOfficesByDiocese(dioceseId);
  }

  if (type && typeof type === 'string') {
    offices = offices.filter((o) => o.type === type);
  }

  res.json({
    success: true,
    data: offices,
    count: offices.length,
  });
});

/**
 * GET /api/v1/offices/:id
 * Get a specific office by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const office = dataStore.getOfficeById(req.params.id);

  if (!office) {
    res.status(404).json({
      success: false,
      error: 'Office not found',
    });
    return;
  }

  res.json({
    success: true,
    data: office,
  });
});

/**
 * POST /api/v1/offices
 * Create a new diocesan office
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, type, description, phone, email, address } = req.body;

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

  const office: DiocesanOffice = {
    id: generateId(),
    name,
    dioceseId,
    type,
    description,
    phone,
    email,
    address,
  };

  const created = dataStore.createOffice(office);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/offices/:id
 * Update an existing office
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateOffice(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Office not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/offices/:id
 * Delete an office
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteOffice(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Office not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Office deleted successfully',
  });
});

/**
 * GET /api/v1/offices/:id/contacts
 * Get staff at a diocesan office
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const office = dataStore.getOfficeById(req.params.id);

  if (!office) {
    res.status(404).json({
      success: false,
      error: 'Office not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByOffice(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
