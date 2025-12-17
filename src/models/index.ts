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
 * Also used to track external dioceses for cross-diocesan contact management
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
  isExternal?: boolean; // True if this is an external diocese being tracked
}

/**
 * Province Model
 * Represents an ecclesiastical province (grouping of dioceses under a metropolitan archbishop)
 */
export interface Province {
  id: string;
  name: string;
  metropolitanDioceseId: string; // The archdiocese that leads the province
  sufFraganDioceseIds: string[]; // List of diocese IDs in the province
  metropolitanId?: string; // Contact ID of the metropolitan archbishop
}

/**
 * Region Model
 * Represents a region within a diocese (grouping of deaneries or parishes)
 * Some dioceses organize parishes into regions for administrative purposes
 */
export interface Region {
  id: string;
  name: string;
  dioceseId: string;
  vicarId?: string; // Contact ID of the regional/episcopal vicar
  deaneryIds?: string[]; // Deaneries in this region (if organized by deanery)
  description?: string;
}

/**
 * Deanery Model
 * Represents a group of parishes within a diocese for coordination
 */
export interface Deanery {
  id: string;
  name: string;
  dioceseId: string;
  regionId?: string; // Optional link to region if diocese uses regions
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
  regionId?: string; // Optional direct link to region
  pastor?: string;
  address: Address;
  phone?: string;
  email?: string;
  website?: string;
  massSchedule?: MassSchedule[];
  established?: Date;
  parishioners?: number;
  canonicalStatus?: ParishCanonicalStatus;
}

export type ParishCanonicalStatus =
  | 'parish' // Full canonical parish
  | 'quasi_parish' // Quasi-parish (mission in formation)
  | 'personal_parish' // Personal parish (non-territorial)
  | 'national_parish'; // National/ethnic parish

/**
 * Mission Model
 * Represents a mission church or chapel (under a parish or directly under diocese)
 * Missions are worship sites that are not full parishes
 */
export interface Mission {
  id: string;
  name: string;
  dioceseId: string;
  parentParishId?: string; // The parish this mission belongs to
  deaneryId?: string;
  address: Address;
  phone?: string;
  email?: string;
  missionType: MissionType;
  massSchedule?: MassSchedule[];
  established?: Date;
  chaplain?: string; // Contact ID of assigned priest/chaplain
  description?: string;
}

export type MissionType =
  | 'mission_church' // Mission church (worship site)
  | 'chapel' // Chapel
  | 'oratory' // Public or semi-public oratory
  | 'campus_chapel' // University/college chapel
  | 'hospital_chapel' // Hospital chapel
  | 'prison_chapel' // Prison/correctional facility chapel
  | 'military_chapel' // Military chapel
  | 'shrine' // Shrine or pilgrimage site
  | 'monastery_chapel'; // Monastery or convent chapel

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
 * Represents Catholic organizations (Catholic Charities, hospitals, SVDP, USCCB, Roman Curia, etc.)
 */
export interface Organization {
  id: string;
  name: string;
  dioceseId?: string; // Optional - null for national/international organizations like USCCB
  type: OrganizationType;
  scope: OrganizationScope;
  address?: Address;
  phone?: string;
  email?: string;
  website?: string;
  description?: string;
  established?: Date;
  parentOrganizationId?: string; // For hierarchical organizations
}

export type OrganizationScope =
  | 'parish' // Parish-level organization
  | 'diocesan' // Diocese-level organization
  | 'provincial' // Province-level organization
  | 'national' // National organization (e.g., USCCB)
  | 'international'; // International organization (e.g., Roman Curia, Vatican)

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
  | 'usccb' // United States Conference of Catholic Bishops
  | 'usccb_committee' // USCCB Committees and Subcommittees
  | 'roman_curia' // Roman Curia (Vatican dicasteries)
  | 'pontifical_council' // Pontifical councils
  | 'religious_congregation' // Religious orders/congregations
  | 'catholic_university'
  | 'seminary'
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
 * Supports tracking contacts from external dioceses and their positions in various organizations
 */
