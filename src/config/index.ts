/**
 * Application Configuration
 */
export interface AppConfig {
  port: number;
  environment: 'development' | 'production' | 'test';
  database: DatabaseConfig;
  api: ApiConfig;
}

export interface DatabaseConfig {
  host: string;
  port: number;
  name: string;
  user?: string;
  password?: string;
}

export interface ApiConfig {
  version: string;
  basePath: string;
}

/**
 * Default configuration values
 */
export const defaultConfig: AppConfig = {
  port: parseInt(process.env.PORT || '3000', 10),
  environment: (process.env.NODE_ENV as AppConfig['environment']) || 'development',
  database: {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT || '5432', 10),
    name: process.env.DB_NAME || 'drm_catholic',
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
  },
  api: {
    version: '1.0.0',
    basePath: '/api/v1',
  },
};

export function getConfig(): AppConfig {
  return defaultConfig;
}
