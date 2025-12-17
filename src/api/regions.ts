import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Region } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/regions
 * Get all regions (groupings of deaneries/parishes within a diocese)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId } = req.query;

  let regions = dataStore.getAllRegions();

  if (dioceseId && typeof dioceseId === 'string') {
    regions = dataStore.getRegionsByDiocese(dioceseId);
  }

  res.json({
    success: true,
    data: regions,
    count: regions.length,
  });
});

/**
 * GET /api/v1/regions/:id
 * Get a specific region by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const region = dataStore.getRegionById(req.params.id);

  if (!region) {
    res.status(404).json({
      success: false,
      error: 'Region not found',
    });
    return;
  }

  res.json({
    success: true,
    data: region,
  });
});

/**
 * POST /api/v1/regions
 * Create a new region
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, vicarId, deaneryIds, description } = req.body;

  if (!name || !dioceseId) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, dioceseId',
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

  const region: Region = {
    id: generateId(),
    name,
    dioceseId,
    vicarId,
    deaneryIds,
    description,
  };

  const created = dataStore.createRegion(region);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/regions/:id
 * Update an existing region
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateRegion(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Region not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/regions/:id
 * Delete a region
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteRegion(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Region not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Region deleted successfully',
  });
});

/**
 * GET /api/v1/regions/:id/deaneries
 * Get all deaneries in a region
 */
router.get('/:id/deaneries', (req: Request, res: Response) => {
  const region = dataStore.getRegionById(req.params.id);

  if (!region) {
    res.status(404).json({
      success: false,
      error: 'Region not found',
    });
    return;
  }

  // Get deaneries that belong to this region
  const deaneries = dataStore.getAllDeaneries().filter((d) => d.regionId === req.params.id);

  res.json({
    success: true,
    data: deaneries,
    count: deaneries.length,
  });
});

/**
 * GET /api/v1/regions/:id/contacts
 * Get contacts in a region
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const region = dataStore.getRegionById(req.params.id);

  if (!region) {
    res.status(404).json({
      success: false,
      error: 'Region not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByRegion(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
