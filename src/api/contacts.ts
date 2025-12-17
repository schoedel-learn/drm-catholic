import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Contact } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/contacts
 * Get all contacts
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, parishId, role } = req.query;

  let contacts = dataStore.getAllContacts();

  if (dioceseId && typeof dioceseId === 'string') {
    contacts = dataStore.getContactsByDiocese(dioceseId);
  }

  if (parishId && typeof parishId === 'string') {
    contacts = contacts.filter((c) => c.parishId === parishId);
  }

  if (role && typeof role === 'string') {
    contacts = contacts.filter((c) => c.role === role);
  }

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

/**
 * GET /api/v1/contacts/:id
 * Get a specific contact by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const contact = dataStore.getContactById(req.params.id);

  if (!contact) {
    res.status(404).json({
      success: false,
      error: 'Contact not found',
    });
    return;
  }

  res.json({
    success: true,
    data: contact,
  });
});

/**
 * POST /api/v1/contacts
 * Create a new contact
 */
router.post('/', (req: Request, res: Response) => {
  const { firstName, lastName, title, role, dioceseId, parishId, email, phone, notes } = req.body;

  if (!firstName || !lastName || !role) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: firstName, lastName, role',
    });
    return;
  }

  const contact: Contact = {
    id: generateId(),
    firstName,
    lastName,
    title,
    role,
    dioceseId,
    parishId,
    email,
    phone,
    notes,
  };

  const created = dataStore.createContact(contact);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/contacts/:id
 * Update an existing contact
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateContact(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Contact not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/contacts/:id
 * Delete a contact
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteContact(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Contact not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Contact deleted successfully',
  });
});

export default router;
