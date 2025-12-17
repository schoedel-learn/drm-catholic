import express, { Application, Request, Response } from 'express';
import { getConfig } from './config';
import {
  diocesesRouter,
  regionsRouter,
  deaneriesRouter,
  parishesRouter,
  missionsRouter,
  schoolsRouter,
  organizationsRouter,
  religiousHousesRouter,
  apostolatesRouter,
  officesRouter,
  contactsRouter,
  positionsRouter,
} from './api';

const app: Application = express();
const config = getConfig();

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Health check endpoint
app.get('/health', (_req: Request, res: Response) => {
  res.json({
    status: 'healthy',
    version: config.api.version,
    timestamp: new Date().toISOString(),
  });
});

// API Routes
app.use(`${config.api.basePath}/dioceses`, diocesesRouter);
app.use(`${config.api.basePath}/regions`, regionsRouter);
app.use(`${config.api.basePath}/deaneries`, deaneriesRouter);
app.use(`${config.api.basePath}/parishes`, parishesRouter);
app.use(`${config.api.basePath}/missions`, missionsRouter);
app.use(`${config.api.basePath}/schools`, schoolsRouter);
app.use(`${config.api.basePath}/organizations`, organizationsRouter);
app.use(`${config.api.basePath}/religious-houses`, religiousHousesRouter);
app.use(`${config.api.basePath}/apostolates`, apostolatesRouter);
app.use(`${config.api.basePath}/offices`, officesRouter);
app.use(`${config.api.basePath}/contacts`, contactsRouter);
app.use(`${config.api.basePath}/positions`, positionsRouter);

// Root endpoint
app.get('/', (_req: Request, res: Response) => {
  res.json({
    name: 'Diocesan Relationship Manager',
    description: 'API for managing Catholic dioceses and archdioceses in the United States',
    version: config.api.version,
    endpoints: {
      health: '/health',
      dioceses: `${config.api.basePath}/dioceses`,
      regions: `${config.api.basePath}/regions`,
      deaneries: `${config.api.basePath}/deaneries`,
      parishes: `${config.api.basePath}/parishes`,
      missions: `${config.api.basePath}/missions`,
      schools: `${config.api.basePath}/schools`,
      organizations: `${config.api.basePath}/organizations`,
      religiousHouses: `${config.api.basePath}/religious-houses`,
      apostolates: `${config.api.basePath}/apostolates`,
      offices: `${config.api.basePath}/offices`,
      contacts: `${config.api.basePath}/contacts`,
      positions: `${config.api.basePath}/positions`,
    },
  });
});

// 404 handler
app.use((_req: Request, res: Response) => {
  res.status(404).json({
    success: false,
    error: 'Endpoint not found',
  });
});

// Start server (only if not in test mode)
if (process.env.NODE_ENV !== 'test') {
  app.listen(config.port, () => {
    console.log(`🏛️  Diocesan Relationship Manager API`);
    console.log(`📍 Running on http://localhost:${config.port}`);
    console.log(`📚 API Base: ${config.api.basePath}`);
    console.log(`🌍 Environment: ${config.environment}`);
  });
}

export default app;