export interface Contact {
  id: string;
  firstName: string;
  lastName: string;
  title?: string;
  role: ContactRole;
  // Primary entity associations - contact's main position
  dioceseId?: string;
  regionId?: string;
  deaneryId?: string;
  parishId?: string;
  missionId?: string;
  schoolId?: string;
  organizationId?: string;
  apostolateId?: string;
  officeId?: string;
  // External diocese tracking - for contacts from other dioceses
  homeDioceseId?: string; // The diocese where this contact is incardinated/belongs
  isExternalContact?: boolean; // True if contact is from another diocese
  // Contact info
  email?: string;
  phone?: string;
  notes?: string;
  // Ministry status
  isClergy?: boolean;
  isReligious?: boolean; // Member of religious order
  clergyType?: ClergyType;
  religiousOrder?: string; // Name of religious order if applicable
  // Multiple positions - a contact can hold multiple roles
  additionalPositions?: ContactPosition[];
}

/**
 * ContactPosition Model
 * Represents an additional position/role held by a contact
 * Allows tracking of positions in USCCB, Roman Curia, other dioceses, etc.
 */
export interface ContactPosition {
  id: string;
  contactId: string;
  role: ContactRole;
  title?: string;
  // The entity where this position is held
  dioceseId?: string;
  organizationId?: string;
  officeId?: string;
  apostolateId?: string;
  parishId?: string;
  schoolId?: string;
  regionId?: string;
  deaneryId?: string;
  missionId?: string;
  // Position details
  startDate?: Date;
  endDate?: Date;
  isPrimary?: boolean;
  notes?: string;
}

export type ContactRole =
  // Diocesan/Church leadership
  | 'pope'
  | 'cardinal'
  | 'archbishop'
  | 'metropolitan'
  | 'bishop'
  | 'auxiliary_bishop'
  | 'bishop_emeritus'
  | 'coadjutor_bishop'
  | 'vicar_general'
  | 'chancellor'
  | 'vice_chancellor'
  | 'episcopal_vicar'
  | 'regional_vicar'
  | 'judicial_vicar'
  | 'adjutant_judicial_vicar'
  | 'promoter_of_justice'
  | 'defender_of_the_bond'
  // Deanery/Region roles
  | 'dean'
  | 'vicar_forane'
  // Parish roles
  | 'pastor'
  | 'parochial_vicar'
  | 'parochial_administrator'
  | 'deacon'
  | 'pastoral_associate'
  | 'director_religious_education'
  | 'music_director'
  | 'business_manager'
  // Mission roles
  | 'chaplain'
  | 'mission_administrator'
  // School roles
  | 'superintendent'
  | 'associate_superintendent'
  | 'principal'
  | 'assistant_principal'
  | 'teacher'
  // Organization/Office roles
  | 'executive_director'
  | 'president'
  | 'vice_president'
  | 'ceo'
  | 'cfo'
  | 'coo'
  | 'administrator'
  | 'director'
  | 'associate_director'
  | 'assistant_director'
  | 'coordinator'
  | 'associate_coordinator'
  | 'manager'
  | 'secretary'
  | 'moderator'
  // USCCB specific roles
  | 'usccb_president'
  | 'usccb_vice_president'
  | 'usccb_treasurer'
  | 'usccb_secretary'
  | 'usccb_committee_chair'
  | 'usccb_committee_member'
  // Roman Curia roles
  | 'prefect'
  | 'secretary'
  | 'undersecretary'
  | 'nuncio'
  | 'apostolic_nuncio'
  // General roles
  | 'staff'
  | 'volunteer'
  | 'board_member'
  | 'board_chair'
  | 'trustee'
  | 'consultant'
  | 'other';

export type ClergyType =
  | 'diocesan_priest'
  | 'religious_priest'
  | 'permanent_deacon'
  | 'transitional_deacon';
