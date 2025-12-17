import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Deanery } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/deaneries
 * Get all deaneries (groupings of parishes)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId } = req.query;

  let deaneries = dataStore.getAllDeaneries();

  if (dioceseId && typeof dioceseId === 'string') {
    deaneries = dataStore.getDeaneriesByDiocese(dioceseId);
  }

  res.json({
    success: true,
    data: deaneries,
    count: deaneries.length,
  });
});

/**
 * GET /api/v1/deaneries/:id
 * Get a specific deanery by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const deanery = dataStore.getDeaneryById(req.params.id);

  if (!deanery) {
    res.status(404).json({
      success: false,
      error: 'Deanery not found',
    });
    return;
  }

  res.json({
    success: true,
    data: deanery,
  });
});

/**
 * POST /api/v1/deaneries
 * Create a new deanery
 */
router.post('/', (req: Request, res: Response) => {
  const { name, dioceseId, dean, description } = req.body;

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

  const deanery: Deanery = {
    id: generateId(),
    name,
    dioceseId,
    dean,
    description,
  };

  const created = dataStore.createDeanery(deanery);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/deaneries/:id
 * Update an existing deanery
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateDeanery(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Deanery not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/deaneries/:id
 * Delete a deanery
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteDeanery(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Deanery not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Deanery deleted successfully',
  });
});

/**
 * GET /api/v1/deaneries/:id/parishes
 * Get all parishes in a deanery
 */
router.get('/:id/parishes', (req: Request, res: Response) => {
  const deanery = dataStore.getDeaneryById(req.params.id);

  if (!deanery) {
    res.status(404).json({
      success: false,
      error: 'Deanery not found',
    });
    return;
  }

  // Filter parishes by deanery
  const parishes = dataStore.getAllParishes().filter((p) => p.deaneryId === req.params.id);

  res.json({
    success: true,
    data: parishes,
    count: parishes.length,
  });
});

export default router;
