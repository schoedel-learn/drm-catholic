import {
  Diocese,
  Parish,
  Contact,
  ContactPosition,
  School,
  Organization,
  Apostolate,
  DiocesanOffice,
  Deanery,
  Province,
  Region,
  Mission,
  ReligiousHouse,
} from '../models';

/**
 * In-memory data store for development
 * Will be replaced with actual database in production
 */
class DataStore {
  private dioceses: Map<string, Diocese> = new Map();
  private provinces: Map<string, Province> = new Map();
  private regions: Map<string, Region> = new Map();
  private deaneries: Map<string, Deanery> = new Map();
  private parishes: Map<string, Parish> = new Map();
  private missions: Map<string, Mission> = new Map();
  private schools: Map<string, School> = new Map();
  private organizations: Map<string, Organization> = new Map();
  private apostolates: Map<string, Apostolate> = new Map();
  private offices: Map<string, DiocesanOffice> = new Map();
  private contacts: Map<string, Contact> = new Map();
  private contactPositions: Map<string, ContactPosition> = new Map();
  private religiousHouses: Map<string, ReligiousHouse> = new Map();

  // Diocese operations
  getAllDioceses(): Diocese[] {
    return Array.from(this.dioceses.values());
  }

  getLocalDioceses(): Diocese[] {
    return this.getAllDioceses().filter((d) => !d.isExternal);
  }

  getExternalDioceses(): Diocese[] {
    return this.getAllDioceses().filter((d) => d.isExternal === true);
  }

  getDioceseById(id: string): Diocese | undefined {
    return this.dioceses.get(id);
  }

  getDiocesesByState(state: string): Diocese[] {
    return this.getAllDioceses().filter(
      (d) => d.state.toLowerCase() === state.toLowerCase()
    );
  }

  getArchdioceses(): Diocese[] {
    return this.getAllDioceses().filter((d) => d.type === 'archdiocese');
  }

  createDiocese(diocese: Diocese): Diocese {
    this.dioceses.set(diocese.id, diocese);
    return diocese;
  }

  updateDiocese(id: string, updates: Partial<Diocese>): Diocese | undefined {
    const existing = this.dioceses.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.dioceses.set(id, updated);
    return updated;
  }

  deleteDiocese(id: string): boolean {
    return this.dioceses.delete(id);
  }

  // Parish operations
  getAllParishes(): Parish[] {
    return Array.from(this.parishes.values());
  }

  getParishById(id: string): Parish | undefined {
    return this.parishes.get(id);
  }

  getParishesByDiocese(dioceseId: string): Parish[] {
    return this.getAllParishes().filter((p) => p.dioceseId === dioceseId);
  }

  createParish(parish: Parish): Parish {
    this.parishes.set(parish.id, parish);
    return parish;
  }

  updateParish(id: string, updates: Partial<Parish>): Parish | undefined {
    const existing = this.parishes.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.parishes.set(id, updated);
    return updated;
  }

  deleteParish(id: string): boolean {
    return this.parishes.delete(id);
  }

  // Contact operations
  getAllContacts(): Contact[] {
    return Array.from(this.contacts.values());
  }

  getContactById(id: string): Contact | undefined {
    return this.contacts.get(id);
  }

