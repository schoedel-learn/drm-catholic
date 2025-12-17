import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { ReligiousHouse } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/religious-houses
 * Get all religious houses (monasteries, convents, friaries, etc.)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, type, religiousOrder } = req.query;

  let religiousHouses = dataStore.getAllReligiousHouses();

  if (dioceseId && typeof dioceseId === 'string') {
    religiousHouses = dataStore.getReligiousHousesByDiocese(dioceseId);
  }

  if (type && typeof type === 'string') {
    religiousHouses = religiousHouses.filter((rh) => rh.type === type);
  }

  if (religiousOrder && typeof religiousOrder === 'string') {
    religiousHouses = religiousHouses.filter((rh) =>
      rh.religiousOrder.toLowerCase().includes(religiousOrder.toLowerCase())
    );
  }

  res.json({
    success: true,
    data: religiousHouses,
    count: religiousHouses.length,
  });
});

/**
 * GET /api/v1/religious-houses/:id
 * Get a specific religious house by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const religiousHouse = dataStore.getReligiousHouseById(req.params.id);

  if (!religiousHouse) {
    res.status(404).json({
      success: false,
      error: 'Religious house not found',
    });
    return;
  }

  res.json({
    success: true,
    data: religiousHouse,
  });
});

/**
 * POST /api/v1/religious-houses
 * Create a new religious house
 */
router.post('/', (req: Request, res: Response) => {
  const {
    name,
    dioceseId,
    type,
    religiousOrder,
    religiousOrderAbbreviation,
    address,
    phone,
    email,
    website,
    superior,
    memberCount,
    foundedDate,
    hasChapel,
    hasRetreatCenter,
    hasGuestHouse,
    acceptsVocations,
    description,
  } = req.body;

  if (!name || !dioceseId || !type || !religiousOrder || !address) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, dioceseId, type, religiousOrder, address',
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

  const religiousHouse: ReligiousHouse = {
    id: generateId(),
    name,
    dioceseId,
    type,
    religiousOrder,
    religiousOrderAbbreviation,
    address,
    phone,
    email,
    website,
    superior,
    memberCount,
    foundedDate: foundedDate ? new Date(foundedDate) : undefined,
    hasChapel,
    hasRetreatCenter,
    hasGuestHouse,
    acceptsVocations,
    description,
  };

  const created = dataStore.createReligiousHouse(religiousHouse);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/religious-houses/:id
 * Update an existing religious house
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateReligiousHouse(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Religious house not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/religious-houses/:id
 * Delete a religious house
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteReligiousHouse(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Religious house not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Religious house deleted successfully',
  });
});

/**
 * GET /api/v1/religious-houses/:id/contacts
 * Get contacts at a religious house
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const religiousHouse = dataStore.getReligiousHouseById(req.params.id);

  if (!religiousHouse) {
    res.status(404).json({
      success: false,
      error: 'Religious house not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByReligiousHouse(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
