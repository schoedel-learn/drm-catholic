import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Diocese } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/dioceses
 * Get all dioceses, with optional filtering
 */
router.get('/', (req: Request, res: Response) => {
  const { state, type } = req.query;

  let dioceses = dataStore.getAllDioceses();

  if (state && typeof state === 'string') {
    dioceses = dataStore.getDiocesesByState(state);
  }

  if (type === 'archdiocese') {
    dioceses = dioceses.filter((d) => d.type === 'archdiocese');
  } else if (type === 'diocese') {
    dioceses = dioceses.filter((d) => d.type === 'diocese');
  }

  res.json({
    success: true,
    data: dioceses,
    count: dioceses.length,
  });
});

/**
 * GET /api/v1/dioceses/:id
 * Get a specific diocese by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const diocese = dataStore.getDioceseById(req.params.id);

  if (!diocese) {
    res.status(404).json({
      success: false,
      error: 'Diocese not found',
    });
    return;
  }

  res.json({
    success: true,
    data: diocese,
  });
});

/**
 * POST /api/v1/dioceses
 * Create a new diocese
 */
router.post('/', (req: Request, res: Response) => {
  const { name, type, province, state, city, established, bishop, website, email, phone, address } =
    req.body;

  if (!name || !type || !state || !city) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, type, state, city',
    });
    return;
  }

  const diocese: Diocese = {
    id: generateId(),
    name,
    type,
    province: province || '',
    state,
    city,
    established: established ? new Date(established) : new Date(),
    bishop,
    website,
    email,
    phone,
    address,
  };

  const created = dataStore.createDiocese(diocese);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/dioceses/:id
 * Update an existing diocese
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateDiocese(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Diocese not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/dioceses/:id
 * Delete a diocese
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteDiocese(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Diocese not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Diocese deleted successfully',
  });
});

/**
 * GET /api/v1/dioceses/:id/parishes
 * Get all parishes in a diocese
 */
router.get('/:id/parishes', (req: Request, res: Response) => {
  const diocese = dataStore.getDioceseById(req.params.id);

  if (!diocese) {
    res.status(404).json({
      success: false,
      error: 'Diocese not found',
    });
    return;
  }

  const parishes = dataStore.getParishesByDiocese(req.params.id);

  res.json({
    success: true,
    data: parishes,
    count: parishes.length,
  });
});

/**
 * GET /api/v1/dioceses/:id/contacts
 * Get all contacts in a diocese
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const diocese = dataStore.getDioceseById(req.params.id);

  if (!diocese) {
    res.status(404).json({
      success: false,
      error: 'Diocese not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByDiocese(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
