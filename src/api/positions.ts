import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { ContactPosition } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/positions
 * Get all contact positions
 */
router.get('/', (req: Request, res: Response) => {
  const { contactId, dioceseId, organizationId } = req.query;

  let positions = dataStore.getAllContactPositions();

  if (contactId && typeof contactId === 'string') {
    positions = dataStore.getPositionsByContact(contactId);
  }

  if (dioceseId && typeof dioceseId === 'string') {
    positions = positions.filter((p) => p.dioceseId === dioceseId);
  }

  if (organizationId && typeof organizationId === 'string') {
    positions = positions.filter((p) => p.organizationId === organizationId);
  }

  res.json({
    success: true,
    data: positions,
    count: positions.length,
  });
});

/**
 * GET /api/v1/positions/:id
 * Get a specific position by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const position = dataStore.getContactPositionById(req.params.id);

  if (!position) {
    res.status(404).json({
      success: false,
      error: 'Position not found',
    });
    return;
  }

  res.json({
    success: true,
    data: position,
  });
});

/**
 * POST /api/v1/positions
 * Create a new contact position (for tracking multiple roles like USCCB, Roman Curia positions)
 */
router.post('/', (req: Request, res: Response) => {
  const {
    contactId,
    role,
    title,
    dioceseId,
    organizationId,
    officeId,
    apostolateId,
    parishId,
    schoolId,
    startDate,
    endDate,
    isPrimary,
    notes,
  } = req.body;

  if (!contactId || !role) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: contactId, role',
    });
    return;
  }

  // Verify contact exists
  const contact = dataStore.getContactById(contactId);
  if (!contact) {
    res.status(400).json({
      success: false,
      error: 'Contact not found',
    });
    return;
  }

  const position: ContactPosition = {
    id: generateId(),
    contactId,
    role,
    title,
    dioceseId,
    organizationId,
    officeId,
    apostolateId,
    parishId,
    schoolId,
    startDate: startDate ? new Date(startDate) : undefined,
    endDate: endDate ? new Date(endDate) : undefined,
    isPrimary,
    notes,
  };

  const created = dataStore.createContactPosition(position);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/positions/:id
 * Update an existing position
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateContactPosition(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Position not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/positions/:id
 * Delete a position
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteContactPosition(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Position not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Position deleted successfully',
  });
});

export default router;
