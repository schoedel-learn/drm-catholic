import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Apostolate } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/apostolates
 * Get all apostolates (ministry groups doing ministry in the name of the Church)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, parishId, type } = req.query;

  let apostolates = dataStore.getAllApostolates();

  if (dioceseId && typeof dioceseId === 'string') {
    apostolates = dataStore.getApostolatesByDiocese(dioceseId);
  }

  if (parishId && typeof parishId === 'string') {
    apostolates = apostolates.filter((a) => a.parishId === parishId);
  }

  if (type && typeof type === 'string') {
    apostolates = apostolates.filter((a) => a.type === type);
  }

  res.json({
    success: true,
    data: apostolates,
    count: apostolates.length,
  });
});

/**
 * GET /api/v1/apostolates/:id
 * Get a specific apostolate by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const apostolate = dataStore.getApostolateById(req.params.id);

  if (!apostolate) {
    res.status(404).json({
      success: false,
      error: 'Apostolate not found',
    });
    return;
  }

  res.json({
    success: true,
    data: apostolate,
  });
});

/**
 * POST /api/v1/apostolates
 * Create a new apostolate
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, parishId, type, description, website, email, phone, meetingSchedule } =
    req.body;

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

  const apostolate: Apostolate = {
    id: generateId(),
    name,
    dioceseId,
    parishId,
    type,
    description,
    website,
    email,
    phone,
    meetingSchedule,
  };

  const created = dataStore.createApostolate(apostolate);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/apostolates/:id
 * Update an existing apostolate
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateApostolate(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Apostolate not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/apostolates/:id
 * Delete an apostolate
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteApostolate(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Apostolate not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Apostolate deleted successfully',
  });
});

/**
 * GET /api/v1/apostolates/:id/contacts
 * Get contacts involved in an apostolate
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const apostolate = dataStore.getApostolateById(req.params.id);

  if (!apostolate) {
    res.status(404).json({
      success: false,
      error: 'Apostolate not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByApostolate(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
