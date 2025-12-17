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
  suffraganDioceseIds: string[]; // List of diocese IDs in the province
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
  // Sacred site designation - a parish can also be a special sacred site
  sacredSiteDesignation?: SacredSiteDesignation;
  isCathedral?: boolean; // Is this the cathedral parish of the diocese
  isBasilica?: boolean; // Designated as a minor or major basilica
  isShrine?: boolean; // Designated as a shrine
  isPilgrimageSite?: boolean; // Notable pilgrimage destination
  basilicaType?: BasilicaType;
  shrineType?: ShrineType;
}

export type SacredSiteDesignation =
  | 'cathedral' // Cathedral church of the diocese
  | 'co_cathedral' // Co-cathedral
  | 'minor_basilica' // Minor basilica designation from Holy See
  | 'major_basilica' // Major basilica (only 4 in Rome)
  | 'national_shrine' // National shrine
  | 'diocesan_shrine' // Diocesan shrine
  | 'pilgrimage_site'; // Major pilgrimage destination

export type BasilicaType =
  | 'major' // Only 4 major basilicas in Rome
  | 'minor'; // Minor basilicas designated by the Pope

export type ShrineType =
  | 'national' // National shrine
  | 'diocesan' // Diocesan shrine
  | 'international'; // International shrine

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
  // Sacred site designation for non-parish shrines/pilgrimage sites in Mission
  isSacredSite?: boolean;
  sacredSiteDesignation?: SacredSiteDesignation;
  shrineType?: ShrineType;
  isPilgrimageSite?: boolean;
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
  | 'pilgrimage_site' // Dedicated pilgrimage site
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
  // Leadership and staff (Contact IDs)
  principal?: string;
  assistantPrincipal?: string;
  // Faculty tracking
  teachers?: string[]; // Array of Contact IDs for teachers
  teacherCount?: number; // Total number of teachers (for tracking even without individual contacts)
  // Support staff
  counselors?: string[]; // Array of Contact IDs for counselors
  counselorCount?: number; // Total number of counselors
  // Additional fields
  enrollment?: number;
  established?: Date;
  accreditation?: string;
  grades?: string; // e.g., "K-8", "9-12", "PreK-8"
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
  // SVDP-specific hierarchy fields
  svdpLevel?: SVDPLevel; // Level in the SVDP hierarchy
  // Program areas for Catholic Charities and SVDP
  programs?: OrganizationProgram[];
}

/**
 * Organization Program
 * Represents a program area within Catholic Charities, SVDP, or similar organizations
 */
export interface OrganizationProgram {
  id: string;
  name: string;
  description?: string;
  directorId?: string; // Contact ID for program director
}

export type OrganizationScope =
  | 'parish' // Parish-level organization
  | 'diocesan' // Diocese-level organization
  | 'provincial' // Province-level organization
  | 'regional' // Regional level (e.g., SVDP district)
  | 'national' // National organization (e.g., USCCB)
  | 'international'; // International organization (e.g., Roman Curia, Vatican)

/**
 * SVDP organizational hierarchy:
 * National Council > Regional Council > District Council > Diocesan Council > Parish Conference
 */
export type SVDPLevel =
  | 'national_council' // National Council of the US
  | 'regional_council' // Regional Council (multi-state)
  | 'district_council' // District Council (within diocesan council)
  | 'diocesan_council' // Diocesan/Archdiocesan Council
  | 'conference'; // Parish-level Conference (the basic unit)

export type OrganizationType =
  | 'catholic_charities'
  | 'catholic_charities_program' // Program area within Catholic Charities
  | 'hospital'
  | 'healthcare_system'
  | 'svdp' // St. Vincent de Paul Society
  | 'svdp_conference' // SVDP Conference (parish level)
  | 'svdp_council' // SVDP Council (diocesan or district level)
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
 * Religious House Model
 * Represents monasteries, convents, friaries, hermitages and other religious communities
 */