  getContactsByDiocese(dioceseId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.dioceseId === dioceseId);
  }

  getContactsByParish(parishId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.parishId === parishId);
  }

  getContactsBySchool(schoolId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.schoolId === schoolId);
  }

  getContactsByOrganization(organizationId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.organizationId === organizationId);
  }

  getContactsByApostolate(apostolateId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.apostolateId === apostolateId);
  }

  getContactsByOffice(officeId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.officeId === officeId);
  }

  createContact(contact: Contact): Contact {
    this.contacts.set(contact.id, contact);
    return contact;
  }

  updateContact(id: string, updates: Partial<Contact>): Contact | undefined {
    const existing = this.contacts.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.contacts.set(id, updated);
    return updated;
  }

  deleteContact(id: string): boolean {
    return this.contacts.delete(id);
  }

  // Deanery operations
  getAllDeaneries(): Deanery[] {
    return Array.from(this.deaneries.values());
  }

  getDeaneryById(id: string): Deanery | undefined {
    return this.deaneries.get(id);
  }

  getDeaneriesByDiocese(dioceseId: string): Deanery[] {
    return this.getAllDeaneries().filter((d) => d.dioceseId === dioceseId);
  }

  createDeanery(deanery: Deanery): Deanery {
    this.deaneries.set(deanery.id, deanery);
    return deanery;
  }

  updateDeanery(id: string, updates: Partial<Deanery>): Deanery | undefined {
    const existing = this.deaneries.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.deaneries.set(id, updated);
    return updated;
  }

  deleteDeanery(id: string): boolean {
    return this.deaneries.delete(id);
  }

  // School operations
  getAllSchools(): School[] {
    return Array.from(this.schools.values());
  }

  getSchoolById(id: string): School | undefined {
    return this.schools.get(id);
  }

  getSchoolsByDiocese(dioceseId: string): School[] {
    return this.getAllSchools().filter((s) => s.dioceseId === dioceseId);
  }

  getSchoolsByParish(parishId: string): School[] {
    return this.getAllSchools().filter((s) => s.parishId === parishId);
  }

  createSchool(school: School): School {
    this.schools.set(school.id, school);
    return school;
  }

  updateSchool(id: string, updates: Partial<School>): School | undefined {
    const existing = this.schools.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.schools.set(id, updated);
    return updated;
  }

  deleteSchool(id: string): boolean {
    return this.schools.delete(id);
  }

  // Organization operations
  getAllOrganizations(): Organization[] {
    return Array.from(this.organizations.values());
  }

  getOrganizationById(id: string): Organization | undefined {
    return this.organizations.get(id);
  }

  getOrganizationsByDiocese(dioceseId: string): Organization[] {
    return this.getAllOrganizations().filter((o) => o.dioceseId === dioceseId);
  }

  getOrganizationsByType(type: string): Organization[] {
    return this.getAllOrganizations().filter((o) => o.type === type);
  }

  createOrganization(organization: Organization): Organization {
    this.organizations.set(organization.id, organization);
    return organization;
  }

  updateOrganization(id: string, updates: Partial<Organization>): Organization | undefined {
    const existing = this.organizations.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.organizations.set(id, updated);
    return updated;
  }

  deleteOrganization(id: string): boolean {
    return this.organizations.delete(id);
  }

  // Apostolate operations
  getAllApostolates(): Apostolate[] {
    return Array.from(this.apostolates.values());
  }

  getApostolateById(id: string): Apostolate | undefined {
    return this.apostolates.get(id);
  }

  getApostolatesByDiocese(dioceseId: string): Apostolate[] {
    return this.getAllApostolates().filter((a) => a.dioceseId === dioceseId);
  }

  getApostolatesByParish(parishId: string): Apostolate[] {
    return this.getAllApostolates().filter((a) => a.parishId === parishId);
  }

  getApostolatesByType(type: string): Apostolate[] {
    return this.getAllApostolates().filter((a) => a.type === type);
  }

  createApostolate(apostolate: Apostolate): Apostolate {
    this.apostolates.set(apostolate.id, apostolate);
    return apostolate;
  }

  updateApostolate(id: string, updates: Partial<Apostolate>): Apostolate | undefined {
    const existing = this.apostolates.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.apostolates.set(id, updated);
    return updated;
  }

  deleteApostolate(id: string): boolean {
    return this.apostolates.delete(id);
  }

  // Diocesan Office operations
  getAllOffices(): DiocesanOffice[] {
    return Array.from(this.offices.values());
  }

  getOfficeById(id: string): DiocesanOffice | undefined {
    return this.offices.get(id);
  }

  getOfficesByDiocese(dioceseId: string): DiocesanOffice[] {
    return this.getAllOffices().filter((o) => o.dioceseId === dioceseId);
  }

  getOfficesByType(type: string): DiocesanOffice[] {
    return this.getAllOffices().filter((o) => o.type === type);
  }

  createOffice(office: DiocesanOffice): DiocesanOffice {
    this.offices.set(office.id, office);
    return office;
  }

  updateOffice(id: string, updates: Partial<DiocesanOffice>): DiocesanOffice | undefined {
    const existing = this.offices.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.offices.set(id, updated);
    return updated;
  }

  deleteOffice(id: string): boolean {
    return this.offices.delete(id);
  }

  // Province operations
  getAllProvinces(): Province[] {
    return Array.from(this.provinces.values());
  }

  getProvinceById(id: string): Province | undefined {
    return this.provinces.get(id);
  }

  createProvince(province: Province): Province {
    this.provinces.set(province.id, province);
    return province;
  }

  updateProvince(id: string, updates: Partial<Province>): Province | undefined {
    const existing = this.provinces.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.provinces.set(id, updated);
    return updated;
  }

  deleteProvince(id: string): boolean {
    return this.provinces.delete(id);
  }

  // Contact Position operations (for tracking multiple roles)
  getAllContactPositions(): ContactPosition[] {
    return Array.from(this.contactPositions.values());
  }

  getContactPositionById(id: string): ContactPosition | undefined {
    return this.contactPositions.get(id);
  }

  getPositionsByContact(contactId: string): ContactPosition[] {
    return this.getAllContactPositions().filter((p) => p.contactId === contactId);
  }

  getPositionsByOrganization(organizationId: string): ContactPosition[] {
    return this.getAllContactPositions().filter((p) => p.organizationId === organizationId);
  }

  getPositionsByDiocese(dioceseId: string): ContactPosition[] {
    return this.getAllContactPositions().filter((p) => p.dioceseId === dioceseId);
  }

  createContactPosition(position: ContactPosition): ContactPosition {
    this.contactPositions.set(position.id, position);
    return position;
  }

  updateContactPosition(id: string, updates: Partial<ContactPosition>): ContactPosition | undefined {
    const existing = this.contactPositions.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.contactPositions.set(id, updated);
    return updated;
  }

  deleteContactPosition(id: string): boolean {
    return this.contactPositions.delete(id);
  }

  // Extended organization queries
  getOrganizationsByScope(scope: string): Organization[] {
    return this.getAllOrganizations().filter((o) => o.scope === scope);
  }

  getNationalOrganizations(): Organization[] {
    return this.getOrganizationsByScope('national');
  }

  getInternationalOrganizations(): Organization[] {
    return this.getOrganizationsByScope('international');
  }

  // Extended contact queries
  getExternalContacts(): Contact[] {
    return this.getAllContacts().filter((c) => c.isExternalContact === true);
  }

  getContactsByHomeDiocese(homeDioceseId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.homeDioceseId === homeDioceseId);
  }

  getClergyContacts(): Contact[] {
    return this.getAllContacts().filter((c) => c.isClergy === true);
  }

  getReligiousContacts(): Contact[] {
    return this.getAllContacts().filter((c) => c.isReligious === true);
  }

  getContactsByRegion(regionId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.regionId === regionId);
  }

  getContactsByDeanery(deaneryId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.deaneryId === deaneryId);
  }

  getContactsByMission(missionId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.missionId === missionId);
  }

  // Region operations
  getAllRegions(): Region[] {
    return Array.from(this.regions.values());
  }

  getRegionById(id: string): Region | undefined {
    return this.regions.get(id);
  }

  getRegionsByDiocese(dioceseId: string): Region[] {
    return this.getAllRegions().filter((r) => r.dioceseId === dioceseId);
  }

  createRegion(region: Region): Region {
    this.regions.set(region.id, region);
    return region;
  }

  updateRegion(id: string, updates: Partial<Region>): Region | undefined {
    const existing = this.regions.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.regions.set(id, updated);
    return updated;
  }

  deleteRegion(id: string): boolean {
    return this.regions.delete(id);
  }

  // Mission operations
  getAllMissions(): Mission[] {
    return Array.from(this.missions.values());
  }

  getMissionById(id: string): Mission | undefined {
    return this.missions.get(id);
  }

  getMissionsByDiocese(dioceseId: string): Mission[] {
    return this.getAllMissions().filter((m) => m.dioceseId === dioceseId);
  }

  getMissionsByParish(parishId: string): Mission[] {
    return this.getAllMissions().filter((m) => m.parentParishId === parishId);
  }

  getMissionsByType(missionType: string): Mission[] {
    return this.getAllMissions().filter((m) => m.missionType === missionType);
  }

  createMission(mission: Mission): Mission {
    this.missions.set(mission.id, mission);
    return mission;
  }

  updateMission(id: string, updates: Partial<Mission>): Mission | undefined {
    const existing = this.missions.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.missions.set(id, updated);
    return updated;
  }

  deleteMission(id: string): boolean {
    return this.missions.delete(id);
  }

  // Religious House operations (monasteries, convents, friaries, etc.)
  getAllReligiousHouses(): ReligiousHouse[] {
    return Array.from(this.religiousHouses.values());
  }

  getReligiousHouseById(id: string): ReligiousHouse | undefined {
    return this.religiousHouses.get(id);
  }

  getReligiousHousesByDiocese(dioceseId: string): ReligiousHouse[] {
    return this.getAllReligiousHouses().filter((rh) => rh.dioceseId === dioceseId);
  }

  getReligiousHousesByType(type: string): ReligiousHouse[] {
    return this.getAllReligiousHouses().filter((rh) => rh.type === type);
  }

  getReligiousHousesByOrder(religiousOrder: string): ReligiousHouse[] {
    return this.getAllReligiousHouses().filter(
      (rh) => rh.religiousOrder.toLowerCase().includes(religiousOrder.toLowerCase())
    );
  }

  createReligiousHouse(religiousHouse: ReligiousHouse): ReligiousHouse {
    this.religiousHouses.set(religiousHouse.id, religiousHouse);
    return religiousHouse;
  }

  updateReligiousHouse(id: string, updates: Partial<ReligiousHouse>): ReligiousHouse | undefined {
    const existing = this.religiousHouses.get(id);
    if (!existing) return undefined;
    const updated = { ...existing, ...updates };
    this.religiousHouses.set(id, updated);
    return updated;
  }

  deleteReligiousHouse(id: string): boolean {
    return this.religiousHouses.delete(id);
  }

  getContactsByReligiousHouse(religiousHouseId: string): Contact[] {
    return this.getAllContacts().filter((c) => c.religiousHouseId === religiousHouseId);
  }

  // Utility methods
  clear(): void {
    this.dioceses.clear();
    this.provinces.clear();
    this.regions.clear();
    this.deaneries.clear();
    this.parishes.clear();
    this.missions.clear();
    this.schools.clear();
    this.organizations.clear();
    this.apostolates.clear();
    this.offices.clear();
    this.contacts.clear();
    this.contactPositions.clear();
    this.religiousHouses.clear();
  }
}

// Singleton instance
export const dataStore = new DataStore();
