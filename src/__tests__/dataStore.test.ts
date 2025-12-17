import { dataStore } from '../services';
import { Diocese, Parish, Contact } from '../models';
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
