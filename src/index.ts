import express, { Application, Request, Response } from 'express';
import { getConfig } from './config';
import { diocesesRouter, parishesRouter, contactsRouter } from './api';

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
app.use(`${config.api.basePath}/parishes`, parishesRouter);
app.use(`${config.api.basePath}/contacts`, contactsRouter);

// Root endpoint
app.get('/', (_req: Request, res: Response) => {
  res.json({
    name: 'Diocesan Relationship Manager',
    description: 'API for managing Catholic dioceses and archdioceses in the United States',
    version: config.api.version,
    endpoints: {
      health: '/health',
      dioceses: `${config.api.basePath}/dioceses`,
      parishes: `${config.api.basePath}/parishes`,
      contacts: `${config.api.basePath}/contacts`,
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