export interface ReligiousHouse {
  id: string;
  name: string;
  dioceseId: string;
  type: ReligiousHouseType;
  religiousOrder: string; // Name of the religious order (e.g., "Benedictines", "Franciscans")
  religiousOrderAbbreviation?: string; // e.g., "OSB", "OFM", "OP"
  address: Address;
  phone?: string;
  email?: string;
  website?: string;
  // Leadership (Contact IDs)
  superior?: string; // Abbot, Abbess, Prior/Prioress, Guardian, etc.
  // Community info
  memberCount?: number;
  foundedDate?: Date;
  // Features
  hasChapel?: boolean;
  hasRetreatCenter?: boolean;
  hasGuestHouse?: boolean;
  acceptsVocations?: boolean;
  description?: string;
}

export type ReligiousHouseType =
  | 'monastery' // Monks (Benedictines, Cistercians, Trappists, etc.)
  | 'abbey' // Monastery headed by an abbot
  | 'priory' // Monastery headed by a prior
  | 'convent' // Community of religious women (nuns or sisters)
  | 'friary' // Franciscan or mendicant community
  | 'hermitage' // Small community or individual hermit dwellings
  | 'motherhouse' // Headquarters of a religious congregation
  | 'provincial_house' // Provincial headquarters of a religious order
  | 'formation_house' // House for novices/those in formation
  | 'retreat_house'; // Religious community focused on retreats

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
  // Youth and Young Adult Ministry
  | 'youth_ministry'
  | 'young_adult'
  | 'campus_ministry'
  // Evangelization and Catechesis
  | 'evangelization'
  | 'catechesis'
  | 'faith_formation'
  | 'adult_faith_formation'
  | 'children_faith_formation'
  | 'ocia_ministry' // Order of Christian Initiation of Adults
  | 'sacramental_preparation'
  // Marriage and Family
  | 'marriage_family'
  | 'marriage_preparation'
  | 'marriage_enrichment'
  | 'marital_healing'
  | 'divorce_ministry'
  | 'annulment_ministry' // Tribunal support
  // Pro-Life and Social Justice
  | 'pro_life'
  | 'respect_life'
  | 'social_justice'
  // Cultural Ministries
  | 'hispanic_ministry'
  | 'vietnamese_ministry'
  | 'black_catholic_ministry'
  | 'korean_ministry'
  | 'african_american_ministry'
  | 'asian_ministry'
  | 'multicultural_ministry'
  // Healthcare and Mental Health
  | 'hospital_ministry'
  | 'mental_health_ministry'
  | 'healthcare_ministry'
  // Disability and Accessibility
  | 'deaf_ministry'
  | 'blind_ministry'
  | 'neurodivergent_ministry'
  | 'disability_ministry'
  // Prison and Social Outreach
  | 'prison_ministry'
  | 'jail_ministry'
  | 'homeless_ministry'
  | 'poverty_outreach'
  // Liturgical and Prayer
  | 'liturgical'
  | 'music_ministry'
  | 'prayer_ministry'
  | 'charismatic'
  // Vocations
  | 'vocations'
  | 'seminary_support'
  // Missionary
  | 'missionary'
  // Lay Organizations
  | 'knights_of_columbus'
  | 'ladies_auxiliary'
  | 'altar_society'
  | 'holy_name_society'
  | 'legion_of_mary'
  // Other
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
  middleName?: string;
  suffix?: string; // Jr., Sr., III, etc.
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
  religiousHouseId?: string; // Link to monastery, convent, friary, etc.
  // External diocese tracking - for contacts from other dioceses
  homeDioceseId?: string; // The diocese where this contact is incardinated/belongs
  isExternalContact?: boolean; // True if contact is from another diocese
  // Contact info
  email?: string;
  phone?: string;
  cellPhone?: string;
  fax?: string;
  website?: string;
  address?: Address;
  mailingAddress?: Address;
  // Social media
  linkedIn?: string;
  twitter?: string;
  facebook?: string;
  // Professional info
  biography?: string;
  dateOfBirth?: Date;
  ordinationDate?: Date;
  appointmentDate?: Date;
  // Ministry status
  isClergy?: boolean;
  isReligious?: boolean; // Member of religious order
  isConsecrated?: boolean; // Consecrated person
  isSeminarian?: boolean;
  clergyType?: ClergyType;
  religiousOrder?: string; // Name of religious order if applicable
  religiousOrderAbbreviation?: string; // e.g., "OSB", "SJ", "OP"
  // Multiple positions - a contact can hold multiple roles
  additionalPositions?: ContactPosition[];
  // Status
  isActive?: boolean;
  isRetired?: boolean;
  notes?: string;
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
  religiousHouseId?: string; // Link to monastery, convent, friary, etc.
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
  | 'archbishop_emeritus'
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
  | 'vicar_for_clergy'
  // Tribunal roles
  | 'judicial_vicar'
  | 'adjutant_judicial_vicar'
  | 'promoter_of_justice'
  | 'defender_of_the_bond'
  | 'judge'
  | 'auditor'
  | 'notary'
  // Deanery/Region roles
  | 'dean'
  | 'vicar_forane'
  // Parish roles
  | 'pastor'
  | 'parochial_vicar'
  | 'assistant_priest'
  | 'parochial_administrator'
  | 'deacon'
  | 'permanent_deacon'
  | 'transitional_deacon'
  | 'retired_priest'
  | 'senior_priest'
  | 'priest_in_residence'
  | 'pastoral_associate'
  | 'pastoral_minister'
  | 'director_religious_education'
  | 'director_faith_formation'
  | 'music_director'
  | 'liturgist'
  | 'business_manager'
  | 'parish_secretary'
  | 'parish_outreach_director'
  | 'parish_outreach_coordinator'
  // Mission roles
  | 'chaplain'
  | 'hospital_chaplain'
  | 'prison_chaplain'
  | 'military_chaplain'
  | 'campus_chaplain'
  | 'mission_administrator'
  // School roles
  | 'superintendent'
  | 'associate_superintendent'
  | 'principal'
  | 'assistant_principal'
  | 'teacher'
  | 'counselor'
  | 'school_counselor'
  | 'school_nurse'
  | 'librarian'
  | 'athletic_director'
  // Religious house roles
  | 'abbot'
  | 'abbess'
  | 'prior'
  | 'prioress'
  | 'guardian' // Franciscan superior
  | 'mother_superior'
  | 'novice_director'
  | 'formation_director'
  | 'vocation_director'
  // Seminary roles
  | 'rector'
  | 'vice_rector'
  | 'spiritual_director'
  | 'academic_dean'
  | 'seminarian'
  // Ministry-specific roles
  | 'missionary'
  | 'evangelist'
  | 'catechist'
  | 'youth_minister'
  | 'young_adult_minister'
  | 'campus_minister'
  | 'marriage_preparation_coordinator'
  | 'ocia_director' // OCIA (Order of Christian Initiation of Adults)
  | 'ocia_coordinator'
  | 'faith_formation_director'
  | 'adult_faith_formation_director'
  | 'children_faith_formation_director'
  | 'sacramental_preparation_coordinator'
  // Social services and counseling
  | 'therapist'
  | 'licensed_counselor'
  | 'social_worker'
  | 'case_manager'
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
  // SVDP specific roles (Society of St. Vincent de Paul)
  | 'svdp_national_president'
  | 'svdp_regional_president'
  | 'svdp_diocesan_president'
  | 'svdp_district_president'
  | 'svdp_conference_president'
  | 'svdp_executive_director'
  | 'svdp_spiritual_advisor'
  | 'svdp_treasurer'
  | 'svdp_secretary'
  // Catholic Charities specific roles
  | 'cc_executive_director'
  | 'cc_director_of_programs'
  | 'cc_program_director'
  | 'cc_case_manager'
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
  | 'program_director'
  | 'program_manager'
  | 'program_coordinator'
  | 'consecrated_person'
  | 'other';

export type ClergyType =
  | 'diocesan_priest'
  | 'religious_priest'
  | 'permanent_deacon'
  | 'transitional_deacon';
