import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Parish } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/parishes
 * Get all parishes
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId } = req.query;

  let parishes = dataStore.getAllParishes();

  if (dioceseId && typeof dioceseId === 'string') {
    parishes = dataStore.getParishesByDiocese(dioceseId);
  }

  res.json({
    success: true,
    data: parishes,
    count: parishes.length,
  });
});

/**
 * GET /api/v1/parishes/:id
 * Get a specific parish by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const parish = dataStore.getParishById(req.params.id);

  if (!parish) {
    res.status(404).json({
      success: false,
      error: 'Parish not found',
    });
    return;
  }

  res.json({
    success: true,
    data: parish,
  });
});

/**
 * POST /api/v1/parishes
 * Create a new parish
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, pastor, address, phone, email, website, massSchedule } = req.body;

  if (!name || !dioceseId || !address) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, dioceseId, address',
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

  const parish: Parish = {
    id: generateId(),
    name,
    dioceseId,
    pastor,
    address,
    phone,
    email,
    website,
    massSchedule,
  };

  const created = dataStore.createParish(parish);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/parishes/:id
 * Update an existing parish
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateParish(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Parish not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/parishes/:id
 * Delete a parish
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteParish(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Parish not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Parish deleted successfully',
  });
});

/**
 * GET /api/v1/parishes/:id/contacts
 * Get all contacts in a parish
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const parish = dataStore.getParishById(req.params.id);

  if (!parish) {
    res.status(404).json({
      success: false,
      error: 'Parish not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByParish(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
