import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { School } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/schools
 * Get all schools, with optional filtering
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, parishId, type } = req.query;

  let schools = dataStore.getAllSchools();

  if (dioceseId && typeof dioceseId === 'string') {
    schools = dataStore.getSchoolsByDiocese(dioceseId);
  }

  if (parishId && typeof parishId === 'string') {
    schools = schools.filter((s) => s.parishId === parishId);
  }

  if (type && typeof type === 'string') {
    schools = schools.filter((s) => s.type === type);
  }

  res.json({
    success: true,
    data: schools,
    count: schools.length,
  });
});

/**
 * GET /api/v1/schools/:id
 * Get a specific school by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const school = dataStore.getSchoolById(req.params.id);

  if (!school) {
    res.status(404).json({
      success: false,
      error: 'School not found',
    });
    return;
  }

  res.json({
    success: true,
    data: school,
  });
});

/**
 * POST /api/v1/schools
 * Create a new school
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, parishId, type, address, phone, email, website, principal, enrollment } =
    req.body;

  if (!name || !dioceseId || !type || !address) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, dioceseId, type, address',
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

  const school: School = {
    id: generateId(),
    name,
    dioceseId,
    parishId,
    type,
    address,
    phone,
    email,
    website,
    principal,
    enrollment,
  };

  const created = dataStore.createSchool(school);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/schools/:id
 * Update an existing school
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateSchool(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'School not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/schools/:id
 * Delete a school
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteSchool(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'School not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'School deleted successfully',
  });
});

/**
 * GET /api/v1/schools/:id/contacts
 * Get all contacts at a school
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const school = dataStore.getSchoolById(req.params.id);

  if (!school) {
    res.status(404).json({
      success: false,
      error: 'School not found',
    });
    return;
  }

  const contacts = dataStore.getContactsBySchool(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
