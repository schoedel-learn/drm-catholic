import { dataStore } from '../services';
import { Diocese, Parish, Contact, ContactPosition, School, Organization, Apostolate, DiocesanOffice, Deanery } from '../models';
import { generateId } from '../utils';

describe('DataStore', () => {
  beforeEach(() => {
    dataStore.clear();
  });

  describe('Diocese Operations', () => {
    const testDiocese: Diocese = {
      id: 'test-1',
      name: 'Diocese of Test City',
      type: 'diocese',
      province: 'Test Province',
      state: 'TX',
      city: 'Test City',
      established: new Date('1900-01-01'),
    };

    it('should create a diocese', () => {
      const created = dataStore.createDiocese(testDiocese);
      expect(created).toEqual(testDiocese);
    });

    it('should get a diocese by ID', () => {
      dataStore.createDiocese(testDiocese);
      const retrieved = dataStore.getDioceseById('test-1');
      expect(retrieved).toEqual(testDiocese);
    });

    it('should get all dioceses', () => {
      dataStore.createDiocese(testDiocese);
      dataStore.createDiocese({ ...testDiocese, id: 'test-2', name: 'Diocese 2' });
      const all = dataStore.getAllDioceses();
      expect(all).toHaveLength(2);
    });

    it('should get dioceses by state', () => {
      dataStore.createDiocese(testDiocese);
      dataStore.createDiocese({ ...testDiocese, id: 'test-2', state: 'CA' });
      const texasDioceses = dataStore.getDiocesesByState('TX');
      expect(texasDioceses).toHaveLength(1);
      expect(texasDioceses[0].state).toBe('TX');
    });

    it('should get archdioceses', () => {
      dataStore.createDiocese(testDiocese);
      dataStore.createDiocese({ ...testDiocese, id: 'test-2', type: 'archdiocese', name: 'Archdiocese' });
      const archdioceses = dataStore.getArchdioceses();
      expect(archdioceses).toHaveLength(1);
      expect(archdioceses[0].type).toBe('archdiocese');
    });

    it('should update a diocese', () => {
      dataStore.createDiocese(testDiocese);
      const updated = dataStore.updateDiocese('test-1', { bishop: 'Bishop Test' });
      expect(updated?.bishop).toBe('Bishop Test');
    });

    it('should delete a diocese', () => {
      dataStore.createDiocese(testDiocese);
      const deleted = dataStore.deleteDiocese('test-1');
      expect(deleted).toBe(true);
      expect(dataStore.getDioceseById('test-1')).toBeUndefined();
    });
  });

  describe('Deanery Operations', () => {
    const testDeanery: Deanery = {
      id: 'deanery-1',
      name: 'North Deanery',
      dioceseId: 'diocese-1',
      description: 'Northern parishes',
    };

    it('should create a deanery', () => {
      const created = dataStore.createDeanery(testDeanery);
      expect(created).toEqual(testDeanery);
    });

    it('should get deaneries by diocese', () => {
      dataStore.createDeanery(testDeanery);
      dataStore.createDeanery({ ...testDeanery, id: 'deanery-2', dioceseId: 'diocese-2' });
      const diocese1Deaneries = dataStore.getDeaneriesByDiocese('diocese-1');
      expect(diocese1Deaneries).toHaveLength(1);
    });
  });

  describe('Parish Operations', () => {
    const testParish: Parish = {
      id: 'parish-1',
      name: 'St. Test Parish',
      dioceseId: 'diocese-1',
      address: {
        street: '123 Test St',
        city: 'Test City',
        state: 'TX',
        zipCode: '12345',
        country: 'USA',
      },
    };

    it('should create a parish', () => {
      const created = dataStore.createParish(testParish);
      expect(created).toEqual(testParish);
    });

    it('should get parishes by diocese', () => {
      dataStore.createParish(testParish);
      dataStore.createParish({ ...testParish, id: 'parish-2', dioceseId: 'diocese-2' });
      const diocese1Parishes = dataStore.getParishesByDiocese('diocese-1');
      expect(diocese1Parishes).toHaveLength(1);
    });
  });

  describe('School Operations', () => {
    const testSchool: School = {
      id: 'school-1',
      name: 'St. Test Catholic School',
      dioceseId: 'diocese-1',
      type: 'elementary',
      address: {
        street: '456 School Rd',
        city: 'Test City',
        state: 'TX',
        zipCode: '12345',
        country: 'USA',
      },
    };

    it('should create a school', () => {
      const created = dataStore.createSchool(testSchool);
      expect(created).toEqual(testSchool);
    });

    it('should get schools by diocese', () => {
      dataStore.createSchool(testSchool);
      dataStore.createSchool({ ...testSchool, id: 'school-2', dioceseId: 'diocese-2' });
      const diocese1Schools = dataStore.getSchoolsByDiocese('diocese-1');
      expect(diocese1Schools).toHaveLength(1);
    });

    it('should get schools by parish', () => {
      dataStore.createSchool({ ...testSchool, parishId: 'parish-1' });
      dataStore.createSchool({ ...testSchool, id: 'school-2', parishId: 'parish-2' });
      const parish1Schools = dataStore.getSchoolsByParish('parish-1');
      expect(parish1Schools).toHaveLength(1);
    });
  });

  describe('Organization Operations', () => {
    const testOrganization: Organization = {
      id: 'org-1',
      name: 'Catholic Charities of Test City',
      dioceseId: 'diocese-1',
      type: 'catholic_charities',
      scope: 'diocesan',
    };

    it('should create an organization', () => {
      const created = dataStore.createOrganization(testOrganization);
      expect(created).toEqual(testOrganization);
    });

    it('should get organizations by diocese', () => {
      dataStore.createOrganization(testOrganization);
      dataStore.createOrganization({ ...testOrganization, id: 'org-2', dioceseId: 'diocese-2' });
      const diocese1Orgs = dataStore.getOrganizationsByDiocese('diocese-1');
      expect(diocese1Orgs).toHaveLength(1);
    });

    it('should get organizations by type', () => {
      dataStore.createOrganization(testOrganization);
      dataStore.createOrganization({ ...testOrganization, id: 'org-2', type: 'hospital' });
      const charities = dataStore.getOrganizationsByType('catholic_charities');
      expect(charities).toHaveLength(1);
    });
  });

  describe('Apostolate Operations', () => {
    const testApostolate: Apostolate = {
      id: 'apostolate-1',
      name: 'Youth Ministry',
      dioceseId: 'diocese-1',
      type: 'youth_ministry',
    };

    it('should create an apostolate', () => {
      const created = dataStore.createApostolate(testApostolate);
      expect(created).toEqual(testApostolate);
    });

    it('should get apostolates by diocese', () => {
      dataStore.createApostolate(testApostolate);
      dataStore.createApostolate({ ...testApostolate, id: 'apostolate-2', dioceseId: 'diocese-2' });
      const diocese1Apostolates = dataStore.getApostolatesByDiocese('diocese-1');
      expect(diocese1Apostolates).toHaveLength(1);
    });

    it('should get apostolates by type', () => {
      dataStore.createApostolate(testApostolate);
      dataStore.createApostolate({ ...testApostolate, id: 'apostolate-2', type: 'pro_life' });
      const youthMinistry = dataStore.getApostolatesByType('youth_ministry');
      expect(youthMinistry).toHaveLength(1);
    });
  });

  describe('Diocesan Office Operations', () => {
    const testOffice: DiocesanOffice = {
      id: 'office-1',
      name: 'Office of Vocations',
      dioceseId: 'diocese-1',
      type: 'vocations',
    };

    it('should create an office', () => {
      const created = dataStore.createOffice(testOffice);
      expect(created).toEqual(testOffice);
    });

    it('should get offices by diocese', () => {
      dataStore.createOffice(testOffice);
      dataStore.createOffice({ ...testOffice, id: 'office-2', dioceseId: 'diocese-2' });
      const diocese1Offices = dataStore.getOfficesByDiocese('diocese-1');
      expect(diocese1Offices).toHaveLength(1);
    });

    it('should get offices by type', () => {
      dataStore.createOffice(testOffice);
      dataStore.createOffice({ ...testOffice, id: 'office-2', type: 'chancery' });
      const vocationsOffices = dataStore.getOfficesByType('vocations');
      expect(vocationsOffices).toHaveLength(1);
    });
  });

  describe('Contact Operations', () => {
    const testContact: Contact = {
      id: 'contact-1',
      firstName: 'John',
      lastName: 'Doe',
      role: 'bishop',
      dioceseId: 'diocese-1',
    };

    it('should create a contact', () => {
      const created = dataStore.createContact(testContact);
      expect(created).toEqual(testContact);
    });

    it('should get contacts by diocese', () => {
      dataStore.createContact(testContact);
      dataStore.createContact({ ...testContact, id: 'contact-2', dioceseId: 'diocese-2' });
      const diocese1Contacts = dataStore.getContactsByDiocese('diocese-1');
      expect(diocese1Contacts).toHaveLength(1);
    });

    it('should get contacts by parish', () => {
      dataStore.createContact({ ...testContact, parishId: 'parish-1' });
      dataStore.createContact({ ...testContact, id: 'contact-2', parishId: 'parish-2' });
      const parish1Contacts = dataStore.getContactsByParish('parish-1');
      expect(parish1Contacts).toHaveLength(1);
    });

    it('should get contacts by school', () => {
      dataStore.createContact({ ...testContact, schoolId: 'school-1', role: 'principal' });
      dataStore.createContact({ ...testContact, id: 'contact-2', schoolId: 'school-2', role: 'principal' });
      const school1Contacts = dataStore.getContactsBySchool('school-1');
      expect(school1Contacts).toHaveLength(1);
    });

    it('should get contacts by organization', () => {
      dataStore.createContact({ ...testContact, organizationId: 'org-1', role: 'executive_director' });
      dataStore.createContact({ ...testContact, id: 'contact-2', organizationId: 'org-2', role: 'executive_director' });
      const org1Contacts = dataStore.getContactsByOrganization('org-1');
      expect(org1Contacts).toHaveLength(1);
    });

    it('should get contacts by apostolate', () => {
      dataStore.createContact({ ...testContact, apostolateId: 'apostolate-1', role: 'director' });
      dataStore.createContact({ ...testContact, id: 'contact-2', apostolateId: 'apostolate-2', role: 'director' });
      const apostolate1Contacts = dataStore.getContactsByApostolate('apostolate-1');
      expect(apostolate1Contacts).toHaveLength(1);
    });

    it('should get contacts by office', () => {
      dataStore.createContact({ ...testContact, officeId: 'office-1', role: 'director' });
      dataStore.createContact({ ...testContact, id: 'contact-2', officeId: 'office-2', role: 'director' });
      const office1Contacts = dataStore.getContactsByOffice('office-1');
      expect(office1Contacts).toHaveLength(1);
    });

    it('should get external contacts', () => {
      dataStore.createContact({ ...testContact, isExternalContact: true, homeDioceseId: 'external-diocese-1' });
      dataStore.createContact({ ...testContact, id: 'contact-2', isExternalContact: false });
      const externalContacts = dataStore.getExternalContacts();
      expect(externalContacts).toHaveLength(1);
    });

    it('should get contacts by home diocese', () => {
      dataStore.createContact({ ...testContact, homeDioceseId: 'home-diocese-1' });
      dataStore.createContact({ ...testContact, id: 'contact-2', homeDioceseId: 'home-diocese-2' });
      const homeDiocese1Contacts = dataStore.getContactsByHomeDiocese('home-diocese-1');
      expect(homeDiocese1Contacts).toHaveLength(1);
    });

    it('should get clergy contacts', () => {
      dataStore.createContact({ ...testContact, isClergy: true, clergyType: 'diocesan_priest' });
      dataStore.createContact({ ...testContact, id: 'contact-2', isClergy: false, role: 'director' });
      const clergyContacts = dataStore.getClergyContacts();
      expect(clergyContacts).toHaveLength(1);
    });
  });

  describe('Contact Position Operations', () => {
    const testPosition: ContactPosition = {
      id: 'position-1',
      contactId: 'contact-1',
      role: 'usccb_committee_chair',
      title: 'Chair, Committee on Divine Worship',
      organizationId: 'usccb-1',
    };

    it('should create a contact position', () => {
      const created = dataStore.createContactPosition(testPosition);
      expect(created).toEqual(testPosition);
    });

    it('should get positions by contact', () => {
      dataStore.createContactPosition(testPosition);
      dataStore.createContactPosition({ ...testPosition, id: 'position-2', contactId: 'contact-2' });
      const contact1Positions = dataStore.getPositionsByContact('contact-1');
      expect(contact1Positions).toHaveLength(1);
    });

    it('should get positions by organization', () => {
      dataStore.createContactPosition(testPosition);
      dataStore.createContactPosition({ ...testPosition, id: 'position-2', organizationId: 'org-2' });
      const usccbPositions = dataStore.getPositionsByOrganization('usccb-1');
      expect(usccbPositions).toHaveLength(1);
    });

    it('should get positions by diocese', () => {
      dataStore.createContactPosition({ ...testPosition, dioceseId: 'diocese-1' });
      dataStore.createContactPosition({ ...testPosition, id: 'position-2', dioceseId: 'diocese-2' });
      const diocese1Positions = dataStore.getPositionsByDiocese('diocese-1');
      expect(diocese1Positions).toHaveLength(1);
    });
  });

  describe('Organization Scope Operations', () => {
    const testOrg: Organization = {
      id: 'org-1',
      name: 'USCCB',
      type: 'usccb',
      scope: 'national',
    };

    it('should get national organizations', () => {
      dataStore.createOrganization(testOrg);
      dataStore.createOrganization({ ...testOrg, id: 'org-2', name: 'Vatican', type: 'roman_curia', scope: 'international' });
      const nationalOrgs = dataStore.getNationalOrganizations();
      expect(nationalOrgs).toHaveLength(1);
      expect(nationalOrgs[0].type).toBe('usccb');
    });

    it('should get international organizations', () => {
      dataStore.createOrganization(testOrg);
      dataStore.createOrganization({ ...testOrg, id: 'org-2', name: 'Roman Curia', type: 'roman_curia', scope: 'international' });
      const internationalOrgs = dataStore.getInternationalOrganizations();
      expect(internationalOrgs).toHaveLength(1);
      expect(internationalOrgs[0].scope).toBe('international');
    });
  });

  describe('External Diocese Operations', () => {
    const testDiocese: Diocese = {
      id: 'diocese-1',
      name: 'Diocese of Test City',
      type: 'diocese',
      province: 'Test Province',
      state: 'TX',
      city: 'Test City',
      established: new Date('1900-01-01'),
      isExternal: false,
    };

    it('should get local dioceses', () => {
      dataStore.createDiocese(testDiocese);
      dataStore.createDiocese({ ...testDiocese, id: 'diocese-2', name: 'External Diocese', isExternal: true });
      const localDioceses = dataStore.getLocalDioceses();
      expect(localDioceses).toHaveLength(1);
      expect(localDioceses[0].isExternal).toBeFalsy();
    });

    it('should get external dioceses', () => {
      dataStore.createDiocese(testDiocese);
      dataStore.createDiocese({ ...testDiocese, id: 'diocese-2', name: 'External Diocese', isExternal: true });
      const externalDioceses = dataStore.getExternalDioceses();
      expect(externalDioceses).toHaveLength(1);
      expect(externalDioceses[0].isExternal).toBe(true);
    });
  });
});

describe('Utility Functions', () => {
  it('should generate unique IDs', () => {
    const id1 = generateId();
    const id2 = generateId();
    expect(id1).not.toBe(id2);
    expect(typeof id1).toBe('string');
  });
});
