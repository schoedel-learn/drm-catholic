/**
 * Diocese Model
 * Represents a Catholic diocese in the United States
 */
export interface Diocese {
  id: string;
  name: string;
  type: 'diocese' | 'archdiocese';
  province: string;
  state: string;
  city: string;
  established: Date;
  bishop?: string;
  website?: string;
  email?: string;
  phone?: string;
  address?: Address;
  parishes?: number;
  catholics?: number;
}

export interface Address {
  street: string;
  city: string;
  state: string;
  zipCode: string;
  country: string;
}

/**
 * Parish Model
 * Represents a Catholic parish within a diocese
 */
export interface Parish {
  id: string;
  name: string;
  dioceseId: string;
  pastor?: string;
  address: Address;
  phone?: string;
  email?: string;
  website?: string;
  massSchedule?: MassSchedule[];
  established?: Date;
  parishioners?: number;
}

export interface MassSchedule {
  dayOfWeek: number; // 0 = Sunday, 6 = Saturday
  time: string;
  language: string;
}

/**
 * Contact Model
 * Represents a contact person within a diocese or parish
 */
export interface Contact {
  id: string;
  firstName: string;
  lastName: string;
  title?: string;
  role: ContactRole;
  dioceseId?: string;
  parishId?: string;
  email?: string;
  phone?: string;
  notes?: string;
}

export type ContactRole =
  | 'bishop'
  | 'auxiliary_bishop'
  | 'vicar_general'
  | 'chancellor'
  | 'pastor'
  | 'parochial_vicar'
  | 'deacon'
  | 'director'
  | 'staff'
  | 'other';
