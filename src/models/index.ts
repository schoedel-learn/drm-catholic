/**
 * Common Address interface used across all entities
 */
export interface Address {
  street: string;
  city: string;
  state: string;
  zipCode: string;
  country: string;
}

/**
 * Diocese Model
 * Represents a Catholic diocese or archdiocese in the United States
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

/**
 * Deanery Model
 * Represents a group of parishes within a diocese for coordination
 */
export interface Deanery {
  id: string;
  name: string;
  dioceseId: string;
  dean?: string; // Contact ID of the dean
  description?: string;
}

/**
 * Parish Model
 * Represents a Catholic parish within a diocese
 */
export interface Parish {
  id: string;
  name: string;
  dioceseId: string;
  deaneryId?: string;
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
 * School Model
 * Represents a Catholic school within a diocese
 */
export interface School {
  id: string;
  name: string;
  dioceseId: string;
  parishId?: string; // Optional link to sponsoring parish
  type: SchoolType;
  address: Address;
  phone?: string;
  email?: string;
  website?: string;
  principal?: string;
  enrollment?: number;
  established?: Date;
  accreditation?: string;
}

export type SchoolType =
  | 'elementary'
  | 'middle'
  | 'high_school'
  | 'k8'
  | 'k12'
  | 'preschool';

/**
 * Organization Model
 * Represents Catholic organizations (Catholic Charities, hospitals, SVDP, etc.)
 */
export interface Organization {
  id: string;
  name: string;
  dioceseId: string;
  type: OrganizationType;
  address?: Address;
  phone?: string;
  email?: string;
  website?: string;
  description?: string;
  established?: Date;
}

export type OrganizationType =
  | 'catholic_charities'
  | 'hospital'
  | 'healthcare_system'
  | 'svdp' // St. Vincent de Paul
  | 'social_services'
  | 'retreat_center'
  | 'cemetery'
  | 'foundation'
  | 'media'
  | 'other';

/**
 * Apostolate Model
 * Represents ministry groups and lay apostolates doing ministry in the name of the Church
 */
export interface Apostolate {
  id: string;
  name: string;
  dioceseId: string;
  parishId?: string; // If parish-based apostolate
  type: ApostolateType;
  description?: string;
  website?: string;
  email?: string;
  phone?: string;
  meetingSchedule?: string;
}

export type ApostolateType =
  | 'youth_ministry'
  | 'young_adult'
  | 'campus_ministry'
  | 'pro_life'
  | 'evangelization'
  | 'catechesis'
  | 'liturgical'
  | 'social_justice'
  | 'hispanic_ministry'
  | 'african_american_ministry'
  | 'asian_ministry'
  | 'prison_ministry'
  | 'hospital_ministry'
  | 'respect_life'
  | 'marriage_family'
  | 'vocations'
  | 'missionary'
  | 'charismatic'
  | 'knights_of_columbus'
  | 'ladies_auxiliary'
  | 'other';

/**
 * Diocesan Office Model
 * Represents administrative offices/departments within the diocesan curia
 */
export interface DiocesanOffice {
  id: string;
  name: string;
  dioceseId: string;
  type: OfficeType;
  description?: string;
  phone?: string;
  email?: string;
  address?: Address;
}

export type OfficeType =
  | 'chancery'
  | 'tribunal'
  | 'finance'
  | 'human_resources'
  | 'communications'
  | 'education'
  | 'worship'
  | 'evangelization'
  | 'vocations'
  | 'youth'
  | 'family_life'
  | 'social_concerns'
  | 'property_facilities'
  | 'archives'
  | 'stewardship'
  | 'development'
  | 'other';

/**
 * Contact Model
 * Represents a contact person within any diocesan entity
 */
export interface Contact {
  id: string;
  firstName: string;
  lastName: string;
  title?: string;
  role: ContactRole;
  // Entity associations - contact can be linked to multiple entities
  dioceseId?: string;
  parishId?: string;
  schoolId?: string;
  organizationId?: string;
  apostolateId?: string;
  officeId?: string;
  // Contact info
  email?: string;
  phone?: string;
  notes?: string;
  // Ministry status
  isClergy?: boolean;
  isReligious?: boolean; // Member of religious order
  clergyType?: ClergyType;
}

export type ContactRole =
  // Diocesan leadership
  | 'bishop'
  | 'archbishop'
  | 'auxiliary_bishop'
  | 'vicar_general'
  | 'chancellor'
  | 'episcopal_vicar'
  | 'judicial_vicar'
  // Parish roles
  | 'pastor'
  | 'parochial_vicar'
  | 'deacon'
  | 'pastoral_associate'
  | 'director_religious_education'
  // School roles
  | 'superintendent'
  | 'principal'
  | 'assistant_principal'
  | 'teacher'
  // Organization roles
  | 'executive_director'
  | 'president'
  | 'ceo'
  | 'administrator'
  // General roles
  | 'director'
  | 'coordinator'
  | 'staff'
  | 'volunteer'
  | 'board_member'
  | 'other';

export type ClergyType =
  | 'diocesan_priest'
  | 'religious_priest'
  | 'permanent_deacon'
  | 'transitional_deacon';
