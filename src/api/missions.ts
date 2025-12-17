import { Router, Request, Response } from 'express';
import { dataStore } from '../services';
import { Mission } from '../models';
import { generateId } from '../utils';

const router = Router();

/**
 * GET /api/v1/missions
 * Get all missions (mission churches, chapels, oratories, etc.)
 */
router.get('/', (req: Request, res: Response) => {
  const { dioceseId, parishId, missionType } = req.query;

  let missions = dataStore.getAllMissions();

  if (dioceseId && typeof dioceseId === 'string') {
    missions = dataStore.getMissionsByDiocese(dioceseId);
  }

  if (parishId && typeof parishId === 'string') {
    missions = missions.filter((m) => m.parentParishId === parishId);
  }

  if (missionType && typeof missionType === 'string') {
    missions = missions.filter((m) => m.missionType === missionType);
  }

  res.json({
    success: true,
    data: missions,
    count: missions.length,
  });
});

/**
 * GET /api/v1/missions/:id
 * Get a specific mission by ID
 */
router.get('/:id', (req: Request, res: Response) => {
  const mission = dataStore.getMissionById(req.params.id);

  if (!mission) {
    res.status(404).json({
      success: false,
      error: 'Mission not found',
    });
    return;
  }

  res.json({
    success: true,
    data: mission,
  });
});

/**
 * POST /api/v1/missions
 * Create a new mission
 */
router.post('/', (req: Request, res: Response) => {
  const {
    name,
    dioceseId,
    parentParishId,
    deaneryId,
    address,
    phone,
    email,
    missionType,
    massSchedule,
    chaplain,
    description,
  } = req.body;

  if (!name || !dioceseId || !missionType || !address) {
    res.status(400).json({
      success: false,
      error: 'Missing required fields: name, dioceseId, missionType, address',
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

  const mission: Mission = {
    id: generateId(),
    name,
    dioceseId,
    parentParishId,
    deaneryId,
    address,
    phone,
    email,
    missionType,
    massSchedule,
    chaplain,
    description,
  };

  const created = dataStore.createMission(mission);

  res.status(201).json({
    success: true,
    data: created,
  });
});

/**
 * PUT /api/v1/missions/:id
 * Update an existing mission
 */
router.put('/:id', (req: Request, res: Response) => {
  const updated = dataStore.updateMission(req.params.id, req.body);

  if (!updated) {
    res.status(404).json({
      success: false,
      error: 'Mission not found',
    });
    return;
  }

  res.json({
    success: true,
    data: updated,
  });
});

/**
 * DELETE /api/v1/missions/:id
 * Delete a mission
 */
router.delete('/:id', (req: Request, res: Response) => {
  const deleted = dataStore.deleteMission(req.params.id);

  if (!deleted) {
    res.status(404).json({
      success: false,
      error: 'Mission not found',
    });
    return;
  }

  res.json({
    success: true,
    message: 'Mission deleted successfully',
  });
});

/**
 * GET /api/v1/missions/:id/contacts
 * Get contacts at a mission
 */
router.get('/:id/contacts', (req: Request, res: Response) => {
  const mission = dataStore.getMissionById(req.params.id);

  if (!mission) {
    res.status(404).json({
      success: false,
      error: 'Mission not found',
    });
    return;
  }

  const contacts = dataStore.getContactsByMission(req.params.id);

  res.json({
    success: true,
    data: contacts,
    count: contacts.length,
  });
});

export default router;
